from configparser import ConfigParser
from datetime import datetime, timedelta

import requests # pip install requests

from utils.currency import Currency, CurrencyBase
from utils.db_connection import DbConnection
from utils.timer import timer

BASE_URL = "https://min-api.cryptocompare.com/data/"
EXCHANGES_PATH = "all/exchanges"
COINS_PATH = "all/coinlist"
RATES_PATH = "histoday?fsym={0}&tsym={1}&limit={2}&toTs={3}&{4}"

DEFAULT_SOURCE = 'CRYPTOCOMPARE'
DEFAULT_PERIOD = 'DAY'


config = ConfigParser()
config.read('../config.ini')

with DbConnection.get_instance(config) as connector:
    def get_raw_data(url):
        r = requests.get(url)
        if r.status_code != 200:
            print("Status", r.status_code, "for url", url)
            return None
        if r is None or r.json() is None:
            print("Empty response for url", url)
            return None
        return r.json()


    def get_rates_url(coin, base, limit, date_to, exchange=""):
        return BASE_URL + RATES_PATH.format(coin, base, limit, round(date_to.timestamp()), exchange)

    def get_pairs_for_date(date):
        pairs = connector.get_list("SELECT DISTINCT base, quote FROM %s WHERE date = '%s' and source='CRYPTOCOMPARE' and period = 'DAY'" %
                                   (connector.price_table, date.replace(hour=0, minute=0, second=0, microsecond=0)))
        result = []
        for pair in pairs:
            result.append((pair[0], pair[1]))
        return result

    def get_max_saved_date(date_from):
        return connector.get_single_value("SELECT max(date) FROM %s WHERE date >= '%s' and source='CRYPTOCOMPARE' and period = 'DAY' LIMIT 1" %
                                          (connector.price_table, date_from.replace(hour=0, minute=0, second=0, microsecond=0)))

    def get_triplets():
        result = []
        all_coins = [c.name for c in Currency]
        all_bases = [b.name for b in CurrencyBase]
        exchanges_data = get_raw_data(BASE_URL + EXCHANGES_PATH)
        for ex in exchanges_data:
            for c in exchanges_data[ex]:
                if c not in all_coins:
                    continue
                for b in exchanges_data[ex][c]:
                    if b not in all_bases:
                        continue
                    result.append((ex, c, b))
        return result

    def get_insert_tuple(ex, coin, base, raw_data_item):
        # exchange, source, base, quote, period, date, price, open, high, low, close, quantity, volume
        return (
            ex,
            DEFAULT_SOURCE,
            coin,
            base,
            DEFAULT_PERIOD,
            datetime.fromtimestamp(raw_data_item['time']).replace(hour=0, minute=0, second=0),
            raw_data_item['volumeto'] / raw_data_item['volumefrom'] if raw_data_item['volumefrom'] > 0 else 0,
            raw_data_item['open'],
            raw_data_item['high'],
            raw_data_item['low'],
            raw_data_item['close'],
            raw_data_item['volumefrom'],
            raw_data_item['volumeto'],
        )

    def insert_rates(data):
        query = "INSERT IGNORE INTO " + connector.price_table + "(exchange, source, base, quote, period, date, " \
                                                                "price, open, high, low, close, quantity, volume) " \
                                                                "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, " \
                                                                "%s, %s) "
        connector.executemany(query, data)


    @timer
    def get_rates_simple(triplets, limit=3650, date_to=datetime.now()):
        insert_data = []
        for t in triplets:
            raw_data = get_raw_data(get_rates_url(t[1], t[2], limit, date_to, t[0]))
            for raw_data_item in raw_data['Data']:
                if raw_data_item['open'] != 0 and raw_data_item['close'] != 0 \
                        and raw_data_item['volumefrom'] != 0 and raw_data_item['volumeto'] != 0:
                    insert_data.append(get_insert_tuple(t[0], t[1], t[2], raw_data_item))
            print(t, "data loaded", "size:", len(raw_data['Data']))

        print("Data loaded: ", len(insert_data), "records")

        n = 100
        for i in range(0, len(insert_data), n):
            insert_rates(insert_data[i:i+n])
            print(i+n, "records inserted")

    def get_rates(triplets, date_from, date_to=datetime.now()-timedelta(days=1)):
        max_saved_date = get_max_saved_date(date_from)
        if max_saved_date is not None:
            if (max_saved_date - date_to).days == 0:
                print("All data exists: max saved date =", max_saved_date)
                return
            date_from = max_saved_date + timedelta(days=1)

        limit = (date_to - date_from).days
        if limit == 0:
            limit += 1
        if limit < 1:
            print("Bad dates for get_rates passed",  date_from, date_to)
            return None

        print("Load data for dates", date_from.strftime("%Y-%m-%d"), "-", date_to.strftime("%Y-%m-%d"))
        get_rates_simple(triplets, limit, date_to + timedelta(days=1))



    #get_rates(get_triplets(), datetime(2018,4,1), datetime(2018,4,7))
    get_rates_simple(get_triplets())

    #print(get_raw_data(get_rates_url('ADA', 'BTC', 1, datetime(2017,10,2), 'Abucoins'))['Data'])
