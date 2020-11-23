<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Utility\NellielLogger;
use Psr\Log\LogLevel;

class LogEvent
{
    private $domain;
    private $logger;
    private $level = LogLevel::INFO;
    private $context = array();

    function __construct(Domain $domain, bool $init_defaults = true)
    {
        $this->domain = $domain;
        $this->logger = new NellielLogger($domain->database());

        if($init_defaults)
        {
            $this->changeContext('domain', $this->domain);
            $this->changeContext('log_table', $this->domain->reference('log_table'));
            $this->changeContext('ip_address', nel_request_ip_address());
            $this->changeContext('hashed_ip_address', nel_request_ip_address(true));
        }
    }

    public function changeLevel(int $level)
    {
        $this->level = $level;
    }

    public function changeContext(string $entry, $data)
    {
        $this->context[$entry] = $data;
    }

    public function send(string $message, int $level = null)
    {
        if (!is_null($level))
        {
            $this->level = $level;
        }

        $this->logger->log($this->level, $message, $this->context);
    }
}
