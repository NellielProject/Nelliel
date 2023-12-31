<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

function nel_special(string $wut_do = null)
{
    if (count($_GET) !== 1 && is_null($wut_do)) {
        return;
    }

    switch ($wut_do) {
    }

    if (isset($_GET['blank']) || isset($_GET['tpilb'])) {
        require_once NEL_WAT_FILES_PATH . 'blank.php';
        nel_blank_page();
    } else if (isset($_GET['dawn'])) {
        header("Content-Type: image/jpeg");
        echo file_get_contents(NEL_WAT_FILES_PATH . 'dawn.jpg');
    } else if (isset($_GET['aeiou'])) {
        header("Location: https://www.youtube.com/watch?v=Hv6RbEOlqRo");
    } else {
        return;
    }

    nel_clean_exit();
}
