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



################################################################################
# Put first 200 assets by marketcap here

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

# Get pair raw prices
badAssets = []
for asset in newIndexCandidates:
    rawPrices = getPairPricesByDateRange2(asset, [baseCurrency, baseCurrency2], dateList)

    # See if there are gaps of more than maxDataGap in rawPrices
    availabilityDates = []

    for rp in rawPrices:
        # base, quote, DATE(date) as dt, price, volume
        date = rp[2]
        dateStr = date.strftime('%Y-%m-%d')
        if dateStr not in availabilityDates:
            availabilityDates.append(dateStr)

    missingInARow = 0
    for i in range (28):
        dateToCheck = dateAddDays(startDateStr, i)
        if dateToCheck not in availabilityDates:
            missingInARow += 1
        else:
            missingInARow = 0
        if missingInARow >= maxDataGap:
            badAssets.append(asset)
    if asset not in badAssets:
        print("%s GOOD" % (asset))
    else:
        print("%s BAD" % (asset))

    goodAssets = [asset for asset in newIndexCandidates if asset not in badAssets]
    if len(goodAssets) == 100:
        break

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
