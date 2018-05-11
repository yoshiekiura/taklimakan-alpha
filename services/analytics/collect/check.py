from configparser import ConfigParser
from datetime import datetime, timedelta

from utils.db_connection import DbConnection

RATES_TABLE_1 = "rates"
RATES_TABLE_2 = "rates_check"


def get_data(raw_data):
    result = {}
    for r in raw_data:
        ex_pair = r[1] + "-" + r[2] + "-" + r[0]
        values = {"o": r[3], "h": r[4], "l": r[5], "c": r[6], "q": r[7], "v": r[8]}
        result[ex_pair] = values
    return result


config = ConfigParser()
config.read('../config.ini')

with open('diff.txt', 'w') as f:
    with DbConnection.get_instance(config) as connector:
        date_start = datetime.now().replace(hour=0, minute=0, second=0, microsecond=0)
        query = "SELECT exchange, base, quote, open, high, low, close, quantity, volume FROM %s WHERE date='%s'"

        for i in range(0, 300):
            dt = date_start - timedelta(days=i)
            print("Process", dt)
            lst1 = connector.get_list(query % (RATES_TABLE_1, dt))
            lst2 = connector.get_list(query % (RATES_TABLE_2, dt))
            if len(lst1) > 0 and len(lst2) > 0:
                data1 = get_data(lst1)
                data2 = get_data(lst2)
                matching_keys = set(data1.keys()) & set(data2.keys())
                for k in matching_keys:
                    if data1[k]["o"] != data2[k]["o"] or data1[k]["h"] != data2[k]["h"] or \
                                    data1[k]["l"] != data2[k]["l"] or data1[k]["c"] != data2[k]["c"] or \
                                    data1[k]["q"] != data2[k]["q"] or data1[k]["v"] != data2[k]["v"]:
                        print(dt, k, data1[k], data2[k], file=f)

                non_matching_keys1 = set(data1.keys()) - matching_keys
                non_matching_keys2 = set(data2.keys()) - matching_keys
                if len(non_matching_keys1):
                    print(dt, "keys in data1 not in data2", non_matching_keys1, file=f)
                if len(non_matching_keys2):
                    print(dt, "keys in data2 not in data1", non_matching_keys2, file=f)
