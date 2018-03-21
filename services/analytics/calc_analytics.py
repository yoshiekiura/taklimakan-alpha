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

def pairToStr(pair):
    return pair[0] + '-' + pair[1]

def getPairPricesByDate(pair_base, pair_quote, date):
    cursor = db.cursor()
    query = ("SELECT * FROM " + price_table + " where base = '" + pair_base + "' and quote = '" + pair_quote + "' and DATE(date) = '" + date + "';")
    cursor.execute(query)
    retval = cursor.fetchall()
    cursor.close()
    return retval

def saveAnalyticsValue(pair, datetime, type_id, value):
    cursor = db.cursor()
    query = "INSERT INTO numerical_analytics (dt, pair, type_id, value) VALUES ('%s', '%s', '%s', '%s') ON DUPLICATE KEY UPDATE value='%s'" % (datetime, pair, type_id, value, value)
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

def getAnalyticsValueForDateRange(pair, type_id, start_date, stop_date):
    cursor = db.cursor()
    query = "SELECT value FROM numerical_analytics where pair = '%s' and type_id = '%s' and DATE(dt) >= '%s' and DATE(dt) <= '%s'" % (pair, type_id, start_date, stop_date)
    cursor.execute(query)
    results = cursor.fetchall()
    cursor.close()
    values = [r[0] for r in results]
    return values

# Get list of dates that are missing in analytics starting at pricesStartDate, but not earlier
# than 1 year ago
def getMissingAnalyticsDates(pair, type_id):

    # Start date depends on type. Price/volume data can start right away,
    # but lagged analytics can only start after its window
    if type_id in ["1", "2"]:
        startDate = datetime.strptime(pricesStartDate, '%Y-%m-%d')
    else:
        startDate = datetime.strptime(pricesStartDate, '%Y-%m-%d') + timedelta(days=maxWindow + extraDataDays)

    yearAgo = datetime.now() - timedelta(days=365)
    if startDate < yearAgo:
        startDate = yearAgo
    startDateStr = startDate.strftime('%Y-%m-%d')

    # Get all dates that are present in DB as string array
    cursor = db.cursor()
    query = "SELECT dt FROM numerical_analytics where pair = '%s' and type_id = '%s' and DATE(dt) >= '%s'" % (pair, type_id, startDateStr)
    cursor.execute(query)
    datesObjList = cursor.fetchall()
    cursor.close()
    datesStr = [d[0].strftime('%Y-%m-%d') for d in datesObjList]

    # "Invert" present dates to get missing dates
    missingDatesStr = []
    curDate = startDate
    while curDate <= datetime.now():
        if curDate.strftime('%Y-%m-%d') not in datesStr:
            missingDatesStr.append(curDate.strftime('%Y-%m-%d'))
        curDate += timedelta(days=1)

    return missingDatesStr

def checkAnalyticsTable():
    cursor = db.cursor()
    query = "CREATE TABLE IF NOT EXISTS %s (dt DATETIME, pair VARCHAR(20), type_id INT(2), value FLOAT, PRIMARY KEY (dt, pair, type_id));" % (analytics_table)
    cursor.execute(query)
    cursor.close()


######################################################################
# Analytics calculation

def calculatePriceAndVolume(pair, date):
    # Get prices and volumes from all exchanges
    rawPrices = getPairPricesByDate(pair[0], pair[1], date)
    totalCost = 0
    totalVol = 0
    for rp in rawPrices:
        price = rp[10]
        volume = rp[12]
        totalCost += price * volume
        totalVol += volume
    if totalVol != 0:
        averagePrice = totalCost / totalVol
        saveAnalyticsValue(pairToStr(pair), date, "1", averagePrice)
        saveAnalyticsValue(pairToStr(pair), date, "2", totalVol)
    else:
        print("Total volume is zero for pair %s, date %s" % (pairToStr(pair), date))

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

# Averate price and volume are calculated in a separate method
# Calculate rest of formulas that are based on averate price and volume
def calculateFormulaForPair(pair, formula, date):

    ##################################################
    # generate date ranges (add extraDataDays)
    stopDate = datetime.strptime(date, '%Y-%m-%d')

    # Volatility
    if formula == "3":
        startDate = stopDate - timedelta(days=volatilityLength+extraDataDays)
    # Alpha
    elif formula == "4":
        startDate = stopDate - timedelta(days=2+extraDataDays)
    # Beta
    elif formula == "5":
        startDate = stopDate - timedelta(days=betaLength+extraDataDays)
    # Sharpe Ratio
    elif formula == "6":
        startDate = stopDate - timedelta(days=sharpeLength+extraDataDays)

    dayCount = (stopDate - startDate).days + 1

    ##################################################
    # Get data for formulas
    startDateStr = startDate.strftime('%Y-%m-%d')
    stopDateStr = stopDate.strftime('%Y-%m-%d')
    assetPrices = getAnalyticsValueForDateRange(pairToStr(pair), "1", startDateStr, stopDateStr)
    index = calculateIndexPrices(baseIndex, startDateStr, stopDateStr)
    market = calculateIndexPrices(marketIndex, startDateStr, stopDateStr)

    ##################################################
    # Calculate and save formula for date

    if len(assetPrices) >= dayCount and len(index) >= dayCount and len(market) >= dayCount:
        # Volatility
        if formula == "3":
            value = af.getVolatility(assetPrices)
        # Alpha
        elif formula == "4":
            value = af.getAlpha(assetPrices, index, market)
        # Beta
        elif formula == "5":
            value = af.getBeta(assetPrices, index)
        # Sharpe Ratio
        elif formula == "6":
            value = af.getSharpeRatio(assetPrices)

        saveAnalyticsValue(pairToStr(pair), date, formula, value)
    else:
        print("WARNING: No data for date " + date)
        print("Data lengths: %d, %d, %d, (of %d)" % (len(assetPrices), len(index), len(market), dayCount))


#def calculateFormulaForIndex(index, formula, date):



checkAnalyticsTable()

# Calculate weighted average prices and total volumes
# Iterate pairs
for pair in pairs:
    missingDates = getMissingAnalyticsDates(pairToStr(pair), "1")
    for date in missingDates:
        calculatePriceAndVolume(pair, date)

# Calculate the rest of formulas
for formula in formulas:
    for pair in pairs:
        if formula not in ["1", "2"]:
            missingDates = getMissingAnalyticsDates(pairToStr(pair), formula)
            for date in missingDates:
                print("Pair: %s, Formula: %s, Date: %s" % (pair, formula, date))
                calculateFormulaForPair(pair, formula, date)

#saveAnalyticsValue("BTC-USD", "2018-03-16 12:00:00", "6", 0.2)
#print(datetime.fromtimestamp(pricesStartDate, timezone.utc).strftime('%Y-%m-%d %H:%M:%S'))
#pprint(getAnalyticsValue("BTC-USD", "2018-03-16", "6"))
#datetime_object = datetime.strptime(pricesStartDate, '%Y-%m-%d')
#print(time.mktime(datetime_object.timetuple()))

######################################################################
# Close DB Connection and SSH (for local dev)

db.disconnect()
if not ('DB_HOST' in os.environ.keys()) and ('DB_USER' in os.environ.keys()) and ('DB_PASSWORD' in os.environ.keys()):
    server.stop()

print("done")
