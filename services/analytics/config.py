######################################################################
# Script configuration

# List of pairs to calculate analytics for
pairs = [
("BTC", "USD"),
("ETH", "USD"),
("LTC", "USD")
]

# List of analytics formulas (type_id) to calculate for each pair
formulas = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10"]

# Prices start timestamp (unix epoch seconds) - first date prices are available
pricesStartDate = "2017-12-01"

# Length of windows for metrics (in days)
maxWindow = 60 # This should be the greatest one
extraDataDays = 2

betaLength = 60
volatilityLength = 60
sharpeLength = 60


# Risk-free daily rate of return (~ Equal to US securities rate)
riskFreeRate = 0.014 / 365.25

# Indexes
baseCurrency = "USD"
baseIndex = [
    ("BTC", 0.1),
    ("ETH", 0.9)
]

marketIndex = [
    ("BTC", 0.34),
    ("ETH", 0.33),
    ("LTC", 0.33)
]
