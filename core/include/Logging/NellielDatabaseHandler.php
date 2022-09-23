<?php
declare(strict_types = 1);

namespace Nelliel\Logging;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Nelliel\Database\NellielPDO;
use PDO;

class NellielDatabaseHandler extends AbstractProcessingHandler
{
    private $database;

    function __construct(NellielPDO $database, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->database = $database;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        if ($record['channel'] === 'system') {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_SYSTEM_LOGS_TABLE .
                '" ("level", "event", "message", "time", "domain_id", "username", "ip_address", "hashed_ip_address", "visitor_id", "moar")
								VALUES (:level, :event, :message, :time, :domain_id, :username, :ip_address, :hashed_ip_address, :visitor_id, :moar)');
        }

        if ($record['channel'] === 'public') {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_PUBLIC_LOGS_TABLE .
                '" ("level", "event", "message", "time", "domain_id", "username", "ip_address", "hashed_ip_address", "visitor_id", "moar")
								VALUES (:level, :event, :message, :time, :domain_id, :username, :ip_address, :hashed_ip_address, :visitor_id, :moar)');
        }

        $prepared->bindValue(':level', $record['level'], PDO::PARAM_INT);
        $prepared->bindValue(':event', $record['extra']['event'], PDO::PARAM_STR);
        $prepared->bindValue(':message', $record['message'], PDO::PARAM_STR);
        $prepared->bindValue(':time', $record['datetime']->format('U'), PDO::PARAM_INT);
        $prepared->bindValue(':domain_id', $record['extra']['domain_id'], PDO::PARAM_STR);
        $prepared->bindValue(':username', $record['extra']['username'], PDO::PARAM_STR);
        $prepared->bindValue(':ip_address', nel_prepare_ip_for_storage($record['extra']['ip_address']), PDO::PARAM_LOB);
        $prepared->bindValue(':hashed_ip_address', $record['extra']['hashed_ip_address'], PDO::PARAM_STR);
        $prepared->bindValue(':visitor_id', $record['extra']['visitor_id'], PDO::PARAM_STR);
        $prepared->bindValue(':moar', $record['extra']['moar'], PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }
}