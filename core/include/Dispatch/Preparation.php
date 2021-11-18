<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\BansAccess;
use Nelliel\DNSBL;
use Nelliel\Redirect;
use Nelliel\Router;
use Nelliel\Snacks;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Domains\DomainSite;
use Nelliel\Output\OutputAboutNelliel;

class Preparation
{

    function __construct()
    {}

    public function prepare()
    {
        if (empty($_GET) && empty($_POST)) {
            return;
        }

        if (isset($_GET['about_nelliel'])) {
            $about_nelliel = new OutputAboutNelliel(nel_site_domain(), false);
            $about_nelliel->render([], false);
            nel_clean_exit();
        }

        if (isset($_GET['blank']) || isset($_GET['tpilb'])) {
            require_once NEL_INCLUDE_PATH . 'wat/blank.php';
            nel_tpilb();
        }
        $dnsbl = new DNSBL(nel_database('core'));
        $dnsbl->checkIP(nel_request_ip_address());

        if (isset($_GET['route'])) {
            $router = new Router($_GET['route'] ?? '');
            $router->addRoutes();

            if ($router->dispatch()) {
                return;
            }
        }

        $inputs = $this->getInputs();
        $cid = $_GET['content-id'] ?? '';

        if (ContentID::isContentID($cid)) {
            $inputs['content_id'] = new ContentID($cid);
        }

        $inputs['modmode'] = isset($_GET['modmode']) ? true : false;
        $goback = isset($_GET['goback']) ? $_GET['goback'] === 'true' : false;

        if ($goback) {
            $redirect = new Redirect();
            $redirect->changeURL($_SERVER['HTTP_REFERER'] ?? '');
            $redirect->doRedirect(true);
        }

        // Add more options here when we implement further domain types
        if (!nel_true_empty($inputs['board_id']) && $inputs['board_id'] !== Domain::SITE) {
            $domain = new DomainBoard($inputs['board_id'], nel_database('core'));
        } else {
            $domain = new DomainSite(nel_database('core'));
        }

        if ($inputs['module'] === 'new-post') {
            $snacks = new Snacks($domain, new BansAccess(nel_database('core')));
            $snacks->applyBan();
            // $snacks->checkHoneypot();
            $dnsbl = new DNSBL(nel_database('core'));
            $dnsbl->checkIP(nel_request_ip_address());
        }

        $authorization = new Authorization($domain->database());
        $session = new Session();
        $module_dispatch = new DispatchModules($authorization, $domain, $session);
        $module_dispatch->dispatch($inputs);
    }

    private function getInputs(): array
    {
        $inputs = array();
        $inputs['module'] = $_GET['module'] ?? '';
        $inputs['section'] = $_GET['section'] ?? '';
        $inputs['subsection'] = $_GET['subsection'] ?? '';
        $inputs['board_id'] = $_GET['board-id'] ?? '';
        $inputs['raw_actions'] = $_GET['actions'] ?? '';
        $inputs['method'] = $_SERVER['REQUEST_METHOD'];

        if (!is_array($inputs['raw_actions'])) {
            $inputs['actions'] = [$inputs['raw_actions']];
        } else {
            $inputs['actions'] = $inputs['raw_actions'];
        }

        return $inputs;
    }
}