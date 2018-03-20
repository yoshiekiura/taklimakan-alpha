######################################################################
# Script configuration

# List of pairs to calculate analytics for
pairs = ["ETH-USD", "BTC-USD"]

# Prices start timestamp (unix epoch seconds) - first date prices are available
pricesStartDate = "2018-01-01"

# Length of windows for metrics (in days)
betaLength = 30
volatilityLength = 30
sharpeLength = 30

# Risk-free daily rate of return (~ Equal to US securities rate)
riskFreeRate = 0.03 / 365.25
