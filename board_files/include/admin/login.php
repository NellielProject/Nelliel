<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/main_panel.php';
require_once INCLUDE_PATH . 'output/management/login_page.php';

function nel_login($dataforce)
{
    if (!nel_session_is_ignored())
    {
        nel_generate_main_panel();
    }
    else
    {
        nel_insert_default_admin(); // Let's make sure there's some kind of admin in the system
        nel_insert_role_defaults(); // Also make sure the role exists
        nel_generate_login_page();
    }
}
