<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Language\Translator;
use Nelliel\Setup\Installer\Installer;
use Nelliel\Setup\Upgrade;
use Nelliel\Utility\FileHandler;

Mustache_Autoloader::register();

require_once NEL_INCLUDE_PATH . 'general_functions.php';

if (file_exists(NEL_GENERATED_FILES_PATH . 'peppers.php')) {
    $peppers = array();
    include_once NEL_GENERATED_FILES_PATH . 'peppers.php';
    define('NEL_TRIPCODE_PEPPER', $peppers['tripcode_pepper']);
    define('NEL_IP_ADDRESS_PEPPER', $peppers['ip_address_pepper']);
    define('NEL_POSTER_ID_PEPPER', $peppers['poster_id_pepper']);
    define('NEL_POST_PASSWORD_PEPPER', $peppers['post_password_pepper']);
    unset($peppers);
}

$file_handler = new FileHandler();
$translator = new Translator($file_handler);
$installer = new Installer($file_handler, $translator);

if (isset($_GET['install'])) {
    $installer->install();
}

if (!$installer->checkInstallDone()) {
    nel_derp(107, _gettext('Installation has not been done yet or is not complete.'));
}

unset($translator);
unset($installer);

$upgrade = new Upgrade($file_handler);

if (isset($_GET['upgrade'])) {
    $upgrade->doUpgrades();
    die();
} else {
    if ($upgrade->needsUpgrade()) {
        nel_derp(110,
            _gettext('Versions do not match. An upgrade may be in progress or something is broken. Try again later.'));
    }
}

unset($upgrade);
unset($file_handler);

require_once NEL_INCLUDE_PATH . 'exit_functions.php';
register_shutdown_function('nel_clean_exit');
set_exception_handler('nel_exception_handler');

require_once NEL_INCLUDE_PATH . 'crypt.php';

date_default_timezone_set(nel_site_domain()->setting('time_zone') ?? 'UTC');

define('NEL_SETUP_GOOD', true);

require_once NEL_WAT_FILES_PATH . 'special.php';
nel_special();

nel_plugins()->loadPlugins();
