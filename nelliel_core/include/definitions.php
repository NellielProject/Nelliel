<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

define('NEL_LIBRARY_PATH', NEL_CORE_PATH . 'libraries/'); // Libraries path

define('NEL_OVER_9000', 9001);
define('NEL_ASSETS_DIR', 'assets');
define('NEL_STYLES_DIR', 'styles');
define('NEL_ICON_SETS_DIR', 'icons');
define('NEL_IMAGES_DIR', 'imagez');
define('NEL_TEMPLATES_DIR', 'templates');
define('NEL_SCRIPTS_DIR', 'scripts');
define('NEL_FONTS_DIR', 'fonts');
define('NEL_GENERAL_DIR', '.nelliel');
define('NEL_CAPTCHA_DIR', 'captchas');
define('NEL_BANNERS_DIR', 'banners');

define('NEL_MAIN_SCRIPT', 'imgboard.php');
define('NEL_MAIN_SCRIPT_QUERY', 'imgboard.php?');
define('NEL_MAIN_INDEX', 'index');
define('NEL_PAGE_EXT', '.html');
define('NEL_JSON_EXT', '.json');

define('NEL_ASSETS_TABLE', 'nelliel_assets');
define('NEL_BANS_TABLE', 'nelliel_bans');
define('NEL_BLOTTER_TABLE', 'nelliel_blotter');
define('NEL_BOARD_DATA_TABLE', 'nelliel_board_data');
define('NEL_BOARD_DEFAULTS_TABLE', 'nelliel_board_defaults');
define('NEL_CAPTCHA_TABLE', 'nelliel_captcha');
define('NEL_CITES_TABLE', 'nelliel_cites');
define('NEL_DNSBL_TABLE', 'nelliel_DNSBL');
define('NEL_EMBEDS_TABLE', 'nelliel_embeds');
define('NEL_FILES_FILTERS_TABLE', 'nelliel_file_filters');
define('NEL_FILETYPES_TABLE', 'nelliel_filetypes');
define('NEL_IF_THENS_TABLE', 'nelliel_if_thens');
define('NEL_LOGS_TABLE', 'nelliel_logs');
define('NEL_NEWS_TABLE', 'nelliel_news');
define('NEL_PERMISSIONS_TABLE', 'nelliel_permissions');
define('NEL_PLUGINS_TABLE', 'nelliel_plugins');
define('NEL_PMS_TABLE', 'nelliel_pms');
define('NEL_OVERBOARD_TABLE', 'nelliel_overboard');
define('NEL_RATE_LIMIT_TABLE', 'nelliel_rate_limit');
define('NEL_REPORTS_TABLE', 'nelliel_reports');
define('NEL_ROLE_PERMISSIONS_TABLE', 'nelliel_role_permissions');
define('NEL_ROLES_TABLE', 'nelliel_roles');
define('NEL_SETTINGS_TABLE', 'nelliel_settings');
define('NEL_SITE_CONFIG_TABLE', 'nelliel_site_config');
define('NEL_STAFF_BOARD_TABLE', 'nelliel_staff_board');
define('NEL_TEMPLATES_TABLE', 'nelliel_templates');
define('NEL_USER_ROLES_TABLE', 'nelliel_user_roles');
define('NEL_USERS_TABLE', 'nelliel_users');
define('NEL_VERSIONS_TABLE', 'nelliel_version');
define('NEL_WORD_FILTERS_TABLE', 'nelliel_word_filters');

define('NEL_ASSETS_FILES_PATH', NEL_BASE_PATH . NEL_ASSETS_DIR . '/');
define('NEL_CONFIG_FILES_PATH', NEL_CORE_PATH . 'configuration/');
define('NEL_CACHE_FILES_PATH', NEL_CORE_PATH . 'cache/');
define('NEL_TEMPLATES_FILES_PATH', NEL_CORE_PATH . NEL_TEMPLATES_DIR . '/');
define('NEL_FONTS_FILES_PATH', NEL_ASSETS_FILES_PATH . NEL_FONTS_DIR . '/');
define('NEL_GENERATED_FILES_PATH', NEL_CORE_PATH . 'generated/');
define('NEL_PLUGINS_FILES_PATH', NEL_CORE_PATH . 'plugins/');
define('NEL_LANGUAGES_FILES_PATH', NEL_CORE_PATH . 'languages/');
define('NEL_LOCALE_FILES_PATH', NEL_LANGUAGES_FILES_PATH . 'locale/');
define('NEL_STYLES_FILES_PATH', NEL_ASSETS_FILES_PATH . NEL_STYLES_DIR . '/');
define('NEL_ICON_SETS_FILES_PATH', NEL_ASSETS_FILES_PATH . NEL_ICON_SETS_DIR . '/');
define('NEL_BANNERS_FILES_PATH', NEL_BASE_PATH . NEL_BANNERS_DIR . '/');
define('NEL_WAT_FILES_PATH', NEL_INCLUDE_PATH . 'wat/');
define('NEL_GENERAL_FILES_PATH', NEL_BASE_PATH . NEL_GENERAL_DIR . '/');
define('NEL_CAPTCHA_FILES_PATH', NEL_GENERAL_FILES_PATH . NEL_CAPTCHA_DIR . '/');


$dirname = pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);

// When running at web root $dirname would result in // which has special meaning and all the URLs are fucked
if($dirname === '/')
{
    define('NEL_BASE_WEB_PATH', '/');
}
else
{
    define('NEL_BASE_WEB_PATH', $dirname . '/');
}

unset($dirname);

define('NEL_ASSETS_WEB_PATH', NEL_BASE_WEB_PATH . NEL_ASSETS_DIR . '/');
define('NEL_SCRIPTS_WEB_PATH', NEL_ASSETS_WEB_PATH . NEL_SCRIPTS_DIR . '/');
define('NEL_IMAGES_WEB_PATH', NEL_ASSETS_WEB_PATH . NEL_IMAGES_DIR . '/');
define('NEL_STYLES_WEB_PATH', NEL_ASSETS_WEB_PATH . NEL_STYLES_DIR . '/');
define('NEL_ICON_SETS_WEB_PATH', NEL_ASSETS_WEB_PATH . NEL_ICON_SETS_DIR . '/');
define('NEL_BANNERS_WEB_PATH', NEL_BASE_WEB_PATH . NEL_BANNERS_DIR . '/');
define('NEL_MAIN_SCRIPT_WEB_PATH', NEL_BASE_WEB_PATH . NEL_MAIN_SCRIPT);
define('NEL_MAIN_SCRIPT_QUERY_WEB_PATH', NEL_BASE_WEB_PATH . NEL_MAIN_SCRIPT_QUERY);
define('NEL_GENERAL_WEB_PATH', NEL_GENERAL_DIR . '/');
define('NEL_CAPTCHA_WEB_PATH', NEL_GENERAL_WEB_PATH . NEL_CAPTCHA_DIR . '/');

define('NEL_SQLITE_DB_DEFAULT_PATH', '../');
