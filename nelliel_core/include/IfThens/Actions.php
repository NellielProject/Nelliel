<?php

namespace Nelliel\IfThens;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

interface Actions
{

    public function do(array $actions);
}
