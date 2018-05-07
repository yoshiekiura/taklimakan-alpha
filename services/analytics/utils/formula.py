import enum
import math
from datetime import datetime, timedelta

import numpy as np
from collections import namedtuple
from utils.index import IndexType

_ = namedtuple('FormulaInfo', ['number', 'date', 'length', 'index'])

RISK_FREE_RATE = 0.014 / 365.25
VOLATILITY_LENGTH = 60
BETA_LENGTH = 60
SHARPE_LENGTH = 60


def validate(fn):
    def new_function(self, asset, index=None):
        if self.value.length is None:
            raise Exception(self.name + ".apply not supported")
        if len(asset) < self.value.length:
            raise Exception("wrong asset length: " + str(len(asset)))
        if index is not None and not same_keys(asset, index):
            raise Exception("asset and index not matched")

        k_prev = None
        for k in sorted(asset.keys()):
            if type(k) is not datetime:
                raise Exception("wrong asset key type: " + str(k))
            if k_prev is not None and not (k - k_prev).days == 1:
                raise Exception("wrong asset keys interval")
            if asset[k] is None:
                raise Exception("price is None for key " + str(k))
            if index is not None and index[k] is None:
                raise Exception("index is None for key " + str(k))
            k_prev = k

        x = fn(self, asset, index)
        return x

    return new_function


class Formula(enum.Enum):
    Price = _(1, datetime(2017, 1, 1), None, None)
    Volume = _(2, datetime(2017, 1, 1), None, None)
    Volatility = _(3, datetime(2017, 3, 3), VOLATILITY_LENGTH, None)
    Alpha = _(4, datetime(2018, 2, 1), BETA_LENGTH, IndexType.INDEX001)
    Beta = _(5, datetime(2018, 2, 1), BETA_LENGTH, IndexType.INDEX001)
    SharpeRatio = _(6, datetime(2017, 3, 3), SHARPE_LENGTH, None)
    ExponentiallyWeightedVolatility = _(7, datetime(2017, 3, 3), VOLATILITY_LENGTH, None)
    ExponentiallyWeightedAlpha = _(8, datetime(2018, 2, 1), BETA_LENGTH, IndexType.INDEX001)
    ExponentiallyWeightedBeta = _(9, datetime(2018, 2, 1), BETA_LENGTH, IndexType.INDEX001)
    ExponentiallyWeightedSharpeRatio = _(10, datetime(2017, 3, 3), SHARPE_LENGTH, None)
    Index = _(11, datetime(2017, 12, 1), None, None)
    AlphaSP500 = _(12, datetime(2017, 12, 1), BETA_LENGTH, IndexType.SP500)
    BetaSP500 = _(13, datetime(2017, 12, 1), BETA_LENGTH, IndexType.SP500)

    def start_date(self, extra_data_days):
        if self in [Formula.Price, Formula.Volume, Formula.Index]:
            return self.value.date
        else:
            return self.value.date + timedelta(days=extra_data_days)

    @validate
    def apply(self, asset, index=None):
        if self == Formula.Volatility:
            return get_volatility(asset)
        if self == Formula.Beta or self == Formula.BetaSP500:
            if index is not None:
                return get_beta(asset, index)
        if self == Formula.Alpha or self == Formula.AlphaSP500:
            if index is not None:
                return get_alpha(asset, index)
        if self == Formula.SharpeRatio:
            return get_sharpe_ratio(asset)
        if self == Formula.ExponentiallyWeightedVolatility:
            return get_volatility(asset, True)
        if self == Formula.ExponentiallyWeightedAlpha:
            if index is not None:
                return get_alpha(asset, index, True)
        if self == Formula.ExponentiallyWeightedBeta:
            if index is not None:
                return get_beta(asset, index, True)
        if self == Formula.ExponentiallyWeightedSharpeRatio:
            if index is not None:
                return get_sharpe_ratio(asset, True)

        return None

    @staticmethod
    def get_by_number(number):
        for f in Formula:
            if f.value.number == number:
                return f
        return None


def get_volatility(d, weighted=False):
    r = get_returns(d)
    return math.sqrt(cov(r, r, weighted))


def get_alpha(d, index, weighted=False):
    beta = get_beta(d, index, weighted)
    r = get_returns(dict((k, d[k]) for k in list(sorted(d.keys())[-2:])))
    r_i = get_returns(dict((k, index[k]) for k in list(sorted(index.keys())[-2:])))
    return r[0] - RISK_FREE_RATE - beta * (r_i[0] - RISK_FREE_RATE)


def get_beta(d, index, weighted=False):
    r = get_returns(d)
    r_i = get_returns(index)
    return cov(r, r_i, weighted) / cov(r_i, r_i, weighted)


def get_sharpe_ratio(d, weighted=False):
    r = get_returns(d)
    mean_r = weighted_mean(r) if weighted else np.mean(r)
    stdev_r = math.sqrt(weighted_cov(r, r)) if weighted else  np.std(r)
    if stdev_r != 0:
        return (mean_r - RISK_FREE_RATE) / stdev_r
    else:
        return 0


def same_keys(d1, d2):
    return set(d1.keys()) == set(d2.keys())


def get_sorted_values(d):
    return list(map(lambda x: x[1], sorted(d.items(), key=lambda x: x[0])))


def get_returns(d):
    v = get_sorted_values(d)
    return [(v[i + 1] - v[i]) / v[i] for i in range(len(v) - 1)]


def cov(a, b, weighted=False):
    if weighted:
        return weighted_cov(a, b)
    return np.cov(a, b)[0][1]


def weighted_cov(a, b):
    a_mean = weighted_mean(a)
    b_mean = weighted_mean(b)
    sum = 0
    total_weight = 0
    for i in range(len(a)):
        iback = len(a) - 1 - i
        w = weight(iback)
        sum += ((a[i] - a_mean) * (b[i] - b_mean) * w)
        total_weight += w
    return sum / total_weight


def weighted_mean(a):
    sum = 0
    total_weight = 0
    i = 0
    for value in a:
        iback = len(a) - 1 - i
        w = weight(iback)
        sum += value * w
        total_weight += w
        i += 1
    return sum / total_weight


def weight(i):
    """
    Weight for weighted mean and covariance
    
    Exponent:
    weightFunc(0) = 1
    weightFunc(30) = 0.5
    
    i: int - number of days back
    """
    return math.exp(math.log(0.5) * i / 30)
