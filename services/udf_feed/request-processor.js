/*
	This file is a node.js module.

	This is a sample implementation of UDF-compatible datafeed wrapper for Quandl (historical data) and yahoo.finance (quotes).
	Some algorithms may be incorrect because it's rather an UDF implementation sample
	then a proper datafeed implementation.
*/

/* global require */
/* global console */
/* global exports */
/* global process */

"use strict";

var version = '2.0.2';

var https = require("https");
var http = require("http");

function dateForLogs() {
	return (new Date()).toISOString() + ': ';
}

var defaultResponseHeader = {
	"Content-Type": "text/plain",
	'Access-Control-Allow-Origin': '*'
};

function sendJsonResponse(response, jsonData) {
	response.writeHead(200, defaultResponseHeader);
	response.write(JSON.stringify(jsonData));
	response.end();
}

function dateToYMD(date) {
	var obj = new Date(date);
	var year = obj.getFullYear();
	var month = obj.getMonth() + 1;
	var day = obj.getDate();
	return year + "-" + month + "-" + day;
}

function sendError(error, response) {
	response.writeHead(200, defaultResponseHeader);
	response.write("{\"s\":\"error\",\"errmsg\":\"" + error + "\"}");
	response.end();
}

function httpGet(datafeedHost, path, callback) {
	var options = {
		host: datafeedHost,
		path: path
	};

	function onDataCallback(response) {
		var result = '';

		response.on('data', function (chunk) {
			result += chunk;
		});

		response.on('end', function () {
			if (response.statusCode !== 200) {
				callback({ status: 'ERR_STATUS_CODE', errmsg: response.statusMessage || '' });
				return;
			}

			callback({ status: 'ok', data: result });
		});
	}

	var req = https.request(options, onDataCallback);

	req.on('socket', function (socket) {
		socket.setTimeout(5000);
		socket.on('timeout', function () {
			console.log(dateForLogs() + 'timeout');
			req.abort();
		});
	});

	req.on('error', function (e) {
		callback({ status: 'ERR_SOCKET', errmsg: e.message || '' });
	});

	req.end();
}

function convertQuandlHistoryToUDFFormat(data) {
	function parseDate(input) {
		var parts = input.split('-');
		return Date.UTC(parts[0], parts[1] - 1, parts[2]);
	}

	function columnIndices(columns) {
		var indices = {};
		for (var i = 0; i < columns.length; i++) {
			indices[columns[i].name] = i;
		}

		return indices;
	}

	var result = {
		t: [],
		c: [],
		o: [],
		h: [],
		l: [],
		v: [],
		s: "ok"
	};

	try {
		var json = JSON.parse(data);
		var datatable = json.datatable;
		var idx = columnIndices(datatable.columns);

		datatable.data.forEach(function (row) {
			result.t.push(parseDate(row[idx.date]) / 1000);
			result.o.push(row[idx.open]);
			result.h.push(row[idx.high]);
			result.l.push(row[idx.low]);
			result.c.push(row[idx.close]);
			result.v.push(row[idx.volume]);
		});

	} catch (error) {
		return null;
	}

	return result;
}

function convertYahooQuotesToUDFFormat(tickersMap, data) {
	if (!data.query || !data.query.results) {
		var errmsg = "ERROR: empty quotes response: " + JSON.stringify(data);
		console.log(dateForLogs() + errmsg);
		return {
			s: "error",
			errmsg: errmsg
		};
	}

	var result = {
		s: "ok",
		d: []
	};

	[].concat(data.query.results.quote).forEach(function (quote) {
		var ticker = tickersMap[quote.symbol];

		// this field is an error token
		if (quote["ErrorIndicationreturnedforsymbolchangedinvalid"] || !quote.StockExchange) {
			result.d.push({
				s: "error",
				n: ticker,
				v: {}
			});
			return;
		}

		result.d.push({
			s: "ok",
			n: ticker,
			v: {
				ch: +(quote.ChangeRealtime || quote.Change),
				chp: +((quote.PercentChange || quote.ChangeinPercent) && (quote.PercentChange || quote.ChangeinPercent).replace(/[+-]?(.*)%/, "$1")),

				short_name: quote.Symbol,
				exchange: quote.StockExchange,
				original_name: quote.StockExchange + ":" + quote.Symbol,
				description: quote.Name,

				lp: +quote.LastTradePriceOnly,
				ask: +quote.AskRealtime,
				bid: +quote.BidRealtime,

				open_price: +quote.Open,
				high_price: +quote.DaysHigh,
				low_price: +quote.DaysLow,
				prev_close_price: +quote.PreviousClose,
				volume: +quote.Volume,
			}
		});
	});
	return result;
}

function proxyRequest(controller, options, response) {
	controller.request(options, function (res) {
		var result = '';

		res.on('data', function (chunk) {
			result += chunk;
		});

		res.on('end', function () {
			if (res.statusCode !== 200) {
				response.writeHead(200, defaultResponseHeader);
				response.write(JSON.stringify({
					s: 'error',
					errmsg: 'Failed to get news'
				}));
				response.end();
				return;
			}
			response.writeHead(200, defaultResponseHeader);
			response.write(result);
			response.end();
		});
	}).end();
}

function RequestProcessor(symbolsDatabase) {
	this._symbolsDatabase = symbolsDatabase;
	this._failedYahooTime = {};
}

function filterDataPeriod(data, fromSeconds, toSeconds) {
	if (!data || !data.t) {
		return data;
	}

	if (data.t[data.t.length - 1] < fromSeconds) {
		return {
			s: 'no_data',
			nextTime: data.t[data.t.length - 1]
		};
	}

	var fromIndex = null;
	var toIndex = null;
	var times = data.t;
	for (var i = 0; i < times.length; i++) {
		var time = times[i];
		if (fromIndex === null && time >= fromSeconds) {
			fromIndex = i;
		}
		if (toIndex === null && time >= toSeconds) {
			toIndex = time > toSeconds ? i - 1 : i;
		}
		if (fromIndex !== null && toIndex !== null) {
			break;
		}
	}

	fromIndex = fromIndex || 0;
	toIndex = toIndex ? toIndex + 1 : times.length;

	var s = data.s;

	if (toSeconds < times[0]) {
		s = 'no_data';
	}

	toIndex = Math.min(fromIndex + 1000, toIndex); // do not send more than 1000 bars for server capacity reasons

	return {
		t: data.t.slice(fromIndex, toIndex),
		o: data.o.slice(fromIndex, toIndex),
		h: data.h.slice(fromIndex, toIndex),
		l: data.l.slice(fromIndex, toIndex),
		c: data.c.slice(fromIndex, toIndex),
		v: data.v.slice(fromIndex, toIndex),
		s: s
	};
}

RequestProcessor.prototype._sendConfig = function (response) {

	var config = {
		supports_search: true,
		supports_group_request: false,
		supports_marks: false,
		supports_timescale_marks: false,
		supports_time: true,
		exchanges: [
			{
				value: "",
				name: "GregsFakeExchange",
				desc: ""
			},
		],
		symbols_types: [
			{
				name: "All types",
				value: ""
			},
			{
				name: "Stock",
				value: "stock"
			},
			{
				name: "Index",
				value: "index"
			},
			{
				name: "Indicator",
				value: "Indicator"
			}
		],
		supported_resolutions: ["1H", "D", "2D", "3D", "W", "3W", "M"]
	};

	response.writeHead(200, defaultResponseHeader);
	response.write(JSON.stringify(config));
	response.end();
};


RequestProcessor.prototype._sendMarks = function (response) {
	var now = new Date();
	now = new Date(Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate())) / 1000;
	var day = 60 * 60 * 24;

	var marks = {
		id: [0, 1, 2, 3, 4, 5],
		time: [now, now - day * 4, now - day * 7, now - day * 7, now - day * 15, now - day * 30],
		color: ["red", "blue", "green", "red", "blue", "green"],
		text: ["Today", "4 days back", "7 days back + Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.", "7 days back once again", "15 days back", "30 days back"],
		label: ["A", "B", "CORE", "D", "EURO", "F"],
		labelFontColor: ["white", "white", "red", "#FFFFFF", "white", "#000"],
		minSize: [14, 28, 7, 40, 7, 14]
	};

	response.writeHead(200, defaultResponseHeader);
	response.write(JSON.stringify(marks));
	response.end();
};

RequestProcessor.prototype._sendTime = function (response) {
	var now = new Date();
	response.writeHead(200, defaultResponseHeader);
	response.write(Math.floor(now / 1000) + '');
	response.end();
};

RequestProcessor.prototype._sendTimescaleMarks = function (response) {
	var now = new Date();
	now = new Date(Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate())) / 1000;
	var day = 60 * 60 * 24;

	var marks = [
		{
			id: "tsm1",
			time: now,
			color: "red",
			label: "A",
			tooltip: ""
		},
		{
			id: "tsm2",
			time: now - day * 4,
			color: "blue",
			label: "D",
			tooltip: ["Dividends: $0.56", "Date: " + new Date((now - day * 4) * 1000).toDateString()]
		},
		{
			id: "tsm3",
			time: now - day * 7,
			color: "green",
			label: "D",
			tooltip: ["Dividends: $3.46", "Date: " + new Date((now - day * 7) * 1000).toDateString()]
		},
		{
			id: "tsm4",
			time: now - day * 15,
			color: "#999999",
			label: "E",
			tooltip: ["Earnings: $3.44", "Estimate: $3.60"]
		},
		{
			id: "tsm7",
			time: now - day * 30,
			color: "red",
			label: "E",
			tooltip: ["Earnings: $5.40", "Estimate: $5.00"]
		},
	];

	response.writeHead(200, defaultResponseHeader);
	response.write(JSON.stringify(marks));
	response.end();
};


RequestProcessor.prototype._sendSymbolSearchResults = function (query, type, exchange, maxRecords, response) {
	if (!maxRecords) {
		throw "wrong_query";
	}

	var result = this._symbolsDatabase.search(query, type, exchange, maxRecords);

	response.writeHead(200, defaultResponseHeader);
	response.write(JSON.stringify(result));
	response.end();
};

RequestProcessor.prototype._prepareSymbolInfo = function (symbolName) {
	var symbolInfo = this._symbolsDatabase.symbolInfo(symbolName);

	if (!symbolInfo) {
		throw "unknown_symbol " + symbolName;
	}

	return {
		"name": symbolInfo.name,
		"exchange-traded": symbolInfo.exchange,
		"exchange-listed": symbolInfo.exchange,
		"timezone": "America/New_York",
		"minmov": 1,
		"minmov2": 0,
		"pointvalue": 1,
		"session": "0930-1630",
		"has_intraday": false,
		"has_no_volume": symbolInfo.type !== "stock",
		"description": symbolInfo.description.length > 0 ? symbolInfo.description : symbolInfo.name,
		"type": symbolInfo.type,
		"supported_resolutions": ["1H", "D", "2D", "3D", "W", "3W", "M", "6M"],
		"pricescale": 100,
		"ticker": symbolInfo.name.toUpperCase()
	};
};

RequestProcessor.prototype._sendSymbolInfo = function (symbolName, response) {
	var info = this._prepareSymbolInfo(symbolName);

	response.writeHead(200, defaultResponseHeader);
	response.write(JSON.stringify(info));
	response.end();
};

RequestProcessor.prototype._sendSymbolHistory = function (symbol, startDateTimestamp, endDateTimestamp, resolution, response) {
	function sendResult(content) {
		var header = Object.assign({}, defaultResponseHeader);
		header["Content-Length"] = content.length;
		response.writeHead(200, header);
		response.write(content, null, function () {
			response.end();
		});
	}

	function secondsToISO(sec) {
		if (sec === null || sec === undefined) {
			return 'n/a';
		}
		return (new Date(sec * 1000).toISOString());
	}

	console.log(dateForLogs() + "Got history request for " + symbol + ", " + resolution + " from " + secondsToISO(startDateTimestamp)+ " to " + secondsToISO(endDateTimestamp));
    console.log("startDateTimestamp = " + startDateTimestamp)
    console.log("endDateTimestamp = " + endDateTimestamp)

	// always request all data to reduce number of requests to quandl
    var day0 = new Date("2003-06-07") / 1000;
    var fromSec = parseInt(startDateTimestamp);
	var toSec = parseInt(endDateTimestamp);


	// Generate some symbol data (sin)
	var data = {
		t: [],
		o: [],
		h: [],
		l: [],
		c: [],
		v: [],
		s: "ok"
	};

    console.log("fromSec=", fromSec);

    for (var i=fromSec; i<=toSec; i += 3600*24) {
        var freq = 1 / (100 * 3600 * 24);
        var vfreq = 1 / (1234 * 24);
        if (symbol == 'TST') {
            freq = 1 / (200 * 3600 * 24);
            vfreq = 1 / (4321 * 24);
        }

        var val = Math.abs(100 * Math.sin(Math.PI * 2 * i * freq));
        var valNext = Math.abs(100 * Math.sin(Math.PI * 2 * (i + 3600 * 24) * freq));
        var vol = Math.abs(1000 * Math.sin(Math.PI * 2 * i * vfreq));

        data.t.push(i);
        data.o.push(val);
        data.c.push(valNext);
        data.h.push(valNext + 5);
        data.l.push(val * 0.995);
        data.v.push(vol);
    }

    console.log("data len = ", data.t.length);

	if (data.t.length !== 0) {
		console.log(dateForLogs() + "Successfully parsed and put to cache " + data.t.length + " bars.");
	} else {
		console.log(dateForLogs() + "Parsing returned empty result.");
	}

	//var filteredData = filterDataPeriod(data, startDateTimestamp, endDateTimestamp);
	//sendResult(JSON.stringify(filteredData));

    sendResult(JSON.stringify(data));

};

RequestProcessor.prototype._quotesQuandlWorkaround = function (tickersMap) {
	var result = {
		s: "ok",
		d: [],
		source: 'xxx',
	};

	return result;
};

RequestProcessor.prototype._sendQuotes = function (tickersString, response) {
};

RequestProcessor.prototype._sendNews = function (symbol, response) {
	var options = {
		host: "feeds.finance.yahoo.com",
		path: "/rss/2.0/headline?s=" + symbol + "&region=US&lang=en-US"
	};

	proxyRequest(https, options, response);
};

RequestProcessor.prototype._sendFuturesmag = function (response) {
	var options = {
		host: "www.futuresmag.com",
		path: "/rss/all"
	};

	proxyRequest(http, options, response);
};

RequestProcessor.prototype.processRequest = function (action, query, response) {
	try {
        console.log("Action rq: ", action);

		if (action === "/config") {
			this._sendConfig(response);
		}
		else if (action === "/symbols" && !!query["symbol"]) {
			this._sendSymbolInfo(query["symbol"], response);
		}
		else if (action === "/search") {
            console.log(`${query["query"]}, ${query["type"]}, ${query["exchange"]}, ${query["limit"]}`);
			this._sendSymbolSearchResults(query["query"], query["type"], query["exchange"], query["limit"], response);
		}
		else if (action === "/history") {
			this._sendSymbolHistory(query["symbol"], query["from"], query["to"], query["resolution"].toLowerCase(), response);
		}
		else if (action === "/quotes") {
			this._sendQuotes(query["symbols"], response);
		}
		else if (action === "/marks") {
			this._sendMarks(response);
		}
		else if (action === "/time") {
			this._sendTime(response);
		}
		else if (action === "/timescale_marks") {
			this._sendTimescaleMarks(response);
		}
		else if (action === "/news") {
			this._sendNews(query["symbol"], response);
		}
		else if (action === "/futuresmag") {
			this._sendFuturesmag(response);
		} else {
			response.writeHead(200, defaultResponseHeader);
			response.write('Datafeed version is ' + version);
			response.end();
		}
	}
	catch (error) {
		sendError(error, response);
		console.error('Exception: ' + error);
	}
};

exports.RequestProcessor = RequestProcessor;
