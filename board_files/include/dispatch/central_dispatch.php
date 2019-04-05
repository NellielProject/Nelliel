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
        $about_page = new \Nelliel\Output\OutputAboutPage(new \Nelliel\DomainSite(nel_database()));
        $about_page->render();
    }

    if (isset($_GET['blank']) || isset($_GET['tpilb']))
    {
        require_once INCLUDE_PATH . 'wat/blank.php';
        nel_tpilb();
    }

    $inputs = array();
    $inputs['module'] = $_GET['module'] ?? '';
    $inputs['board_id'] = $_GET['board_id'] ?? '';
    $inputs['action'] = $_GET['action'] ?? '';
    $inputs['content_id'] = $_GET['content-id'] ?? '';
    $inputs['modmode'] = $_GET['modmode'] ?? false;

    if($inputs['board_id'] === '')
    {
        $domain = new \Nelliel\DomainSite(nel_database());
    }
    else
    {
        $domain = new \Nelliel\DomainBoard($inputs['board_id'], nel_database());
    }

    $snacks = new \Nelliel\Snacks(nel_database(), new \Nelliel\BanHammer(nel_database()));
    $snacks->applyBan($domain, $inputs);
    $snacks->checkHoneypot($domain);

    require_once INCLUDE_PATH . 'dispatch/module_dispatch.php';
    $inputs = nel_module_dispatch($inputs, $domain);
    nel_plugins()->processHook('nel-in-after-central-dispatch', [$inputs, $domain]);
}
