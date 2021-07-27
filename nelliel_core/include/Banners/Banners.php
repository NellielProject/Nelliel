<?php
declare(strict_types = 1);

namespace Nelliel\Banners;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Utility\FileHandler;

class Banners
{
    private $domain;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function getList(string $banners_path): array
    {
        $file_handler = new FileHandler();
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