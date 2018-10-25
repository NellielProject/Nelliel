<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_admin_dispatch($inputs)
{
    $sessions = new \Nelliel\Sessions();
    $inputs = nel_plugins()->processHook('nel-inb4-admin-dispatch', array(), $inputs);
    nel_verify_login_or_session($inputs['manage'], $inputs['action']);

    switch ($inputs['module'])
    {

        case 'render':
            switch ($inputs['action'])
            {
                case 'view-index':
                    require_once INCLUDE_PATH . 'output/main_generation.php';
                    nel_main_thread_generator($inputs['board_id'], 0, false, intval($inputs['section']));
                    break;

                case 'view-thread':
                    require_once INCLUDE_PATH . 'output/thread_generation.php';
                    nel_thread_generator($inputs['board_id'], false, intval($inputs['section']));
                    break;
            }

            break;

        case 'login':
            nel_login();
            break;

        case 'logout':
            $sessions->terminateSession();
            nel_clean_exit(true);
            break;

        case 'main-panel':
            if (!is_null($inputs['board_id']))
            {
                nel_render_main_board_panel($inputs['board_id']);
            }
            else
            {
                nel_render_main_panel();
            }

            break;

        case 'staff':
            $staff_panel = new \Nelliel\Admin\AdminStaff(nel_database(), nel_authorize());
            $staff_panel->actionDispatch($inputs);
            break;

        case 'site-settings':
            $site_settings_panel = new \Nelliel\Admin\AdminSiteSettings(nel_database(), nel_authorize());
            $site_settings_panel->actionDispatch($inputs);
            break;

        case 'manage-boards':
            $create_board_panel = new \Nelliel\Admin\AdminManageBoards(nel_database(), nel_authorize());
            $create_board_panel->actionDispatch($inputs);
            break;

        case 'file-filter':
            $file_filters_panel = new \Nelliel\Admin\AdminFileFilters(nel_database(), nel_authorize(),
                    $inputs['board_id']);
            $file_filters_panel->actionDispatch($inputs);
            break;

        case 'default-board-settings':
            $board_settings_panel = new \Nelliel\Admin\AdminBoardSettings(nel_database(), nel_authorize());
            $board_settings_panel->actionDispatch($inputs);
            break;

        case 'language':
            if ($inputs['action'] === 'extract-gettext')
            {
                $language = new \Nelliel\language\Language(nel_authorize());
                $language->extractLanguageStrings(LANGUAGE_PATH . 'extracted/extraction' . date('Y-m-d_H-i-s') . '.pot');
            }

            nel_render_main_panel();
            break;

        case 'reports':
            $reports_panel = new \Nelliel\Admin\AdminReports(nel_database(), nel_authorize(), $inputs['board_id']);
            $reports_panel->actionDispatch($inputs);
            break;

        case 'board-settings':
            $board_settings_panel = new \Nelliel\Admin\AdminBoardSettings(nel_database(), nel_authorize(),
                    $inputs['board_id']);
            $board_settings_panel->actionDispatch($inputs);
            break;

        case 'bans':
            $bans_panel = new \Nelliel\Admin\AdminBans(nel_database(), nel_authorize(), $inputs['board_id']);
            $bans_panel->actionDispatch($inputs);
            break;

        case 'threads':
            $content_id = new \Nelliel\ContentID($inputs['content_id']);
            $fgsfds = new \Nelliel\FGSFDS();

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
                                '?module=render&action=view-thread&section=' . $fgsfds->getCommandData('noko', 'topic') .
                                '&board_id=' . $inputs['board_id'] . '">';
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
                                '?module=render&action=view-index&section=0&board_id=' . $inputs['board_id'] . '">';
                    }
                    else
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' .
                                nel_parameters_and_data()->boardReferences($inputs['board_id'], 'board_directory') . '/' .
                                PHP_SELF2 . PHP_EXT . '">';
                    }
                }
            }
            else if ($inputs['action'] === 'delete-post')
            {
                $post = new \Nelliel\Content\ContentPost(nel_database(), $content_id, $inputs['board_id']);
                $post->remove();
            }
            else if ($inputs['action'] === 'delete-thread')
            {
                $thread = new \Nelliel\Content\ContentThread(nel_database(), $content_id, $inputs['board_id']);
                $thread->remove();
            }
            else if ($inputs['action'] === 'sticky' || $inputs['action'] === 'unsticky')
            {
                if ($content_id->isPost())
                {
                    $post = new \Nelliel\Content\ContentPost(nel_database(), $content_id, $inputs['board_id']);
                    $post->convertToThread();
                    $new_content_id = new \Nelliel\ContentID();
                    $new_content_id->thread_id = $content_id->post_id;
                    $new_content_id->post_id = $content_id->post_id;
                    $new_thread = new \Nelliel\Content\ContentThread(nel_database(), $new_content_id, $inputs['board_id']);
                    $new_thread->sticky();
                }
                else
                {
                    $thread = new \Nelliel\Content\ContentThread(nel_database(), $content_id, $inputs['board_id']);
                    $thread->sticky();
                }
            }
            else if ($inputs['action'] === 'lock' || $inputs['action'] === 'unlock')
            {
                $thread = new \Nelliel\Content\ContentThread(nel_database(), $content_id, $inputs['board_id']);
                $thread->lock();
            }
            else if ($inputs['action'] === 'delete-file')
            {
                $file = new \Nelliel\Content\ContentFile(nel_database(), $content_id, $inputs['board_id']);
                $file->remove();
            }
            else if ($inputs['action'] === 'ban-file')
            {
                ; // TODO: Add file hash
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
                                '?module=render&action=view-index&section=0&board_id=' . $inputs['board_id'] . '">';
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
                                '?module=render&action=view-index&section=0&board_id=' . $inputs['board_id'] . '">';
                    }
                    else
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' .
                                nel_parameters_and_data()->boardReferences($inputs['board_id'], 'board_directory') . '/' .
                                PHP_SELF2 . PHP_EXT . '">';
                    }

                    nel_clean_exit(true, $inputs['board_id']);
                }
            }

            $threads_panel = new \Nelliel\Admin\AdminThreads(nel_database(), nel_authorize(), $inputs['board_id']);
            $threads_panel->actionDispatch($inputs);
            break;

        case 'regen':
            $regen = new \Nelliel\Regen();

            if ($inputs['action'] === 'all-pages')
            {
                $regen->allPages($inputs['board_id']);
            }

            if ($inputs['action'] === 'all-caches')
            {
                $regen->boardCache($inputs['board_id']);
            }

            nel_render_main_board_panel($inputs['board_id']);
            break;

        case 'multi':
            $content_id = new \Nelliel\ContentID($inputs['content_id']);

            if ($inputs['action'] === 'ban.delete-post' || $inputs['action'] === 'ban.delete-thread')
            {
                if ($inputs['action'] === 'ban.delete-post')
                {
                    $post = new \Nelliel\Content\ContentPost(nel_database(), $content_id, $inputs['board_id']);
                    $post->remove();
                }
                else if ($inputs['action'] === 'ban.delete-thread')
                {
                    $thread = new \Nelliel\Content\ContentThread(nel_database(), $content_id, $inputs['board_id']);
                    $thread->remove();
                }

                $regen = new \Nelliel\Regen();
                $regen->threads($inputs['board_id'], true, $content_id->thread_id);
                $regen->index($inputs['board_id']);
                $inputs['action'] = 'new';
                $bans_panel = new \Nelliel\Admin\AdminBans(nel_database(), nel_authorize(), $inputs['board_id']);
                $bans_panel->actionDispatch($inputs);
            }

            break;

        default:
            nel_login();
            break;
    }
}
