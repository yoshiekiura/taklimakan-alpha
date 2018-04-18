<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LevelExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [ new TwigFilter('level', array($this, 'levelFilter')) ];
    }

    public function levelFilter($level)
    {
        switch ($level) {
            case '1':
                return 'Easy';
            case '2':
                return 'Moderate';
            case '3':
                return 'Advanced';
            case '3':
                return 'Expert';
            default:
                return '---';
        }
    }

}
