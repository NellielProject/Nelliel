<?php

declare(strict_types=1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

interface NellielCacheInterface
{

    public function regenCache();

    public function deleteCache();
}