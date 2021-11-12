<?php
declare(strict_types = 1);

namespace Nelliel\Banners;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Utility\FileHandler;

class Banners
{

    function __construct()
    {}

    public function getList(string $banners_path, bool $exclude_hidden): array
    {
        $file_handler = new FileHandler();
        $banners_list = $file_handler->recursiveFileList($banners_path, 0);

        if ($exclude_hidden) {
            $filtered_banners = array();

            foreach ($banners_list as $banner) {
                if ($banner->getFilename()[0] !== '.') {
                    $filtered_banners[] = $banner;
                }
            }

            $banners_list = $filtered_banners;
        }

        return $banners_list;
    }

    public function getRandomBanner(array $banners_list)
    {
        $banner = null;
        $banners_count = count($banners_list);

        if ($banners_count > 0) {
            $banner = $banners_list[mt_rand(0, $banners_count - 1)];
        }

        return $banner;
    }

    public function serveBanner(string $banners_web_path, string $filename): void
    {
        header('Location: ' . $banners_web_path . $filename);
    }
}