<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Database\NellielPDO;
use Nelliel\Output\OutputPrivateMessages;
use Nelliel\Tables\TablePrivateMessages;
use PDO;

class PrivateMessage
{
    private $database;
    private $session;
    private $sql_compatibility;
    private $table;
    private $message_id;
    private $data = array();

    function __construct(NellielPDO $database, Session $session, int $message_id = null)
    {
        $this->database = $database;
        $this->session = $session;
        $this->sql_compatibility = nel_utilities()->sqlCompatibility();
        $this->table = new TablePrivateMessages($this->database, $this->sql_compatibility);
        $this->changeData('message_id', $message_id);

        if (!is_null($message_id)) {
            $this->message_id = $message_id;
            $this->load();
        }
    }

    public function load(): void
    {
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_PRIVATE_MESSAGES_TABLE . '" WHERE "message_id" = ?');
        $prepared->bindValue(1, $this->id(), PDO::PARAM_INT);
        $data = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if (is_array($data)) {
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
        $this->changeData('recipient', utf8_strtolower($_POST['recipient'] ?? ''));
        $this->changeData('message', utf8_strtolower($_POST['message'] ?? ''));
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

    public function reply()
    {
        $this->canAccess();
        $output_private_messages = new OutputPrivateMessages(nel_site_domain(), false);
        $output_private_messages->newMessage(['reply_id' => $this->message_id], false);
        nel_clean_exit();
    }

    public function view()
    {
        $this->canAccess();
        $output_private_messages = new OutputPrivateMessages(nel_site_domain(), false);
        $output_private_messages->viewMessage(['message_id' => $this->message_id], false);
        nel_clean_exit();
    }

    public function send(): void
    {
        $this->changeData('time_sent', time());
        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_PRIVATE_MESSAGES_TABLE .
            '" ("sender", "recipient", "message", "time_sent") VALUES (?, ?, ?, ?) ');
        $prepared->bindValue(1, $this->data('sender'), PDO::PARAM_STR);
        $prepared->bindValue(2, $this->data('recipient'), PDO::PARAM_STR);
        $prepared->bindValue(3, $this->data('message'), PDO::PARAM_STR);
        $prepared->bindValue(4, $this->data('time_sent'), PDO::PARAM_INT);
        $this->database->executePrepared($prepared);

        $prepared = $this->database->prepare(
            'SELECT "message_id" FROM "' . NEL_PRIVATE_MESSAGES_TABLE .
            '" WHERE "sender" = ? AND "recipient" = ? AND "time_sent" = ?');
        $prepared->bindValue(1, $this->data('sender'), PDO::PARAM_STR);
        $prepared->bindValue(2, $this->data('recipient'), PDO::PARAM_STR);
        $prepared->bindValue(3, $this->data('time_sent'), PDO::PARAM_INT);
        $message_id = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

        if ($message_id !== false) {
            $this->message_id = $message_id;
        }
    }

    public function markRead(): void
    {
        $this->canAccess();
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_PRIVATE_MESSAGES_TABLE . '" SET "message_read" = 1 WHERE "message_id" = ?');
        $prepared->bindValue(1, $this->message_id, PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    public function delete(): void
    {
        $this->canAccess();
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_PRIVATE_MESSAGES_TABLE . '" WHERE "message_id" = ?');
        $prepared->bindValue(1, $this->message_id, PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    public function canAccess()
    {
        if ($this->data('sender') !== $this->session->user()->id() &&
            $this->data('recipient') !== $this->session->user()->id() &&
            !$this->session->user()->checkPermission(nel_site_domain(), 'perm_manage_private_messages')) {
            nel_derp(225, __('You cannot access that private message.'));
        }
    }
}
