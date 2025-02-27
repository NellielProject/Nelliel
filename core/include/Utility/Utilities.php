<?php
declare(strict_types = 1);

namespace Nelliel\Utility;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;

class Utilities
{
    private CacheHandler $cache_handler;
    private FileHandler $file_handler;
    private RateLimit $rate_limit;
    private SQLCompatibility $sql_compatibility;
    private SQLHelpers $sql_helpers;

    function __construct(NellielPDO $database)
    {
        $this->cache_handler = new CacheHandler();
        $this->file_handler = new FileHandler();
        $this->rate_limit = new RateLimit($database);
        $this->sql_compatibility = new SQLCompatibility($database);
        $this->sql_helpers = new SQLHelpers($database);
    }

    public function cacheHandler(): CacheHandler
    {
        return $this->cache_handler;
    }

    public function fileHandler(): FileHandler
    {
        return $this->file_handler;
    }

    public function rateLimit(): RateLimit
    {
        return $this->rate_limit;
    }

    public function sqlCompatibility(): SQLCompatibility
    {
        return $this->sql_compatibility;
    }

    public function sqlHelpers(): SQLHelpers
    {
        return $this->sql_helpers;
    }
}