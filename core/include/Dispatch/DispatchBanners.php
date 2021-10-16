<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Banners\Banners;
use Nelliel\Domains\Domain;

class DispatchBanners extends Dispatch
{
    private $site_domain;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->site_domain = nel_site_domain();
    }

    public function dispatch(array $inputs)
    {
        $banners = new Banners($this->domain);

        switch ($inputs['section']) {
            case 'random':
                $banners_list = array();
                $web_path = NEL_BANNERS_WEB_PATH;

                if ($this->site_domain->setting('show_board_banners')) {
                    if ($this->domain->id() !== Domain::SITE) {
                        $banners_list = $banners->getList($this->domain->reference('banners_path'));
                        $web_path = $this->domain->reference('banners_web_path');
                    }
                }

                if ($this->site_domain->setting('show_site_banners')) {
                    if ($this->domain->id() === Domain::SITE || empty($banners_list)) {
                        $banners_list = $banners->getList($this->site_domain->reference('banners_path'));
                        $web_path = $this->site_domain->reference('banners_web_path');
                    }
                }

                $banner = $banners->getRandomBanner($banners_list);

                if (!is_null($banner)) {
                    $banners->serveBanner($web_path, $banner->getFilename());
                }

                break;
        }
    }
}