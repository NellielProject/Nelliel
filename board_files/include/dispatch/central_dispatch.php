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
    $inputs['module'] = $_GET['module'] ?? null;
    $inputs['board_id'] = $_GET['board_id'] ?? '';
    $inputs['action'] = $_GET['action'] ?? null;
    $inputs['content_id'] = $_GET['content-id'] ?? null;
    $inputs['modmode'] = $_GET['modmode'] ?? false;
    $domain = new \Nelliel\Domain($inputs['board_id'], new \Nelliel\CacheHandler(), nel_database());
    $snacks = new \Nelliel\Snacks(nel_database(), new \Nelliel\BanHammer(nel_database()));
    $snacks->applyBan($domain);
    $snacks->checkHoneypot($domain);
    $domain->renderInstance(new \Nelliel\RenderCore());

    require_once INCLUDE_PATH . 'dispatch/module_dispatch.php';
    $inputs = nel_module_dispatch($inputs, $domain);
    nel_plugins()->processHook('nel-in-after-central-dispatch', [$inputs, $domain]);
}
