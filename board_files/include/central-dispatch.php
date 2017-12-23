<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Process the mode field input
//
function nel_process_mode_input($mode)
{
    return preg_split('#->#', $mode);
}

//
// This handles the GET requests
//
function nel_process_get($dataforce)
{
    $authorize = nel_authorize();

    switch ($dataforce['get_mode']) // Moar modes
    {
        case 'display':
            if (!empty($_SESSION)) // For expanding a thread TODO: Fix this
            {
                if (is_null($dataforce['response_id']))
                {
                    nel_regen_index($dataforce);
                }
                else
                {
                    nel_regen_threads($dataforce, false, array($dataforce['response_id']));
                }
            }

            die();

        case 'admin':
            nel_login($dataforce);
            die();
    }

    if(isset($_GET['about_nelliel']))
    {
        require_once INCLUDE_PATH . 'about.php';
        nel_about_nelliel_screen();
    }

    if(isset($_GET['blank']) || isset($_GET['tpilb']))
    {
        require_once INCLUDE_PATH . 'about.php';
        nel_tpilb();
    }
}

//
// This handles the POST requests
//
function nel_process_post($dataforce)
{
    global $fgsfds;

    if (!isset($dataforce['mode']))
    {
        return;
    }

    $authorize = nel_authorize();
    $mode = nel_process_mode_input($dataforce['mode']);

    switch ($mode[0])
    {
        case 'update':
            $updates = 0;

            // TODO: this doesn't wurk rite
            if (!empty($_SESSION) && isset($dataforce['admin_mode']) && $dataforce['admin_mode'] === 'modmode')
            {
                if ($dataforce['banpost'])
                {
                    nel_ban_hammer($dataforce);
                }

                if ($dataforce['delpost'])
                {
                    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_post_delete'))
                    {
                        nel_derp(108, array('origin' => 'DISPATCH'));
                    }

                    $updates = nel_thread_updates($dataforce);
                }

                nel_regen_threads($dataforce, true, $updates);
                nel_regen_index($dataforce);

                //echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                nel_clean_exit($dataforce, TRUE);
            }

            $updates = nel_thread_updates($dataforce);
            nel_regen_threads($dataforce, true, $updates);
            nel_regen_index($dataforce);
            nel_clean_exit($dataforce, FALSE);

        case 'new_post':
            nel_process_new_post($dataforce);

            if ($fgsfds['noko'])
            {
                /*if (isset($dataforce['get_mode']))
                {
                    echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF . '?mode=display&post=' . $fgsfds['noko_topic'] . '">';
                }
                else
                {*/
                    echo '<meta http-equiv="refresh" content="1;URL=' . PAGE_DIR . $fgsfds['noko_topic'] . '/' . $fgsfds['noko_topic'] . '.html">';
                //}
            }
            else
            {
                /*if (!empty($_SESSION))
                {
                    echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF . '?mode=display&page=0">';
                }
                else
                {*/
                    echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF2 . PHP_EXT . '">';
                //}
            }

            nel_clean_exit($dataforce, TRUE);

        case 'admin':
            admin_dispatch($dataforce, $mode);
    }
}

function admin_dispatch($dataforce, $mode)
{
    $authorize = nel_authorize();
    nel_initialize_session($dataforce);

    switch ($mode[1])
    {
        case 'staff':
            require_once INCLUDE_PATH . 'admin/staff-panel.php';
            nel_staff_panel($dataforce);
            break;

        case 'ban':
            require_once INCLUDE_PATH . 'admin/bans-panel.php';
            nel_ban_control($dataforce);
            break;

        case 'modmode':
            nel_thread_updates($dataforce);
            echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF . '?mode=display&page=0">';
            break;

        case 'settings':
            require_once INCLUDE_PATH . 'admin/settings-panel.php';
            nel_settings_control($dataforce);
            break;

        case 'regen':
            if ($mode[2] === 'full')
            {
                nel_regen_all_pages($dataforce);
            }

            if ($mode[2] === 'index')
            {
                nel_regen_index($dataforce);
            }

            if ($mode[2] === 'thread')
            {
                nel_regen_threads($dataforce, true, null);
            }

            if ($mode[2] === 'cache')
            {
                nel_regen_cache($dataforce);
            }

            nel_login($dataforce);
            break;

        case 'thread':
            require_once INCLUDE_PATH . 'admin/threads-panel.php';
            nel_thread_panel($dataforce, $authorize);
            break;

        case 'login':
            nel_login($dataforce);
            break;

        default:
            nel_derp(153, array('origin' => 'DISPATCH'));
    }

    nel_clean_exit($dataforce, TRUE);
}
