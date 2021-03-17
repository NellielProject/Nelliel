<?php

declare(strict_types=1);

namespace Nelliel\IfThens;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

interface Conditions
{

    public function check(array $conditions): bool;
}
