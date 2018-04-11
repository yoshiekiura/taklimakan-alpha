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
import operator

# sudo pip install sshtunnel
import sshtunnel
import sys

######################################################################
# DB and tables
db_name = "crypto"
price_table = "rates"
analytics_table = "numerical_analytics"


######################################################################
# Working with YYYY-MM-DD string dates

def dateAddDays(date, days):
    dateObj = datetime.strptime(date, '%Y-%m-%d')
    dateObj = dateObj + timedelta(days=days)
    return dateObj.strftime('%Y-%m-%d')

def dateSubDays(date, days):
    dateObj = datetime.strptime(date, '%Y-%m-%d')
    dateObj = dateObj - timedelta(days=days)
    return dateObj.strftime('%Y-%m-%d')


######################################################################


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

def getPairPricesByDateRange(pair_base, pair_quote, dateList):
    cursor = db.cursor()
    dateList = ["'" + date + "'" for date in dateList]
    dateListStr = ','.join(dateList)
    query = "SELECT base, quote, DATE(date) as dt, close, quantity, exchange FROM %s where base = '%s' and quote = '%s' and DATE(date) in (%s) and exchange != 'ALL';" % (price_table, pair_base, pair_quote, dateListStr)
    cursor.execute(query)
    retval = cursor.fetchall()
    cursor.close()
    return retval

def getPairPricesByDateRange2(pair_base, pair_quoteList, dateList):
    cursor = db.cursor()
    dateList = ["'" + date + "'" for date in dateList]
    dateListStr = ','.join(dateList)
    pair_quoteList = ["'" + pair_quote + "'" for pair_quote in pair_quoteList]
    pair_quoteListStr = ','.join(pair_quoteList)
    query = "SELECT base, quote, DATE(date) as dt, close, quantity, exchange FROM %s where base = '%s' and quote in (%s) and DATE(date) in (%s) and exchange != 'ALL';" % (price_table, pair_base, pair_quoteListStr, dateListStr)
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

analyticsValueBuffer = {}
def getAnalyticsValueForDateRangeBuffered(pair, type_id, start_date, stop_date):
    global analyticsValueBuffer
    values = []

    currentDate = start_date
    dateAfterStop = dateAddDays(stop_date, 1)
    while (currentDate != dateAfterStop):
        if pair not in analyticsValueBuffer.keys():
            analyticsValueBuffer[pair] = {}

        if type_id not in analyticsValueBuffer[pair].keys():
            analyticsValueBuffer[pair][type_id] = {}

        if currentDate not in analyticsValueBuffer[pair][type_id].keys():
            v = getAnalyticsValueForDateRange(pair, type_id, currentDate, currentDate)
            if len(v) > 0:
                analyticsValueBuffer[pair][type_id][currentDate] = v[0]
            else:
                analyticsValueBuffer[pair][type_id][currentDate] = -1000000

        if analyticsValueBuffer[pair][type_id][currentDate] != -1000000:
            values.append(analyticsValueBuffer[pair][type_id][currentDate])

        currentDate = dateAddDays(currentDate, 1)

    return values

# Get list of dates that are missing in analytics starting at pricesStartDate
def getMissingAnalyticsDates(pair, type_id):

    # Start date depends on type. Price/volume data can start right away,
    # but lagged analytics can only start after its window
    if calculateAllDates:
        if type_id in ["1", "2", "11"]:
            startDate = datetime.strptime(startDateByType[type_id], '%Y-%m-%d')
        else:
            startDate = datetime.strptime(startDateByType[type_id], '%Y-%m-%d') + timedelta(days=extraDataDays)
    else:
        startDate = datetime.now() - timedelta(days=backDays)

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
    while curDate <= datetime.now() - timedelta(days=1):
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

def calculatePriceAndVolumeRange(pair, dateList):
    # Get all prices and volumes for given dates
    pprint(pair)
    if len(dateList) == 0:
        return []
    rawPrices = getPairPricesByDateRange(pair[0], pair[1], dateList)

    # structurize data into days
    prices = {}
    volumes = {}
    for rp in rawPrices:
        # base, quote, DATE(date) as dt, price, volume
        price = rp[3]
        volume = rp[4]
        date = rp[2]
        exchange = rp[5]

        # Arrange data by date buckets
        if date in volumes.keys():
            prices[date][exchange] = price
            volumes[date][exchange] = volume
        else:
            prices[date] = {}
            volumes[date] = {}
            prices[date][exchange] = price
            volumes[date][exchange] = volume

    # For each day select top 10 exchanges
    for date in volumes.keys():
        volumes[date] = dict(sorted(volumes[date].items(), key=operator.itemgetter(1), reverse=True)[:min(10, len(volumes[date]))])

        # Calculate weighted average to filter out bad exchanges
        totalCost = 0
        totalWeight = 0
        for exchange in volumes[date].keys():
            totalCost += prices[date][exchange] * volumes[date][exchange]
            totalWeight += volumes[date][exchange]
        weightedAveragePrice = totalCost / totalWeight

        # Take only exchanges that are within 15% of weighted average price
        prices[date] = { exchange: prices[date][exchange] for exchange in volumes[date].keys() if abs(prices[date][exchange] - weightedAveragePrice)/weightedAveragePrice <= 0.15 }

        # Aggregate average price for each day
        averagePrice = float(sum(prices[date].values())) / len(prices[date])
        totalVolume = float(sum(volumes[date].values()))
        saveAnalyticsValue(pairToStr(pair), date, "1", averagePrice)
        saveAnalyticsValue(pairToStr(pair), date, "2", totalVolume)

    # Check if we got all prices
    result = True
    for date in dateList:
        if date not in volumes.keys():
            #print("Total volume is zero for pair %s, date %s" % (pairToStr(pair), date))
            result = False
    return result

def calculatePriceAndVolumeRange2(pair, dateList):
    # Get all prices and volumes for given dates
    pprint(pair)
    if len(dateList) == 0:
        return []
    rawPrices = getPairPricesByDateRange2(pair[0], [baseCurrency, baseCurrency2], dateList)

    # structurize data into days
    pairToBaseStr = baseCurrency2 + '-' + baseCurrency  # BTC to USD

    prices = {}
    volumes = {}
    for rp in rawPrices:
        priceFound = False

        # base, quote, DATE(date) as dt, price, volume
        date = rp[2]
        # Price may be in main pair currency (pair[1]) or in second currency (USD or BTC, whatever is not main)
        if (rp[1] == pair[1]):
            price = rp[3]
            priceFound = True
        else:
            basePrices = getAnalyticsValueForDateRange(pairToBaseStr, "1", date, date)
            if len(basePrices) > 0:
                usdPerBtc = basePrices[0]
                if rp[1] == "BTC":
                    # We need price in USD/X, but rp[3] is price in BTC/X
                    price = rp[3] * usdPerBtc
                else:
                    # We need price in BTC/X, but rp[3] is price in USD/X
                    price = rp[3] / usdPerBtc
                priceFound = True
        volume = rp[4]
        exchange = rp[5]

        # Arrange data by date buckets
        if priceFound:
            if date in volumes.keys():
                prices[date][exchange] = price
                volumes[date][exchange] = volume
            else:
                prices[date] = {}
                volumes[date] = {}
                prices[date][exchange] = price
                volumes[date][exchange] = volume

    # For each day select top 10 exchanges
    for date in volumes.keys():
        volumes[date] = dict(sorted(volumes[date].items(), key=operator.itemgetter(1), reverse=True)[:min(10, len(volumes[date]))])

        # Calculate weighted average to filter out bad exchanges
        totalCost = 0
        totalWeight = 0
        for exchange in volumes[date].keys():
            totalCost += prices[date][exchange] * volumes[date][exchange]
            totalWeight += volumes[date][exchange]
        weightedAveragePrice = totalCost / totalWeight

        # Take only exchanges that are within 15% of weighted average price
        prices[date] = { exchange: prices[date][exchange] for exchange in volumes[date].keys() if abs(prices[date][exchange] - weightedAveragePrice)/weightedAveragePrice <= 0.15 }

        # Aggregate average price for each day
        if len(prices[date]) >= 1:
            averagePrice = float(sum(prices[date].values())) / len(prices[date])
            totalVolume = float(sum(volumes[date].values()))
            saveAnalyticsValue(pairToStr(pair), date, "1", averagePrice)
            saveAnalyticsValue(pairToStr(pair), date, "2", totalVolume)

    # Check if we got all prices
    result = True
    for date in dateList:
        if date not in volumes.keys():
            #print("Total volume is zero for pair %s, date %s" % (pairToStr(pair), date))
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

def getExtrapolatedAssetPrice(pairStr, date):
    for i in range(maxDataGap):
        dtStr = dateSubDays(date, i)
        assetPrices = getAnalyticsValueForDateRange(pairStr, "1", dtStr, dtStr)
        if len(assetPrices) != 0:
            if (i != 0):
                print("WARNING: No base currency price for asset (%s) on date %s. Using extrapolation." % (pairStr, date))
                if saveExtrapolatedPrices:
                    saveAnalyticsValue(pairStr, date, "1", assetPrices[0])
            return assetPrices
    print("ERROR: No base currency price for asset (%s) on date %s. No extrapolation available." % (pairStr, date))
    return []

def calculateIndexPrice(indexPortfolio, date):
    indexPrice = 0
    weightCount = 0

    for asset in indexPortfolio:
        pairStr = asset[0] + '-' + baseCurrency
        assetWeight = asset[1]
        assetPrices = getExtrapolatedAssetPrice(pairStr, date)

        if len(assetPrices) != 0:
            indexPrice += assetPrices[0] * assetWeight
            weightCount += assetWeight
        else:
            print("WARNING: No base currency price for asset (%s) on date %s." % (asset[0], date))

    if (weightCount != 0):
        indexPrice = indexPrice / weightCount
        saveAnalyticsValue(indexName, date, "11", indexPrice)
    else:
        indexPrice = 0
        print("ERROR: No data for index at all on %s" % (date))

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
        startDate = stopDate - timedelta(days=betaLength+extraDataDays)
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
        startDate = stopDate - timedelta(days=betaLength+extraDataDays)
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
    assetPrices = getAnalyticsValueForDateRangeBuffered(pairToStr(pair), "1", startDateStr, stopDateStr)
    index = getAnalyticsValueForDateRangeBuffered(indexName, "11", startDateStr, stopDateStr)

    ##################################################
    # Get required data lengths for formula
    assetDataRequired = dayCount
    indexDataRequired = 0

    # Alpha
    if formula == "4":
        indexDataRequired = dayCount
    # Beta
    elif formula == "5":
        indexDataRequired = dayCount
    # Exponentially Weighted Alpha
    elif formula == "8":
        indexDataRequired = dayCount
    # Exponentially Weighted Beta
    elif formula == "9":
        indexDataRequired = dayCount

    ##################################################
    # Calculate and save formula for date

    if len(assetPrices) >= assetDataRequired and len(index) >= indexDataRequired:
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

# output index json
'''
json = "["
pyarray = ""
for asset in baseIndex:
    pair = (asset[0], baseCurrency)
    pair2 = (asset[0], baseCurrency2)

    assetVolumesUsd = getAnalyticsValueForDateRange(pairToStr(pair), "2", dateSubDays("2018-04-05", 30), "2018-04-05")
    assetVolumesBtc = getAnalyticsValueForDateRange(pairToStr(pair2), "2", dateSubDays("2018-04-05", 30), "2018-04-05")

    json += "\""
    json += asset[0]
    pyarray += '('
    pyarray += "\""
    pyarray += asset[0]
    pyarray += "\""
    pyarray += ', '
    pyarray += "\""
    if len(assetVolumesUsd) != 0:
        json += "-USD"
        pyarray += "USD"
    else:
        json += "-BTC"
        pyarray += "BTC"
    json += "\", "
    pyarray += "\""
    pyarray += '),\n'

json += "]"
print(json)
print(pyarray)
'''

checkAnalyticsTable()

################################################################################
#

newIndexCandidates = [
    "BTC",
    "ETH",
    "XRP",
    "BCH",
    "LTC",
    "EOS",
    "ADA",
    "XLM",
    "NEO",
    "MIOTA",
    "XMR",
    "TRX",
    "DASH",
    "USDT",
    "XEM",
    "VEN",
    "ETC",
    "BNB",
    "XVG",
    "QTUM",
    "ONT",
    "OMG",
    "LSK",
    "ICX",
    "BTG",
    "ZEC",
    "NANO",
    "BTM",
    "STEEM",
    "WAN",
    "BCN",
    "PPT",
    "DGD",
    "SC",
    "STRAT",
    "BCD",
    "BTS",
    "WAVES",
    "DOGE",
    "DCR",
    "RHOC",
    "MKR",
    "AE",
    "SNT",
    "ZRX",
    "ZIL",
    "KMD",
    "REP",
    "ARDR",
    "IOST",
    "AION",
    "LRC",
    "WTC",
    "KCS",
    "ARK",
    "GNT",
    "HSR",
    "PIVX",
    "DGB",
    "CNX",
    "CENNZ",
    "ELF",
    "BAT",
    "MONA",
    "QASH",
    "VERI",
    "FCT",
    "DRGN",
    "ELA",
    "NAS",
    "GAS",
    "SUB",
    "ETHOS",
    "XIN",
    "GXS",
    "SYS",
    "RDD",
    "R",
    "FUN",
    "KNC",
    "ETN",
    "SALT",
    "SKY",
    "XZC",
    "GBYTE",
    "MAID",
    "NXT",
    "NCASH",
    "LINK",
    "STORM",
    "POWR",
    "BNT",
    "ENG",
    "PART",
    "REQ",
    "WAX",
    "NEBL",
    "POA",
    "DENT",
    "STORJ",
    "PAY",
    "DCN",
    "FSN",
    "CND",
    "ICN",
    "HPB",
    "NXS",
    "EMC",
    "ZEN",
    "GNX",
    "KIN",
    "VTC",
    "MAN",
    "ACT",
    "POLY",
    "MANA",
    "CVC",
    "BOS",
    "DROP",
    "NULS",
    "QSP",
    "GAME",
    "ENJ",
    "MTL",
    "SMART",
    "BLOCK",
    "GNO",
    "RLC",
    "AGI",
    "UBQ",
    "DTR",
    "SRN",
    "MNX",
    "MCO",
    "TNB",
    "CS",
    "RDN",
    "POE",
    "THETA",
    "BTX",
    "ANT",
    "GVT",
    "IGNIS",
    "XDN",
    "AUTO",
    "MITH",
    "ABT",
    "BLZ",
    "SAN"
]



today = datetime.now()
todayStr = today.strftime('%Y-%m-%d')
startDateStr = dateSubDays(todayStr, 28)

# Create date list: Start 28 days ago and finish yesterday
dateList = [dateAddDays(startDateStr, i) for i in range (28)]

pprint(dateList)

# Get pair raw prices
badAssets = []
for asset in newIndexCandidates:
    rawPrices = getPairPricesByDateRange2(asset, [baseCurrency, baseCurrency2], dateList)
    pprint(rawPrices)

    # See if there are gaps of more than maxDataGap in rawPrices
    availabilityDates = []

    for rp in rawPrices:
        priceFound = False

        # base, quote, DATE(date) as dt, price, volume
        date = rp[2]
        if date not in availabilityDates:
            availabilityDates.append(date)

    missingInARow = 0
    for i in range (28):
        dateToCheck = dateAddDays(startDateStr, i)
        print(dateToCheck)
        if dateToCheck not in availabilityDates:
            missingInARow += 1
            print("missing")
        else:
            missingInARow = 0
            print("present")
        if missingInARow >= maxDataGap:
            badAssets.append(asset)
    if asset not in badAssets:
        print("%s GOOD" % (asset))
    else:
        print("%s BAD" % (asset))

goodAssets = [asset for asset in newIndexCandidates if asset not in badAssets]

print("baseIndex = [")
for ga in goodAssets:
    print('    (\"%s\", 0.01),' % (ga))
print("]")

######################################################################
# Close DB Connection and SSH (for local dev)

db.disconnect()
if not ('DB_HOST' in os.environ.keys()) and ('DB_USER' in os.environ.keys()) and ('DB_PASSWORD' in os.environ.keys()):
    server.stop()

print("done")
timeStop = int(round(time.time()))
duration = timeStop - timeStart
print("Duration = %s s" % (str(duration)))
