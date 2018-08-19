<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_general_dispatch($inputs)
{
    $inputs = nel_plugins()->processHook('inb4-general-dispatch', array(), $inputs);

    switch ($inputs['module'])
    {
        case 'threads':
            if ($inputs['action'] === 'new-post')
            {
                require_once INCLUDE_PATH . 'post/post.php';
                nel_process_new_post($inputs);
                $board_references = nel_parameters_and_data()->boardReferences($inputs['board_id']);

                if (nel_fgsfds('noko'))
                {
                    if (nel_sessions()->sessionIsActive())
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF .
                                '?manage=modmode&module=view-thread&section=' . nel_fgsfds('noko_topic') . '&board_id=' .
                                $inputs['board_id'] . '">';
                    }
                    else
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . $board_references['board_directory'] . '/' .
                                $board_references['page_dir'] . '/' . nel_fgsfds('noko_topic') . '/' .
                                nel_fgsfds('noko_topic') . '.html">';
                    }
                }
                else
                {
                    if (nel_sessions()->sessionIsActive())
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF .
                                '?manage=modmode&module=view-index&section=0&board_id=' . $inputs['board_id'] . '">';
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
                $reports = new \Nelliel\Reports();
                $reports->collectReportedContent();

                if (nel_sessions()->sessionIsActive())
                {
                    echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF .
                    '?manage=modmode&module=view-index&section=0&board_id=' . $inputs['board_id'] . '">';
                }
                else
                {
                    echo '<meta http-equiv="refresh" content="1;URL=' .
                            nel_parameters_and_data()->boardReferences($inputs['board_id'], 'board_directory') . '/' .
                            PHP_SELF2 . PHP_EXT . '">';
                }
            }

            break;
    }
}
