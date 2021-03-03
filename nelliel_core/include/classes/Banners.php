<?php
declare(strict_types = 1);

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;

class Banners
{
    protected $domain;
    protected $site_domain;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->site_domain = nel_site_domain();
    }

    public function dispatch(array $inputs): void
    {
        switch ($inputs['actions'][0])
        {
            case 'get-random':
                $banners_list = array();
                $web_path = NEL_BANNERS_WEB_PATH;

                if ($this->site_domain->setting('show_board_banners'))
                {
                    if ($this->domain->id() !== Domain::SITE)
                    {
                        $banners_list = $this->getList($this->domain->reference('banners_path'));
                        $web_path = $this->domain->reference('banners_web_path');
                    }
                }

                if ($this->site_domain->setting('show_site_banners'))
                {
                    if ($this->domain->id() === Domain::SITE || empty($banners_list))
                    {
                        $banners_list = $this->getList($this->site_domain->reference('banners_path'));
                        $web_path = $this->site_domain->reference('banners_web_path');
                    }
                }

                $banner = $this->getRandomBanner($banners_list);

                if (!is_null($banner))
                {
                    $this->serveBanner($web_path, $banner->getFilename());
                }

                break;
        }
    }

    public function getList(string $banners_path): array
    {
        $file_handler = new \Nelliel\Utility\FileHandler();
        $banners_list = $file_handler->recursiveFileList($banners_path, 0);
        return $banners_list;
    }

    public function getRandomBanner(array $banners_list)
    {
        $banner = null;
        $banners_count = count($banners_list);

        if ($banners_count > 0)
        {
            $banner = $banners_list[mt_rand(0, $banners_count - 1)];
        }

        return $banner;
    }

    public function serveBanner(string $banners_web_path, string $filename): void
    {
        header('Location: ' . $banners_web_path . $filename);
    }
}