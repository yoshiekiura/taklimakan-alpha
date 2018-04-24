<?php

// CRONTAB: 0 17    * * *   root    php /var/www/services/equity.php &
// CLI RUN: php /var/www/services/equity.php &

date_default_timezone_set("UTC"); // All data have to be stored in UTC time vs date_default_timezone_set("Europe/Moscow");

// There are different field set between Ubuntu and Windows 10
// Ubuntu 17 : ["USER"] => "tkln" vs Windows 10 : ["USERNAME"] => "sgotsulyak"

//////////////////////////////////////////////////////////////////////
// Constants

$startDate = '2017-01-01';
$fredApiBase = 'https://api.stlouisfed.org/fred/series/observations';
$writeToDB = true;
$listOfSymbols = ["SP500", "DJIA", "RU2000PR", "NASDAQ100", "NIKKEI225", "BAMLHYH0A0HYM2TRIV", "BAMLHE00EHYITRIV", "PALLFNFINDEXQ", "POILWTIUSDM", "POILBREUSDM", "VIXCLS"];
$db_conn_str = "mysql:host=localhost;dbname=crypto";
$db_user = "root";
$db_table = "equity_rate";

//////////////////////////////////////////////////////////////////////
// Tools

// Log working progress
// C:\Users\SGOTSU~1\AppData\Local\Temp\rates.log
function logger($file, $string) {
    $dir = sys_get_temp_dir();
    $string = "\n" . (new DateTime())->format("Y-m-d") . " | " . (new DateTime())->format("H:i:s") . " | ". $string;
    file_put_contents($dir . "/" . $file . ".log", $string, FILE_APPEND | LOCK_EX);
}
logger("equity", "--- Starting Equity Task ---");

//////////////////////////////////////////////////////////////////////
// DB Connection

if ($writeToDB) {
    $db_pass = getenv('DB_PASSWORD', true);
    if (!isset($db_pass) || $db_pass == '') {
        logger("equity", "DB_PASSWORD is not set");
        die("DB_PASSWORD is not set");
    }

    $db = new PDO($db_conn_str, $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // We have to see ERRORS
    if (!$db) {
        logger("equity", "[ERROR] Can't connect to DB!");
        die("\n[ERROR] Can't connect to DB!");
    }

    // Prepare DB queries
    $createQuery = $db->prepare("CREATE TABLE IF NOT EXISTS {$db_table} (dt DATETIME, symbol VARCHAR(20), value FLOAT, PRIMARY KEY (dt, symbol));");
    $insertQuery = $db->prepare("INSERT IGNORE INTO {$db_table} (dt, symbol, value) VALUES (:dt, :symbol, :value )");
}

//////////////////////////////////////////////////////////////////////
// DB Operations

function createTable() {
    $db = $GLOBALS['db'];
    $createQuery = $GLOBALS['createQuery'];

    try {
        $db->beginTransaction();
        $createQuery->execute();
        $db->commit();

    } catch(Exception $e) {
        echo $e->getMessage();
        logger("equity", $e->getMessage());
        $db->commit();
    }
}

function writeData($symbol, $prices) {
    $db = $GLOBALS['db'];
    $insertQuery = $GLOBALS['insertQuery'];

    try {
        $db->beginTransaction();
        foreach ($prices as $dt => $p) {
            $insertQuery->execute([
                "dt" => $dt,
                "symbol" => $symbol,
                "value" => $p
            ]);
        }
        $db->commit();
    } catch(Exception $e) {
        // Duplicate entry? It's all right. In any other case, show error message
        if (strpos($e->getMessage(), "SQLSTATE[23000]") != false) {
            echo $e->getMessage();
            logger("equity", $e->getMessage());
        }
        $db->commit();
    }
}


//////////////////////////////////////////////////////////////////////
// Access to Fred API

/**
* Get historical data for one symbol, extrapoliation of missing dates is done
* starting from first available price after $startDate
*
* @param $symbol - symbol to fetchall
* @return array of key-value pairs (date => price)
*/
function getFredSymbol($symbol) {
    $url = $GLOBALS['fredApiBase'] . "?series_id={$symbol}&api_key=66db042bb9caa79be7bfd6ab719c384b&file_type=json";
    $headers = [ "http" => [ "method" => "GET", "header" => "" ] ];
    $context = stream_context_create($headers);

    // Trying to eliminate problems so waiting 10 seconds in case of network outage
    $json = false;
    while ($json === false) {
        $json = file_get_contents($url, false, NULL);
        if ($json) break; else sleep(10);
    }

    $rows = json_decode($json);

    // Convert to array of tuples and extrapolate
    $tuples = array();
    foreach ($rows->observations as $value) {
        //array_push($tuples, array($value->date => $value->value));
        $tuples[$value->date] = $value->value;
    }

    // Extrapolate
    $extrapolated = array();
    $currentDate = $GLOBALS['startDate'];
    $yesterday = date('Y-m-d', time() - 60 * 60 * 24);
    $lastPrice = false;

    // Run loop from $startDate to today
    while ($currentDate <= $yesterday) {
        if ((isset($tuples[$currentDate])) && (is_numeric($tuples[$currentDate]))) {
            $lastPrice = $tuples[$currentDate];
        }

        if ($lastPrice !== false) {
            $extrapolated[$currentDate] = $lastPrice;
        }
        $currentDate = date( "Y-m-d", strtotime( "{$currentDate} +1 day" ) );
    }

    return $extrapolated;
}


//////////////////////////////////////////////////////////////////////
// "Main"

if ($writeToDB) createTable();

foreach ($listOfSymbols as $symbol) {
    $prices = getFredSymbol($symbol);
    if ($writeToDB) {
        writeData($symbol, $prices);
    }
}
