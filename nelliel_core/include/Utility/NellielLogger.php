<?php

namespace Nelliel\Utility;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;
use Nelliel\NellielPDO;
use Psr\Log\LoggerInterface;

class NellielLogger implements LoggerInterface
{
    private $database;

    public function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function emergency($message, array $context = array())
    {
        $this->log(0, $message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->log(1, $message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->log(2, $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->log(3, $message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->log(4, $message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->log(5, $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->log(6, $message, $context);
    }

    public function debug($message, array $context = array())
    {
        $this->log(7, $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        $data = array();

        if (!is_int($level))
        {
            $data['level'] = 7;
        }
        else
        {
            $data['level'] = $level;
        }

        $data['domain_id'] = $context['domain_id'] ?? null;
        $data['event_id'] = $context['event_id'] ?? 'UNKNOWN';
        $data['originator'] = $context['originator'] ?? '';
        $data['ip_address'] = $context['ip_address'] ?? null;
        $data['hashed_ip_address'] = $context['hashed_ip_address'] ?? null;
        $data['time'] = time();
        $data['message'] = $message;
        $table = $context['table'] ?? NEL_LOGS_TABLE;
        $this->dbInsert($table, $data);
    }

    protected function dbInsert(string $table, array $data)
    {
        $prepared = $this->database->prepare(
                'INSERT INTO "' . $table .
                '" ("level", "domain_id",  "event_id", "originator", "ip_address", "hashed_ip_address", "time", "message")
								VALUES (:level, :domain_id, :event_id, :originator, :ip_address, :hashed_ip_address, :time, :message)');
        $prepared->bindValue(':level', $data['level'], PDO::PARAM_INT);
        $prepared->bindValue(':domain_id', $data['domain_id'], PDO::PARAM_STR);
        $prepared->bindValue(':event_id', $data['event_id'], PDO::PARAM_STR);
        $prepared->bindValue(':originator', $data['originator'], PDO::PARAM_STR);
        $prepared->bindValue(':ip_address', nel_prepare_ip_for_storage($data['ip_address']), PDO::PARAM_LOB);
        $prepared->bindValue(':hashed_ip_address', nel_prepare_hash_for_storage($data['hashed_ip_address']), PDO::PARAM_LOB);
        $prepared->bindValue(':time', $data['time'], PDO::PARAM_INT);
        $prepared->bindValue(':message', $data['message'], PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }
}