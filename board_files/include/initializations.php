<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

ignore_user_abort(TRUE);

// TODO: Clean all these up along with the other includes
require_once INCLUDE_PATH . 'output/posting_form.php';
require_once INCLUDE_PATH . 'output/header.php';
require_once INCLUDE_PATH . 'output/post.php';
require_once INCLUDE_PATH . 'output/footer.php';

if(RUN_SETUP_CHECK)
{
    setup_check(INPUT_BOARD_ID);
}

$dataforce = array();
$dataforce['mode'] = NULL;
$dataforce['get_mode'] = NULL;
$dataforce['login_valid'] = false;

if (!empty($_POST))
{
    $dataforce['mode'] = (isset($_POST['mode'])) ? $_POST['mode']: NULL;
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
if (!file_exists(CACHE_PATH . INPUT_BOARD_ID . '/board_settings.nelcache')) // TODO: Clear this out once we're converted to the new board setting system
{
    if(nel_cache_board_settings(INPUT_BOARD_ID) !== false)
    {
        require_once CACHE_PATH . INPUT_BOARD_ID . '/board_settings.nelcache';
    }
}
else
{
    require_once CACHE_PATH . INPUT_BOARD_ID . '/board_settings.nelcache';
}


/*// Cached filetype settings

if (!file_exists(CACHE_PATH . INPUT_BOARD_ID . '/filetype_settings.nelcache'))
{
    nel_cache_filetype_settings(INPUT_BOARD_ID);
}

require_once CACHE_PATH . INPUT_BOARD_ID . '/filetype_settings.nelcache';*/
