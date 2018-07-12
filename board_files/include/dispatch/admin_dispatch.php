<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_admin_dispatch($inputs)
{
    $inputs = nel_plugins()->processHook('inb4-admin-dispatch', array(), $inputs);
    nel_verify_login_or_session($inputs['manage'], $inputs['action']);

    if ($inputs['manage'] === 'login')
    {
        nel_login();
    }
    else if ($inputs['manage'] === 'logout')
    {
        nel_sessions()->initializeSession($inputs['manage'], $inputs['action']);
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
                nel_staff_panel($inputs['section'], $inputs['action']);
                break;

            case 'site-settings':
                require_once INCLUDE_PATH . 'admin/site_settings_panel.php';
                nel_site_settings_control($inputs['action']);
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
                require_once INCLUDE_PATH . 'admin/file_filters.php';
                nel_manage_file_filters($inputs['action']);

            case 'default-board-settings':
                require_once INCLUDE_PATH . 'admin/board_settings_panel.php';
                nel_board_settings_control($inputs['board_id'], $inputs['action'], true);
                break;

            case 'language':
                if ($inputs['action'] === 'extract-gettext')
                {
                    nel_language()->extractLanguageStrings(
                            LANGUAGE_PATH . 'extracted/extraction' . date('Y-m-d_H-i-s') . '.pot');
                }

                nel_render_main_panel();
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
                require_once INCLUDE_PATH . 'admin/board_settings_panel.php';
                nel_board_settings_control($inputs['board_id'], $inputs['action']);
                break;

            case 'bans':
                require_once INCLUDE_PATH . 'admin/bans_panel.php';
                nel_ban_control($inputs);
                break;

            case 'threads':
                require_once INCLUDE_PATH . 'admin/threads_panel.php';
                nel_thread_panel($inputs['board_id'], $inputs['action']);
                break;

            case 'regen':
                $regen = new \Nelliel\Regen();

                if ($inputs['action'] === 'pages-all')
                {
                    $regen->allPages($inputs['board_id']);
                }

                if ($inputs['action'] === 'cache-all')
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
                $thread_handler = new \Nelliel\ThreadHandler($inputs['board_id']);

                if ($inputs['action2'] === 'delete-post')
                {
                    $updates = $thread_handler->removePost($_GET['post-id']);
                }
                else if ($inputs['action2'] === 'delete-thread')
                {
                    $updates = $thread_handler->removeThread($_GET['thread-id']);
                }
                else if ($inputs['action2'] === 'sticky')
                {
                    $updates = $thread_handler->stickyThread($_GET['thread-id']);
                }
                else if ($inputs['action2'] === 'unsticky')
                {
                    $updates = $thread_handler->unstickyThread($_GET['thread-id']);
                }
                else if ($inputs['action2'] === 'lock')
                {
                    $updates = $thread_handler->lockThread($_GET['thread-id']);
                }
                else if ($inputs['action2'] === 'unlock')
                {
                    $updates = $thread_handler->unlockThread($_GET['thread-id']);
                }
                else if ($inputs['action2'] === 'delete-file')
                {
                    $updates = $thread_handler->removeFile($_GET['post-id'], $_GET['file-order']);
                }
                else if ($inputs['action2'] === 'ban-file')
                {
                    ;
                }

                $regen = new \Nelliel\Regen();
                $regen->threads($inputs['board_id'], true, $updates);
                $regen->index($inputs['board_id']);
                nel_clean_exit(true, $inputs['board_id']);
                break;

            case 'bans':
                require_once INCLUDE_PATH . 'admin/file-filters.php';
                nel_manage_file_filters($inputs);
                break;

            case 'multi':
                if ($inputs['action2'] === 'ban.delete-post' || $inputs['action2'] === 'ban.delete-thread')
                {
                    $thread_handler = new \Nelliel\ThreadHandler($inputs['board_id']);

                    if ($inputs['action2'] === 'ban.delete-post')
                    {
                        $updates = $thread_handler->removePost($_GET['post-id']);
                    }
                    else if ($inputs['action2'] === 'ban.delete-thread')
                    {
                        $updates = $thread_handler->removeThread($_GET['thread-id']);
                    }

                    $regen = new \Nelliel\Regen();
                    $regen->threads($inputs['board_id'], true, $updates);
                    $regen->index($inputs['board_id']);
                    require_once INCLUDE_PATH . 'admin/bans_panel.php';
                    $inputs['action2'] = 'new';
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
