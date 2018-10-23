<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_general_dispatch($inputs)
{
    $sessions = new \Nelliel\Sessions();
    $fgsfds = new \Nelliel\FGSFDS();
    $inputs = nel_plugins()->processHook('nel-inb4-general-dispatch', array(), $inputs);

    switch ($inputs['module'])
    {
        case 'threads':
            if ($inputs['action'] === 'new-post')
            {
                require_once INCLUDE_PATH . 'post/post.php';
                nel_process_new_post($inputs);
                $board_references = nel_parameters_and_data()->boardReferences($inputs['board_id']);

                if ($fgsfds->getCommand('noko') !== false)
                {
                    if ($sessions->sessionIsActive())
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF .
                                '?module=modmode&area=view-thread&section=' . $fgsfds->getCommandData('noko', 'topic') . '&board_id=' .
                                $inputs['board_id'] . '">';
                    }
                    else
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . $board_references['board_directory'] . '/' .
                                $board_references['page_dir'] . '/' . $fgsfds->getCommandData('noko', 'topic') . '/' .
                                $fgsfds->getCommandData('noko', 'topic') . '.html">';
                    }
                }
                else
                {
                    if ($sessions->sessionIsActive())
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF .
                                '?module=modmode&area=view-index&section=0&board_id=' . $inputs['board_id'] . '">';
                    }
                    else
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' .
                                nel_parameters_and_data()->boardReferences($inputs['board_id'], 'board_directory') . '/' .
                                PHP_SELF2 . PHP_EXT . '">';
                    }
                }
            }
            else
            {
                if (isset($_POST['form_submit_report']))
                {
                    $reports_panel = new \Nelliel\Admin\AdminReports(nel_database(), nel_authorize(), $inputs['board_id']);
                    $reports_panel->actionDispatch($inputs);

                    if ($sessions->sessionIsActive())
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF .
                                '?module=modmode&area=view-index&section=0&board_id=' . $inputs['board_id'] . '">';
                    }
                    else
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' .
                                nel_parameters_and_data()->boardReferences($inputs['board_id'], 'board_directory') . '/' .
                                PHP_SELF2 . PHP_EXT . '">';
                    }
                }

                if (isset($_POST['form_submit_delete']))
                {
                    $thread_handler = new \Nelliel\ThreadHandler(nel_database(), $inputs['board_id']);
                    $thread_handler->processContentDeletes();

                    if ($sessions->sessionIsActive())
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF .
                        '?module=modmode&area=view-index&section=0&board_id=' . $inputs['board_id'] . '">';
                    }
                    else
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' .
                                nel_parameters_and_data()->boardReferences($inputs['board_id'], 'board_directory') . '/' .
                                PHP_SELF2 . PHP_EXT . '">';
                    }

                    nel_clean_exit(true, $inputs['board_id']);
                    break;
                }
            }

            break;
    }
}
