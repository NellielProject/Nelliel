<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_general_dispatch($board_id, $dataforce)
{
    $module = (isset($_GET['module'])) ? $_GET['module'] : null;
    $board_id = (isset($_GET['board_id'])) ? $_GET['board_id'] : null;
    $action = (isset($_POST['action'])) ? $_POST['action'] : null;

    if ($manage === 'general')
    {
        switch ($module)
        {
            case 'post':
                if ($action === 'new-post')
                {
                    nel_process_new_post($board_id, $dataforce);

                    if (nel_fgsfds('noko'))
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . nel_board_references($board_id, 'page_dir') .
                             nel_fgsfds('noko_topic') . '/' . nel_fgsfds('noko_topic') . '.html">';
                    }
                    else
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' .
                             nel_board_references($board_id, 'board_directory') . '/' . PHP_SELF2 . PHP_EXT . '">';
                    }
                }

                nel_clean_exit($dataforce, true);
                break;
        }
    }
}

/*function general_dispatch($board_id, $dataforce)
{
    switch ($dataforce['mode_segments'][1])
    {
        case 'bans':
            if ($dataforce['mode_segments'][2] === 'appeal')
            {
                nel_apply_ban($dataforce);
            }
            break;

        case 'post':
            if ($dataforce['mode_segments'][2] === 'new')
            {
                nel_process_new_post($board_id, $dataforce);

                if (nel_fgsfds('noko'))
                {
                    echo '<meta http-equiv="refresh" content="1;URL=' . nel_board_references($board_id, 'page_dir') .
                         nel_fgsfds('noko_topic') . '/' . nel_fgsfds('noko_topic') . '.html">';
                }
                else
                {
                    echo '<meta http-equiv="refresh" content="1;URL=' .
                         nel_board_references($board_id, 'board_directory') . '/' . PHP_SELF2 . PHP_EXT . '">';
                }
            }

            nel_clean_exit($dataforce, true);
            break;

        case 'threads':
            if ($dataforce['mode_segments'][2] === 'update')
            {
                $updates = nel_thread_updates($dataforce, $board_id);
                nel_regen_threads($dataforce, $board_id, true, $updates);
                nel_regen_index($dataforce, $board_id);
                nel_clean_exit($dataforce, false);
            }

            break;
    }
}*/