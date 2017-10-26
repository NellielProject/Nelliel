<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Check for threads that need archive status changed
//
function nel_update_archive_status($dataforce)
{
    $dbh = nel_get_database_handle();

    if (BS_OLD_THREADS === 'NOTHING')
    {
        return;
    }

    // Need to fetch moar data like archive status
    $result =  $dbh->query('SELECT thread_id, archive_status FROM ' . THREAD_TABLE . ' ORDER BY sticky desc, last_bump_time desc');
    $thread_list = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);
    $start_buffer = BS_THREADS_PER_PAGE * $dataforce['max_pages'];
    $end_buffer = BS_THREADS_PER_PAGE * BS_PAGE_BUFFER;

    if ($end_buffer == 0)
    {
        $end_buffer = $start_buffer;
    }

    $line = 0;

    foreach ($thread_list as $thread)
    {
        if ($line < $start_buffer && $thread['archive_status'] !== '0')
        {
             $dbh->query('UPDATE ' . THREAD_TABLE . ' SET archive_status=0 WHERE thread_id=' . $thread['thread_id']);
        }
        else if ($line >= $start_buffer && $line <= $end_buffer && $thread['archive_status'] !== '1')
        {
             $dbh->query('UPDATE ' . THREAD_TABLE . ' SET archive_status=1 WHERE thread_id=' . $thread['thread_id']);
        }
        else if ($line >= $end_buffer && $thread['archive_status'] !== '2')
        {
             $dbh->query('UPDATE ' . THREAD_TABLE . ' SET archive_status=2 WHERE thread_id=' . $thread['thread_id']);
        }
        ++ $line;
    }

    // Below does the shift to archive
    $result =  $dbh->query('SELECT thread_id FROM ' . THREAD_TABLE . ' WHERE archive_status=2');
    $move_list = $result->fetchAll(PDO::FETCH_COLUMN);
    unset($result);

    foreach ($move_list as $thread)
    {
        if (BS_OLD_THREADS === 'ARCHIVE')
        {
             $dbh->query('INSERT INTO ' . ARCHIVE_POST_TABLE . ' SELECT * FROM ' . POST_TABLE . ' WHERE parent_thread=' . $thread);
             $dbh->query('DELETE FROM ' . POST_TABLE . ' WHERE parent_thread=' . $thread);
             $dbh->query('INSERT INTO ' . ARCHIVE_THREAD_TABLE . ' SELECT * FROM ' . THREAD_TABLE . ' WHERE thread_id=' . $$thread);
             $dbh->query('DELETE FROM ' . THREAD_TABLE . ' WHERE thread_id=' . $thread);
             $dbh->query('INSERT INTO ' . ARCHIVE_FILE_TABLE . ' SELECT * FROM ' . FILE_TABLE . ' WHERE parent_thread=' . $thread);
             $dbh->query('DELETE FROM ' . FILE_TABLE . ' WHERE parent_thread=' . $thread);
            nel_move_file(SRC_PATH . $thread, ARC_SRC_PATH . $thread);
            nel_move_file(THUMB_PATH . $thread, ARC_THUMB_PATH . $thread);
            nel_move_file(PAGE_PATH . $thread, ARC_PAGE_PATH . $thread);
        }
        else if (BS_OLD_THREADS === 'PRUNE')
        {
             $dbh->query('DELETE FROM ' . POST_TABLE . ' WHERE parent_thread=' . $move_list[$i]);
             $dbh->query('DELETE FROM ' . THREAD_TABLE . ' WHERE thread_id=' . $move_list[$i]);
             $dbh->query('DELETE FROM ' . FILE_TABLE . ' WHERE parent_thread=' . $move_list[$i]);
            nel_delete_thread_directories($thread);
        }

         $dbh->query('UPDATE ' . ARCHIVE_THREAD_TABLE . ' SET archive_status=0 WHERE archive_status=2');
    }
}
