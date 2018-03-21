######################################################################
# Script configuration

# List of pairs to calculate analytics for
pairs = [
("BTC", "USD"),
("ETH", "USD"),
("LTC", "USD")
]

# List of analytics formulas (type_id) to calculate for each pair
formulas = ["1", "2", "3", "4", "5", "6"]

# Prices start timestamp (unix epoch seconds) - first date prices are available
pricesStartDate = "2018-01-01"

# Length of windows for metrics (in days)
maxWindow = 30 # This should be the greatest one
extraDataDays = 2

betaLength = 30
volatilityLength = 30
sharpeLength = 30


# Risk-free daily rate of return (~ Equal to US securities rate)
riskFreeRate = 0.03 / 365.25

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
