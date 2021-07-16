<?php
declare(strict_types = 1);

namespace Nelliel\Modules\Banners;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Modules\Account\Session;

class Dispatch
{
    private $domain;
    private $site_domain;
    private $session;

    function __construct(Domain $domain, Authorization $authorization, Session $session)
    {
        $this->domain = $domain;
        $this->site_domain = nel_site_domain();
        $this->session = $session;
    }

    public function dispatch(array $inputs)
    {
        $banners = new Banners($this->domain);

        switch ($inputs['actions'][0])
        {
            case 'get-random':
                $banners_list = array();
                $web_path = NEL_BANNERS_WEB_PATH;

                if ($this->site_domain->setting('show_board_banners'))
                {
                    if ($this->domain->id() !== Domain::SITE)
                    {
                        $banners_list = $banners->getList($this->domain->reference('banners_path'));
                        $web_path = $this->domain->reference('banners_web_path');
                    }
                }

                if ($this->site_domain->setting('show_site_banners'))
                {
                    if ($this->domain->id() === Domain::SITE || empty($banners_list))
                    {
                        $banners_list = $banners->getList($this->site_domain->reference('banners_path'));
                        $web_path = $this->site_domain->reference('banners_web_path');
                    }
                }

                $banner = $banners->getRandomBanner($banners_list);

                if (!is_null($banner))
                {
                    $banners->serveBanner($web_path, $banner->getFilename());
                }

                break;
        }
    }
}