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
	type_id INT(2),
	value FLOAT
);

INSERT INTO analytics_type_dictionary (id, name) VALUES
(1, "Price"),
(2, "Volume"),
(3, "Volatility"),
(4, "Alpha"),
(5, "Beta"),
(6, "Sharpe Ratio");
