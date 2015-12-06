<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Check for threads that need archive status changed
// Such as being moved back to the active pages when threads are deleted
//
function update_archive_status($dataforce)
{
    global $dbh;
    
    if (BS_OLD_THREADS === 'NOTHING')
    {
        return;
    }
    
    $result = $dbh->query('SELECT post_number FROM ' . POSTTABLE . ' WHERE response_to=0 ORDER BY sticky desc,last_update desc');
    $thread_list = $result->fetchALL(PDO::FETCH_COLUMN);
    unset($result);
    $start_buffer = BS_THREADS_PER_PAGE * $dataforce['max_pages'];
    $end_buffer = BS_THREADS_PER_PAGE * BS_PAGE_BUFFER;
    
    if ($end_buffer == 0)
    {
        $end_buffer = $start_buffer;
    }
    
    $line = 0;
    $thread_count = count($thread_list);
    
    while ($line < $thread_count) // fix undefined error
    {
        if ($line < $start_buffer && $thread_list[$line]['archive_status'] !== '0')
        {
            $dbh->query('UPDATE ' . POSTTABLE . ' SET archive_status=0 WHERE post_number=' . $thread_list[$line] . '');
        }
        else if ($line >= $start_buffer && $line <= $end_buffer && $thread_list[$line]['archive_status'] !== '1')
        {
            $dbh->query('UPDATE ' . POSTTABLE . ' SET archive_status=1 WHERE post_number=' . $thread_list[$line] . '');
        }
        else if ($line >= $end_buffer && $thread_list[$line]['archive_status'] !== '2')
        {
            $dbh->query('UPDATE ' . POSTTABLE . ' SET archive_status=2 WHERE post_number=' . $thread_list[$line] . '');
        }
        ++ $line;
    }
    
    // Below does the shift to archive
    $result = $dbh->query('SELECT post_number FROM ' . POSTTABLE . ' WHERE archive_status=2');
    $move_list = $result->fetchALL(PDO::FETCH_COLUMN);
    unset($result);
    $total = count($move_list);
    
    if ($total !== 0)
    {
        $i = 0;
        while ($i < $total)
        {
            if (BS_OLD_THREADS === 'ARCHIVE')
            {
                $result = $dbh->query('SELECT * FROM ' . POSTTABLE . ' WHERE post_number=' . $move_list[$i] . ' UNION SELECT * FROM ' . POSTTABLE . ' WHERE response_to=' . $move_list[$i] . '');
                $thread_ready = $result->fetchALL(PDO::FETCH_NUM);
                unset($result);
                $w = 0;
                $total_to_move = count($thread_ready);
                $arch_shift = $dbh->prepare('INSERT INTO ' . ARCHIVETABLE . '
					(post_number,name,tripcode,secure_tripcode,email,subject,comment,host,password,post_time,has_file,last_update,response_to,last_response,post_count,sticky,mod_post,mod_comment,archive_status,locked)
					VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                
                while ($w < $total_to_move)
                {
                    $arch_shift->execute($thread_ready[$w]);
                    ++ $w;
                }
                
                $dbh->query('DELETE FROM ' . POSTTABLE . ' WHERE response_to=' . $move_list[$i] . ' OR post_number=' . $move_list[$i] . '');
                
                $result = $dbh->query('SELECT * FROM ' . FILETABLE . ' WHERE parent_thread=' . $move_list[$i] . '');
                $file_ready = $result->fetchALL(PDO::FETCH_NUM);
                unset($result);
                $w = 0;
                $total_to_move = count($file_ready);
                $arch_shift = $dbh->prepare('INSERT INTO ' . ARCHIVEFILETABLE . '
					(parent_thread,post_ref,file_order,supertype,subtype,mime,filename,extension,image_width,image_height,preview_name,preview_width,preview_height,filesize,md5,source,license)
					VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                
                while ($w < $total_to_move)
                {
                    $arch_shift->execute($file_ready[$w]);
                    ++ $w;
                }
                
                $dbh->query('DELETE FROM ' . FILETABLE . ' WHERE parent_thread=' . $move_list[$i] . '');
                move_file(SRC_PATH . $move_list[$i], ARC_SRC_PATH . $move_list[$i]);
                move_file(THUMB_PATH . $move_list[$i], ARC_THUMB_PATH . $move_list[$i]);
                move_file(PAGE_PATH . $move_list[$i], ARC_PAGE_PATH . $move_list[$i]);
            }
            
            if (BS_OLD_THREADS === 'PRUNE')
            {
                eraser_gun(PAGE_PATH . $move_list[$i], NULL, TRUE);
                eraser_gun(SRC_PATH . $move_list[$i], NULL, TRUE);
                eraser_gun(THUMB_PATH . $move_list[$i], NULL, TRUE);
            }
            ++ $i;
        }
        
        $dbh->query('UPDATE ' . ARCHIVETABLE . ' SET archive_status=0 WHERE archive_status=2');
    }
}