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

    if ($inputs['manage'] === 'login')
    {
        nel_login();
    }
    else if ($inputs['manage'] === 'logout')
    {
        $sessions->terminateSession();
        nel_clean_exit(true);
    }
    else if ($inputs['manage'] === 'general')
    {
        switch ($inputs['module'])
        {
            case 'main-panel':
                nel_render_main_panel();
                break;

            case 'staff':
                require_once INCLUDE_PATH . 'admin/staff_panel.php';
                nel_staff_panel($inputs);
                break;

            case 'site-settings':
                require_once INCLUDE_PATH . 'admin/site_settings_panel.php';
                nel_site_settings_control($inputs);
                break;

            case 'create-board':
                require_once INCLUDE_PATH . 'output/management/create_board.php';

                if ($inputs['action'] === 'create')
                {
                    require_once INCLUDE_PATH . 'admin/create_board.php';
                    nel_create_new_board();
                }

                nel_render_create_board_panel();
                break;

            case 'file-filter':
                $file_filters_panel = new \Nelliel\PanelFileFilters(nel_database(), nel_authorize());
                $file_filters_panel->actionDispatch($inputs);
                break;

            case 'default-board-settings':
                $board_settings_panel = new \Nelliel\PanelBoardSettings(nel_database(), nel_authorize());
                $board_settings_panel->actionDispatch($inputs);
                break;

            case 'language':
                if ($inputs['action'] === 'extract-gettext')
                {
                    $language = new \Nelliel\language\Language();
                    $language->extractLanguageStrings(
                            LANGUAGE_PATH . 'extracted/extraction' . date('Y-m-d_H-i-s') . '.pot');
                }

                nel_render_main_panel();
                break;

            case 'reports':
                require_once INCLUDE_PATH . 'output/management/reports_panel.php';
                nel_render_reports_panel();
                break;

            default:
                nel_login();
                break;
        }
    }
    else if ($inputs['manage'] === 'board')
    {
        switch ($inputs['module'])
        {
            case 'board-settings':
                $board_settings_panel = new \Nelliel\PanelBoardSettings(nel_database(), nel_authorize(), $inputs['board_id']);
                $board_settings_panel->actionDispatch($inputs);
                break;

            case 'bans':
                require_once INCLUDE_PATH . 'admin/bans_panel.php';
                nel_ban_control($inputs);
                break;

            case 'threads':
                require_once INCLUDE_PATH . 'admin/threads_panel.php';
                nel_thread_panel($inputs);
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

            case 'main-panel':
                nel_render_main_board_panel($inputs['board_id']);
                break;
        }
    }
    else if ($inputs['manage'] === 'modmode')
    {
        $content_id = new \Nelliel\ContentID($inputs['content_id']);

        switch ($inputs['module'])
        {
            case 'view-index':
                require_once INCLUDE_PATH . 'output/main_generation.php';
                nel_main_thread_generator($inputs['board_id'], 0, false, intval($inputs['section']));
                break;

            case 'view-thread':
                require_once INCLUDE_PATH . 'output/thread_generation.php';
                nel_thread_generator($inputs['board_id'], false, intval($inputs['section']));
                break;

            case 'threads':
                if ($inputs['action'] === 'delete-post')
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
                    if($content_id->isPost())
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

                $regen = new \Nelliel\Regen();
                $regen->threads($inputs['board_id'], true, $content_id->thread_id);
                $regen->index($inputs['board_id']);
                nel_clean_exit(true, $inputs['board_id']);
                break;

            case 'bans':
                require_once INCLUDE_PATH . 'admin/bans_panel.php';
                nel_ban_control($inputs);
                break;

            case 'multi':
                require_once INCLUDE_PATH . 'admin/bans_panel.php';

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
                    require_once INCLUDE_PATH . 'admin/bans_panel.php';
                    $inputs['action'] = 'new';
                    nel_ban_control($inputs);
                }

                break;
        }
    }
    else
    {
        nel_derp(400, _gettext('No valid management action specified.'));
    }
}
