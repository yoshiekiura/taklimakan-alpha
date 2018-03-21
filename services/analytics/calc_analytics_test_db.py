# sudo apt install python3-dev libpython3-dev
# sudo apt install python3-mysqldb
import MySQLdb
import matplotlib.pyplot as plt
from pprint import pprint
import os
import time
from datetime import datetime, timezone, timedelta
import aformulas as af
from config import *

# sudo pip install sshtunnel
import sshtunnel
import sys

import random


def plotArray(y):
    x = [i for i in range(len(y))]
    plt.plot(x, y)
    plt.show()

######################################################################
# DB and tables
db_name = "crypto"
price_table = "rates"
analytics_table = "numerical_analytics"


######################################################################
# DB Connection
if ('DB_HOST' in os.environ.keys()) and ('DB_USER' in os.environ.keys()) and ('DB_PASSWORD' in os.environ.keys()):
    db_host = os.environ['DB_HOST']
    db_user = os.environ['DB_USER']
    db_pass = os.environ['DB_PASSWORD']
    db = MySQLdb.connect(host=db_host, user=db_user, passwd=db_pass, db=db_name)
    print("Connected to DB (server)")
else:
    # sudo pip install mysql-connector-python-rf
    import mysql.connector
    sys.path.insert(0, '../')
    from local_config import *

    sshtunnel.SSH_TIMEOUT = 5.0
    sshtunnel.TUNNEL_TIMEOUT = 5.0

    server = sshtunnel.SSHTunnelForwarder(
            (db_ssh_host, db_ssh_port),
            ssh_username=db_ssh_username,
            ssh_password=db_ssh_password,
            remote_bind_address=(db_remote_bind_address, db_remote_mysql_port),
            local_bind_address=(db_local_bind_address, db_local_mysql_port))

    server.start()

    db = mysql.connector.connect(
        user=db_user,
        password=db_pass,
        host=db_local_bind_address,
        database=db_name,
        port=db_local_mysql_port)

    print("Connected to DB (local)")


def getAnalyticsValueForDateRange(pair, type_id, start_date, stop_date):
    cursor = db.cursor()
    query = "SELECT value FROM numerical_analytics where pair = '%s' and type_id = '%s' and DATE(dt) >= '%s' and DATE(dt) <= '%s'" % (pair, type_id, start_date, stop_date)
    cursor.execute(query)
    results = cursor.fetchall()
    cursor.close()
    values = [r[0] for r in results]
    return values

def calculateIndexPrices(indexPortfolio, startDate, stopDate):
    startDateObj = datetime.strptime(startDate, '%Y-%m-%d')
    stopDateObj = datetime.strptime(stopDate, '%Y-%m-%d')
    dayCount = (stopDateObj - startDateObj).days + 1
    pricesSum = [0 for i in range(dayCount)]

    for asset in indexPortfolio:
        pairStr = asset[0] + '-' + baseCurrency
        assetWeight = asset[1]
        assetPrices = getAnalyticsValueForDateRange(pairStr, "1", startDate, stopDate)
        if len(assetPrices) == dayCount:
            pricesSum = [pricesSum[i] + assetPrices[i] * assetWeight for i in range(dayCount)]

    return pricesSum


def saveToFile(fileName, data, startDate):
    curDate = datetime.strptime(startDate, '%Y-%m-%d')

    file = open(fileName, "wt")
    file.write("date,value\n")

    for i in range(len(data)):
        dateStr = curDate.strftime('%Y-%m-%d')
        file.write("%s,%f\n" % (dateStr, data[i]))
        curDate = curDate + timedelta(days=1)

    file.close()

######################################################################
# Analytics calculation

price  = getAnalyticsValueForDateRange("ETH-USD", "1", "2018-01-01", "2018-03-15")
volume = getAnalyticsValueForDateRange("ETH-USD", "2", "2018-01-01", "2018-03-15")
volatility = getAnalyticsValueForDateRange("ETH-USD", "3", "2018-02-02", "2018-03-15")
alpha = getAnalyticsValueForDateRange("ETH-USD", "4", "2018-02-02", "2018-03-15")
beta = getAnalyticsValueForDateRange("ETH-USD", "5", "2018-02-02", "2018-03-15")
sharpe = getAnalyticsValueForDateRange("ETH-USD", "6", "2018-02-02", "2018-03-15")

saveToFile("price.csv", price, "2018-01-01")
saveToFile("volume.csv", volume, "2018-01-01")
saveToFile("volatility.csv", volatility, "2018-02-02")
saveToFile("alpha.csv", alpha, "2018-02-02")
saveToFile("beta.csv", beta, "2018-02-02")
saveToFile("sharpe.csv", sharpe, "2018-02-02")

# save indexes
index = calculateIndexPrices(baseIndex, "2018-01-01", "2018-03-15")
market = calculateIndexPrices(marketIndex, "2018-01-01", "2018-03-15")

saveToFile("index.csv", index, "2018-01-01")
saveToFile("market.csv", market, "2018-01-01")


plotArray(market)
