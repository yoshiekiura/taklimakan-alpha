<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PriceExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [ new TwigFilter('price', array($this, 'priceFilter')) ];
    }

    public function priceFilter($price)
    {
        if (0 == floatval($price))
            return 'Free';
        else
            return '$' . round($price, 2);
    }

}

