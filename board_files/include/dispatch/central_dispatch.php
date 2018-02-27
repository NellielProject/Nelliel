<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_central_dispatch()
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
        nel_admin_dispatch();
    }

    if(isset($_GET['module']))
    {
        require_once INCLUDE_PATH . 'dispatch/general_dispatch.php';
        nel_general_dispatch(INPUT_BOARD_ID);
    }
}
