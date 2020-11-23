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

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->logger = new NellielLogger($domain->database());
        $this->changeContext('domain', $this->domain);
        $this->changeContext('domain_id', $this->domain->id());
        $this->changeContext('log_table', $this->domain->reference('log_table'));
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
