<?php

namespace App\Repository\udf;

use App\Entity\udf\Symbol;

class SymbolRepository
{
    public function __construct()
    {
        $this->symbols = array(
            new Symbol("ABSSIN", "Abs(sin(time))", "GregsFakeExchange", "stock"),
            new Symbol("TST", "Test Indicator", "GregsFakeExchange", "indicator")
        );
    }

    /**
    * TODO: Implement
    *
    */
    public function search($searchString, $type, $exchange, $maxRecords)
    {
        return $this->symbols;
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
