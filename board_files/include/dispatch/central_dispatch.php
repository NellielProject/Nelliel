<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Process the mode field input
//
/*function nel_process_mode_input($mode)
{
    return preg_split('#->#', $mode);
}


function nel_assemble_dispatch_data()
{
    if(!empty($_GET))
    {
        return;
    }
}*/


function nel_central_dispatch($dataforce)
{
    $authorize = nel_authorize();

    if(empty($_GET) && empty($_POST))
    {
        return;
    }

    if(isset($_GET['about_nelliel']))
    {
        require_once INCLUDE_PATH . 'wat/about.php';
        nel_about_nelliel_screen();
    }

    if(isset($_GET['blank']) || isset($_GET['tpilb']))
    {
        require_once INCLUDE_PATH . 'wat/wat.php';
        nel_tpilb();
    }

    if(isset($_GET['manage']))
    {
        require_once INCLUDE_PATH . 'dispatch/admin_dispatch.php';
        nel_admin_dispatch($dataforce);
    }

    if(isset($_GET['module']))
    {
        require_once INCLUDE_PATH . 'dispatch/general_dispatch.php';
        nel_general_dispatch($dataforce);
    }
}
//
// This handles the GET requests
//
/*function nel_process_get($dataforce)
{
    $authorize = nel_authorize();
    $board_id = (isset($_GET['board_id'])) ? $_GET['board_id'] : null;

    if(empty($_GET))
    {
        return;
    }

    if(isset($_GET['manage']))
    {
        nel_new_admin_dispatch($dataforce, $board_id);
    }

    switch ($_GET['mode'])
    {
        case 'manage':
            nel_verify_login_or_session($dataforce);
            nel_login($dataforce);
            break;

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
        require_once INCLUDE_PATH . 'wat/about.php';
        nel_about_nelliel_screen();
    }

    if(isset($_GET['blank']) || isset($_GET['tpilb']))
    {
        require_once INCLUDE_PATH . 'wat/wat.php';
        nel_tpilb();
    }
}*/

//
// This handles the POST requests
//
/*function nel_process_post($dataforce)
{
    if (!isset($dataforce['mode']))
    {
        return;
    }

    $authorize = nel_authorize();
    $dataforce['mode_segments'] = nel_process_mode_input($dataforce['mode']);

    switch ($dataforce['mode_segments'][0])
    {
        case 'admin':
            admin_dispatch(INPUT_BOARD_ID, $dataforce);
            break;

        case 'general':
            general_dispatch(INPUT_BOARD_ID, $dataforce);
            break;
    }


    switch ($dataforce['mode_segments'][0])
    {
        default:
            nel_derp(200, nel_stext('ERROR_200'));
    }

    nel_clean_exit($dataforce, true);
}*/
