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

insert into pair_set (id, name, data) values (1, 'Assets of Interest', '["BTC", "GAME", "RLC", "BTX", "SMART", "DTR", "CVC", "BLOCK", "MNX", "MANA", "ANT", "VTC", "BTCD", "SKY", "CS", "PLR", "NXS", "ICN", "ENJ", "PART", "STORJ", "CND", "NULS", "MTL", "BNT", "PAY", "DCN", "POWR", "GBYTE", "MAID", "ENG", "LINK", "EMC", "NXT", "RDD", "NEBL", "SALT", "REQ", "XZC", "SUB", "KNC", "ETN", "R", "SYS", "FUN", "GAS", "GNT", "MONA", "FCT", "QASH", "DGB", "LRC", "BAT", "PIVX", "CNX", "ARK", "ARDR", "HSR", "KMD", "SNT", "ZRX", "WTC", "DCR", "VERI", "REP", "AE", "DOGE", "BTM", "BTS", "BCD", "WAVES", "STEEM", "BCN", "STRAT", "SC", "PPT", "DGD", "XVG", "ZEC", "BTG", "OMG", "LSK", "ICX", "BNB", "QTUM", "VEN", "ETC", "XEM", "USDT", "TRX", "XMR", "DASH", "NEO", "XLM", "ADA", "EOS", "LTC", "BCH", "XRP", "ETH"]');
