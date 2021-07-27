<?php

declare(strict_types=1);

namespace Nelliel\IfThens;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

interface Conditions
{

    public function check(array $conditions): bool;
}
