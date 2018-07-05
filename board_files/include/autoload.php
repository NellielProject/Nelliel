<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

spl_autoload_register(function ($class)
{
    $prefix = 'Nelliel\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0)
    {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file))
    {
        require $file;
    }
});

spl_autoload_register(function ($class)
{
    $prefix = 'Nelliel\\';
    $base_dir = __DIR__ . '/classes/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0)
    {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file))
    {
        require $file;
    }
});

require_once LIBRARY_PATH . 'phpDOMExtend/autoload.php';
require_once LIBRARY_PATH . 'NellielTemplates/autoload.php';
require_once LIBRARY_PATH . 'SmallPHPGettext/autoload.php';
