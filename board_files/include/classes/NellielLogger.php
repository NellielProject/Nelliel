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

    public function emergency(string $message, array $context = array())
    {
        $this->log(0, $message, $context);
    }

    public function alert(string $message, array $context = array())
    {
        $this->log(1, $message, $context);
    }

    public function critical(string $message, array $context = array())
    {
        $this->log(2, $message, $context);
    }

    public function error(string $message, array $context = array())
    {
        $this->log(3, $message, $context);
    }

    public function warning(string $message, array $context = array())
    {
        $this->log(4, $message, $context);
    }

    public function notice(string $message, array $context = array())
    {
        $this->log(5, $message, $context);
    }

    public function info(string $message, array $context = array())
    {
        $this->log(6, $message, $context);
    }

    public function debug(string $message, array $context = array())
    {
        $this->log(7, $message, $context);
    }

    public function log($level, string $message, array $context = array())
    {
        $data = array();

        if (!is_int($level))
        {
            ; // Handle non-int levels
        }
        else
        {
            $data['level'] = $level;
        }

        $data['area'] = 0;
        $data['event_id'] = isset($context['event_id']) ?? 'UNKNOWN';
        $data['originator_id'] = isset($context['originator_id']) ?? 'UNKNOWN';
        $data['ip_address'] = isset($context['ip_address']) ?? null;
        $data['time'] = time();
        $data['message'] = $message;

        $prepared = $this->database->prepare(
                'INSERT INTO "' . $context['table'] .
                '" ("area", "level", "event_id", "originator_id", "ip_address", "time", "message")
								VALUES (:area, :level, :event_id, :originator_id, :ip_address, :time, :message)');
        $prepared->bindParam(':area', $data['area'], PDO::PARAM_STR);
        $prepared->bindParam(':level', $data['level'], PDO::PARAM_INT);
        $prepared->bindParam(':event_id', $data['event_id'], PDO::PARAM_STR);
        $prepared->bindParam(':originator_id', $data['originator_id'], PDO::PARAM_STR);
        $encoded_ip = @inet_pton($data['ip_address']);
        $prepared->bindParam(':ip_address', $encoded_ip, PDO::PARAM_LOB);
        $prepared->bindParam(':time', $data['time'], PDO::PARAM_INT);
        $prepared->bindParam(':message', $data['message'], PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }
}