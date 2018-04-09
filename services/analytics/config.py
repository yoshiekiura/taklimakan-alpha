######################################################################
# Script configuration

# List of pairs to calculate analytics for

#pairs = [("ADA", "USD")]


pairs = [
("BTC", "USD"),
("GAME", "USD"),
("RLC", "USD"),
("BTX", "BTC"),
("SMART", "USD"),
("DTR", "BTC"),
("CVC", "USD"),
("BLOCK", "BTC"),
("MNX", "USD"),
("MANA", "USD"),
("ANT", "USD"),
("VTC", "USD"),
("BTCD", "BTC"),
("SKY", "BTC"),
("CS", "USD"),
("PLR", "USD"),
("NXS", "BTC"),
("ICN", "BTC"),
("ENJ", "USD"),
("PART", "BTC"),
("STORJ", "USD"),
("CND", "USD"),
("NULS", "BTC"),
("MTL", "USD"),
("BNT", "USD"),
("PAY", "USD"),
("DCN", "USD"),
("POWR", "BTC"),
("GBYTE", "BTC"),
("MAID", "USD"),
("ENG", "BTC"),
("LINK", "BTC"),
("EMC", "USD"),
("NXT", "USD"),
("RDD", "USD"),
("NEBL", "BTC"),
("SALT", "BTC"),
("REQ", "USD"),
("XZC", "USD"),
("SUB", "USD"),
("KNC", "USD"),
("ETN", "BTC"),
("R", "USD"),
("SYS", "USD"),
("FUN", "USD"),
("GAS", "BTC"),
("GNT", "USD"),
("MONA", "BTC"),
("FCT", "BTC"),
("QASH", "USD"),
("DGB", "USD"),
("LRC", "USD"),
("BAT", "USD"),
("PIVX", "USD"),
("CNX", "USD"),
("ARK", "BTC"),
("ARDR", "BTC"),
("HSR", "USD"),
("KMD", "USD"),
("SNT", "USD"),
("ZRX", "USD"),
("WTC", "BTC"),
("DCR", "USD"),
("VERI", "USD"),
("REP", "USD"),
("AE", "BTC"),
("DOGE", "USD"),
("BTM", "BTC"),
("BTS", "USD"),
("BCD", "USD"),
("WAVES", "USD"),
("STEEM", "BTC"),
("BCN", "USD"),
("STRAT", "USD"),
("SC", "BTC"),
("PPT", "BTC"),
("DGD", "USD"),
("XVG", "USD"),
("ZEC", "USD"),
("BTG", "USD"),
("OMG", "USD"),
("LSK", "USD"),
("ICX", "USD"),
("BNB", "BTC"),
("QTUM", "USD"),
("VEN", "USD"),
("ETC", "USD"),
("XEM", "USD"),
("USDT", "USD"),
("TRX", "USD"),
("XMR", "USD"),
("DASH", "USD"),
("NEO", "USD"),
("XLM", "USD"),
("ADA", "USD"),
("EOS", "USD"),
("LTC", "USD"),
("BCH", "USD"),
("XRP", "USD"),
("ETH", "USD")
]


# List of analytics formulas (type_id) to calculate for each pair
formulas = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10"]

# Prices start timestamp  - first date prices are available
startDateByType = {"1": "2017-01-01", "2": "2017-01-01", "3": "2017-03-03", "4": "2018-02-01", "5": "2018-02-01", "6": "2017-03-03", "7": "2017-03-03", "8": "2018-02-01", "9": "2018-02-01", "10": "2017-03-03", "11": "2017-12-01"}

# If set, it will calculate analytics for all dates starting from pricesStartDate
# plus data windows. Otherwise, it will calculate only starting from backDays
# back from now
calculateAllDates = True
backDays = 3
saveExtrapolatedPrices = True

# Maximum number of days price data is extrapolated
maxDataGap = 8

# Length of windows for metrics (in days)
maxWindow = 60 # This should be the greatest one
extraDataDays = 2

betaLength = 60
volatilityLength = 60
sharpeLength = 60

# Number of top exchanges by volume to consider for price and volume calculation
topExchangeCount = 10

# Risk-free daily rate of return (~ Equal to US securities rate)
riskFreeRate = 0.014 / 365.25

# Indexes
baseCurrency = "USD"
baseCurrency2 = "BTC"
indexName = "INDEX001"

'''
baseIndex = [
    ("RLC", 0.01),
    ("BTX", 0.01),
    ("ANT", 0.01),
    ("BTCD", 0.01),
    ("CS", 0.01),
    ("PLR", 0.01),
    ("ICN", 0.01),
    ("STORJ", 0.01),
    ("MTL", 0.01),
    ("BNT", 0.01),
    ("PAY", 0.01),
    ("FUN", 0.01),
    ("BAT", 0.01),
    ("PIVX", 0.01),
    ("REP", 0.01),
    ("DGD", 0.01),
    ("QTUM", 0.01),
    ("NEO", 0.01)
]
'''


baseIndex = [
    ("BTC", 0.01),

    #("QSP", 0.01), # There is data, but this is extra asset
    #("AGI", 0.01),
    ("GAME", 0.01),
    ("RLC", 0.01),
    ("BTX", 0.01),
    ("SMART", 0.01),
    ("DTR", 0.01),
    ("CVC", 0.01),
    #("ACT", 0.01),
    ("BLOCK", 0.01),

    ("MNX", 0.01),
    ("MANA", 0.01),
    ("ANT", 0.01),
    ("VTC", 0.01),
    #("GNX", 0.01),
    ("BTCD", 0.01),
    ("SKY", 0.01),
    ("CS", 0.01),
    #("GVT", 0.01),
    #("AUTO", 0.01),
    ("PLR", 0.01),
    ("NXS", 0.01),
    #("XPA", 0.01),
    #("MITH", 0.01),
    #("POLY", 0.01),
    #("MAN", 0.01),
    ("ICN", 0.01),
    ("ENJ", 0.01),

    ("PART", 0.01),
    ("STORJ", 0.01),
    ("CND", 0.01),
    ("NULS", 0.01),
    ("MTL", 0.01),
    ("BNT", 0.01),
    ("PAY", 0.01),
    ("DCN", 0.01),
    #("DENT", 0.01),
    ("POWR", 0.01),
    ("GBYTE", 0.01),
    ("MAID", 0.01),
    ("ENG", 0.01),
    ("LINK", 0.01),
    #("NCASH", 0.01),
    ("EMC", 0.01),
    #("KIN", 0.01),
    ("NXT", 0.01),
    ("RDD", 0.01),
    ("NEBL", 0.01),
    ("SALT", 0.01),
    ("REQ", 0.01),
    ("XZC", 0.01),
    #("ELF", 0.01),
    ("SUB", 0.01),
    ("KNC", 0.01),
    ("ETN", 0.01),
    #("GXS", 0.01),
    #("STORM", 0.01),
    ("R", 0.01),
    ("SYS", 0.01),
    ("FUN", 0.01),
    #("DRGN", 0.01),
    ("GAS", 0.01),
    ("GNT", 0.01),
    ("MONA", 0.01),
    ("FCT", 0.01),
    ("QASH", 0.01),
    #("IOST", 0.01),
    #("NAS", 0.01),
    ("DGB", 0.01),
    #("ETHOS", 0.01),
    ("LRC", 0.01),
    #("KCS", 0.01),
    ("BAT", 0.01),
    ("PIVX", 0.01),
    ("CNX", 0.01),
    ("ARK", 0.01),
    ("ARDR", 0.01),
    ("HSR", 0.01),
    #("AION", 0.01),
    ("KMD", 0.01),
    #("ZIL", 0.01),
    ("SNT", 0.01),
    ("ZRX", 0.01),
    ("WTC", 0.01),
    ("DCR", 0.01),
    ("VERI", 0.01),
    ("REP", 0.01),
    ("AE", 0.01),
    ("DOGE", 0.01),
    #("MKR", 0.01),
    ("BTM", 0.01),
    ("BTS", 0.01),
    #("RHOC", 0.01),
    ("BCD", 0.01),
    ("WAVES", 0.01),
    ("STEEM", 0.01),
    #("ONT", 0.01),
    ("BCN", 0.01),
    ("STRAT", 0.01),
    ("SC", 0.01),
    ("PPT", 0.01),
    ("DGD", 0.01),
    ("XVG", 0.01),
    ("ZEC", 0.01),
    #("NANO", 0.01),
    ("BTG", 0.01),
    ("OMG", 0.01),
    ("LSK", 0.01),
    ("ICX", 0.01),
    ("BNB", 0.01),
    ("QTUM", 0.01),
    ("VEN", 0.01),
    ("ETC", 0.01),
    ("XEM", 0.01),
    ("USDT", 0.01),
    ("TRX", 0.01),
    ("XMR", 0.01),
    ("DASH", 0.01),
    #("MIOTA", 0.01),
    ("NEO", 0.01),
    ("XLM", 0.01),
    ("ADA", 0.01),
    ("EOS", 0.01),
    ("LTC", 0.01),
    ("BCH", 0.01),
    ("XRP", 0.01),
    ("ETH", 0.01)
]
