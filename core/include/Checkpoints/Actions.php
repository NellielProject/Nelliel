<?php

declare(strict_types=1);

namespace Nelliel\Checkpoints;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

interface Actions
{

    public function do(array $actions);
}
