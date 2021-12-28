<?php
declare(strict_types = 1);

namespace Nelliel\Logging;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Nelliel\NellielPDO;
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
        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_LOGS_TABLE .
            '" ("level", "channel", "event", "message", "time", "domain_id", "user_id", "ip_address", "hashed_ip_address", "moar")
								VALUES (:level, :channel, :event, :message, :time, :domain_id, :user_id, :ip_address, :hashed_ip_address, :moar)');
        $prepared->bindValue(':level', $record['level'], PDO::PARAM_INT);
        $prepared->bindValue(':channel', $record['channel'], PDO::PARAM_STR);
        $prepared->bindValue(':event', $record['extra']['event'], PDO::PARAM_STR);
        $prepared->bindValue(':message', $record['message'], PDO::PARAM_STR);
        $prepared->bindValue(':time', $record['datetime']->format('U'), PDO::PARAM_INT);
        $prepared->bindValue(':domain_id', $record['extra']['domain_id'], PDO::PARAM_STR);
        $prepared->bindValue(':user_id', $record['extra']['user_id'], PDO::PARAM_STR);
        $prepared->bindValue(':ip_address', $record['extra']['ip_address'], PDO::PARAM_LOB);
        $prepared->bindValue(':hashed_ip_address', $record['extra']['hashed_ip_address'], PDO::PARAM_STR);
        $prepared->bindValue(':moar', $record['extra']['moar'], PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }
}