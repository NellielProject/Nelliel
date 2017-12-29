<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

ignore_user_abort(TRUE);

// TODO: Clean all these up along with the other includes
require_once LIBRARY_PATH . 'portable-utf8/portable-utf8.php';
require_once INCLUDE_PATH . 'wat.php';
require_once INCLUDE_PATH . 'setup/setup.php';
require_once INCLUDE_PATH . 'output/posting_form.php';
require_once INCLUDE_PATH . 'output/header.php';
require_once INCLUDE_PATH . 'output/post.php';
require_once INCLUDE_PATH . 'output/footer.php';

if(RUN_SETUP_CHECK)
{
    setup_check();
}

require_once INCLUDE_PATH . 'language/language.php';

$dataforce = array();
$enabled_types = array();
$dataforce['archive_update'] = FALSE;
$dataforce['sp_field1'] = (!empty($_POST[nel_stext('TEXT_SPAMBOT_FIELD1')])) ? $_POST[nel_stext('TEXT_SPAMBOT_FIELD1')] : NULL;
$dataforce['sp_field2'] = (!empty($_POST[nel_stext('TEXT_SPAMBOT_FIELD2')])) ? $_POST[nel_stext('TEXT_SPAMBOT_FIELD2')] : NULL;
$dataforce['mode'] = NULL;
$dataforce['get_mode'] = NULL;
$dataforce['login_valid'] = false;

if (!empty($_POST))
{
    $dataforce['mode'] = (isset($_POST['mode'])) ? $_POST['mode']: NULL;
    $dataforce['admin_pass'] = (isset($_POST['super_sekrit'])) ? $_POST['super_sekrit'] : NULL;
    $dataforce['username'] = (isset($_POST['username'])) ? $_POST['username'] : NULL;
    $dataforce['expand_thread'] = (isset($_POST['expand_thread'])) ? $_POST['expand_thread'] : NULL;
    $dataforce['delpost'] = (isset($_POST['delpost'])) ? TRUE : FALSE;
    $dataforce['banpost'] = (isset($_POST['banpost'])) ? TRUE : FALSE;
    $dataforce['response_to'] = (isset($_POST['response_to']) && is_numeric($_POST['response_to'])) ? (int) $_POST['response_to'] : NULL;
    $dataforce['only_delete_file'] = (isset($_POST['onlyimgdel'])) ? TRUE : FALSE;
}

if (!empty($_GET))
{
    $dataforce['get_mode'] = (isset($_GET['mode'])) ? $_GET['mode'] : NULL;
    $dataforce['current_page'] = (isset($_GET['page'])) ? $_GET['page'] : NULL;
    $dataforce['expand'] = (isset($_GET['expand'])) ? TRUE : FALSE;
    $dataforce['collapse'] = (isset($_GET['collapse'])) ? TRUE : FALSE;
    $dataforce['response_id'] = (isset($_GET['post']) && is_numeric($_GET['post'])) ? (int) $_GET['post'] : NULL;
}

$fgsfds = array('noko' => FALSE, 'noko_topic' => 0, 'sage' => FALSE, 'sticky' => FALSE);
$link_resno = 0;

// Load caching routines and handle current cache files

require_once INCLUDE_PATH . 'cache-functions.php'; // I liek cache

// Cached board settings
if (!file_exists(CACHE_PATH . 'board_settings.nelcache'))
{
    nel_cache_board_settings();
}

require_once CACHE_PATH . 'board_settings.nelcache';

// Cached filetype settings
if (!file_exists(CACHE_PATH . 'filetype_settings.nelcache'))
{
    nel_cache_filetype_settings();
}

require_once CACHE_PATH . 'filetype_settings.nelcache';
$dataforce['max_pages'] = BS_PAGE_LIMIT;
