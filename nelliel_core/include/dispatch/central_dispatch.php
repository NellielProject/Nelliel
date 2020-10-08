<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

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
        require_once NEL_INCLUDE_PATH . 'wat/about_nelliel.php';
        nel_about_page(new \Nelliel\DomainSite(nel_database()));
    }

    if (isset($_GET['blank']) || isset($_GET['tpilb']))
    {
        require_once NEL_INCLUDE_PATH . 'wat/blank.php';
        nel_tpilb();
    }

    $inputs = array();
    $inputs['module'] = $_GET['module'] ?? '';
    $inputs['domain_id'] = $_GET['domain_id'] ?? '';
    $inputs['board_id'] = $_GET['board_id'] ?? '';
    $inputs['action'] = $_GET['action'] ?? '';
    $inputs['content_id'] = $_GET['content-id'] ?? '';
    $goback = $_GET['goback'] ?? false;

    if ($goback)
    {
        $redirect = new \Nelliel\Redirect();
        $redirect->changeURL($_SERVER['HTTP_REFERER']);
        $redirect->doRedirect(true);
    }

    if ($inputs['board_id'] === '' || $inputs['domain_id'] === '_site_')
    {
        $domain = new \Nelliel\DomainSite(nel_database());
    }
    else
    {
        $domain = new \Nelliel\DomainBoard($inputs['board_id'], nel_database());
    }

    if(isset($_GET['modmode']) || isset($_POST['modmode']))
    {
        $inputs['modmode'] = $_GET['modmode'] ?? false;
        $session = new \Nelliel\Account\Session();
    }

    $snacks = new \Nelliel\Snacks(nel_database(), new \Nelliel\BanHammer(nel_database()));
    $snacks->applyBan($domain, $inputs);
    $snacks->checkHoneypot($domain);

    require_once NEL_INCLUDE_PATH . 'dispatch/module_dispatch.php';
    $inputs = nel_module_dispatch($inputs, $domain);
    $inputs = nel_plugins()->processHook('nel-in-after-central-dispatch', [$domain], $inputs);
}
