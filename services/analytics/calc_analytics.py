# sudo apt install python3-dev libpython3-dev
# sudo apt install python3-mysqldb
import MySQLdb
import matplotlib.pyplot as plt
import numpy as np
import matplotlib.pyplot as plt
from pprint import pprint
import os
import time
from datetime import datetime, timezone

# sudo pip install sshtunnel
import sshtunnel
import sys

######################################################################
# Script configuration

# List of pairs to calculate analytics for
pairs = ["ETH-USD", "BTC-USD"]

# Prices start timestamp (unix epoch seconds) - first date prices are available
pricesStartDate = "2018-01-01"


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

######################################################################
# Operations with DB

def getPairPricesByDate(pair_base, pair_quote, date):
    cursor = db.cursor()
    query = ("SELECT * FROM " + price_table + " where base = '" + pair_base + "' and quote = '" + pair_quote + "' and DATE(date) = '" + date + "';")
    cursor.execute(query)
    retval = cursor.fetchall()
    cursor.close()
    return retval

def saveAnalyticsValue(pair, datetime, type_id, value):
    cursor = db.cursor()
    query = "INSERT INTO numerical_analytics (dt, pair, type_id, value) VALUES ('%s', '%s', '%s', '%s')" % (datetime, pair, type_id, value)
    cursor.execute(query)
    db.commit()
    cursor.close()

def getAnalyticsValue(pair, date, type_id):
    cursor = db.cursor()
    query = "SELECT * FROM numerical_analytics where pair = '%s' and DATE(dt) = '%s' and type_id = '%s'" % (pair, date, type_id)
    cursor.execute(query)
    retval = cursor.fetchall()
    cursor.close()
    return retval


######################################################################
# Math and logic

def cov(a, b):
    if len(a) != len(b):
        return
    a_mean = np.mean(a)
    b_mean = np.mean(b)
    sum = 0
    for i in range(0, len(a)):
        sum += ((a[i] - a_mean) * (b[i] - b_mean))
    return sum/(len(a)-1)

def getBeta(asset, index):
    subasset = asset[-betaLength:]
    subindex = index[-betaLength:]
    aiCov = cov(subasset, subindex)
    iVar = cov(subindex, subindex)
    return aiCov/iVar

######################################################################
# Analytics calculation

#b = getBeta(ethPrices, btcPrices)

#saveAnalyticsValue("BTC-USD", "2018-03-16 12:00:00", "6", 0.1)

#print(datetime.fromtimestamp(pricesStartDate, timezone.utc).strftime('%Y-%m-%d %H:%M:%S'))

#pprint(getAnalyticsValue("BTC-USD", "2018-03-16", "6"))


#datetime_object = datetime.strptime(pricesStartDate, '%Y-%m-%d')


#print(time.mktime(datetime_object.timetuple()))

prices = getPairPricesByDate("ETH", "USD", "2018-03-01")
pprint(prices)


######################################################################
# Close DB Connection and SSH (for local dev)

db.disconnect()
if not ('DB_HOST' in os.environ.keys()) and ('DB_USER' in os.environ.keys()) and ('DB_PASSWORD' in os.environ.keys()):
    server.stop()
