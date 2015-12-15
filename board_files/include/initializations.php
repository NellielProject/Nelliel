<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}
$dbh;
if (SQLTYPE === 'MYSQL')
{
    $dbh = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB, MYSQL_USER, MYSQL_PASS);
    $dbh->exec("SET CHARACTER SET utf8");
}
else if (SQLTYPE === 'SQLITE')
{
    $dbh = new PDO('sqlite:' . SQLITE_DB_LOCATION . SQLITE_DB_NAME);
}
else
{
    die("No valid database type specified in config. Can't do shit cap'n!");
}

if (ini_get('date.timezone') === '')
{
    date_default_timezone_set('UTC');
}

ignore_user_abort(TRUE);
require_once INCLUDE_PATH . 'setup.php';
require_once INCLUDE_PATH . 'file-handling.php';
setup_check($dbh);
generate_auth_file();
include_once FILES_PATH . '/auth_data.nel.php';
require_once INCLUDE_PATH . 'language.php';

$template_info = array();
$rendervar = array();
$dataforce = array();
$enabled_types = array();

$dataforce['mode'] = (isset($_POST['mode'])) ? $_POST['mode'] : NULL;
$dataforce['mode_extra'] = (isset($_POST['mode2'])) ? $_POST['mode2'] : NULL;
$dataforce['admin_mode'] = (isset($_POST['adminmode'])) ? $_POST['adminmode'] : NULL;
$dataforce['name'] = (isset($_POST['notanonymous'])) ? $_POST['notanonymous'] : '';
$dataforce['email'] = (isset($_POST['spamtarget'])) ? $_POST['spamtarget'] : '';
$dataforce['subject'] = (isset($_POST['verb'])) ? $_POST['verb'] : '';
$dataforce['comment'] = (isset($_POST['wordswordswords'])) ? $_POST['wordswordswords'] : '';
$dataforce['fgsfds'] = (isset($_POST['fgsfds'])) ? $_POST['fgsfds'] : NULL;
$dataforce['file_source'] = (isset($_POST['sauce'])) ? $_POST['sauce'] : NULL;
$dataforce['file_license'] = (isset($_POST['loldrama'])) ? $_POST['loldrama'] : NULL;
$dataforce['pass'] = (isset($_POST['sekrit'])) ? $_POST['sekrit'] : NULL;
$dataforce['admin_pass'] = (isset($_POST['super_sekrit'])) ? $_POST['super_sekrit'] : NULL;
$dataforce['username'] = (isset($_POST['username'])) ? $_POST['username'] : NULL;
$dataforce['usrdel'] = (isset($_POST['usrdel'])) ? $_POST['usrdel'] : NULL;
$dataforce['expand_thread'] = (isset($_POST['expand_thread'])) ? $_POST['expand_thread'] : NULL;
$dataforce['banpost'] = (isset($_POST['banpost'])) ? TRUE : FALSE;
$dataforce['banid'] = (isset($_POST['banid']) && is_numeric($_POST['banid'])) ? (int) $_POST['banid'] : NULL;
$dataforce['banreason'] = (isset($_POST['banreason'])) ? $_POST['banreason'] : NULL;
$dataforce['banip'] = (isset($_POST['ban_ip'])) ? $_POST['ban_ip'] : NULL;
$dataforce['timedays'] = (isset($_POST['timedays'])) && is_numeric($_POST['timedays']) ? (int) $_POST['timedays'] : NULL;
$dataforce['timehours'] = (isset($_POST['timehours'])) && is_numeric($_POST['timehours']) ? (int) $_POST['timehours'] : NULL;
$dataforce['only_delete_file'] = (isset($_POST['onlyimgdel'])) ? TRUE : FALSE;
$dataforce['response_to'] = (isset($_POST['response_to']) && is_numeric($_POST['response_to'])) ? (int) $_POST['response_to'] : NULL;
$dataforce['page_gen'] = 'main';
$dataforce['mode2'] = (isset($_GET['mode'])) ? $_GET['mode'] : NULL;
$dataforce['current_page'] = (isset($_GET['page'])) ? $_GET['page'] : NULL;
$dataforce['expand'] = (isset($_GET['expand'])) ? TRUE : FALSE;
$dataforce['collapse'] = (isset($_GET['collapse'])) ? TRUE : FALSE;
$dataforce['response_id'] = (isset($_GET['post']) && is_numeric($_GET['post'])) ? (int) $_GET['post'] : NULL;
$dataforce['archive_update'] = FALSE;
$dataforce['sp_field1'] = (isset($_POST[stext('TEXT_SPAMBOT_FIELD1')])) ? $_POST[stext('TEXT_SPAMBOT_FIELD1')] : NULL;
$dataforce['sp_field2'] = (isset($_POST[stext('TEXT_SPAMBOT_FIELD2')])) ? $_POST[stext('TEXT_SPAMBOT_FIELD2')] : NULL;
$dataforce['post_links'] = '';

$link_updates = '';
$start_html = 0;
$end_html = 0;
$total_html = 0;
$fgsfds = array('noko' => FALSE, 'noko_topic' => 0, 'sage' => FALSE, 'sticky' => FALSE);
$link_resno = 0;

// Load caching routines and handle current cache files
require_once INCLUDE_PATH . 'cache-functions.php'; // I liek cache
$dataforce['max_pages'] = BS_PAGE_LIMIT;
?>
