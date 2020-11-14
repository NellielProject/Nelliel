<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

interface NellielCacheInterface
{
    public function regenCache();
    public function deleteCache();
}