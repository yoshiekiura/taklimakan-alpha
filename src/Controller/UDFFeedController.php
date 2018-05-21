<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\MakerBundle\Validator;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use App\Entity\udf\Symbol;
use App\Repository\udf\SymbolRepository;

class UDFFeedController extends Controller
{
    /**
     * @Route("/api/v1/udf/health", name="udf")
     */
    public function index(Request $request)
    {
        return $this->json(array('status' => 'OK'));
    }

    /**
     * @Route("/api/v1/udf/config", name="udf_config")
     */
    public function config(Request $request)
    {
        $config = (object) [
            'supports_search' => true,
            'supports_group_request' => false,
            'supports_marks' => false,
            'supports_timescale_marks' => false,
            'supports_time' => true,
            'exchanges' => [
                (object) [
                    "value" => "",
                    "name" => "GregsFakeExchange",
                    "desc" => ""
                ],
            ],
            'symbols_types' => [
                (object) [
                    "name" => "All types",
                    "value" => ""
                ],
                (object) [
                    "name" => "Stock",
                    "value" => "stock"
                ],
                (object) [
                    "name" => "Index",
                    "value" => "index"
                ],
                (object) [
                    "name" => "Indicator",
                    "value" => "Indicator"
                ]
            ],
            'supported_resolutions' => ["1H", "D", "2D", "3D", "W", "3W", "M"]
        ];

        return $this->json($config);
    }

    /**
     * @Route("/api/v1/udf/time", name="udf_time")
     */
    public function time(Request $request)
    {
        return $this->json(time());
    }

    /**
     * @Route("/api/v1/udf/symbols", name="udf_symbols")
     */
    public function symbols(Request $request)
    {
        $symbolName = $request->query->get("symbol");

        $symrepo = $this->getDoctrine()->getRepository(Symbol::class);
        $symrepo->initSymbols();
        $symbolInfo = $symrepo->getSymbolInfo($symbolName);

        $ret = [];
        if ($symbolInfo !== null) {
            $ret = [
                "name" => $symbolInfo->getName(),
                "exchange-traded" => $symbolInfo->getExchange(),
                "exchange-listed" => $symbolInfo->getExchange(),
                "timezone" => "America/New_York",
                "minmov" => 1,
                "minmov2" => 0,
                "pointvalue" => 1,
                "session" => "24x7",
                "has_intraday" => false,
                "has_no_volume" => $symbolInfo->getType() !== "stock",
                "description" => $symbolInfo->getDescription(),
                "type" => $symbolInfo->getType(),
                "supported_resolutions" => ["1H", "D", "2D", "3D", "W", "3W", "M", "6M"],
                "pricescale" => 100,
                "ticker" => $symbolInfo->getName()
            ];
        }

        return $this->json($ret);
    }

    /**
     * @Route("/api/v1/udf/search", name="udf_search")
     */
    public function search(Request $request)
    {
        $searchString = $request->query->get("query");
        $type = $request->query->get("type");
        $exchange = $request->query->get("exchange");
        $maxRecords = $request->query->get("limit");

        $symrepo = $this->getDoctrine()->getRepository(Symbol::class);
        $symrepo->initSymbols();
        $results = $symrepo->search($searchString, $type, $exchange, $maxRecords);

        $returnArray = [];

        foreach ($results as $sym)
        {
            $returnArray[] = [
                "symbol" => $sym->getName(),
                "full_name" => $sym->getName(),
                "description" => $sym->getDescription(),
                "exchange" => $sym->getExchange(),
                "type" => $sym->getType()
            ];
        }

        return $this->json($returnArray);
    }


    private function getSymbolHistory($symbol, $type_id, $startDt, $stopDt)
    {
        $sql = "SELECT * FROM numerical_analytics WHERE type_id = '$type_id' AND pair = '$symbol' AND DATE(dt) >= '$startDt' AND DATE(dt) <= '$stopDt'";
        $query = $this->getDoctrine()->getConnection()->prepare($sql);
        $query->execute();

        $rows = $query->fetchAll();

        $data = [];
        foreach ($rows as $row)
            $data[] = [ $row['dt'], $row['value'] ];

        return $data;
    }

    /**
     * @Route("/api/v1/udf/history", name="udf_history")
     */
    public function history(Request $request)
    {
        $symbol = $request->query->get("symbol");
        $startDateTimestamp = $request->query->get("from");
        $endDateTimestamp = $request->query->get("to");
        $resolution = $request->query->get("resolution");

        $fromSec = intval($startDateTimestamp);
        $toSec = intval($endDateTimestamp);

        // Load real data from DB
        $inputFields = explode(" - ", $symbol);
        $dbSym = $inputFields[0];
        $type_id = intval($inputFields[1]);

        $startDt = date("Y-m-d", $fromSec);
        $stopDt = date("Y-m-d", $toSec);
        $values = $this->getSymbolHistory($dbSym, $type_id, $startDt, $stopDt);

        // Get volume simultaneously with price
        if ($type_id == "1") {
            $volValues = $this->getSymbolHistory($dbSym, "2", $startDt, $stopDt);
        }

        // Format UDF output
        $data = (object) [
            "t" => [],
            "o" => [],
            "h" => [],
            "l" => [],
            "c" => [],
            "v" => [],
            "s" => "ok"
        ];

        $len = count($values);
        for ($i=0; $i<$len; $i++) {
            $val = $values[$i];
            $epochTime = strtotime($val[0]);

            $data->t[] = $epochTime;

            // For now we only have one (averaged) price instead of open, close, high, low
            // So we use previous candle close for next candle open, min for low, and max for high
            $prev = $values[$i][1];
            if ($i > 0) {
                $prev = $values[$i-1][1];
            }
            $data->o[] = $prev;
            $data->c[] = $val[1];
            $data->h[] = max($prev, $val[1]);
            $data->l[] = min($prev, $val[1]);


            if ($type_id == "1") {
                $data->v[] = $volValues[$i][1];
            } else {
                $data->v[] = 0;
            }
        }

        return $this->json($data);
    }

}
