<?php
declare(strict_types = 1);

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Banners
{

    function __construct()
    {
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