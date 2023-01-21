<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Setup\Setup;
use Nelliel\Setup\Upgrade;
use Nelliel\Tables\TableSettings;

Mustache_Autoloader::register();

require_once NEL_INCLUDE_PATH . 'crypt.php';
nel_set_password_algorithm(NEL_PASSWORD_PREFERRED_ALGORITHM);

require_once NEL_INCLUDE_PATH . 'general_functions.php';
$file_handler = nel_utilities()->fileHandler();
$setup = new Setup(nel_database('core'), nel_utilities()->sqlCompatibility(), $file_handler);

if (isset($_GET['install'])) {
    $setup->install();
}

if (!$setup->checkInstallDone()) {
    nel_derp(107, _gettext('Installation has not been done yet or is not complete.'));
}

unset($setup);

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

if (file_exists(NEL_GENERATED_FILES_PATH . 'peppers.php')) {
    $peppers = array();
    include_once NEL_GENERATED_FILES_PATH . 'peppers.php';
    define('NEL_TRIPCODE_PEPPER', $peppers['tripcode_pepper']);
    define('NEL_IP_ADDRESS_PEPPER', $peppers['ip_address_pepper']);
    define('NEL_POSTER_ID_PEPPER', $peppers['poster_id_pepper']);
    define('NEL_POST_PASSWORD_PEPPER', $peppers['post_password_pepper']);
    unset($peppers);
}

unset($file_handler);

define('NEL_SETUP_GOOD', true);

require_once NEL_WAT_FILES_PATH . 'special.php';
nel_special();

nel_plugins()->loadPlugins();
