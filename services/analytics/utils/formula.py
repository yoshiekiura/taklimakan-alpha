import enum
import math
from datetime import datetime, timedelta

import numpy as np
from collections import namedtuple

_ = namedtuple('FormulaInfo', ['number', 'date', 'length'])


class Formula(enum.Enum):
    Price = _(1, datetime(2017, 1, 1), None)
    Volume = _(2, datetime(2017, 1, 1), None)
    Volatility = _(3, datetime(2017, 3, 3), 60)
    Alpha = _(4, datetime(2018, 2, 1), 60)
    Beta = _(5, datetime(2018, 2, 1), 60)
    SharpeRatio = _(6, datetime(2017, 3, 3), 60)
    ExponentiallyWeightedVolatility = _(7, datetime(2017, 3, 3), 60)
    ExponentiallyWeightedAlpha = _(8, datetime(2018, 2, 1), 60)
    ExponentiallyWeightedBeta = _(9, datetime(2018, 2, 1), 60)
    ExponentiallyWeightedSharpeRatio = _(10, datetime(2017, 3, 3), 60)
    Index = _(11, datetime(2017, 12, 1), None)

    def start_date(self, extra_data_days):
        if self in [Formula.Price, Formula.Volume, Formula.Index]:
            return self.value.date
        else:
            return self.value.date + timedelta(days=extra_data_days)

    def apply(self, asset, index):
        if self == Formula.Volatility:
            return self.get_volatility(asset)
        if self == Formula.Beta:
            return self.get_beta(asset, index)
        return None

    @staticmethod
    def get_volatility(d):
        r = get_returns(d)
        return math.sqrt(cov(r, r))

    @staticmethod
    def get_beta(d, index):
        r = get_returns(d)
        r_i = get_returns(index)
        return cov(r, r_i) / cov(r_i, r_i)


#### TODO move somewhere to special utils
def same_keys(d1, d2):
    return set(d1.keys()) == set(d2.keys())


def get_sorted_values(d):
    return list(map(lambda x: x[1], sorted(d.items(), key=lambda x: x[0])))


def get_returns(d):
    v = get_sorted_values(d)
    return [(v[i + 1] - v[i]) / v[i] for i in range(len(v) - 1)]


def cov(a, b):
    return np.cov(a, b)[0][1]
