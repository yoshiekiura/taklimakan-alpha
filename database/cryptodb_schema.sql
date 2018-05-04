-- Schema to store historical cryptocurrency data
-- cryptodb_schema.sql

-- Version 0.0.0 / 2018-03-12 / First attempt to arhcitect rates table
-- Version 0.1.0 / 2018-03-16 / Works just fine
-- Version 0.2.0 / 2018-04-12 / Merged with analytics tables
-- Version 0.2.1 / 2018-04-23 / Migrate to utf8mb4 according to the latest recommendations

-- CoinAPI Data Feed
-- https://rest.coinapi.io/v1/ohlcv/BITSTAMP_SPOT_BTC_USD/history?period_id=1DAY&time_start=2017-01-01T00:00:00&time_end=2018-01-01T00:00:00&limit=100000

-- CryptoCompare Data Feed
-- https://min-api.cryptocompare.com/data/histoday?fsym=BTC&tsym=USD&limit=365

create database if not exists crypto character set utf8mb4 collate utf8mb4_general_ci;

create table if not exists rates
(
--    id bigint auto_increment, -- Do we really need this?

    exchange varchar(20) not null, -- Exchange symbol
    source   varchar(20) not null, -- Source of data like cryptocompare or coinapi

    base  varchar(10) not null, -- Base currency (from) of the trading pair like btc, etc
    quote varchar(10) not null, -- Quote currency (to) of the pair like usd, etc

    date   datetime    not null, -- Start interval / mandatory
    period varchar(10) not null, -- Inteval name like min, day, 5min, 10 day, etc

    price double not null, -- Actual rate value / mandatory

    open  double not null, -- OHLC / optional
    high  double not null, -- OHLC / optional
    low   double not null, -- OHLC / optional
    close double not null, -- OHLC / optional

    quantity double default null, -- Base units sold
    volume   double default null, -- Total = price * quantity

    trades bigint default null, -- Trades count / optional

--    primary key (id) using hash,

    -- In order to speed up operations we have to use fields with NOT NULL defaults
    -- There are no evidence which type is more efficient HASH vs. BTREE

    index  ix (source, exchange, base, quote, period, date), -- using hash,
    unique uni (source, exchange, base, quote, period, date) -- using hash

) engine=InnoDB default character set=utf8mb4;

CREATE TABLE analytics_type_dictionary
(
	id INT(2),
	name VARCHAR(256),
	PRIMARY KEY (id)
);

CREATE TABLE numerical_analytics
(
	dt DATETIME,
	pair VARCHAR(20),
	type_id INT(2),
	value FLOAT,
    PRIMARY KEY (dt, pair, type_id)
);

INSERT INTO analytics_type_dictionary (id, name) VALUES
(1, "Price"),
(2, "Volume"),
(3, "Volatility"),
(4, "Alpha"),
(5, "Beta"),
(6, "Sharpe Ratio"),
(7, "Exponentially Weighted Volatility"),
(8, "Exponentially Weighted Alpha"),
(9, "Exponentially Weighted Beta"),
(10, "Exponentially Weighted Sharpe Ratio"),
(11, "Base Index");

create table pair_set ( id INT(2), name VARCHAR(256), data TEXT);

insert into pair_set (id, name, data) values (1, 'Assets of Interest', '["BTC-USD","ETH-USD","BCH-USD","LTC-USD","EOS-USD","ADA-USD","XLM-USD","DASH-USD","XMR-USD","TRX-USD","USDT-USD","XEM-USD","ETC-USD","VEN-USD","BNB-BTC","ICX-USD","LSK-USD","OMG-USD","BTG-USD","ZEC-USD","XVG-USD","PPT-BTC","SC-BTC","STRAT-USD","BCN-USD","STEEM-BTC","WAVES-USD","BCD-USD","BTS-USD","BTM-BTC","DOGE-USD","AE-BTC","VERI-USD","DCR-USD","WTC-BTC","ZRX-USD","SNT-USD","KMD-USD","HSR-USD","ARDR-BTC","ARK-BTC","CNX-USD","LRC-USD","DGB-USD","QASH-USD","FCT-BTC","MONA-BTC","GNT-USD","GAS-BTC","SYS-USD","R-USD","ETN-BTC","KNC-USD","SUB-USD","XZC-USD","REQ-USD","SALT-BTC","NEBL-BTC","RDD-USD","NXT-USD","EMC-USD","LINK-BTC","ENG-BTC","MAID-USD","GBYTE-BTC","POWR-BTC","DCN-USD","NULS-BTC","CND-USD","PART-BTC","ENJ-USD","NXS-BTC","SKY-BTC","VTC-USD","MANA-USD","MNX-USD","BLOCK-BTC","CVC-USD","DTR-BTC","SMART-USD","GAME-USD","RLC-USD", "BTX-BTC", "ANT-USD", "BTCD-BTC", "CS-USD", "PLR-USD", "ICN-BTC", "STORJ-USD", "MTL-USD", "BNT-USD", "PAY-USD", "FUN-USD", "BAT-USD", "PIVX-USD", "REP-USD", "DGD-USD", "QTUM-USD", "NEO-USD", "NEU-USD"]');
