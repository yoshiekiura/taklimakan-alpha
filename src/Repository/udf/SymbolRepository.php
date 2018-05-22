<?php

namespace App\Repository\udf;

use App\Entity\udf\Symbol;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
//use Doctrine\Common\Collections\ArrayCollection;

class SymbolRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Symbol::class);

    }

    public function initSymbols() {
        $this->symbols = array();

        // Init Symbols

        // Read unique pairs
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $sql = 'SELECT distinct(pair) FROM numerical_analytics order by pair';
        $query = $conn->prepare($sql);
        $query->execute();
        $pairRows = $query->fetchAll();

        // For each pair add all formulas
        $formulas = [
            "1" => "Price",
            "2" => "Volume",
            "3" => "Volatility",
            "4" => "Alpha",
            "5" => "Beta",
            "6" => "Sharpe Ratio",
            "7" => "Exp. Weighted Volatility",
            "8" => "Exp. Weighted Alpha",
            "9" => "Exp. Weighted Beta",
            "10" => "Exp. Weighted Sharpe Ratio",
            "11" => "Base Index",
            "12" => "Alpha vs SP500",
            "13" => "Beta vs SP500"
        ];

        foreach ($pairRows as $row) {
            foreach ($formulas as $fkey => $f) {
                if (($row['pair'] != "INDEX001") && ($fkey != "11")) {
                    if ($row['pair'] == "BTC-BTC") continue;


                    if ($fkey == "1") {
                        $this->symbols[] = new Symbol($row['pair']." - ".$fkey, $row['pair']." - ".$f, "GregsFakeExchange", "stock");
                    } else {
                        $this->symbols[] = new Symbol($row['pair']." - ".$fkey, $row['pair']." - ".$f, "GregsFakeExchange", "indicator");
                    }
                } else if (($row['pair'] == "INDEX001") && ($fkey == "11")) {
                    $this->symbols[] = new Symbol($row['pair']." - ".$fkey, $row['pair']." - Price", "GregsFakeExchange", "index");
                }
            }
        }
    }

    /**
    * TODO: Implement
    *
    */
    public function search($searchString, $type, $exchange, $maxRecords)
    {
        $result = [];
        foreach ($this->symbols as $sym) {
            $match = true;

            if ($searchString !== "" && strpos(strtolower($sym->getName()), strtolower($searchString)) !== 0)
                $match = false;
            if ($type !== "" && strpos(strtolower($sym->getType()), strtolower($type)) === FALSE)
                $match = false;

            if ($match)
                $result[] = $sym;

            if (count($result) >= $maxRecords)
                break;
        }




        return $result;
    }

    public function getSymbolInfo($name)
    {
        foreach ($this->symbols as $sym)
        {
            if ($sym->getName() == $name)
                return $sym;
        }
    }
}
