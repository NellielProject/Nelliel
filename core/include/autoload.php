<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

require_once NEL_LIBRARY_PATH . 'phpDOMExtend/autoload.php';
require_once NEL_LIBRARY_PATH . 'SmallPHPGettext/autoload.php';
require_once NEL_VENDOR_PATH . 'autoload.php';
spl_autoload_register('\Nelliel\API\Plugin\PluginAPI::autoload');