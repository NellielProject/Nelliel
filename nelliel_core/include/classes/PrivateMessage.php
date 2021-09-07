<?php
declare(strict_types = 1);

namespace Nelliel;

use Nelliel\Tables\TablePrivateMessages;
use PDO;
use Nelliel\Account\Session;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class PrivateMessage
{
    private $database;
    private $session;
    private $sql_compatibility;
    private $table;
    private $message_id = 0;
    private $data = array();

    function __construct(NellielPDO $database, Session $session, int $message_id = 0)
    {
        $this->database = $database;
        $this->session = $session;
        $this->sql_compatibility = nel_utilities()->sqlCompatibility();
        $this->table = new TablePrivateMessages($this->database, $this->sql_compatibility);
        $this->changeData('entry', $message_id);

        if ($message_id > 0)
        {
            $this->message_id = $message_id;
            $this->load();
        }
    }

    public function load(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_PRIVATE_MESSAGES_TABLE . '" WHERE "entry" = ?');
        $prepared->bindValue(1, $this->id(), PDO::PARAM_INT);
        $data = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if (is_array($data))
        {
            $this->data = $data;
        }
    }

    public function id(): int
    {
        return $this->message_id;
    }

    public function collectFromPOST(): void
    {
        $this->changeData('sender', $this->session->user()->id());
        $this->changeData('recipient', $_POST['recipient'] ?? '');
        $this->changeData('message', $_POST['message'] ?? '');
    }

    public function data(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function changeData(string $key, $data)
    {
        $column_types = $this->table->columnTypes();
        $type = $column_types[$key]['php_type'] ?? '';
        $new_data = nel_typecast($data, $type);
        $old_data = $this->data($key);
        $this->data[$key] = $new_data;
        return $old_data;
    }

    public function send(): void
    {
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_PRIVATE_MESSAGES_TABLE .
                '" ("sender", "recipient", "message", "time") VALUES (?, ?, ?, ?) ' .
                $this->sql_compatibility->return(NEL_SQLTYPE) . ' "entry"');
        $prepared->bindValue(1, $this->data('sender'), PDO::PARAM_STR);
        $prepared->bindValue(2, $this->data('recipient'), PDO::PARAM_STR);
        $prepared->bindValue(3, $this->data('message'), PDO::PARAM_STR);
        $prepared->bindValue(4, time(), PDO::PARAM_INT);
        $message_id = $this->database->executePreparedFetch($prepared, [], PDO::FETCH_COLUMN);

        if ($message_id !== false)
        {
            $this->message_id = $message_id;
        }

        $this->load();
    }

    public function markRead(): void
    {
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_PRIVATE_MESSAGES_TABLE . '" SET "message_read" = 1 WHERE "entry" = ?');
        $prepared->bindValue(1, $this->message_id, PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    public function delete(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_PRIVATE_MESSAGES_TABLE . '" WHERE "entry" = ?');
        $prepared->bindValue(1, $this->message_id, PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }
}
