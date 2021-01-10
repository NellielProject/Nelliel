<?php

namespace Nelliel\IfThens;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

interface Conditions
{

    public function check(array $conditions): bool;
}
