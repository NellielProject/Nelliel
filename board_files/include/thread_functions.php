<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_thread_updates($board_id)
{
    $archive = new \Nelliel\ArchiveAndPrune($board_id);
    $thread_handler = new \Nelliel\ThreadHandler($board_id);
    $returned_list = array();
    $update_archive = false;

    foreach ($_POST as $input)
    {
        $sub = explode('_', $input, 4);

        switch ($sub[0])
        {
            case 'deletefile':
                if($thread_handler->verifyDeletePerms($sub[2]))
                {
                    $thread_handler->removePostFilesFromDisk($sub[2], $sub[3]);
                    $thread_handler->removePostFilesFromDatabase($sub[2], $sub[3]);
                }

                break;

            case 'deletethread':
                $thread_handler->removeThread($sub[1]);
                $update_archive = true;
                break;

            case 'deletepost':
                if($thread_handler->verifyDeletePerms($sub[2]))
                {
                    $thread_handler->removePost($sub[2]);
                }

                break;

            case 'threadsticky':
                $thread_handler->stickyThread($sub[1]);
                $update_archive = true;
                break;

            case 'threadunsticky':
                $thread_handler->unStickyThread($sub[1]);
                $update_archive = true;
                break;
        }

        if (isset($sub[1]) && !in_array($sub[1], $returned_list))
        {
            array_push($returned_list, $sub[1]);
        }
    }

    if($update_archive)
    {
        $archive->updateAllArchiveStatus();

        if(nel_board_settings($board_id, 'old_threads') === 'ARCHIVE')
        {
            $archive->moveThreadsToArchive();
            $archive->moveThreadsFromArchive();
        }
        else if(nel_board_settings($board_id, 'old_threads') === 'PRUNE')
        {
            $archive->pruneThreads();
        }
    }

    return $returned_list;
}
