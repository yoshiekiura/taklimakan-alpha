import functools
import operator
from configparser import ConfigParser
from datetime import datetime, timedelta

from utils.currency import Currency, CurrencyBase
from utils.db_connection import DbConnection
from utils.formula import Formula
from utils.index import IndexType
from utils.timer import timer

MAX_DATA_GAP = 8

config = ConfigParser()
config.read('config.ini')

with DbConnection.get_instance(config) as connector:
    # TODO move to utils
    def format_list(lst):
        return ",".join(["'%s'" % item for item in lst])


    @functools.lru_cache(maxsize=None)
    def get_btc_to_usd(date):
        return connector.get_single_value("SELECT value FROM %s where pair = '%s' and type_id = '%s' and dt = '%s'" %
                                          (connector.analytics_table, Currency.BTC.get_pair_str(),
                                           Formula.Price.value.number, date))


    def get_raw_prices(currency, dates):
        return connector.get_list(
            "SELECT base, quote, date as dt, close, quantity, exchange FROM %s where base = '%s' and quote in (%s) and date in (%s) and exchange != 'ALL'" %
            (connector.price_table, currency.name, format_list([CurrencyBase.USD.name, CurrencyBase.BTC.name]),
             format_list(dates)))

    def save_analytics_value(pair, date, formula, value, extrapolated=False):
        connector.execute(
            "INSERT INTO %s (dt, pair, type_id, value, is_extrapolated) VALUES ('%s', '%s', '%s', '%s', %s) ON DUPLICATE KEY UPDATE value='%s', is_extrapolated=%s" %
            (connector.analytics_table, date, pair, formula.value.number, value, extrapolated, value, extrapolated))


    @functools.lru_cache(maxsize=None)
    def get_pairs_prices(pairs, date):
        return connector.get_list(
            "SELECT pair, value, is_extrapolated FROM %s WHERE pair in (%s) and dt = '%s' and type_id = %s" %
            (connector.analytics_table, pairs, date, Formula.Price.value.number))


    @functools.lru_cache(maxsize=None)
    def get_pair_prices(pair, date_from, date_to):
        return connector.get_list(
            "SELECT dt, value, is_extrapolated FROM %s WHERE pair = '%s' AND dt >= '%s' AND dt < '%s' and type_id = %s ORDER BY dt DESC" %
            (connector.analytics_table, pair, date_from, date_to, Formula.Price.value.number))


    # date_to: exclusively
    @functools.lru_cache(maxsize=None)
    def get_all_prices(date_from, date_to):
        result = dict(
            (c, dict((d, None) for d in [date_from + timedelta(days=x) for x in range(0, (date_to - date_from).days)]))
            for c in Currency)
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
    def get_indexes(index, date_from, date_to):
        values = []
        if index == IndexType.INDEX001:
            values = connector.get_list(
                "SELECT dt, value FROM %s WHERE pair = '%s' AND type_id = %s AND dt >= '%s' AND dt < '%s'" %
                (connector.analytics_table, IndexType.INDEX001.name, Formula.Index.value.number, date_from, date_to))
        else:
            values = connector.get_list("SELECT dt, value FROM %s WHERE symbol = '%s' AND dt >= '%s' AND dt < '%s'" %
                                        (connector.index_table, index.name, date_from, date_to))

        result = dict((d, None) for d in [date_from + timedelta(days=x) for x in range(0, (date_to - date_from).days)])
        for v in values:
            result[v[0]] = v[1]
        return result

    # TODO deprecated
    def get_missing_analytics_dates(pair, formula):
        if config.getboolean("other", "calculate_all_dates"):
            start_date = formula.start_date(config.getint("other", "extra_data_days"))
        else:
            start_date = datetime.now().replace(hour=0, minute=0, second=0, microsecond=0) - timedelta(
                days=config.getint("other", "back_days"))

        # TODO здесь можно написать нормальный SQL-запрос, чтобы не тащить столько данных, а сразу посчитать нужные даты
        result = connector.get_list(
            "SELECT dt FROM %s where pair = '%s' and type_id = '%s' and DATE(dt) >= '%s' ORDER BY dt" %
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


    def create_dates_temp_table():
        connector.execute("""
        CREATE TEMPORARY TABLE IF NOT EXISTS dates (INDEX(dt))
          AS(
            SELECT CAST('2016-01-01' + INTERVAL a + b + c + d DAY AS DATETIME) AS dt
              FROM
                (SELECT 0 a UNION SELECT 1 a UNION SELECT 2 UNION SELECT 3
                 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7
                 UNION SELECT 8 UNION SELECT 9 ) d,
                (SELECT 0 b UNION SELECT 10 UNION SELECT 20
                 UNION SELECT 30 UNION SELECT 40 UNION SELECT 50
                 UNION SELECT 60 UNION SELECT 70 UNION SELECT 80 UNION SELECT 90) m,
                (SELECT 0 c UNION SELECT 100 UNION SELECT 200  UNION SELECT 300
                 UNION SELECT 400 UNION SELECT 500 UNION SELECT 600 UNION SELECT 700
                 UNION SELECT 800 UNION SELECT 900) y,
                (SELECT 0 d UNION SELECT 1000 UNION SELECT 2000  UNION SELECT 3000
                UNION SELECT 4000 UNION SELECT 5000 UNION SELECT 6000 UNION SELECT 7000
                UNION SELECT 8000 UNION SELECT 9000) yy
            
              WHERE '2016-01-01' + INTERVAL a + b + c + d DAY < NOW() - INTERVAL 1 DAY
              ORDER BY a + b + c + d DESC
        )
        """)

    def create_pairs_temp_table():
        query_parts = []
        for c in Currency:
            for base in CurrencyBase:
                query_parts.append("SELECT '" + c.get_pair_str(base) + "' pair")
        query_parts.append("SELECT '" + IndexType.INDEX001.name + "' pair")
        query = """
          CREATE TEMPORARY TABLE IF NOT EXISTS pairs (INDEX(pair))
          AS (
            SELECT pair FROM (""" + " UNION ".join(query_parts) + """) d
          )
        """
        connector.execute(query)


    def get_missing_dates(formula):
        if config.getboolean("other", "calculate_all_dates"):
            start_date = formula.start_date(config.getint("other", "extra_data_days"))
        else:
            start_date = datetime.now().replace(hour=0, minute=0, second=0, microsecond=0) - timedelta(
                days=config.getint("other", "back_days"))

        create_dates_temp_table()
        create_pairs_temp_table()

        query = """
        SELECT distinct p.pair, d.dt
        FROM pairs p, dates d
        where not exists(select 1 from {0} where pair = p.pair and type_id = '{2}' and dt=d.dt)
        and d.dt >= '{1}'
        """
        raw_res = connector.get_list(query.format(connector.analytics_table, start_date.strftime("%Y-%m-%d"), formula.value.number))
        result = {}
        for r in raw_res:
            if r[0] not in result:
                result[r[0]] = []
            result[r[0]].append(r[1])
        return result


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

        grouped_data = {}
        for item in raw_data:
            if item['exchange'] not in grouped_data:
                grouped_data[item['exchange']] = {}
            grouped_data[item['exchange']][item['currency']] = item

        volumes = []
        prices_data = []
        for exchange in grouped_data:
            if base_currency.name in grouped_data[exchange]:
                item = grouped_data[exchange][base_currency.name]
                volumes.append(item['volume'])
                prices_data.append(dict(item))
            else:
                item = grouped_data[exchange][
                    CurrencyBase.USD.name if base_currency == CurrencyBase.BTC else CurrencyBase.BTC.name]
                btc_to_usd = get_btc_to_usd(date)
                if btc_to_usd is not None:
                    new_item = dict(item)
                    new_item['price'] = item['price'] / btc_to_usd \
                        if base_currency == CurrencyBase.BTC else item['price'] * btc_to_usd
                    prices_data.append(new_item)

        if len(prices_data) > 10:
            # For each day select top 10 exchanges
            prices_data = sorted(prices_data, key=operator.itemgetter('volume'), reverse=True)[:10]

        total_cost = 0
        total_volume = 0
        for item in prices_data:
            total_cost += item['price'] * item['volume']
            total_volume += item['volume']
        weighted_average_price = total_cost / total_volume
        prices_data = list(
            filter(lambda x: abs(x['price'] - weighted_average_price) / weighted_average_price <= 0.15, prices_data))

        result_volume = sum(sorted(volumes[:max(len(volumes),10)])) if len(volumes) > 0 else None
        result_price = float(sum(map(lambda x: x['price'], prices_data))) / len(prices_data) if len(prices_data) > 0 else None

        return result_price, result_volume


    def extrapolate_price(pair_str, missing_date):
        print("Extrapolate price", pair_str, missing_date)
        price = None
        date = missing_date
        while price is None and date > Formula.Price.value.date:
            date -= timedelta(days=1)
            data = get_pair_prices(pair_str, date, date + timedelta(days=1))
            if len(data) > 0:
                price = data[0][1]
        return price


    @timer
    def process_price_and_volume():
        missing_dates_all = get_missing_dates(Formula.Price)
        for currency in Currency:
            print("Process price and volume for", currency.name)
            usd_base = currency.get_pair_str(CurrencyBase.USD)
            btc_base = currency.get_pair_str(CurrencyBase.BTC)
            missing = {
                CurrencyBase.USD: missing_dates_all[usd_base] if usd_base in missing_dates_all else [],
                CurrencyBase.BTC: missing_dates_all[btc_base] if btc_base in missing_dates_all else []
            }

            if len(missing[CurrencyBase.BTC]) == 0 and len(missing[CurrencyBase.USD]) == 0:
                continue

            raw_data = get_raw_prices(currency, list(set(missing[CurrencyBase.BTC] + missing[CurrencyBase.USD])))
            data_by_dates = {}
            for row in raw_data:
                if not row[2] in data_by_dates:
                    data_by_dates[row[2]] = []
                data_by_dates[row[2]].append(
                    {'currency': row[1], 'price': row[3], 'volume': row[4], 'exchange': row[5]})

            for base in missing.keys():
                for date in sorted(missing[base]):
                    price_and_volume = get_price_and_volume(date, data_by_dates[date],
                                                            base) if date in data_by_dates else None
                    if price_and_volume is None or price_and_volume[0] is None:
                        price = extrapolate_price(currency.get_pair_str(base), date)
                        if price is None:
                            print("Failed extrapolate price", currency.get_pair_str(base), date)
                        else:
                            save_analytics_value(currency.get_pair_str(base), date, Formula.Price, price, True)
                        continue
                    else:
                        save_analytics_value(currency.get_pair_str(base), date, Formula.Price, price_and_volume[0])

                    if price_and_volume is not None and price_and_volume[1] is not None:
                        save_analytics_value(currency.get_pair_str(base), date, Formula.Volume, price_and_volume[1])

    @timer
    def process_index():
        missing_dates = get_missing_dates(Formula.Index)
        print("Process index")
        if IndexType.INDEX001.name in missing_dates:
            for date in missing_dates[IndexType.INDEX001.name]:
                prices = get_pairs_prices(format_list(list(map(lambda x: x.get_pair_str(CurrencyBase.USD), Currency.base_index()))), date)

                index_price = 0
                weight_count = 0
                for price in prices:
                    currency = Currency.get_by_pair_str(price[0])
                    if currency is not None:
                        # check if price was extrapolated
                        if price[2]:
                            skip = True
                            # search for earlier real prices
                            prev_prices = get_pair_prices(price[0], min(missing_dates) - timedelta(days=8),
                                                          max(missing_dates))
                            for p in prev_prices:
                                if not p[2] and (date - p[0]).days < MAX_DATA_GAP:
                                    skip = False
                                    break
                            if skip:
                                break

                        index_price += price[1] * currency.value.weight
                        weight_count += currency.value.weight
                if weight_count != 0:
                    index_price = index_price / weight_count
                    save_analytics_value(IndexType.INDEX001.name, date, Formula.Index, index_price)

    @timer
    def process_formula(f):
        missing_dates_all = get_missing_dates(f)
        for currency in Currency:
            if currency.get_pair_str() not in missing_dates_all:
                continue
            dates = missing_dates_all[currency.get_pair_str()]
            print(currency.name + " " + f.name + " " + str(dates))
            for date in sorted(dates):
                date_start = date - timedelta(f.value.length)
                date_end = date + timedelta(1)

                data = get_all_prices(date_start, date_end)
                index = None
                if f.value.index is not None:
                    index = get_indexes(f.value.index, date_start, date_end)

                try:
                    value = f.apply(data[currency], index)
                    if value is not None:
                        save_analytics_value(currency.get_pair_str(), date, f, value)
                except Exception as e:
                    print("Error", e)

    @timer
    def process_all(formulas):
        if Formula.Price in formulas:
            process_price_and_volume()
        if Formula.Index in formulas:
            process_index()
        for f in formulas:
            if f not in [Formula.Index, Formula.Price, Formula.Volume]:
                process_formula(f)


    formulas = []
    f_str = config['other']['formulas']
    if f_str is None or f_str == "ALL":
        formulas = list(Formula)
    else:
        for number in f_str.split(","):
            f = None
            try:
                f = Formula.get_by_number(int(number))
            except ValueError:
                print("Bad formula number", number)
            if f is not None:
                formulas.append(f)

    process_all(formulas)
