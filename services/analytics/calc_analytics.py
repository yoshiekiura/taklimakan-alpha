# sudo apt install python3-dev libpython3-dev
# sudo apt install python3-mysqldb
import MySQLdb
import matplotlib.pyplot as plt
import numpy as np
import matplotlib.pyplot as plt
from pprint import pprint
import os

######################################################################
# DB and tables
db_name = "pricedata"
price_table = "price"
analytics_table = "numerical_analytics"


######################################################################
# DB Connection

if ('DB_HOST' in os.environ.keys()) and ('DB_USER' in os.environ.keys()) and ('DB_PASSWORD' in os.environ.keys()):
	db_host = os.environ['DB_HOST']
	db_user = os.environ['DB_USER']
	db_pass = os.environ['DB_PASSWORD']
else:
	db_host = 'localhost'
	db_user = 'root'
	db_pass = '123'

print("db_host: " + db_host)
print("db_user: " + db_user)

db = MySQLdb.connect(host=db_host, user=db_user, passwd=db_pass, db=db_name)
print("Connected to DB")

######################################################################
# List of pairs to calculate analytics for

pairs = ["ETH-USD", "BTC-USD"]


######################################################################
# Operations with DB

def getPairPriceByDate(table_name, pair, date):
    cursor = db.cursor()
    query = ("SELECT * FROM " + table_name + " order by transaction_id limit " + str(start) + "," + str(count))
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
