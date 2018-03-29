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
