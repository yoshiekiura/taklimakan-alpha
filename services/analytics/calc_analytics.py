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
    query = "SELECT * FROM %s where base = '%s' and quote = '%s' and DATE(date) = '%s' ORDER BY volume desc LIMIT %d;" % (price_table, pair_base, pair_quote, date, topExchangeCount)
    cursor.execute(query)
    retval = cursor.fetchall()
    cursor.close()
    return retval


def getPairPricesByDateRange(pair_base, pair_quote, dateList):
    cursor = db.cursor()
    dateList = ["'" + date + "'" for date in dateList]
    dateListStr = ','.join(dateList)
    limit = topExchangeCount * len(dateList)

    #select DATE(date) as dt, AVG(close) from rates where base = 'MNX' and quote = 'USD' and exchange in (select distinct(exchange) from rates order by volume ) group by date;

    query = "SELECT base, quote, DATE(date) as dt, close, volume FROM %s where base = '%s' and quote = '%s' and DATE(date) in (%s) ORDER BY volume desc LIMIT %d;" % (price_table, pair_base, pair_quote, dateListStr, limit)
    print(query)
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
    if calculateAllDates:
        if type_id in ["1", "2", "11"]:
            startDate = datetime.strptime(pricesStartDate, '%Y-%m-%d')
        else:
            startDate = datetime.strptime(pricesStartDate, '%Y-%m-%d') + timedelta(days=maxWindow + extraDataDays)
    else:
        startDate = datetime.now() - timedelta(days=backDays)

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
    # Get prices and volumes from top 10 exchanges
    rawPrices = getPairPricesByDate(pair[0], pair[1], date)
    totalCost = 0
    totalVol = 0
    for rp in rawPrices:
        price = rp[10]
        volume = rp[12]
        # average price is unweighted
        totalCost += price
        totalVol += volume
    if totalVol != 0:
        averagePrice = totalCost / len(rawPrices)
        saveAnalyticsValue(pairToStr(pair), date, "1", averagePrice)
        saveAnalyticsValue(pairToStr(pair), date, "2", totalVol)
        return True
    else:
        print("Total volume is zero for pair %s, date %s" % (pairToStr(pair), date))
    return False

def calculatePriceAndVolumeRange(pair, dateList):
    # Get prices and volumes from top 10 exchanges
    pprint(pair)
    rawPrices = getPairPricesByDateRange(pair[0], pair[1], dateList)
    pprint(rawPrices)
    totalCost = {}
    totalVol = {}
    priceCount = {}
    for rp in rawPrices:
        # base, quote, DATE(date) as dt, price, volume
        price = rp[3]
        volume = rp[4]
        date = rp[2]

        # Arrange data by date buckets
        if date in totalVol.keys():
            totalCost[date] += price
            totalVol[date] += volume
            priceCount[date] += 1
        else:
            totalCost[date] = price
            totalVol[date] = volume
            priceCount[date] = 1

    result = True
    for date in dateList:
        if date in totalVol.keys() and totalVol[date] != 0:
            # average price is unweighted
            averagePrice = totalCost[date] / priceCount[date]
            saveAnalyticsValue(pairToStr(pair), date, "1", averagePrice)
            saveAnalyticsValue(pairToStr(pair), date, "2", totalVol[date])
        else:
            print("Total volume is zero for pair %s, date %s" % (pairToStr(pair), date))
            result = False
    return result

def calculateUSDPrice(pair, date):
    pairToBaseStr = baseCurrency2 + '-' + baseCurrency  # BTC to USD
    pairStr = pair[0] + '-' + baseCurrency2             # X to BTC
    basePrices = getAnalyticsValueForDateRange(pairToBaseStr, "1", date, date)
    assetPrices = getAnalyticsValueForDateRange(pairStr, "1", date, date)

    if len(basePrices) == 1 and len(assetPrices) == 1:
        usdPrice = assetPrices[0] * basePrices[0]
        saveAnalyticsValue(pair[0] + '-' + baseCurrency, date, "1", usdPrice)
        #print("assetPrices[0] = %.20f" % (assetPrices[0]))
        #print("basePrices[0] = %.20f" % (basePrices[0]))
        #print("usdPrice = %.20f on %s" % (usdPrice, date))
    else:
        print("WARNING: No price for pair (%s) or (%s) on date %s" % (pairStr, pairToBaseStr, date))
        pprint(assetPrices)
        pprint(basePrices)

lastPrice = {}
def calculateIndexPrice(indexPortfolio, date):
    global lastPrice
    indexPrice = 0
    weightCount = 0

    for asset in indexPortfolio:
        pairStr = asset[0] + '-' + baseCurrency
        assetWeight = asset[1]
        assetPrices = getAnalyticsValueForDateRange(pairStr, "1", date, date)

        if len(assetPrices) != 0:
            indexPrice += assetPrices[0] * assetWeight
            weightCount += assetWeight
            lastPrice[asset[0]] = assetPrices[0]
        elif asset[0] in lastPrice.keys():
            print("WARNING: No base currency price for asset (%s) on date %s. Using extrapolation." % (asset[0], date))
            indexPrice += lastPrice[asset[0]] * assetWeight
            weightCount += assetWeight
        else:
            print("WARNING: No base currency price for asset (%s) on date %s." % (asset[0], date))

    indexPrice = indexPrice / weightCount
    saveAnalyticsValue(indexName, date, "11", indexPrice)

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
    # Exponentially Weighted Volatility
    elif formula == "7":
        startDate = stopDate - timedelta(days=volatilityLength+extraDataDays)
    # Exponentially Weighted Alpha
    elif formula == "8":
        startDate = stopDate - timedelta(days=2+extraDataDays)
    # Exponentially Weighted Beta
    elif formula == "9":
        startDate = stopDate - timedelta(days=betaLength+extraDataDays)
    # Exponentially Weighted Sharpe Ratio
    elif formula == "10":
        startDate = stopDate - timedelta(days=sharpeLength+extraDataDays)

    dayCount = (stopDate - startDate).days + 1

    ##################################################
    # Get data for formulas
    startDateStr = startDate.strftime('%Y-%m-%d')
    stopDateStr = stopDate.strftime('%Y-%m-%d')
    assetPrices = getAnalyticsValueForDateRange(pairToStr(pair), "1", startDateStr, stopDateStr)
    index = calculateIndexPrices(baseIndex, startDateStr, stopDateStr)

    ##################################################
    # Calculate and save formula for date

    if len(assetPrices) >= dayCount and len(index) >= dayCount:
        # Volatility
        if formula == "3":
            value = af.getVolatility(assetPrices)
        # Alpha
        elif formula == "4":
            value = af.getAlpha(assetPrices, index)
        # Beta
        elif formula == "5":
            value = af.getBeta(assetPrices, index)
        # Sharpe Ratio
        elif formula == "6":
            value = af.getSharpeRatio(assetPrices)
        # Exponentially Weighted Volatility
        elif formula == "7":
            value = af.getWeightedVolatility(assetPrices)
        # Exponentially Weighted Alpha
        elif formula == "8":
            value = af.getWeightedAlpha(assetPrices, index)
        # Exponentially Weighted Beta
        elif formula == "9":
            value = af.getWeightedBeta(assetPrices, index)
        # Exponentially Weighted Sharpe Ratio
        elif formula == "10":
            value = af.getWeightedSharpeRatio(assetPrices)

        saveAnalyticsValue(pairToStr(pair), date, formula, value)
    else:
        print("WARNING: No data for date " + date)
        print("Data lengths: %d, %d, (of %d)" % (len(assetPrices), len(index), dayCount))


#def calculateFormulaForIndex(index, formula, date):

timeStart = int(round(time.time()))
print(timeStart)

checkAnalyticsTable()

################################################################################
# Prepare data for index

# Calculate prices for pairs used in index
'''
for asset in baseIndex:
    pair1 = (asset[0], baseCurrency)
    pair2 = (asset[0], baseCurrency2)
    missingDates1 = getMissingAnalyticsDates(pairToStr(pair1), "1")
    for date in missingDates1:
        if not calculatePriceAndVolume(pair1, date):
            # Calculate USD prices for pairs used in index based on their BTC prices
            calculatePriceAndVolume(pair2, date)
            calculateUSDPrice(pair1, date)
'''

'''
for asset in baseIndex:
    pair1 = (asset[0], baseCurrency)
    pair2 = (asset[0], baseCurrency2)
    missingDates1 = getMissingAnalyticsDates(pairToStr(pair1), "1")
    if not calculatePriceAndVolumeRange(pair1, missingDates1):
        # Calculate USD prices for pairs used in index based on their BTC prices
        calculatePriceAndVolumeRange(pair2, missingDates1)
    for date in missingDates1:
        calculateUSDPrice(pair1, date)
'''

# Check data: Which index assets are still missing USD prices more than maxDataGap?
print("Index length = ", len(baseIndex))

for asset in baseIndex:
    pair = (asset[0], baseCurrency)
    missingDates = getMissingAnalyticsDates(pairToStr(pair), "1")
    missingInARowCount = 0
    missing = False
    for i in range(len(missingDates) - 1):
        prevDateObj = datetime.strptime(missingDates[i], '%Y-%m-%d')
        dateObj = datetime.strptime(missingDates[i+1], '%Y-%m-%d')
        cutOff = datetime.strptime("2018-04-02", '%Y-%m-%d')

        if (dateObj < cutOff):
            if (prevDateObj + timedelta(days=1)) == dateObj:
                missingInARowCount += 1
            else:
                missingInARowCount = 0
            if (missingInARowCount >= maxDataGap):
                missing = True
    if missing:
        #print("ERROR: Missing USD price after all calculations for %s (more than maxDataGap days in a row)" % (asset[0]))
        print(asset[0])
        #pprint(missingDates)


# Calculate average prices and total volumes for important pairs (not necessarily used in idex)
'''
for pair in pairs:
    missingDates = getMissingAnalyticsDates(pairToStr(pair), "1")
    for date in missingDates:
        calculatePriceAndVolume(pair, date)
'''

# Calculate index price
'''
missingDates = getMissingAnalyticsDates(indexName, "11")
for date in missingDates:
    calculateIndexPrice(baseIndex, date)
'''

################################################################################


# Calculate the rest of formulas
'''
for formula in formulas:
    for pair in pairs:
        if formula not in ["1", "2", "11"]:
            missingDates = getMissingAnalyticsDates(pairToStr(pair), formula)
            for date in missingDates:
                print("Pair: %s, Formula: %s, Date: %s" % (pair, formula, date))
                calculateFormulaForPair(pair, formula, date)
'''

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
timeStop = int(round(time.time()))
duration = timeStop - timeStart
print("Duration = %s s" % (str(duration)))
