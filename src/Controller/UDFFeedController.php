<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\MakerBundle\Validator;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use App\Entity\usd\Symbol;
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

        $symrepo = new SymbolRepository();
        $symbolInfo = $symrepo->getSymbolInfo($symbolName);

        $ret = [
            "name" => $symbolInfo->getName(),
            "exchange-traded" => $symbolInfo->getExchange(),
            "exchange-listed" => $symbolInfo->getExchange(),
            "timezone" => "America/New_York",
            "minmov" => 1,
            "minmov2" => 0,
            "pointvalue" => 1,
            "session" => "0930-1630",
            "has_intraday" => false,
            "has_no_volume" => $symbolInfo->getType() !== "stock",
            "description" => $symbolInfo->getDescription(),
            "type" => $symbolInfo->getType(),
            "supported_resolutions" => ["1H", "D", "2D", "3D", "W", "3W", "M", "6M"],
            "pricescale" => 100,
            "ticker" => $symbolInfo->getName()
        ];

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

        $symrepo = new SymbolRepository();
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

    	// Generate some symbol data (sin)
        $data = (object) [
            "t" => [],
            "o" => [],
            "h" => [],
            "l" => [],
            "c" => [],
            "v" => [],
            "s" => "ok"
        ];

        for ($i=$fromSec; $i<=$toSec; $i += 3600*24) {
            $freq = 1 / (100 * 3600 * 24);
            $vfreq = 1 / (1234 * 24);
            if ($symbol == 'TST') {
                $freq = 1 / (200 * 3600 * 24);
                $vfreq = 1 / (4321 * 24);
            }

            $val = abs(100 * sin(3.141 * 2 * $i * $freq));
            $valNext = abs(100 * sin(3.141 * 2 * ($i + 3600 * 24) * $freq));
            $vol = abs(1000 * sin(3.141 * 2 * $i * $vfreq));

            $data->t[] = $i;
            $data->o[] = $val;
            $data->c[] = $valNext;
            $data->h[] = $valNext + 5;
            $data->l[] = $val * 0.995;
            $data->v[] = $vol;
        }

        return $this->json($data);
    }

}
