<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class ThreadHandler
{
    private $dbh;
    private $file_handler;
    private $board_id;

    function __construct($board_id)
    {
        $this->dbh = nel_database();
        $this->file_handler = new \Nelliel\FileHandler();
        $this->board_id = $board_id;
    }

    public function processContentDeletes()
    {
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);
        $returned_list = array();
        $update_archive = false;

        foreach ($_POST as $name => $value)
        {
            if (\Nelliel\ContentID::isContentID($name))
            {
                $content_id = new \Nelliel\ContentID($name);
            }
            else
            {
                continue;
            }

            if ($value === 'action')
            {
                if ($content_id->isThread())
                {
                    $this->removeThread($content_id);
                    $update_archive = true;
                }
                else if ($content_id->isPost())
                {
                    $this->removePost($content_id);
                }
                else if ($content_id->isFile())
                {
                    $this->removeFile($content_id);
                }
            }

            if (!in_array($content_id->thread_id, $returned_list))
            {
                array_push($returned_list, $content_id->thread_id);
            }
        }

        if ($update_archive)
        {
            $archive = new \Nelliel\ArchiveAndPrune($this->board_id);
            $archive->updateAllArchiveStatus();

            if ($board_settings['old_threads'] === 'ARCHIVE')
            {
                $archive->moveThreadsToArchive();
                $archive->moveThreadsFromArchive();
            }
            else if ($board_settings['old_threads'] === 'PRUNE')
            {
                $archive->pruneThreads();
            }
        }

        return $returned_list;
    }

    public function getPostData($post_id)
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $this->dbh->prepare(
                'SELECT * FROM "' . $board_references['post_table'] . '" WHERE "post_number" = ? LIMIT 1');
        return $this->dbh->executePreparedFetch($prepared, array($post_id), PDO::FETCH_ASSOC, true);
    }

    /*public function getPostFiles($post_id)
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $this->dbh->prepare('SELECT * FROM "' . $board_references['file_table'] . '" WHERE "post_ref" = ?');
        return $this->dbh->executePreparedFetchAll($prepared, array($post_id), PDO::FETCH_ASSOC);
    }

    public function getPostParentThreadId($post_id)
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $this->dbh->prepare(
                'SELECT "parent_thread" FROM "' . $board_references['post_table'] . '" WHERE "post_number" = ? LIMIT 1');
        return $this->dbh->executePreparedFetch($prepared, array($post_id), PDO::FETCH_COLUMN, true);
    }

    public function getThreadData($thread_id)
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $this->dbh->prepare(
                'SELECT * FROM "' . $board_references['thread_table'] . '" WHERE "thread_id" = ? LIMIT 1');
        return $this->dbh->executePreparedFetch($prepared, array($thread_id), PDO::FETCH_ASSOC, true);
    }

    public function getAllThreadPosts($thread_id)
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $this->dbh->prepare(
                'SELECT * FROM "' . $board_references['post_table'] .
                '" WHERE "parent_thread" = ? ORDER BY "post_number"');
        return $this->dbh->executePreparedFetchAll($prepared, array($thread_id), PDO::FETCH_ASSOC);
    }*/

    /*public function getNextToLastPostInThread($thread_id, $no_sage = false)
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);

        if ($no_sage)
        {
            $prepared = $this->dbh->prepare(
                    'SELECT *  FROM "' . $board_references['post_table'] .
                    '" WHERE "parent_thread" = ? AND "sage" = 0 ORDER BY "post_number" DESC LIMIT 2');
        }
        else
        {
            $prepared = $this->dbh->prepare(
                    'SELECT *  FROM "' . $board_references['post_table'] .
                    '" WHERE "parent_thread" = ? ORDER BY "post_number" DESC LIMIT 2');
        }

        $post_data = $this->dbh->executePreparedFetchAll($prepared, array($thread_id), PDO::FETCH_ASSOC);

        if (array_key_exists(1, $post_data))
        {
            return $post_data[1];
        }

        return false;
    }

    public function getLastPostInThread($thread_id, $no_sage = false)
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);

        if ($no_sage)
        {
            $prepared = $this->dbh->prepare(
                    'SELECT *  FROM "' . $board_references['post_table'] .
                    '" WHERE "parent_thread" = ? AND "sage" = 0 ORDER BY "post_number" DESC LIMIT 1');
        }
        else
        {
            $prepared = $this->dbh->prepare(
                    'SELECT *  FROM "' . $board_references['post_table'] .
                    '" WHERE "parent_thread" = ? ORDER BY "post_number" DESC LIMIT 1');
        }

        return $this->dbh->executePreparedFetch($prepared, array($thread_id), PDO::FETCH_ASSOC, true);
    }*/

    public function convertPostToThread($post_id)
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        nel_create_thread_directories($post_id);
        $post_data = $this->getPostData($post_id);
        $columns = array('thread_id', 'first_post', 'last_post', 'last_bump_time', 'total_files', 'last_update',
            'post_count', 'sticky');
        $values = $this->dbh->generateParameterIds($columns);
        $query = $this->dbh->buildBasicInsertQuery($board_references['thread_table'], $columns, $values);
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(':thread_id', $post_id, PDO::PARAM_INT);
        $prepared->bindValue(':first_post', $post_id, PDO::PARAM_INT);
        $prepared->bindValue(':last_post', $post_id, PDO::PARAM_INT);
        $prepared->bindValue(':last_bump_time', $post_data['post_time'], PDO::PARAM_INT);
        $prepared->bindValue(':total_files', $post_data['file_count'], PDO::PARAM_INT);
        $prepared->bindValue(':last_update', $thread_info['last_update'], PDO::PARAM_INT);
        $prepared->bindValue(':post_count', 1, PDO::PARAM_INT);
        $prepared->bindValue(':sticky', 1, PDO::PARAM_INT);
        $this->dbh->executePrepared($prepared);

        $prepared = $dbh->prepare(
                'UPDATE "' . $board_references['post_table'] .
                '" SET "parent_thread" = ?, "op" = 1 WHERE "post_number" = ?');
        $dbh->executePrepared($prepared, array($post_id, $post_id));

        if ($post_data['has_file'])
        {
            $src_path = $this->file_handler->pathFileJoin($board_references['src_path'], $post_data['parent_thread']);
            $thumb_path = $this->file_handler->pathFileJoin($board_references['thumb_path'], $post_data['parent_thread']);
            $src_dest = $this->file_handler->pathFileJoin($board_references['src_path'], $post_id);
            $thumb_dest = $this->file_handler->pathFileJoin($board_references['thumb_path'], $post_id);

            $prepared = $this->dbh->prepare(
                    'UPDATE "' . $board_references['file_table'] . '" SET "parent_thread" = ? WHERE "post_ref" = ?');
            $this->dbh->executePrepared($prepared, array($post_id, $post_id));

            $prepared = $this->dbh->prepare(
                    'SELECT "filename", "extension", "preview_name", "preview_extension" FROM "' .
                    $board_references['file_table'] . '" WHERE "post_ref" = ?');
            $file_data = $this->dbh->executePreparedFetchAll($prepared, array($post_id), PDO::FETCH_ASSOC);
            $file_count = count($file_data);
            $line = 0;

            while ($line < $file_count)
            {
                $filename = $file_data[$line]['filename'] . '.' . $file_data[$line]['extension'];
                $preview = $file_data[$line]['preview_name'] . '.' . $file_data[$line]['preview_extension'];
                $this->file_handler->moveFile($this->file_handler->pathFileJoin($src_path, $filename),
                        $this->file_handler->pathFileJoin($src_dest, $filename));
                $this->file_handler->moveFile($this->file_handler->pathFileJoin($thumb_path, $preview),
                        $this->file_handler->pathFileJoin($thumb_dest, $preview));
                ++ $line;
            }
        }
    }

    public function removeFile($content_id)
    {
        $file = new \Nelliel\ContentFile($this->dbh, $content_id, $this->board_id);
        $this->verifyDeletePerms($content_id->post_id);
        $file->remove();
    }

    public function removePost($content_id)
    {
        $post = new \Nelliel\ContentPost($this->dbh, $content_id, $this->board_id);
        $this->verifyDeletePerms($content_id->post_id);
        $post->remove();
    }


    public function removeThread($content_id)
    {
        $thread = new \Nelliel\ContentThread($this->dbh, $content_id, $this->board_id);
        $this->verifyDeletePerms($content_id->thread_id);
        $thread->remove();
    }

    public function verifyDeletePerms($post_id)
    {
        $authorize = nel_authorize();
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);

        if (!is_numeric($post_id))
        {
            nel_derp(30, _gettext('Id of thread or post was non-numeric. How did you even do that?'));
        }

        $prepared = $this->dbh->prepare(
                'SELECT "post_password", "mod_post" FROM "' . $board_references['post_table'] .
                '" WHERE "post_number" = ? LIMIT 1');
        $post_data = $this->dbh->executePreparedFetch($prepared, array($post_id), PDO::FETCH_ASSOC, true);

        if ($post_data === false)
        {
            return false;
        }

        $flag = false;

        if (nel_sessions()->sessionIsActive())
        {
            $flag = $authorize->roleLevelCheck($authorize->userHighestLevelRole($_SESSION['username'], $this->board_id),
                    $authorize->userHighestLevelRole($post_data['mod_post'], $this->board_id));

            if (!$flag)
            {
                $flag = nel_verify_salted_hash($_POST['update_sekrit'], $post_data['post_password']);
            }
        }
        else
        {
            $flag = nel_verify_salted_hash($_POST['update_sekrit'], $post_data['post_password']);
        }

        if (!$flag)
        {
            nel_derp(31, _gettext('Password is wrong or you are not allowed to delete that.'));
        }

        return true;
    }
}