import operator
from datetime import datetime, timedelta
from configparser import ConfigParser
from functools import reduce

import functools

from utils.db_connection import DbConnection
from utils.currency import Currency, CurrencyBase
from utils.formula import Formula

config = ConfigParser()
config.read('config.ini')

with DbConnection(True, config) as connector:

    # TODO move to utils
    def format_list(lst):
        return ",".join(["'%s'" % item for item in lst])

    @functools.lru_cache(maxsize=None)
    def get_btc_to_usd(date):
        return connector.get_single_value("SELECT value FROM %s where pair = '%s' and type_id = '%s' and dt = '%s'" %
                                          (connector.analytics_table, Currency.BTC.get_pair_str(), Formula.Price.value.number, date))

    def get_raw_prices(currency, dates):
        return connector.get_list("SELECT base, quote, date as dt, close, quantity, exchange FROM %s where base = '%s' and quote in (%s) and date in (%s) and exchange != 'ALL'" %
                                  (connector.price_table, currency.name, format_list([CurrencyBase.USD.name, CurrencyBase.BTC.name]), format_list(dates)))

    def save_analytics_value(pair, date, formula, value, extrapolated=False):
        connector.execute("INSERT INTO %s (dt, pair, type_id, value, is_extrapolated) VALUES ('%s', '%s', '%s', '%s', '%s') ON DUPLICATE KEY UPDATE value='%s', is_extrapolated='%s'" %
                          (connector.analytics_table, date, pair, formula.value.number, value, extrapolated, value, extrapolated))

    @functools.lru_cache(maxsize=None)
    def get_pairs_prices(pairs, date):
        return connector.get_list("SELECT pair, value FROM %s WHERE pair in (%s) and dt = '%s' and type_id = %s" %
                                  (connector.analytics_table, pairs, date, Formula.Price.value.number))

    def get_pair_prices(pair, date_from, date_to):
        return connector.get_list("SELECT dt, value FROM %s WHERE pair = '%s' AND dt >= '%s' AND dt < '%s' and type_id = %s ORDER BY dt DESC" %
                                  connector.analytics_table, pair, date_from, date_to, Formula.Price.value.number)

    # date_to: exclusively
    def get_all_prices(date_from, date_to):
        result = dict((c, dict((d, None) for d in [date_from + timedelta(days=x) for x in range(0, (date_to - date_from).days)])) for c in Currency)
        pairs = format_list(list(map(lambda x: x.get_pair_str(), Currency)))
        date = date_from
        while date < date_to:
            prices = get_pairs_prices(pairs, date)
            for price in prices:
                c = Currency.get_by_pair_str(price[0])
                result[c][date] = price[1]
            date += timedelta(days=1)
        return result

    @functools.lru_cache(maxsize=None)
    # date_to: exclusively
    def get_indexes(date_from, date_to):
        index_name = config['other']['index_name']
        values = connector.get_list("SELECT dt, value FROM %s WHERE pair = '%s' AND type_id = %s AND dt >= '%s' AND dt < '%s'" %
                                    (connector.analytics_table, index_name, Formula.Index.value.number, date_from, date_to))
        result = dict((d, None) for d in [date_from + timedelta(days=x) for x in range(0, (date_to - date_from).days)])
        for v in values:
            result[v[0]] = v[1]
        return result


    def get_missing_analytics_dates(pair, formula):
        if config.getboolean("other", "calculate_all_dates"):
            start_date = formula.start_date(config.getint("other", "extra_data_days"))
        else:
            start_date = datetime.now().replace(hour=0, minute=0, second=0, microsecond=0) - timedelta(days=config.getint("other", "back_days"))

        # TODO здесь можно написать нормальный SQL-запрос, чтобы не тащить столько данных, а сразу посчитать нужные даты
        result = connector.get_list("SELECT dt FROM %s where pair = '%s' and type_id = '%s' and DATE(dt) >= '%s' ORDER BY dt" %
                                    (connector.analytics_table, pair, formula.value.number, start_date))
        missing_dates = []
        curr_date = start_date
        for row in result:
            while curr_date != row[0]:
                missing_dates.append(curr_date)
                curr_date += timedelta(days=1)
            curr_date += timedelta(days=1)

        while curr_date <= datetime.now() - timedelta(days=1):
            missing_dates.append(curr_date)
            curr_date += timedelta(days=1)

        return missing_dates


    def get_price_and_volume(date, raw_data, base_currency):
        """
        Calculate price and volume for one date 
        
        date: datetime
            date for calculation
        raw_data: list of dicts with data for one date
            dict structure: {'currency': , 'price': , 'volume': , 'exchange': }     
        base_currency: CurrencyBase
        return: pair (average price, total volume) 
        """
        if raw_data is None or len(raw_data) == 0:
            return None

        data = []
        for item in raw_data:
            if item['currency'] == base_currency.name:
                data.append(dict(item))
            else:
                btc_to_usd = get_btc_to_usd(date)
                if btc_to_usd is None:
                    continue
                new_item = dict(item)
                new_item['price'] = item['price'] / btc_to_usd if base_currency == CurrencyBase.BTC else item['price'] * btc_to_usd
                data.append(new_item)

        if len(data) > 10:
            # For each day select top 10 exchanges
            data = sorted(data, key=operator.itemgetter('volume'), reverse=True)[:10]

        total_cost = 0
        total_volume = 0
        for item in data:
            total_cost += item['price'] * item['volume']
            total_volume += item['volume']
        weighted_average_price = total_cost / total_volume

        # Take only exchanges that are within 15% of weighted average price
        data = list(filter(lambda x: abs(x['price'] - weighted_average_price)/weighted_average_price <= 0.15, data))

        if len(data) == 0:
            return None

        price = 0
        volume = 0
        for item in data:
            price += item['price']
            volume += item['volume']

        return float(price) / len(data), float(volume)

    def extrapolate_price(pair_str, missing_date):
        max_data_gap = config['other']['max_data_gap']
        # find prices to max_data_gap days before (ordered by date desc)
        date_from = missing_date - timedelta(days=max_data_gap)
        data = get_pair_prices(pair_str, date_from, missing_date)
        if len(data) > 0:
            price = data[0][1]
            dates = [missing_date - timedelta(days=x) for x in range(0, (data[0][0] - missing_date).days)]
            for date in dates:
                save_analytics_value(pair_str, date, Formula.Price, price, True)
            return dates
        # TODO в этом случае цена никогда не экстраполируется - хорошо бы куда-то добавить этот признак, но я еще не придумала, куда
        return None


    def process_price_and_volume():
        for currency in Currency:
            print(currency.name)
            missing = {
                CurrencyBase.USD: get_missing_analytics_dates(currency.get_pair_str(CurrencyBase.USD), Formula.Price),
                CurrencyBase.BTC: get_missing_analytics_dates(currency.get_pair_str(CurrencyBase.BTC), Formula.Price)
            }

            if len(missing[CurrencyBase.BTC]) == 0 and len(missing[CurrencyBase.USD]) == 0:
                continue

            raw_data = get_raw_prices(currency, list(set(missing[CurrencyBase.BTC] + missing[CurrencyBase.USD])))
            data_by_dates = {}
            for row in raw_data:
                if not row[2] in data_by_dates:
                    data_by_dates[row[2]] = []
                data_by_dates[row[2]].append({'currency': row[1], 'price': row[3], 'volume': row[4], 'exchange': row[5]})

            for base in missing.keys():
                for date in sorted(missing[base]):
                    price_and_volume = get_price_and_volume(date, data_by_dates[date], base) if date in data_by_dates else None
                    if price_and_volume is None:
                        extrapolate_price(currency.get_pair_str(base), date)
                        continue

                    save_analytics_value(currency.get_pair_str(base), date, Formula.Price, price_and_volume[0])
                    save_analytics_value(currency.get_pair_str(base), date, Formula.Volume, price_and_volume[1])


    def process_index():
        index_name = config['other']['index_name']
        missing_dates = get_missing_analytics_dates(index_name, Formula.Index)
        for date in missing_dates:
            prices = format_list(get_pairs_prices(list(map(lambda x: x.get_pair_str(CurrencyBase.USD), Currency.base_index())), date))
            index_price = 0
            weight_count = 0
            for price in prices:
                currency = Currency.get_by_pair_str(price[0])
                if currency is not None:
                    index_price += price[1] * currency.value.weight
                    weight_count += currency.value.weight
            if weight_count != 0:
                index_price = index_price / weight_count
                save_analytics_value(index_name, date, Formula.Index, index_price)


    def process_formula(f):
        for currency in Currency:
            dates = get_missing_analytics_dates(currency.get_pair_str(), f)
            for date in sorted(dates):
                date_start = date - timedelta(f.value.length)
                date_end = date + timedelta(1)

                data = get_all_prices(date_start, date_end)
                index = get_indexes(date_start, date_end)

                fl = True
                for dt in data[currency]:
                    if data[currency][dt] is None:
                        fl = False
                        break
                if not fl:
                    continue


                value = f.apply(data[currency], index)

                print(value)

                #if value is not None:
                    #save_analytics_value(currency.get_pair_str(), date, f, value)



    #process_price_and_volume()
    #print(format_list(list(map(lambda x: x.get_pair_str(CurrencyBase.USD), Currency.base_index()))))
    #process_index()

    process_formula(Formula.Beta)
    process_formula(Formula.Volatility)

