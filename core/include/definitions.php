<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

// Everything in this file should a hard-coded constant

define('NEL_DEFAULT_LOCALE', 'en_US');

define('NEL_LIBRARY_PATH', NEL_CORE_PATH . 'libraries/'); // Provided libraries path
define('NEL_VENDOR_PATH', NEL_BASE_PATH . 'vendor/'); // Composer vendor path

define('OVER_9000', 9001);
define('NEL_VISITOR_ID_VERSION', 1);
define('NEL_HELLIPSIS', '…');
define('NEL_ASSETS_DIR', 'assets');
define('NEL_STYLES_DIR', 'styles');
define('NEL_IMAGE_SETS_DIR', 'image_sets');
define('NEL_MEDIA_DIR', 'media');
define('NEL_TEMPLATES_DIR', 'templates');
define('NEL_SCRIPTS_DIR', 'scripts');
define('NEL_GENERAL_DIR', '.nelliel');
define('NEL_CAPTCHA_DIR', 'captchas');
define('NEL_BANNERS_DIR', 'banners');
define('NEL_DOCUMENTATION_DIR', 'documentation');

define('NEL_MAIN_SCRIPT', 'imgboard.php');
define('NEL_MAIN_SCRIPT_QUERY', 'imgboard.php?');
define('NEL_MAIN_INDEX', 'index');
define('NEL_PAGE_EXT', '.html');
define('NEL_JSON_EXT', '.json');

define('NEL_BAN_APPEALS_TABLE', 'nelliel_ban_appeals');
define('NEL_BANS_TABLE', 'nelliel_bans');
define('NEL_BLOTTER_TABLE', 'nelliel_blotter');
define('NEL_BOARD_DATA_TABLE', 'nelliel_board_data');
define('NEL_BOARD_CONFIGS_TABLE', 'nelliel_board_configs');
define('NEL_BOARD_DEFAULTS_TABLE', 'nelliel_board_defaults');
define('NEL_CACHE_TABLE', 'nelliel_cache');
define('NEL_CAPCODES_TABLE', 'nelliel_capcodes');
define('NEL_CAPTCHA_TABLE', 'nelliel_captcha');
define('NEL_CITES_TABLE', 'nelliel_cites');
define('NEL_CONTENT_OPS_TABLE', 'nelliel_content_ops');
define('NEL_DOMAIN_REGISTRY_TABLE', 'nelliel_domain_registry');
define('NEL_EMBEDS_TABLE', 'nelliel_embeds');
define('NEL_FILE_FILTERS_TABLE', 'nelliel_file_filters');
define('NEL_FILETYPE_CATEGORIES_TABLE', 'nelliel_filetype_categories');
define('NEL_FILETYPES_TABLE', 'nelliel_filetypes');
define('NEL_GLOBAL_RECENTS_TABLE', 'nelliel_global_recents');
define('NEL_IMAGE_SETS_TABLE', 'nelliel_image_sets');
define('NEL_IP_INFO_TABLE', 'nelliel_ip_info');
define('NEL_IP_NOTES_TABLE', 'nelliel_ip_notes');
define('NEL_MARKUP_TABLE', 'nelliel_markup');
define('NEL_NEWS_TABLE', 'nelliel_news');
define('NEL_NOTICEBOARD_TABLE', 'nelliel_noticeboard');
define('NEL_OVERBOARD_TABLE', 'nelliel_overboard');
define('NEL_PAGES_TABLE', 'nelliel_pages');
define('NEL_PERMISSIONS_TABLE', 'nelliel_permissions');
define('NEL_PLUGIN_CONFIGS_TABLE', 'nelliel_plugin_configs');
define('NEL_PLUGINS_TABLE', 'nelliel_plugins');
define('NEL_PRIVATE_MESSAGES_TABLE', 'nelliel_private_messages');
define('NEL_PUBLIC_LOGS_TABLE', 'nelliel_public_logs');
define('NEL_R9K_CONTENT_TABLE', 'nelliel_r9k_content');
define('NEL_R9K_MUTES_TABLE', 'nelliel_r9k_mutes');
define('NEL_RATE_LIMIT_TABLE', 'nelliel_rate_limit');
define('NEL_REPORTS_TABLE', 'nelliel_reports');
define('NEL_ROLE_PERMISSIONS_TABLE', 'nelliel_role_permissions');
define('NEL_ROLES_TABLE', 'nelliel_roles');
define('NEL_SCRIPTS_TABLE', 'nelliel_scripts');
define('NEL_SETTING_OPTIONS_TABLE', 'nelliel_setting_options');
define('NEL_SETTINGS_TABLE', 'nelliel_settings');
define('NEL_SITE_CONFIG_TABLE', 'nelliel_site_config');
define('NEL_STATISTICS_TABLE', 'nelliel_statistics');
define('NEL_STYLES_TABLE', 'nelliel_styles');
define('NEL_SYSTEM_LOGS_TABLE', 'nelliel_system_logs');
define('NEL_TEMPLATES_TABLE', 'nelliel_templates');
define('NEL_USER_ROLES_TABLE', 'nelliel_user_roles');
define('NEL_USERS_TABLE', 'nelliel_users');
define('NEL_VERSIONS_TABLE', 'nelliel_version');
define('NEL_VISITOR_INFO_TABLE', 'nelliel_visitor_info');
define('NEL_WORDFILTERS_TABLE', 'nelliel_wordfilters');

define('NEL_ASSETS_FILES_PATH', NEL_PUBLIC_PATH . NEL_ASSETS_DIR . '/');
define('NEL_CONFIG_FILES_PATH', NEL_BASE_PATH . 'configuration/');
define('NEL_CACHE_FILES_PATH', NEL_CORE_PATH . 'cache/');
define('NEL_TEMPLATES_FILES_PATH', NEL_BASE_PATH . NEL_TEMPLATES_DIR . '/');
define('NEL_GENERATED_FILES_PATH', NEL_CORE_PATH . 'generated/');
define('NEL_PLUGINS_FILES_PATH', NEL_BASE_PATH . 'plugins/');
define('NEL_LANGUAGES_FILES_PATH', NEL_BASE_PATH . 'languages/');
define('NEL_LOCALE_FILES_PATH', NEL_LANGUAGES_FILES_PATH . 'locale/');
define('NEL_STYLES_FILES_PATH', NEL_ASSETS_FILES_PATH . NEL_STYLES_DIR . '/');
define('NEL_IMAGE_SETS_FILES_PATH', NEL_ASSETS_FILES_PATH . NEL_IMAGE_SETS_DIR . '/');
define('NEL_BANNERS_FILES_PATH', NEL_ASSETS_FILES_PATH . NEL_BANNERS_DIR . '/');
define('NEL_WAT_FILES_PATH', NEL_INCLUDE_PATH . 'wat/');
define('NEL_GENERAL_FILES_PATH', NEL_PUBLIC_PATH . NEL_GENERAL_DIR . '/');
define('NEL_CAPTCHA_FILES_PATH', NEL_GENERAL_FILES_PATH . NEL_CAPTCHA_DIR . '/');
define('NEL_SCRIPTS_FILES_PATH', NEL_ASSETS_FILES_PATH . NEL_SCRIPTS_DIR . '/');
define('NEL_MEDIA_FILES_PATH', NEL_ASSETS_FILES_PATH . NEL_MEDIA_DIR . '/');

define('NEL_ASSETS_WEB_PATH', NEL_BASE_WEB_PATH . NEL_ASSETS_DIR . '/');
define('NEL_SCRIPTS_WEB_PATH', NEL_ASSETS_WEB_PATH . NEL_SCRIPTS_DIR . '/');
define('NEL_MEDIA_WEB_PATH', NEL_ASSETS_WEB_PATH . NEL_MEDIA_DIR . '/');
define('NEL_STYLES_WEB_PATH', NEL_ASSETS_WEB_PATH . NEL_STYLES_DIR . '/');
define('NEL_IMAGE_SETS_WEB_PATH', NEL_ASSETS_WEB_PATH . NEL_IMAGE_SETS_DIR . '/');
define('NEL_BANNERS_WEB_PATH', NEL_ASSETS_WEB_PATH . NEL_BANNERS_DIR . '/');
define('NEL_MAIN_SCRIPT_WEB_PATH', NEL_BASE_WEB_PATH . NEL_MAIN_SCRIPT);
define('NEL_MAIN_SCRIPT_QUERY_WEB_PATH', NEL_BASE_WEB_PATH . NEL_MAIN_SCRIPT_QUERY);
define('NEL_GENERAL_WEB_PATH', NEL_GENERAL_DIR . '/');
define('NEL_CAPTCHA_WEB_PATH', NEL_GENERAL_WEB_PATH . NEL_CAPTCHA_DIR . '/');

define('NEL_INTERNAL_FILE_HEADER',
    '<?php
// This file was generated by the Nelliel imageboard software for internal use only
defined(\'NELLIEL_VERSION\') or die(\'NOPE.AVI\');
');

define('NEL_WEB_PROTOCOL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
define('NEL_SITE_DOMAIN', $_SERVER['HTTP_HOST'] ?? 'localhost');
define('NEL_URL_BASE', NEL_WEB_PROTOCOL . '://' . NEL_SITE_DOMAIN);


