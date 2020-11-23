<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Utility\NellielLogger;

class LogEvent
{
    private $domain;
    private $logger;
    private $level = 6;
    private $context = array();

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->logger = new NellielLogger($domain->database());
        $this->changeContext('domain_id', $this->domain->id());
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
