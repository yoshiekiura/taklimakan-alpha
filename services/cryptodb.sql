-- Sheme to store historical cryptocurrency data
-- cryptodb.sql

-- Version 0.0.0 / 2018-03-12 / First attempt to arhcitect rates table
-- Version 0.1.0 / 2018-03-16 / Works just fine

-- CoinAPI Data Feed
-- https://rest.coinapi.io/v1/ohlcv/BITSTAMP_SPOT_BTC_USD/history?period_id=1DAY&time_start=2017-01-01T00:00:00&time_end=2018-01-01T00:00:00&limit=100000

-- CryptoCompare Data Feed
-- https://min-api.cryptocompare.com/data/histoday?fsym=BTC&tsym=USD&limit=365

create database if not exists crypto character set utf8 collate utf8_general_ci;

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

) engine=InnoDB default character set=utf8;


-- CREATE TABLE trade
-- (
    -- ex_id VARCHAR(20),
    -- pair_id VARCHAR(20),
    -- tx_id VARCHAR(20),
    -- timestamp VARCHAR(20),
    -- price FLOAT,
    -- amount FLOAT,
    -- side int(1),
    -- PRIMARY KEY (transaction_id)
-- );
-- ALTER TABLE `trade` ADD INDEX (`timestamp`);
