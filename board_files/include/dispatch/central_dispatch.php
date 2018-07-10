<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'admin/login.php'; // TODO: Make login a class

function nel_central_dispatch()
{
    nel_plugins()->processHook('inb4-central-dispatch', array());
    $authorize = nel_authorize();

    if (empty($_GET) && empty($_POST))
    {
        return;
    }

    if (isset($_GET['about_nelliel']))
    {
        require_once INCLUDE_PATH . 'wat/about_nelliel.php';
        nel_about_nelliel_screen();
    }

    if (isset($_GET['blank']) || isset($_GET['tpilb']))
    {
        require_once INCLUDE_PATH . 'wat/blank.php';
        nel_tpilb();
    }

    $inputs = array();
    $inputs['manage'] = (isset($_GET['manage'])) ? $_GET['manage'] : null;
    $inputs['module'] = (isset($_GET['module'])) ? $_GET['module'] : null;
    $inputs['section'] = (isset($_GET['section'])) ? $_GET['section'] : null;
    $inputs['board_id'] = (isset($_GET['board_id'])) ? $_GET['board_id'] : null;
    $inputs['action'] = (isset($_POST['action'])) ? $_POST['action'] : null;
    $inputs['action2'] = (isset($_GET['action'])) ? $_GET['action'] : null;

    if (isset($_GET['manage']))
    {
        require_once INCLUDE_PATH . 'dispatch/admin_dispatch.php';
        nel_admin_dispatch($inputs);
    }

    if (isset($_GET['module']))
    {
        require_once INCLUDE_PATH . 'dispatch/general_dispatch.php';
        nel_general_dispatch($inputs);
    }
}
