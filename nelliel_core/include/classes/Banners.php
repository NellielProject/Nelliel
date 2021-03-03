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

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function dispatch(array $inputs)
    {
        switch ($inputs['actions'][0])
        {
            case 'get-random':
                $banner = $this->getRandomBanner($this->domain->reference('banners_path'));

                if (!nel_true_empty($banner))
                {
                    $this->serveBanner($this->domain->reference('banners_web_path'), $banner);
                }

                break;
        }
    }

    public function getRandomBanner(string $banners_path): string
    {
        $banner = '';
        $file_handler = new \Nelliel\Utility\FileHandler();
        $banners_list = $file_handler->recursiveFileList($banners_path, 0);
        $banners_count = count($banners_list);

        if ($banners_count > 0)
        {
            $banner = $banners_list[mt_rand(0, $banners_count - 1)]->getFilename();
        }

        return $banner;
    }

    public function serveBanner(string $banners_web_path, string $filename): void
    {
        header('Location: ' . $banners_web_path . $filename);
    }
}