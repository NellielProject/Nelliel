<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ContentThread extends ContentBase
{

    function __construct($database, $content_id, $board_id)
    {
        $this->database = $database;
        $this->content_id = $content_id;
        $this->board_id = $board_id;
    }

    private function validThreadData($data_name, $default)
    {
        if (isset($this->thread_data[$data_name]))
        {
            return $this->thread_data[$data_name];
        }

        return $default;
    }

    public function loadFromDatabase($temp_database = null)
    {
        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $database->prepare(
                'SELECT * FROM "' . $board_references['thread_table'] . '" WHERE "thread_id" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->content_id->thread_id], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        $this->thread_data = $result;
        return true;
    }

    public function removeFromDatabase($temp_database = null)
    {
        if (empty($this->content_id->thread_id))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $database->prepare('DELETE FROM "' . $board_references['thread_table'] . '" WHERE "thread_id" = ?');
        $database->executePrepared($prepared, [$this->content_id->thread_id]);
        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->thread_data) || empty($this->content_id->thread_id))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $references = nel_parameters_and_data()->boardReferences($this->board_id);
        $columns = array('thread_id', 'first_post', 'last_post', 'last_bump_time', 'total_files', 'last_update',
            'post_count', 'thread_sage', 'sticky', 'archive_status', 'locked');
        $values = $database->generateParameterIds($columns);
        $query = $database->buildBasicInsertQuery($references['thread_table'], $columns, $values);
        $prepared = $database->prepare($query);
        $prepared->bindValue(':thread_id', $this->content_id->thread_id, PDO::PARAM_INT);
        $prepared->bindValue(':first_post', $this->validThreadData('first_post', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_post', $this->validThreadData('last_post', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_bump_time', $this->validThreadData('last_bump_time', 0), PDO::PARAM_INT);
        $prepared->bindValue(':total_files', $this->validThreadData('total_files', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_update', $this->validThreadData('last_update', 0), PDO::PARAM_INT);
        $prepared->bindValue(':post_count', $this->validThreadData('post_count', 0), PDO::PARAM_INT);
        $prepared->bindValue(':thread_sage', $this->validThreadData('thread_sage', 0), PDO::PARAM_INT);
        $prepared->bindValue(':sticky', $this->validThreadData('sticky', 0), PDO::PARAM_INT);
        $prepared->bindValue(':archive_status', $this->validThreadData('archive_status', 0), PDO::PARAM_INT);
        $prepared->bindValue(':locked', $this->validThreadData('locked', 0), PDO::PARAM_INT);
        $database->executePrepared($prepared);
        return true;
    }
}