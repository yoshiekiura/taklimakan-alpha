<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SourceExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [ new TwigFilter('source', array($this, 'sourceFilter')) ];
    }

    public function sourceFilter($url)
    {
        $domain = self::getDomain($url);
        $source = 'Taklimakan';

        switch ($domain) {
            case 'cointelegraph.com':
                $source = 'CoinTelegraph'; break;
            case 'coindesk.com':
                $source = 'CoinDesk'; break;
            case 'cryptovest.com':
                $source = 'Cryptovest'; break;
            case 'bitcoinist.com':
                $source = 'Bitcoinist'; break;
            default:
                $source = ucfirst($domain);
        }

        return $source;
    }

    public function getDomain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];

        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs))
            return $regs['domain'];

        $parse = parse_url($url);
        if (isset($parse['host']))
            $domain = $parse['host'];
        else
            $domain = 'Taklimakan';

        return $domain;
    }
}
