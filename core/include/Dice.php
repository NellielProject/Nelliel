<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class Dice
{
    const DICE_REGEX = '/(\d+)d(\d+)(?:([\+|-]\d+))?/';

    function __construct()
    {}

    public function roll(int $dice, int $sides, int $modifier = 0): array
    {
        $results = array();
        $results['sides'] = $sides;
        $results['dice'] = $dice;
        $results['modifier'] = $modifier;
        $results['total'] = 0;

        for ($i = 1; $i <= $dice; $i ++) {
            $random_result = mt_rand(1, $sides);
            $results['rolls'][] = $random_result;
            $results['total'] += $random_result;
        }

        $results['total'] += $modifier;
        return $results;
    }
}
