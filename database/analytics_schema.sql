USE pricedata;

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

insert into pair_set (id, name, data) values (1, 'Assets of Interest', '["BTC-USD","ETH-USD","BCH-USD","LTC-USD","EOS-USD","ADA-USD","XLM-USD","DASH-USD","XMR-USD","TRX-USD","USDT-USD","XEM-USD","ETC-USD","VEN-USD","BNB-BTC","ICX-USD","LSK-USD","OMG-USD","BTG-USD","ZEC-USD","XVG-USD","PPT-BTC","SC-BTC","STRAT-USD","BCN-USD","STEEM-BTC","WAVES-USD","BCD-USD","BTS-USD","BTM-BTC","DOGE-USD","AE-BTC","VERI-USD","DCR-USD","WTC-BTC","ZRX-USD","SNT-USD","KMD-USD","HSR-USD","ARDR-BTC","ARK-BTC","CNX-USD","LRC-USD","DGB-USD","QASH-USD","FCT-BTC","MONA-BTC","GNT-USD","GAS-BTC","SYS-USD","R-USD","ETN-BTC","KNC-USD","SUB-USD","XZC-USD","REQ-USD","SALT-BTC","NEBL-BTC","RDD-USD","NXT-USD","EMC-USD","LINK-BTC","ENG-BTC","MAID-USD","GBYTE-BTC","POWR-BTC","DCN-USD","NULS-BTC","CND-USD","PART-BTC","ENJ-USD","NXS-BTC","SKY-BTC","VTC-USD","MANA-USD","MNX-USD","BLOCK-BTC","CVC-USD","DTR-BTC","SMART-USD","GAME-USD"]');
