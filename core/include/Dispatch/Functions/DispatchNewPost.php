<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Functions;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\FGSFDS;
use Nelliel\Redirect;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\NewPost\NewPost;
use Nelliel\Output\OutputPost;
use Nelliel\Snacks;
use Nelliel\BansAccess;
use Nelliel\DNSBL;

class DispatchNewPost extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs)
    {
        $snacks = new Snacks($this->domain, new BansAccess(nel_database('core')));
        $snacks->applyBan();
        $dnsbl = new DNSBL(nel_database('core'));
        $dnsbl->checkIP(nel_request_ip_address());

        if (isset($inputs['parameters']['modmode'])) {
            $this->session->init(true);
            $this->session->toggleModMode();
        }

        $new_post = new NewPost($this->domain, $this->session);
        $new_post->processPost();

        $redirect = new Redirect();
        $redirect->doRedirect(true);
        $fgsfds = new FGSFDS();

        if ($fgsfds->commandIsSet('noko') || $this->domain->setting('always_noko')) {
            if ($this->session->inModmode($this->domain)) {
                $url = nel_build_router_url(
                    [$this->domain->id(), $this->domain->reference('page_directory'),
                        $fgsfds->getCommandData('noko', 'topic'), $fgsfds->getCommandData('noko', 'topic')], false,
                    'modmode');
            } else {
                $url = $this->domain->reference('board_directory') . '/' . $this->domain->reference('page_directory') .
                    '/' . $fgsfds->getCommandData('noko', 'topic') . '/' .
                    sprintf(nel_site_domain()->setting('thread_filename_format'),
                        $fgsfds->getCommandData('noko', 'topic')) . NEL_PAGE_EXT;
            }
        } else {
            if ($this->session->inModmode($this->domain)) {
                $url = nel_build_router_url([$this->domain->id()], true, 'modmode');
            } else {
                $url = $this->domain->reference('board_directory') . '/' . NEL_MAIN_INDEX . NEL_PAGE_EXT;
            }
        }

        $redirect->URL($url);
        $output_post = new OutputPost($this->domain, true);
        echo $output_post->postSuccess(['forward_url' => $url], false);
        nel_clean_exit();
    }
}