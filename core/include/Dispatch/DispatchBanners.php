<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Banners\Banners;
use Nelliel\Domains\Domain;

class DispatchBanners extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs): void
    {
        $banners = new Banners($this->domain);

        switch ($inputs['section']) {
            case 'random':
                $banners_list = array();
                $web_path = NEL_BANNERS_WEB_PATH;
                $banners_list = $banners->getList($this->domain->reference('banners_path'), true);
                $web_path = $this->domain->reference('banners_web_path');

                if (!empty($banners_list)) {
                    $banner = $banners->getRandomBanner($banners_list);

                    if (!is_null($banner)) {
                        $banners->serveBanner($web_path, $banner->getFilename());
                    }
                }

                break;
        }
    }
}