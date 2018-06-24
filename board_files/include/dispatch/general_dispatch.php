<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_general_dispatch()
{
    $module = (isset($_GET['module'])) ? $_GET['module'] : null;
    $section = (isset($_GET['section'])) ? $_GET['section'] : null;
    $board_id = (isset($_GET['board_id'])) ? $_GET['board_id'] : null;
    $action = (isset($_POST['action'])) ? $_POST['action'] : null;

    switch ($module)
    {
        case 'post':
            require_once INCLUDE_PATH . 'post/post.php';

            if ($action === 'new-post')
            {
                nel_process_new_post($board_id);

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

            break;

        case 'threads':
            if ($action === 'update')
            {
                $thread_handler = new \Nelliel\ThreadHandler($board_id);
                $updates = $thread_handler->threadUpdates();
                nel_regen_threads($board_id, true, $updates);
                nel_regen_index($board_id);
                nel_clean_exit(true, $board_id);
            }

            break;
    }
}
