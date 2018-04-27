from collections import namedtuple
import enum

_ = namedtuple('CurrencyInfo', ['name', 'base', 'weight'])


class CurrencyBase(enum.Enum):
    USD = "USD"
    BTC = "BTC"


class Currency(enum.Enum):
    BTC = _("BTC", CurrencyBase.USD, 0.01)
    ETH = _("ETH", CurrencyBase.USD, 0.01)
    XRP = _("XRP", CurrencyBase.USD, 0.01)
    ETC = _("ETC", CurrencyBase.USD, 0.01)
    XEM = _("XEM", CurrencyBase.USD, 0.01)
    USDT = _("USDT", CurrencyBase.USD, 0.01)
    TRX = _("TRX", CurrencyBase.USD, 0.01)
    XMR = _("XMR", CurrencyBase.USD, 0.01)
    DASH = _("DASH", CurrencyBase.USD, 0.01)
    NEO = _("NEO", CurrencyBase.USD, 0.01)
    XLM = _("XLM", CurrencyBase.USD, 0.01)
    ADA = _("ADA", CurrencyBase.USD, 0.01)
    EOS = _("EOS", CurrencyBase.USD, 0.01)
    LTC = _("LTC", CurrencyBase.USD, 0.01)
    BCH = _("BCH", CurrencyBase.USD, 0.01)
    GAME = _("GAME", CurrencyBase.USD, 0.01)
    RLC = _("RLC", CurrencyBase.USD, 0.01)
    BTX = _("BTX", CurrencyBase.BTC, 0.01)
    SMART = _("SMART", CurrencyBase.USD, 0.01)
    DTR = _("DTR", CurrencyBase.BTC, 0.01)
    CVC = _("CVC", CurrencyBase.USD, 0.01)
    BLOCK = _("BLOCK", CurrencyBase.BTC, 0.01)
    MNX = _("MNX", CurrencyBase.USD, 0.01)
    MANA = _("MANA", CurrencyBase.USD, 0.01)
    ANT = _("ANT", CurrencyBase.USD, 0.01)
    VTC = _("VTC", CurrencyBase.USD, 0.01)
    BTCD = _("BTCD", CurrencyBase.BTC, 0.01)
    SKY = _("SKY", CurrencyBase.BTC, 0.01)
    CS = _("CS", CurrencyBase.USD, 0.01)
    PLR = _("PLR", CurrencyBase.USD, 0.01)
    NXS = _("NXS", CurrencyBase.BTC, 0.01)
    ICN = _("ICN", CurrencyBase.BTC, 0.01)
    ENJ = _("ENJ", CurrencyBase.USD, 0.01)
    PART = _("PART", CurrencyBase.BTC, 0.01)
    STORJ = _("STORJ", CurrencyBase.USD, 0.01)
    CND = _("CND", CurrencyBase.USD, 0.01)
    NULS = _("NULS", CurrencyBase.BTC, 0.01)
    MTL = _("MTL", CurrencyBase.USD, 0.01)
    BNT = _("BNT", CurrencyBase.USD, 0.01)
    PAY = _("PAY", CurrencyBase.USD, 0.01)
    DCN = _("DCN", CurrencyBase.USD, 0.01)
    POWR = _("POWR", CurrencyBase.BTC, 0.01)
    GBYTE = _("GBYTE", CurrencyBase.BTC, 0.01)
    MAID = _("MAID", CurrencyBase.USD, 0.01)
    ENG = _("ENG", CurrencyBase.BTC, 0.01)
    LINK = _("LINK", CurrencyBase.BTC, 0.01)
    EMC = _("EMC", CurrencyBase.USD, 0.01)
    NXT = _("NXT", CurrencyBase.USD, 0.01)
    RDD = _("RDD", CurrencyBase.USD, 0.01)
    NEBL = _("NEBL", CurrencyBase.BTC, 0.01)
    SALT = _("SALT", CurrencyBase.BTC, 0.01)
    REQ = _("REQ", CurrencyBase.USD, 0.01)
    XZC = _("XZC", CurrencyBase.USD, 0.01)
    SUB = _("SUB", CurrencyBase.USD, 0.01)
    KNC = _("KNC", CurrencyBase.USD, 0.01)
    ETN = _("ETN", CurrencyBase.BTC, 0.01)
    R = _("R", CurrencyBase.USD, 0.01)
    SYS = _("SYS", CurrencyBase.USD, 0.01)
    FUN = _("FUN", CurrencyBase.USD, 0.01)
    GAS = _("GAS", CurrencyBase.BTC, 0.01)
    GNT = _("GNT", CurrencyBase.USD, 0.01)
    MONA = _("MONA", CurrencyBase.BTC, 0.01)
    FCT = _("FCT", CurrencyBase.BTC, 0.01)
    QASH = _("QASH", CurrencyBase.USD, 0.01)
    DGB = _("DGB", CurrencyBase.USD, 0.01)
    LRC = _("LRC", CurrencyBase.USD, 0.01)
    BAT = _("BAT", CurrencyBase.USD, 0.01)
    PIVX = _("PIVX", CurrencyBase.USD, 0.01)
    CNX = _("CNX", CurrencyBase.USD, 0.01)
    ARK = _("ARK", CurrencyBase.BTC, 0.01)
    ARDR = _("ARDR", CurrencyBase.BTC, 0.01)
    HSR = _("HSR", CurrencyBase.USD, 0.01)
    KMD = _("KMD", CurrencyBase.USD, 0.01)
    SNT = _("SNT", CurrencyBase.USD, 0.01)
    ZRX = _("ZRX", CurrencyBase.USD, 0.01)
    WTC = _("WTC", CurrencyBase.BTC, 0.01)
    DCR = _("DCR", CurrencyBase.USD, 0.01)
    VERI = _("VERI", CurrencyBase.USD, 0.01)
    REP = _("REP", CurrencyBase.USD, 0.01)
    AE = _("AE", CurrencyBase.BTC, 0.01)
    DOGE = _("DOGE", CurrencyBase.USD, 0.01)
    BTM = _("BTM", CurrencyBase.BTC, 0.01)
    BTS = _("BTS", CurrencyBase.USD, 0.01)
    BCD = _("BCD", CurrencyBase.USD, 0.01)
    WAVES = _("WAVES", CurrencyBase.USD, 0.01)
    STEEM = _("STEEM", CurrencyBase.BTC, 0.01)
    BCN = _("BCN", CurrencyBase.USD, 0.01)
    STRAT = _("STRAT", CurrencyBase.USD, 0.01)
    SC = _("SC", CurrencyBase.BTC, 0.01)
    PPT = _("PPT", CurrencyBase.BTC, 0.01)
    DGD = _("DGD", CurrencyBase.USD, 0.01)
    XVG = _("XVG", CurrencyBase.USD, 0.01)
    ZEC = _("ZEC", CurrencyBase.USD, 0.01)
    BTG = _("BTG", CurrencyBase.USD, 0.01)
    OMG = _("OMG", CurrencyBase.USD, 0.01)
    LSK = _("LSK", CurrencyBase.USD, 0.01)
    ICX = _("ICX", CurrencyBase.USD, 0.01)
    BNB = _("BNB", CurrencyBase.BTC, 0.01)
    QTUM = _("QTUM", CurrencyBase.USD, 0.01)
    VEN = _("VEN", CurrencyBase.USD, 0.01)
    NEU = _("NEU", CurrencyBase.USD, None)

    @staticmethod
    def base_index():
        return list(filter(lambda c: c.value.weight is not None, Currency))

    @staticmethod
    def get_by_pair_str(pair):
        lst = pair.split('-')
        if len(lst) == 2:
            for c in Currency:
                if c.name == lst[0]:
                    return c
        return None

    def get_pair_str(self, base=None):
        if base is None:
            base = self.value.base
        return self.value.name + '-' + base.value