<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'admin/login.php';
require_once INCLUDE_PATH . 'thread_functions.php';

function nel_admin_dispatch()
{
    $manage = (isset($_GET['manage'])) ? $_GET['manage'] : null;
    $module = (isset($_GET['module'])) ? $_GET['module'] : null;
    $section = (isset($_GET['section'])) ? $_GET['section'] : null;
    $board_id = (isset($_GET['board_id'])) ? $_GET['board_id'] : null;
    $action = (isset($_POST['action'])) ? $_POST['action'] : null;
    nel_verify_login_or_session($manage, $action);

    if ($manage === 'login')
    {
        nel_login();
    }
    else if ($manage === 'logout')
    {
        nel_sessions()->initializeSession($manage, $action);
    }
    else if ($manage === 'general')
    {
        switch ($module)
        {
            case 'main-panel':
                nel_render_main_panel();
                break;

            case 'staff':
                require_once INCLUDE_PATH . 'admin/staff_panel.php';
                nel_staff_panel($section, $action);
                break;

            case 'site-settings':
                require_once INCLUDE_PATH . 'admin/site_settings_panel.php';
                nel_site_settings_control($action);
                break;

            case 'create-board':
                require_once INCLUDE_PATH . 'output/management/create_board.php';

                if ($action === 'create')
                {
                    require_once INCLUDE_PATH . 'admin/create_board.php';
                    nel_create_new_board();
                }

                nel_render_create_board_panel();
                break;

            case 'file-filter':
                require_once INCLUDE_PATH . 'admin/file_filters.php';
                nel_manage_file_filters($action);

            default:
                nel_login();
                break;
        }
    }
    else if ($manage === 'board')
    {
        switch ($module)
        {
            case 'board-settings':
                require_once INCLUDE_PATH . 'admin/board_settings_panel.php';
                nel_board_settings_control($board_id, $action);
                break;

            case 'bans':
                require_once INCLUDE_PATH . 'admin/bans_panel.php';
                nel_ban_control($board_id, $action);
                break;

            case 'threads':
                require_once INCLUDE_PATH . 'admin/threads_panel.php';
                nel_thread_panel($board_id, $action);
                break;

            case 'regen':
                if ($action === 'pages-all')
                {
                    nel_regen_all_pages($board_id);
                }

                if ($action === 'cache-all')
                {
                    nel_regen_cache($board_id);
                }

                nel_render_main_board_panel($board_id);
                break;

            case 'main-panel':
                nel_render_main_board_panel($board_id);
                break;
        }
    }
    else
    {
        nel_derp(400, nel_stext('ERROR_400'));
    }
}
