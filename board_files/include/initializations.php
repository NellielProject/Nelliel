<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

ignore_user_abort(TRUE);

// TODO: Clean all these up along with the other includes
require_once LIBRARY_PATH . 'portable-utf8/portable-utf8.php';
require_once INCLUDE_PATH . 'setup/setup.php';
require_once INCLUDE_PATH . 'output/posting_form.php';
require_once INCLUDE_PATH . 'output/header.php';
require_once INCLUDE_PATH . 'output/post.php';
require_once INCLUDE_PATH . 'output/footer.php';

if(RUN_SETUP_CHECK)
{
    setup_check();
}

$dataforce = array();
$dataforce['mode'] = NULL;
$dataforce['get_mode'] = NULL;
$dataforce['login_valid'] = false;

if (!empty($_POST))
{
    $dataforce['mode'] = (isset($_POST['mode'])) ? $_POST['mode']: NULL;

    if(isset($_POST['board_id']))
    {
        define('INPUT_BOARD_ID', $_POST['board_id']);
    }
    else
    {
        define('INPUT_BOARD_ID', null);
    }
}

if (!empty($_GET))
{
    $dataforce['get_mode'] = (isset($_GET['mode'])) ? $_GET['mode'] : NULL;
    $dataforce['current_page'] = (isset($_GET['page'])) ? $_GET['page'] : NULL;
    $dataforce['expand'] = (isset($_GET['expand'])) ? TRUE : FALSE;
    $dataforce['collapse'] = (isset($_GET['collapse'])) ? TRUE : FALSE;
    $dataforce['response_id'] = (isset($_GET['post']) && is_numeric($_GET['post'])) ? (int) $_GET['post'] : NULL;
}

// Load caching routines and handle current cache files

require_once INCLUDE_PATH . 'cache_functions.php'; // I liek cache

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
