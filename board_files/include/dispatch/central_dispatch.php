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
            break;

        case 'admin':
            nel_verify_login_or_session($dataforce);
            nel_login($dataforce);
            break;

        case 'log_out':
            nel_initialize_session($dataforce);
            break;
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
    $dataforce['mode_segments'] = nel_process_mode_input($dataforce['mode']);

    switch ($dataforce['mode_segments'][0])
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
                        nel_derp(352, nel_stext('ERROR_352'));
                    }

                    $updates = nel_thread_updates($dataforce);
                }

                nel_regen_threads($dataforce, true, $updates);
                nel_regen_index($dataforce);
                nel_clean_exit($dataforce, TRUE);
            }

            $updates = nel_thread_updates($dataforce);
            nel_regen_threads($dataforce, true, $updates);
            nel_regen_index($dataforce);
            break;

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

            break;

        case 'admin':
            admin_dispatch($dataforce);
            break;

        default:
            nel_derp(200, nel_stext('ERROR_200'));
    }

    nel_clean_exit($dataforce, true);
}
