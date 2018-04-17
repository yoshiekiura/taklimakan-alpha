# sudo apt install python3-dev libpython3-dev
# sudo apt install python3-mysqldb
import MySQLdb
import matplotlib.pyplot as plt
from pprint import pprint
import os
import time
from datetime import datetime, timezone
import aformulas as af

# sudo pip install sshtunnel
import sshtunnel
import sys

import random


def plotArray(y):
    x = [i for i in range(len(y))]
    plt.plot(x, y)
    plt.show()

######################################################################
# Analytics calculation

# generate some test data
af.initBrownian(850)
ethPrices = [af.getNextBrownian(random.uniform(-100, 100)) for j in range(1000) ]
af.initBrownian(10000)
btcPrices = [af.getNextBrownian(random.uniform(-1000, 1000)) for j in range(1000) ]
af.initBrownian(100)
market = [af.getNextBrownian(random.uniform(-0.9, 1.1)) for j in range(1000) ]

# single values
b = af.getBeta(ethPrices, btcPrices)
print("Beta: %.2f" % (b))

ev = af.getVolatility(ethPrices)
print("Volatility: %.2f %%" % (ev*100))

ea = af.getAlpha(ethPrices, btcPrices, market)
print("Alpha: %.2f" % (ea))

sr = af.getSharpeRatio(ethPrices)
print("Sharpe ratio: %.2f" % (sr))

# array values
ba = [af.getBeta(ethPrices[0:800 + i], btcPrices[0:800 + i]) for i in range(200)]
eva = [af.getVolatility(ethPrices[0:800 + i]) for i in range(200)]
eaa = [af.getAlpha(ethPrices[0:800 + i], btcPrices[0:800 + i], market[0:800 + i]) for i in range(200)]
sra = [af.getSharpeRatio(ethPrices[0:800 + i]) for i in range(200)]

#x = [i for i in range(1000)]
#plt.plot(x, ethPrices[0:1000])
#plt.plot(x, btcPrices[0:1000])
#plt.show()

#plotArray(ba)
#plotArray(eva)
#plotArray(eaa)
plotArray(sra)
