<?php

declare(strict_types=1);


namespace Nelliel\Utility;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use PDO;

class NellielLogger extends AbstractLogger
{
    private $database;
    private $level_map = [LogLevel::EMERGENCY => 0, LogLevel::ALERT => 1, LogLevel::CRITICAL => 2, LogLevel::ERROR => 3,
        LogLevel::WARNING => 4, LogLevel::NOTICE => 5, LogLevel::INFO => 6, LogLevel::DEBUG => 7];

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function log($level, $message, array $context = array())
    {
        if (!array_key_exists($level, $this->level_map) || $this->level_map[$level] < 0 || $this->level_map[$level] > 7)
        {
            throw new InvalidArgumentException(_gettext('Invalid log level. Level given: ') . $level);
        }

        $data = array();
        $data['domain'] = $context['domain'] ?? '';
        $data['level'] = $this->level_map[$level];
        $data['event_id'] = $context['event_id'] ?? 'UNKNOWN';
        $data['originator'] = $context['originator'] ?? null;
        $data['ip_address'] = $context['ip_address'] ?? null;
        $data['hashed_ip_address'] = $context['hashed_ip_address'] ?? null;
        $data['time'] = time();
        $data['message'] = $message;
        $table = $data['domain']->reference('log_table') ?? NEL_LOGS_TABLE;
        $this->dbInsert($table, $data);
    }

    protected function dbInsert(string $table, array $data)
    {
        $prepared = $this->database->prepare(
                'INSERT INTO "' . $table .
                '" ("level", "domain_id",  "event_id", "originator", "ip_address", "hashed_ip_address", "time", "message")
								VALUES (:level, :domain_id, :event_id, :originator, :ip_address, :hashed_ip_address, :time, :message)');
        $prepared->bindValue(':level', $data['level'], PDO::PARAM_INT);
        $prepared->bindValue(':domain_id', $data['domain']->id(), PDO::PARAM_STR);
        $prepared->bindValue(':event_id', $data['event_id'], PDO::PARAM_STR);
        $prepared->bindValue(':originator', $data['originator'], PDO::PARAM_STR);
        $prepared->bindValue(':ip_address', nel_prepare_ip_for_storage($data['ip_address']), PDO::PARAM_LOB);
        $prepared->bindValue(':hashed_ip_address', nel_prepare_hash_for_storage($data['hashed_ip_address']),
                PDO::PARAM_LOB);
        $prepared->bindValue(':time', $data['time'], PDO::PARAM_INT);
        $prepared->bindValue(':message', $data['message'], PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }
}