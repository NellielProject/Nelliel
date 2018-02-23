<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_new_admin_dispatch($dataforce)
{
    $authorize = nel_authorize();
    nel_verify_login_or_session($dataforce);
    $manage = (isset($_GET['manage'])) ? $_GET['manage'] : null;
    $module = (isset($_GET['module'])) ? $_GET['module'] : null;
    $board_id = (isset($_GET['board_id'])) ? $_GET['board_id'] : null;
    $action = (isset($_POST['action'])) ? $_POST['action'] : null;

    if ($manage === 'general')
    {
        switch ($module)
        {
            case 'login':
                nel_login($dataforce);
                break;

            case 'main-panel':
                nel_generate_main_panel();
                break;

            case 'staff':
                require_once INCLUDE_PATH . 'admin/staff_panel.php';
                nel_staff_panel($dataforce);
                break;

            case 'site-settings':
                require_once INCLUDE_PATH . 'admin/site_settings_panel.php';
                nel_site_settings_control($dataforce);
                break;

            case 'create-board':
                require_once INCLUDE_PATH . 'output/management/create_board.php';
                nel_generate_create_board_panel();
                break;

            default:
                nel_login($dataforce);
                break;
        }
    }
    else if ($manage === 'board')
    {
        switch ($module)
        {
            case 'board-settings':
                require_once INCLUDE_PATH . 'admin/board_settings_panel.php';
                nel_board_settings_control($board_id, $action, $dataforce);
                break;

            case 'bans':
                require_once INCLUDE_PATH . 'admin/bans_panel.php';
                nel_ban_control($board_id, $action, $dataforce);
                break;

            case 'threads':
                require_once INCLUDE_PATH . 'admin/threads_panel.php';
                nel_thread_panel($board_id, $action, $dataforce, $authorize);
                break;

            case 'regen':
                if ($dataforce['mode_segments'][2] === 'full')
                {
                    nel_regen_all_pages($dataforce, $board_id);
                }

                if ($dataforce['mode_segments'][2] === 'index')
                {
                    nel_regen_index($dataforce, $board_id);
                }

                if ($dataforce['mode_segments'][2] === 'thread')
                {
                    nel_regen_threads($dataforce, $board_id, true, null);
                }

                if ($dataforce['mode_segments'][2] === 'cache')
                {
                    nel_regen_cache($board_id, $dataforce);
                }

                nel_login($dataforce);
                break;

            case 'main-panel':
                nel_generate_main_board_panel($board_id);
                break;
        }
    }
}

function admin_dispatch($board_id, $dataforce)
{
    $authorize = nel_authorize();
    nel_verify_login_or_session($dataforce);

    switch ($dataforce['mode_segments'][1])
    {
        case 'staff':
            require_once INCLUDE_PATH . 'admin/staff_panel.php';
            nel_staff_panel($dataforce);
            break;

        case 'ban':
            require_once INCLUDE_PATH . 'admin/bans_panel.php';
            nel_ban_control($board_id, $dataforce);
            break;

        case 'modmode':
            nel_thread_updates($dataforce, $board_id);
            echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF . '?mode=display&page=0">';
            break;

        case 'board-settings':
            require_once INCLUDE_PATH . 'admin/board_settings_panel.php';
            nel_board_settings_control($board_id, $dataforce);
            break;

        case 'site-settings':
            require_once INCLUDE_PATH . 'admin/site_settings_panel.php';
            nel_site_settings_control($dataforce);
            break;

        case 'regen':
            if ($dataforce['mode_segments'][2] === 'full')
            {
                nel_regen_all_pages($dataforce, $board_id);
            }

            if ($dataforce['mode_segments'][2] === 'index')
            {
                nel_regen_index($dataforce, $board_id);
            }

            if ($dataforce['mode_segments'][2] === 'thread')
            {
                nel_regen_threads($dataforce, $board_id, true, null);
            }

            if ($dataforce['mode_segments'][2] === 'cache')
            {
                nel_regen_cache($board_id, $dataforce);
            }

            nel_login($dataforce);
            break;

        case 'thread':
            require_once INCLUDE_PATH . 'admin/threads_panel.php';
            nel_thread_panel($board_id, $dataforce, $authorize);
            break;

        case 'login':
            nel_login($dataforce);
            break;

        case 'selectboard':
            nel_login($dataforce);
            break;

        case 'board':
            require_once INCLUDE_PATH . 'admin/create_board.php';
            $board_id = nel_create_new_board();
            nel_regen_all_pages($dataforce, $board_id);
            nel_regen_cache($board_id, $dataforce);
            nel_login($dataforce);
            break;

        case 'createboard':
            require_once INCLUDE_PATH . 'output/management/create_board.php';
            nel_generate_create_board_panel();
            break;

        default:
            nel_derp(400, nel_stext('ERROR_400'));
    }

    nel_clean_exit($dataforce, TRUE);
}
