<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Path definitions
//

define('CONFIG_PATH', BASE_PATH . 'configuration/'); // Base cache path
define('LIBRARY_PATH', FILES_PATH . 'libraries/'); // Libraries path
define('PLUGINS_PATH', FILES_PATH . 'plugins/'); // Base plugins path
define('TEMPLATE_PATH', FILES_PATH . 'templates/nelliel/'); // Base template path
define('LANGUAGE_PATH', FILES_PATH . 'languages/'); // Language files path
define('CACHE_PATH', FILES_PATH . 'cache/'); // Base cache path
define('WEB_PATH', FILES_PATH . 'web/'); // Base cache path

define('SQLITE_DB_DEFAULT_PATH', FILES_PATH); // Base SQLite DB location

$dataforce = array();
$dataforce['login_valid'] = false;
$dataforce['mode'] = (isset($_POST['mode'])) ? $_POST['mode'] : NULL;
$dataforce['get_mode'] = (isset($_GET['mode'])) ? $_GET['mode'] : NULL;
$dataforce['current_page'] = (isset($_GET['page'])) ? $_GET['page'] : NULL;
$dataforce['expand'] = (isset($_GET['expand'])) ? TRUE : FALSE;
$dataforce['collapse'] = (isset($_GET['collapse'])) ? TRUE : FALSE;
$dataforce['response_id'] = (isset($_GET['post']) && is_numeric($_GET['post'])) ? (int) $_GET['post'] : NULL;
