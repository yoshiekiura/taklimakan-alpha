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

# Weight for weighted mean and covariance
#
# Exponent:
# weightFunc(0) = 1
# weightFunc(30) = 0.5
#
# @param i - number of days back
def weightFunc(i):
    return math.exp(math.log(0.5) * i / 30)

def weightedMean(a):
    sum = 0
    totalWeight = 0
    i = 0
    for value in a:
        iback = len(a) - 1 - i
        weight = weightFunc(iback)
        sum += value * weight
        totalWeight += weight
        i += 1
    return sum / totalWeight

def weightedCov(a, b):
     if len(a) != len(b):
         return
     a_mean = weightedMean(a)
     b_mean = weightedMean(b)
     sum = 0
     totalWeight = 0
     for i in range(len(a)):
         iback = len(a) - 1 - i
         weight = weightFunc(iback)
         sum += ((a[i] - a_mean) * (b[i] - b_mean) * weight)
         totalWeight += weight
     return sum / totalWeight

def getReturns(subasset):
    return [(subasset[i+1] - subasset[i])/subasset[i] for i in range(len(subasset) - 1)]

def getBeta(asset, index):
    subasset = asset[-betaLength-1:]
    subindex = index[-betaLength-1:]
    assetReturns = getReturns(subasset)
    indexReturns = getReturns(subindex)
    aiCov = cov(assetReturns, indexReturns)
    iVar = cov(indexReturns, indexReturns)
    return aiCov/iVar

def getWeightedBeta(asset, index):
    subasset = asset[-betaLength-1:]
    subindex = index[-betaLength-1:]
    assetReturns = getReturns(subasset)
    indexReturns = getReturns(subindex)
    aiCov = weightedCov(assetReturns, indexReturns)
    iVar = weightedCov(indexReturns, indexReturns)
    return aiCov/iVar

def getVolatility(asset):
    subasset = asset[-volatilityLength-1:]
    returns = getReturns(subasset)
    return math.sqrt(cov(returns, returns))

def getWeightedVolatility(asset):
    subasset = asset[-volatilityLength-1:]
    returns = getReturns(subasset)
    return math.sqrt(weightedCov(returns, returns))

def getAlpha(asset, index):
    subasset = asset[-2:]
    subindex = index[-2:]

    beta = getBeta(asset, index)
    assetReturns = getReturns(subasset)
    indexReturns = getReturns(subindex)

    return assetReturns[0] - riskFreeRate - beta * (indexReturns[0] - riskFreeRate)

def getWeightedAlpha(asset, index):
    subasset = asset[-2:]
    subindex = index[-2:]

    beta = getWeightedBeta(asset, index)
    assetReturns = getReturns(subasset)
    indexReturns = getReturns(subindex)

    return assetReturns[0] - riskFreeRate - beta * (indexReturns[0] - riskFreeRate)


def getSharpeRatio(asset):
    subasset = asset[-sharpeLength-1:]
    assetReturns = getReturns(subasset)
    meanReturn = np.mean(assetReturns)
    stdevReturn = np.std(assetReturns)
    if stdevReturn != 0:
        return (meanReturn - riskFreeRate) / stdevReturn
    else:
        return 0

def getWeightedSharpeRatio(asset):
    subasset = asset[-sharpeLength-1:]
    assetReturns = getReturns(subasset)
    meanReturn = weightedMean(assetReturns)
    stdevReturn = math.sqrt(weightedCov(assetReturns, assetReturns))
    if stdevReturn != 0:
        return (meanReturn - riskFreeRate) / stdevReturn
    else:
        return 0

current = 0
def initBrownian(newCurrent):
    global current
    current = newCurrent

def getNextBrownian(delta):
    global current
    retval = current + delta
    current += delta
    return retval
