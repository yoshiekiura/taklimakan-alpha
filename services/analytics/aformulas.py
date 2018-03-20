import numpy as np
from pprint import pprint
import time
from datetime import datetime, timezone
import random
import math
from config import *

######################################################################
# Math and logic

def cov(a, b):
    return np.cov(a, b)[0][1]

def getReturns(subasset):
    original = subasset[0]
    return [(subasset[i+1] - original)/original for i in range(len(subasset) - 1)]

def getBeta(asset, index):
    subasset = asset[-betaLength:]
    subindex = index[-betaLength:]
    assetReturns = getReturns(subasset)
    indexReturns = getReturns(subindex)
    aiCov = cov(assetReturns, indexReturns)
    iVar = cov(indexReturns, indexReturns)
    return aiCov/iVar

def getVolatility(asset):
    subasset = asset[-volatilityLength:]
    returns = getReturns(subasset)
    return math.sqrt(cov(returns, returns))

def getAlpha(asset, index, market):
    subasset = asset[-2:]
    subindex = index[-2:]
    submarket = index[-2:]

    beta = getBeta(asset, index)
    assetReturns = getReturns(subasset)
    marketReturns = getReturns(submarket)

    return assetReturns[0] - riskFreeRate - beta * (marketReturns[0] - riskFreeRate)

def getSharpeRatio(asset):
    subasset = asset[-sharpeLength:]
    assetReturns = getReturns(subasset)
    meanReturn = np.mean(assetReturns)
    stdevReturn = np.std(assetReturns)
    return (meanReturn - riskFreeRate) / stdevReturn

current = 0
def initBrownian(newCurrent):
    global current
    current = newCurrent

def getNextBrownian(delta):
    global current
    retval = current + delta
    current += delta
    return retval
