<?php

declare(strict_types=1);


namespace Nelliel\Utility;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class Utilities
{
    private $cache_handler;
    private $file_handler;
    private $logger;
    private $rate_limit;

    function __construct()
    {
        $this->cache_handler = new CacheHandler();
        $this->file_handler = new FileHandler();
        $this->logger = new NellielLogger(nel_database());
        $this->rate_limit = new RateLimit(nel_database());
    }

    public function cacheHandler()
    {
        return $this->cache_handler;
    }

    public function fileHandler()
    {
        return $this->file_handler;
    }

    public function logger()
    {
        return $this->logger;
    }

    public function rateLimit()
    {
        return $this->rate_limit;
    }
}