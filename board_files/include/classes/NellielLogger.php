<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;
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

        $data['area'] = $context['area'] ?? 'UNKNOWN';
        $data['event_id'] = $context['event_id'] ?? 'UNKNOWN';
        $data['originator'] = $context['originator'] ?? '';
        $data['ip_address'] = $context['ip_address'] ?? null;
        $data['time'] = time();
        $data['message'] = $message;
        $this->dbInsert($context['table'], $data);
    }

    protected function dbInsert(string $table, array $data) {
        $prepared = $this->database->prepare(
                'INSERT INTO "' . $table .
                '" ("area", "level", "event_id", "originator", "ip_address", "time", "message")
								VALUES (:area, :level, :event_id, :originator, :ip_address, :time, :message)');
        $prepared->bindParam(':area', $data['area'], PDO::PARAM_STR);
        $prepared->bindParam(':level', $data['level'], PDO::PARAM_INT);
        $prepared->bindParam(':event_id', $data['event_id'], PDO::PARAM_STR);
        $prepared->bindParam(':originator', $data['originator'], PDO::PARAM_STR);
        $encoded_ip = @inet_pton($data['ip_address']);
        $prepared->bindParam(':ip_address', $encoded_ip, PDO::PARAM_LOB);
        $prepared->bindParam(':time', $data['time'], PDO::PARAM_INT);
        $prepared->bindParam(':message', $data['message'], PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }
}