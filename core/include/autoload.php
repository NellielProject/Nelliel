<?php
defined('NELLIEL_VERSION') or die('NOPE.AVI');

function nel_autoload_core($class, $prefix, $base_directory): void
{
    $len = utf8_strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = utf8_substr($class, $len);
    $file = $base_directory . utf8_str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
}

require_once NEL_LIBRARY_PATH . 'phpDOMExtend/autoload.php';
require_once NEL_LIBRARY_PATH . 'SmallPHPGettext/autoload.php';
require_once NEL_VENDOR_PATH . 'autoload.php';