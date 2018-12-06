<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'login.php';

function nel_central_dispatch()
{
    nel_plugins()->processHook('nel-inb4-central-dispatch', array());
    $authorization = new \Nelliel\Auth\Authorization(nel_database());

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
    $inputs['manage'] = (isset($_GET['manage'])) ? true : false;
    $inputs['module'] = (isset($_GET['module'])) ? $_GET['module'] : null;
    $inputs['section'] = (isset($_GET['section'])) ? $_GET['section'] : null;
    $inputs['board_id'] = (isset($_GET['board_id'])) ? $_GET['board_id'] : '';
    $inputs['action'] = (isset($_GET['action'])) ? $_GET['action'] : null;
    $inputs['content_id'] = (isset($_GET['content-id'])) ? $_GET['content-id'] : null;
    $inputs['modmode'] = (isset($_GET['modmode'])) ? $_GET['modmode'] : false;

    $snacks = new \Nelliel\Snacks(nel_database(), new \Nelliel\BanHammer(nel_database()));
    $snacks->applyBan($inputs);

    require_once INCLUDE_PATH . 'dispatch/module_dispatch.php';
    nel_module_dispatch($inputs);
    nel_plugins()->processHook('nel-in-after-central-dispatch', array());
}
