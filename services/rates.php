<?php

//foreach ($argv as )

// --sync allows to get latest feed for 24H, and --max gets all historic data
if (in_array("--sync", $argv))
    $mode = "sync";
else
    if (in_array("--max", $argv))
        $mode = "max";
    else
        die("[ERROR] Please use --sync or --max flags!");

// $mode = in_array("--sync", $argv) ? "sync" : "max";

// 1 = two days vs. 3650 = 10 years
// We'll get yesterday and one day before info with limit = 2
$limit = $mode == "sync" ? 1 : 3650;

// date_default_timezone_set("Europe/Moscow");
// All data have to be stored in UTC time
date_default_timezone_set("UTC");

$db = new PDO("mysql:host=localhost;dbname=crypto", "root", "usbw");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // We have to see ERRORS
if (!$db) die("\n[ERROR] Can't connect to DB!");

$query = $db->prepare("INSERT IGNORE INTO rates (exchange, source, base, quote, period, date, price, open, high, low, close, quantity, volume, trades)
    VALUES (:exchange, :source, :base, :quote, :period, :date, :price, :open, :high, :low, :close, :quantity, :volume, :trades )");

logger("rates", "--- Starting Rates Task ---");

// Get most used crypto symbols from CryptoCompare
// $crypto = getTopSymbols();

// There are total 2325 symbols as of March 2018
$crypto = getSymbols();

// How many times fiat symbol is used on exchanges from all/exchanges ?
// USD = 2118, CNY = 228, EUR = 197, JPY = 68, GBP = 44, RUB = 43, CAD = 21, SGD = 17, AUD = 16, CHF = 15, HKD = 10
$fiats = ["USD", "CNY", "EUR", "JPY", "GBP", "RUB", "CAD", "SGD", "AUD", "CHF"];

// Combine together
$symbols = array_merge($crypto, $fiats);
$exchanges = array_merge(getExchanges(), ["All" => "ALL"]);
$markets = getMarkets();

// !OPINIONATED Most popular crypto exchanges
// Please see 24H trade volume and marketshare
// https://cryptocoincharts.info/markets/info
// https://coinmarketcap.com/exchanges/volume/24-hour/
/*
$topex = ["Binance" => "Binance", "Huobi" => "Huobi", "Bitfinex" => "Bitfinex",
          "GDAX" => "Coinbase", "Kraken" => "Kraken", "Bittrex" => "Bittrex",
          "Poloniex" => "Poloniex", "CEX.IO" => "Cexio", "EXMO" => "Exmo",
          "YoBit" => "Yobit", "Kucoin" => "Kucoin", "Bistamp" => "Bistamp", "Gemini" => "Gemini"];
$exchanges = array_merge($topex, ["All" => "ALL"]);
*/

foreach ($exchanges as $ex) {

    $ex = strtoupper($ex);

    foreach ($symbols as $base) {
        foreach ($symbols as $quote) {

            // The Twins?
            if ($base == $quote) continue;

            // Exchange does not support the base currency?
            if (!property_exists($markets[$ex], $base)) continue;

            // Are there active market BASE/QUOTE in the exchange?
            if (!in_array($quote, $markets[$ex]->$base)) continue;

            //echo "| $ex $base/$quote ";
            logger("rates", "$ex :: $base/$quote");

            // !FIXME We have to know EXACTLY wich paris exchange support
            // and do not request unsupported pairs.
            // There is first naive and slow implementation, we check ALL of them

            // NB! Please be sure that we commit in UTC here!
            $today = (new DateTime())->format("Y-m-d");
            $yesterday = (new DateTime("-1 day"))->format("Y-m-d");

            // In fact, we got two days past today with CryptoCompare
            // NB! We can't use TODAY cause info about current trades will be incomplete
            $rates = getRates($base, $quote, $yesterday, "DAY", $ex, $limit);

            // If there are some error or unsupported pair, just continue
            if (!is_array($rates)) continue;

            try {
            $db->beginTransaction();

            foreach ($rates as $row) {

                // If there are now historical data for the period, skip row
                if (!$row->open && !$row->close) continue;
                if (!$row->volumeto || !$row->volumefrom) continue;

                // With CryptoCompare we compute price by dividing volume and quantity
                // And there no info about number of trades, like at CoinAPI
                $date = DateTime::createFromFormat("U", $row->time);
                $date = $date->format("Y-m-d H:i:s");

                //if ($row->volumeto && $row->volumefrom)
                $price = $row->volumeto / $row->volumefrom;
                // else
                //    $price = null;

                // NB! That does not work welL! Exchange may be under attack so it freeze the course with zero volume of treades
                // See Bitfinex between 2nd and 10th August of 2016 or Cryptsy after 15th of January, 2016
                // If there are now data on volume but price of opening and closing, use them
                // if (!$price && ($row->open == $row->close))
                //    $price = $row->open;

                // NB! Start transaction to impove speed of insert
                $query->execute([
                    "exchange" => strtoupper($ex), "source" => "CRYPTOCOMPARE",
                    "base" => strtoupper($base), "quote" => strtoupper($quote),
                    "period" => "DAY", "date" => $date,
                    "price" => $price, "open" => $row->open, "high" => $row->high, "low" => $row->low, "close" => $row->close,
                    "quantity" => $row->volumefrom, "volume" => $row->volumeto, "trades" => null
                ]);

            }

            $db->commit();

            } catch(Exception $e) {
                // Duplicate entry? It's all right. In any other case, show error message
                if (strpos($e->getMessage(), "SQLSTATE[23000]") != false) {
                    echo $e->getMessage();
                    logger("rates", $e->getMessage());
                }
                $db->commit();
            }

        }
    }

}


// Access to CryptoCompare API

function getCryptoCompare($url) {
    $base = "https://min-api.cryptocompare.com/data/";
    $headers = [ "http" => [ "method" => "GET", "header" => "" ] ];
    $context = stream_context_create($headers);

    // Trying to eliminate problems so waiting 10 seconds in case of network outage
    $json = false;
    while ($json === false) {
        $json = file_get_contents($base . $url, false, $context);
        if ($json) break; else sleep(10);
    }

    $rows = json_decode($json);
    return $rows;
}

// Get info on ALL exchanges

function getExchanges() {
    $response = getCryptoCompare("all/exchanges");
    if ($response === false) return null;

    $exchanges = [];
    foreach ($response as $ex => $markets)
        $exchanges[] = strtoupper($ex);

    return $exchanges;
}

// Get info on ALL markets

function getMarkets() {
    $response = getCryptoCompare("all/exchanges");
    if ($response === false) return null;

    $markets = [];
    foreach ($response as $ex => $market)
        $markets[strtoupper($ex)] = $market;

    return $markets;
}


// Get info on all exchanges

function getTopExchanges($topex) {
    $response = getCryptoCompare("all/exchanges");
    if ($response === false)
        return null;
    foreach ($response as $ex) {
        if (in_array($ex, $topex))
            var_dump($ex);
    }
}

// Get TOP symbols by opinion of CryptoCompare

function getTopSymbols() {
    $response = getCryptoCompare("all/coinlist");
    if ($response->Response != "Success")
        return null;

    // IDs of TOP10 crypto currencies by CryptoCompare
    // "1182,7605,5038,24854,3807,3808,202330,5324,5031,20131"
    $watchList = explode(',', $response->DefaultWatchlist->CoinIs);

    $symbols = [];
    foreach ($response->Data as $cur)
        if (in_array($cur->Id, $watchList))
            $symbols[] = $cur->Symbol;

    return $symbols;
}

// Get ALL symbols by opinion of CryptoCompare

function getSymbols() {
    $response = getCryptoCompare("all/coinlist");
    if ($response->Response != "Success")
        return null;

    $symbols = [];
    foreach ($response->Data as $cur)
            $symbols[] = $cur->Symbol;

    return $symbols;
}


// Get rates for crypto pair
// Limit of 3650 is useful to get daily historical data for the last 10 years since the Bitcoin appeared

function getRates($base, $quote, $date, $period = "DAY", $exchange = "ALL", $limit = 3650) {

    if ($period == "DAY") {
        $to = strtotime($date);
        $ex = $exchange == "ALL" ? "" : "e=$exchange";
        $url = "histoday?fsym=$base&tsym=$quote&limit=$limit&toTs=$to&$ex";

        $response = getCryptoCompare($url);
        if ($response->Response != "Success")
            return null;
        else
            return $response->Data;
    }
    else
        return null;

}

// Log working progress
// C:\Users\SGOTSU~1\AppData\Local\Temp\rates.log
function logger($file, $string) {
    $dir = sys_get_temp_dir();
    $string = "\n" . (new DateTime())->format("Y-m-d") . " | " . (new DateTime())->format("H:i:s") . " | ". $string;
    file_put_contents($dir . "/" . $file . ".log", $string, FILE_APPEND | LOCK_EX);
}

/* CoinAPI

$headers = [
    "http" => [
        "method" => "GET",
        "header" => //"Accept-language: en\r\n" .
            //"Cookie: foo=bar\r\n" .
            "X-CoinAPI-Key: 0328B632-A182-4554-B9CD-32082A1BE65D\r\n"
    ]
];

$url = 'https://rest.coinapi.io/v1/ohlcv/BITSTAMP_SPOT_BTC_USD/history?period_id=1DAY&time_start=2017-01-01T00:00:00&time_end=2018-01-01T00:00:00&limit=100000';

*/
