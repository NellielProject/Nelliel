<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use \PDO;

class ThreadHandler
{
    private $dbh;
    private $file_handler;

    function __construct()
    {
        $this->dbh = nel_database();
        $this->file_handler = nel_file_handler();
    }

    public function stickyThread($thread_id, $post_id = null)
    {
        if (!is_null($post_id))
        {
            $prepared = $this->dbh->prepare('SELECT "parent_thread" FROM "' . POST_TABLE .
                 '" WHERE "post_number" = ? LIMIT 1');
            $post_data = $this->dbh->executePreparedFetch($prepared, array($post_id), PDO::FETCH_ASSOC, true);

            // If this is not already a thread, make the post into one
            if ($post_data['parent_thread'] != $post_id)
            {
                $this->convertPostToThread($post_id);
            }
        }

        $prepared = $this->dbh->prepare('UPDATE "' . THREAD_TABLE . '" SET "sticky" = 1 WHERE "thread_id" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        return;
    }

    public function unStickyThread($thread_id)
    {
        $prepared = $this->dbh->prepare('UPDATE "' . THREAD_TABLE . '" SET "sticky" = 0 WHERE "thread_id" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        return;
    }

    public function getPostData($post_id)
    {
        $prepared = $this->dbh->prepare('SELECT * FROM "' . POST_TABLE . '" WHERE "post_number" = ? LIMIT 1');
        return $this->dbh->executePreparedFetch($prepared, array($post_id), PDO::FETCH_ASSOC, true);
    }

    public function getPostFiles($post_id)
    {
        $prepared = $this->dbh->prepare('SELECT * FROM "' . FILE_TABLE . '" WHERE "post_ref" = ?');
        return $this->dbh->executePreparedFetchAll($prepared, array($post_id), PDO::FETCH_ASSOC);
    }

    public function getPostParentThreadId($post_id)
    {
        $prepared = $this->dbh->prepare('SELECT "parent_thread" FROM "' . POST_TABLE .
             '" WHERE "post_number" = ? LIMIT 1');
        return $this->dbh->executePreparedFetch($prepared, array($post_id), PDO::FETCH_COLUMN, true);
    }

    public function getThreadData($thread_id)
    {
        $prepared = $this->dbh->prepare('SELECT * FROM "' . THREAD_TABLE . '" WHERE "thread_id" = ? LIMIT 1');
        return $this->dbh->executePreparedFetch($prepared, array($thread_id), PDO::FETCH_ASSOC, true);
    }

    public function getAllThreadPosts($thread_id)
    {
        $prepared = $this->dbh->prepare('SELECT * FROM "' . POST_TABLE .
             '" WHERE "parent_thread" = ? ORDER BY "post_number"');
        return $this->dbh->executePreparedFetchAll($prepared, array($thread_id), PDO::FETCH_ASSOC);
    }

    public function getNextToLastPostInThread($thread_id, $no_sage = false)
    {
        if ($no_sage)
        {
            $prepared = $this->dbh->prepare('SELECT *  FROM "' . POST_TABLE .
                 '" WHERE "parent_thread" = ? AND "sage" = 0 ORDER BY "post_number" DESC LIMIT 2');
        }
        else
        {
            $prepared = $this->dbh->prepare('SELECT *  FROM "' . POST_TABLE .
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
        if ($no_sage)
        {
            $prepared = $this->dbh->prepare('SELECT *  FROM "' . POST_TABLE .
                 '" WHERE "parent_thread" = ? AND "sage" = 0 ORDER BY "post_number" DESC LIMIT 1');
        }
        else
        {
            $prepared = $this->dbh->prepare('SELECT *  FROM "' . POST_TABLE .
                 '" WHERE "parent_thread" = ? ORDER BY "post_number" DESC LIMIT 1');
        }

        return $this->dbh->executePreparedFetch($prepared, array($thread_id), PDO::FETCH_ASSOC, true);
    }

    public function convertPostToThread($post_id)
    {
        nel_create_thread_directories($post_id);
        $post_data = $this->getPostData($post_id);
        $columns = array('thread_id', 'first_post', 'last_post', 'last_bump_time', 'total_files', 'total_externals',
            'last_update', 'post_count', 'sticky');
        $values = $this->dbh->generateParameterIds($columns);
        $query = $this->dbh->buildBasicInsertQuery(THREAD_TABLE, $columns, $values);
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(':thread_id', $post_id, PDO::PARAM_INT);
        $prepared->bindValue(':first_post', $post_id, PDO::PARAM_INT);
        $prepared->bindValue(':last_post', $post_id, PDO::PARAM_INT);
        $prepared->bindValue(':last_bump_time', $post_data['post_time'], PDO::PARAM_INT);
        $prepared->bindValue(':total_files', $post_data['file_count'], PDO::PARAM_INT);
        $prepared->bindValue(':total_externals', $post_data['external_count'], PDO::PARAM_INT);
        $prepared->bindValue(':last_update', $thread_info['last_update'], PDO::PARAM_INT);
        $prepared->bindValue(':post_count', 1, PDO::PARAM_INT);
        $prepared->bindValue(':sticky', 1, PDO::PARAM_INT);
        $this->dbh->executePrepared($prepared);

        $prepared = $dbh->prepare('UPDATE "' . POST_TABLE .
             '" SET "parent_thread" = ?, "op" = 1 WHERE "post_number" = ?');
        $dbh->executePrepared($prepared, array($post_id, $post_id));

        if ($post_data['has_file'])
        {
            $src_path = $this->file_handler->pathFileJoin(SRC_PATH, $post_data['parent_thread']);
            $thumb_path = $this->file_handler->pathFileJoin(THUMB_PATH, $post_data['parent_thread']);
            $src_dest = $this->file_handler->pathFileJoin(SRC_PATH, $post_id);
            $thumb_dest = $this->file_handler->pathFileJoin(THUMB_PATH, $post_id);

            $prepared = $this->dbh->prepare('UPDATE "' . FILE_TABLE . '" SET "parent_thread" = ? WHERE "post_ref" = ?');
            $this->dbh->executePrepared($prepared, array($post_id, $post_id));

            $prepared = $this->dbh->prepare('SELECT "filename", "extension", "preview_name" FROM "' . FILE_TABLE .
                 '" WHERE "post_ref" = ?');
            $file_data = $this->dbh->executePreparedFetchAll($prepared, array($post_id), PDO::FETCH_ASSOC);
            $file_count = count($file_data);
            $line = 0;

            while ($line < $file_count)
            {
                $filename = $file_data[$line]['filename'] . $file_data[$line]['extension'];
                $preview = $file_data[$line]['preview_name'];
                $this->file_handler->moveFile($this->file_handler->pathFileJoin($src_path, $filename), $this->file_handler->pathFileJoin($src_dest, $filename));
                $this->file_handler->moveFile($this->file_handler->pathFileJoin($thumb_path, $preview), $this->file_handler->pathFileJoin($thumb_dest, $preview));
                ++ $line;
            }
        }
    }

    public function removePost($post_id)
    {
        $post_data = $this->getPostData($post_id);
        $post_files = $this->getPostFiles($post_id);
        $this->removePostFilesFromDatabase($post_id, null, count($post_files));
        $this->removePostFilesFromDisk($post_id);
        $this->removePostFromDatabase($post_id);
    }

    public function removePostFromDatabase($post_id)
    {
        $post_data = $this->getPostData($post_id);
        $prepared = $this->dbh->prepare('DELETE FROM "' . POST_TABLE . '" WHERE "post_number" = ?');
        $this->dbh->executePrepared($prepared, array($post_id));
        $thread_id = $post_data['parent_thread'];
        $thread_data = $this->getThreadData($thread_id);
        $new_count = $thread_data['post_count'] - 1;
        $new_last = $this->getLastPostInThread($thread_id);
        $last_bump = $new_last['post_time'];
        $total_files = $thread_data['total_files'] - $post_data['file_count'];

        if ($new_last['sage'] != 0)
        {
            $last_nosage = $this->getLastPostInThread($thread_id, true);
            $last_bump = $last_nosage['post_time'];
        }

        $prepared = $this->dbh->prepare('UPDATE "' . THREAD_TABLE .
             '" SET "post_count" = ?, "last_post" = ?, "last_update" = ?, "last_bump_time" = ?, "total_files" = ? WHERE "thread_id" = ?');
        $prepared->bindValue(1, $new_count, PDO::PARAM_INT);
        $prepared->bindValue(2, $new_last['post_number'], PDO::PARAM_INT);
        $prepared->bindValue(3, $new_last['post_time'], PDO::PARAM_INT);
        $prepared->bindValue(4, $last_bump, PDO::PARAM_INT);
        $prepared->bindValue(5, $total_files, PDO::PARAM_INT);
        $prepared->bindValue(6, $post_data['parent_thread'], PDO::PARAM_INT);
        $this->dbh->executePrepared($prepared, null, true);
    }

    public function removePostFilesFromDatabase($post_ref, $order = null, $quantity = 1)
    {
        if (is_null($order))
        {
            $prepared = $this->dbh->prepare('DELETE FROM "' . FILE_TABLE . '" WHERE "post_ref" = ?');
            $this->dbh->executePrepared($prepared, array($post_ref));
        }
        else
        {
            $prepared = $this->dbh->prepare('DELETE FROM "' . FILE_TABLE . '" WHERE "post_ref" = ? AND "file_order" = ?');
            $this->dbh->executePrepared($prepared, array($post_ref, $order));
        }

        $this->subtractFromFileCount($post_ref, $quantity);
    }

    public function removePostFilesFromDisk($post_id, $file_order = null)
    {
        $thread_id = $this->getPostParentThreadId($post_id);

        if (is_null($file_order))
        {
            $prepared = $this->dbh->prepare('SELECT "filename", "extension", "preview_name" FROM "' . FILE_TABLE .
                 '" WHERE "post_ref" = ?');
        }
        else
        {
            $prepared = $this->dbh->prepare('SELECT "filename", "extension", "preview_name" FROM "' . FILE_TABLE .
                 '" WHERE "post_ref" = ? AND "file_order" = ?');
        }

        $file_data = $this->dbh->executePreparedFetchAll($prepared, array($post_id, $file_order), PDO::FETCH_ASSOC, true);

        if ($file_data !== false)
        {
            foreach ($file_data as $file)
            {
                $filename = $file['filename'] . '.' . $file['extension'];
                $this->file_handler->eraserGun($this->file_handler->pathJoin(SRC_PATH, $thread_id), $filename);
                $this->file_handler->eraserGun($this->file_handler->pathJoin(THUMB_PATH, $thread_id), $file['preview_name']);
            }
        }
    }

    public function removeThread($thread_id)
    {
        $this->verifyDeletePerms($thread_id);
        $this->removeThreadFromDatabase($thread_id);
        $this->removeThreadFilesFromDatabase($thread_id);
        nel_delete_thread_directories($thread_id);
    }

    public function removeThreadFromDatabase($thread_id)
    {
        $prepared = $this->dbh->prepare('DELETE FROM "' . POST_TABLE . '" WHERE "parent_thread" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare('DELETE FROM "' . THREAD_TABLE . '" WHERE "thread_id" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $this->removeThreadFilesFromDatabase($ref);
    }

    public function removeThreadFilesFromDatabase($thread_id)
    {
        $prepared = $this->dbh->prepare('DELETE FROM "' . FILE_TABLE . '" WHERE "parent_thread" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
    }

    public function subtractFromFileCount($post_id, $quantity)
    {
        $prepared = $this->dbh->prepare('SELECT "parent_thread", "file_count", "has_file" FROM "' . POST_TABLE .
             '" WHERE "post_number" = ? LIMIT 1');
        $post_files = $this->dbh->executePreparedFetch($prepared, array($post_id), PDO::FETCH_ASSOC, true);
        $post_files['file_count'] -= $quantity;
        $thread_id = $post_files['parent_thread'];

        if ($post_files['file_count'] <= 0)
        {
            $post_files['file_count'] = 0;
            $post_files['has_file'] = 0;
        }

        $prepared = $this->dbh->prepare('SELECT "total_files" FROM "' . THREAD_TABLE . '" WHERE "thread_id" = ? LIMIT 1');
        $total_files = $this->dbh->executePreparedFetch($prepared, array($thread_id), PDO::FETCH_COLUMN, true);
        $total_files -= $quantity;

        if ($total_files <= 0)
        {
            $total_files = 0;
        }

        $prepared = $this->dbh->prepare('UPDATE "' . POST_TABLE .
             '" SET "has_file" = ?, "file_count" = ? WHERE "post_number" = ?');
        $this->dbh->executePrepared($prepared, array($post_files['has_file'], $post_files['file_count'], $post_id));
        $prepared = $this->dbh->prepare('UPDATE "' . THREAD_TABLE . '" SET "total_files" = ? WHERE "thread_id" = ?');
        $this->dbh->executePrepared($prepared, array($total_files, $thread_id));
    }

    function createThreadDirectories($thread_id)
    {
        $this->file_handler->createDirectory(SRC_PATH . $thread_id, DIRECTORY_PERM);
        $this->file_handler->createDirectory(THUMB_PATH . $thread_id, DIRECTORY_PERM);
        $this->file_handler->createDirectory(PAGE_PATH . $thread_id, DIRECTORY_PERM);
    }

    function removeThreadDirectories($thread_id)
    {
        $this->file_handler->eraserGun($this->file_handler->pathJoin(PAGE_PATH, $thread_id), null, true);
        $this->file_handler->eraserGun($this->file_handler->pathJoin(SRC_PATH, $thread_id), null, true);
        $this->file_handler->eraserGun($this->file_handler->pathJoin(THUMB_PATH, $thread_id), null, true);
    }

    public function verifyDeletePerms($post_id)
    {
        $authorize = nel_authorize();

        if (!is_numeric($post_id))
        {
            nel_derp(30, nel_stext('ERROR_30'));
        }

        $prepared = $this->dbh->prepare('SELECT "post_password", "mod_post" FROM "' . POST_TABLE .
             '" WHERE "post_number" = ? LIMIT 1');
        $post_data = $this->dbh->executePreparedFetch($prepared, array($post_id), PDO::FETCH_ASSOC, true);

        $flag = false;

        if (nel_session_is_active())
        {
            $flag = $authorize->role_level_check($authorize->get_user_role($_SESSION['username']), $authorize->get_user_role($post_data['mod_post'], $_POST['board_id']));

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
            nel_derp(31, nel_stext('ERROR_31'));
        }
    }
}