<?php
declare(strict_types = 1);

namespace Nelliel\Setup\Installer;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use Nelliel\FrontEnd\FrontEndData;
use Nelliel\Language\Language;
use Nelliel\Language\Translator;
use Nelliel\Render\RenderCoreSimple;
use Nelliel\Setup\GenerateFiles;
use Nelliel\Tables\TableBanAppeals;
use Nelliel\Tables\TableBans;
use Nelliel\Tables\TableBlotter;
use Nelliel\Tables\TableBoardConfigs;
use Nelliel\Tables\TableBoardData;
use Nelliel\Tables\TableBoardDefaults;
use Nelliel\Tables\TableCache;
use Nelliel\Tables\TableCapcodes;
use Nelliel\Tables\TableCaptcha;
use Nelliel\Tables\TableCites;
use Nelliel\Tables\TableContentOps;
use Nelliel\Tables\TableDomainRegistry;
use Nelliel\Tables\TableEmbeds;
use Nelliel\Tables\TableFileFilters;
use Nelliel\Tables\TableFiletypeCategories;
use Nelliel\Tables\TableFiletypes;
use Nelliel\Tables\TableGlobalRecents;
use Nelliel\Tables\TableIPInfo;
use Nelliel\Tables\TableIPNotes;
use Nelliel\Tables\TableImageSets;
use Nelliel\Tables\TableLogs;
use Nelliel\Tables\TableMarkup;
use Nelliel\Tables\TableNews;
use Nelliel\Tables\TableNoticeboard;
use Nelliel\Tables\TableOverboard;
use Nelliel\Tables\TablePages;
use Nelliel\Tables\TablePermissions;
use Nelliel\Tables\TablePluginConfigs;
use Nelliel\Tables\TablePlugins;
use Nelliel\Tables\TablePosts;
use Nelliel\Tables\TablePrivateMessages;
use Nelliel\Tables\TableR9KContent;
use Nelliel\Tables\TableR9KMutes;
use Nelliel\Tables\TableRateLimit;
use Nelliel\Tables\TableReports;
use Nelliel\Tables\TableRolePermissions;
use Nelliel\Tables\TableRoles;
use Nelliel\Tables\TableScripts;
use Nelliel\Tables\TableSettingOptions;
use Nelliel\Tables\TableSettings;
use Nelliel\Tables\TableSiteConfig;
use Nelliel\Tables\TableStatistics;
use Nelliel\Tables\TableStyles;
use Nelliel\Tables\TableTemplates;
use Nelliel\Tables\TableThreadArchives;
use Nelliel\Tables\TableThreads;
use Nelliel\Tables\TableUploads;
use Nelliel\Tables\TableUserRoles;
use Nelliel\Tables\TableUsers;
use Nelliel\Tables\TableVersions;
use Nelliel\Tables\TableVisitorInfo;
use Nelliel\Tables\TableWordfilters;
use Nelliel\Utility\FileHandler;
use Nelliel\Utility\SQLCompatibility;
use PDO;

class Installer
{
    private $file_handler;
    private $translator;
    private $render_core;
    private $installer_variables = array();

    function __construct(FileHandler $file_handler, Translator $translator)
    {
        $this->file_handler = $file_handler;
        $this->translator = $translator;
        $this->render_core = new RenderCoreSimple(NEL_INCLUDE_PATH . 'Setup/Installer/templates/');
    }

    public function install()
    {
        if ($this->checkInstallDone()) {
            nel_derp(108, __('Installation has already been completed!'));
        }

        $step = $_GET['step'] ?? '';

        if ($step === '') {
            $render_data['page_title'] = __('Install key check');
            $this->output('install_key', $render_data);
        }

        if (!file_exists(NEL_GENERATED_FILES_PATH . 'installer_variables.php')) {
            $this->writeVariables();
        }

        $installer_variables = array();
        opcache_invalidate(NEL_GENERATED_FILES_PATH . 'installer_variables.php');
        include NEL_GENERATED_FILES_PATH . 'installer_variables.php';
        $this->installer_variables = $installer_variables;

        if ($step === 'verify-install-key') {
            $this->installKeyCheck();
        }

        $this->setLanguage($step);
        $language = new Language();
        $language->changeLanguage($this->installer_variables['default_language']);

        if (!file_exists(NEL_CONFIG_FILES_PATH . 'dnsbl.php')) {
            copy(NEL_CONFIG_FILES_PATH . 'dnsbl.php.example', NEL_CONFIG_FILES_PATH . 'dnsbl.php');
        }

        if (!file_exists(NEL_CONFIG_FILES_PATH . 'checkpoints.php')) {
            copy(NEL_CONFIG_FILES_PATH . 'checkpoints.php.example', NEL_CONFIG_FILES_PATH . 'checkpoints.php');
        }

        $environment_check = new EnvironmentCheck($this->translator);
        $environment_check->check($step);

        $database_setup = new DatabaseSetup($this->file_handler, $this->translator);
        $database_setup->setup($step);

        $crypt_setup = new CryptSetup($this->file_handler, $this->translator);
        $crypt_setup->setup($step);

        $database = nel_database('core');

        echo '
<!DOCTYPE html>
<html>
	<head>
        <meta http-equiv="content-type"  content="text/html;charset=utf-8">
        <title>' . __('Installation running') . '</title>
        <link rel="stylesheet" type="text/css" href="' . NEL_STYLES_WEB_PATH . 'core/base_style.css' .
            '">
	</head>
	<body>
';

        echo sprintf(__('Database type chosen is: %s'), $database->config()['sqltype']) . '<br>';
        echo __('No database problems detected.'), '<br>';

        $generate_files = new GenerateFiles($this->file_handler);
        $install_id = base64_encode(random_bytes(33));

        if ($generate_files->peppers(false)) {
            $peppers = array();
            include NEL_GENERATED_FILES_PATH . 'peppers.php';
            define('NEL_TRIPCODE_PEPPER', $peppers['tripcode_pepper']);
            define('NEL_IP_ADDRESS_PEPPER', $peppers['ip_address_pepper']);
            define('NEL_POSTER_ID_PEPPER', $peppers['poster_id_pepper']);
            define('NEL_POST_PASSWORD_PEPPER', $peppers['post_password_pepper']);
            echo __('Peppers file has been created.'), '<br>';
        } else {
            echo __('Peppers file already present.'), '<br>';
        }

        $this->createCoreTables($database, nel_utilities()->sqlCompatibility());
        $this->createCoreDirectories();
        $this->installCoreTemplates();
        $this->installCoreStyles();
        $this->installCoreImageSets();

        $prepared = $database->prepare(
            'UPDATE "' . NEL_SITE_CONFIG_TABLE . '" SET "setting_value" = ? WHERE "setting_name" = \'locale\'');
        $prepared->bindValue(1, $this->installer_variables['default_language'], PDO::PARAM_STR);
        $database->executePrepared($prepared);

        $site_domain = Domain::getDomainFromID(Domain::SITE);
        $regen = new Regen();
        $site_domain->regenCache();
        $regen->news($site_domain);
        $regen->faq($site_domain);
        $generate_files->installDone();

        if ($this->ownerCreated()) {
            echo __('Site owner account already created.'), '<br>';
            echo __(
                'Install has finished with no apparent problems! When you\'re ready to continue, follow this link to the login page: '), '<br>';
            echo '<a href="' . NEL_BASE_WEB_PATH . 'imgboard.php?route=/' . Domain::SITE . '/account/login">' .
                __('Login page') . '</a>';
            echo '
    </body>
</html>';
            die();
        } else {
            echo '
    </p>
    <p>
' .
                __(
                    'No problems so far! To complete setup, a site owner account needs to be created. This account will have all permissions by default. It is also necessary to use the site settings control panel.') .
                '
    </p>
    <form accept-charset="utf-8" action="imgboard.php?route=/' . Domain::SITE .
                '/account/register" method="post">
        <input type="hidden" name="create_owner" value="' . $install_id .
                '"
        <div class="display-table">
            <div class="display-row">
                <label for="register_username" class="display-cell display-cell form-label">' . __('Username') .
                '</label>
                <input id="register_username" class="display-cell display-cell form-input" type="text" name="register_username" maxlength="255">
            </div>
            <div class="display-row">
                <label for="register_super_sekrit" class="display-cell display-cell form-label">' . __('Password') .
                '</label>
                <input id="register_super_sekrit" class="display-cell display-cell form-input" type="password" name="register_super_sekrit" maxlength="' .
                nel_crypt_config()->accountPasswordOptions()['max_length'] .
                '">
            </div>
            <div class="display-row">
                <label for="register_super_sekrit_confirm" class="display-cell display-cell form-label">' . __('Confirm password') .
                '</label>
                <input id="register_super_sekrit_confirm" class="display-cell display-cell form-input" type="password" name="register_super_sekrit_confirm" maxlength="' .
                nel_crypt_config()->accountPasswordOptions()['max_length'] .
                '">
            </div>
            <div class="display-row">
                <input class="display-cell display-cell form-input" type="submit" value="' . __('Submit') .
                '">
            </div>
        </div>
    </form>
</body></html>';
            $generate_files->ownerCreate($install_id);
            $generate_files->versions();
            die();
        }
    }

    private function installKeyCheck(): void
    {
        if ($this->installer_variables['install_key_confirmed'] ?? false) {
            return;
        }

        $install_key = '';
        include NEL_CONFIG_FILES_PATH . 'install_key.php';
        $given_install_key = $_POST['install_key'] ?? '';

        if ($install_key === '' || $given_install_key !== $install_key) {
            nel_derp(114, __('Install key does not match or is invalid.'));
        }

        $this->installer_variables['install_key_confirmed'] = true;
        $this->writeVariables();
    }

    private function setLanguage(string $step): void
    {
        if (isset($this->installer_variables['default_language'])) {
            return;
        }

        if ($step === 'set-language') {
            $this->installer_variables['default_language'] = strval($_POST['default_language'] ?? NEL_DEFAULT_LOCALE);
            $this->writeVariables();
        } else {
            $render_data = array();
            $render_data['languages'][] = ['value' => 'en_US', 'label' => __('English (United States)')];
            $this->output('language_select', $render_data);
        }
    }

    private function writeVariables()
    {
        $this->file_handler->writeInternalFile(NEL_GENERATED_FILES_PATH . 'installer_variables.php',
            '$installer_variables = ' . var_export($this->installer_variables, true) . ';', true);
    }

    public function ownerCreated()
    {
        return file_exists(NEL_GENERATED_FILES_PATH . 'create_owner.php');
    }

    public function checkInstallDone()
    {
        return file_exists(NEL_GENERATED_FILES_PATH . 'install_done.php');
    }

    public function createCoreTables(NellielPDO $database, SQLCompatibility $sql_compatibility): void
    {
        // Versions is first as all table creation relies on it
        $versions_table = new TableVersions($database, $sql_compatibility);
        $versions_table->createTable(null, true);

        // Domain registry is second as many tables rely on it
        $domain_registry_table = new TableDomainRegistry($database, $sql_compatibility);
        $domain_registry_table->createTable(null, true);

        // The following tables rely on the domain registry table
        $board_data_table = new TableBoardData($database, $sql_compatibility);
        $board_data_table->createTable(null, true);
        $file_filters_table = new TableFileFilters($database, $sql_compatibility);
        $file_filters_table->createTable(null, true);
        $overboard_table = new TableOverboard($database, $sql_compatibility);
        $overboard_table->createTable(null, true);
        $cites_table = new TableCites($database, $sql_compatibility);
        $cites_table->createTable(null, true);
        $wordfilters_table = new TableWordfilters($database, $sql_compatibility);
        $wordfilters_table->createTable(null, true);
        $captcha_table = new TableCaptcha($database, $sql_compatibility);
        $captcha_table->createTable(null, true);
        $board_configs_table = new TableBoardConfigs($database, $sql_compatibility);
        $board_configs_table->createTable(null, true);
        $pages_table = new TablePages($database, $sql_compatibility);
        $pages_table->createTable(null, true);
        $cache_table = new TableCache($database, $sql_compatibility);
        $cache_table->createTable(null, true);
        $r9k_content_table = new TableR9KContent($database, $sql_compatibility);
        $r9k_content_table->createTable(null, true);
        $r9k_mutes_table = new TableR9KMutes($database, $sql_compatibility);
        $r9k_mutes_table->createTable(null, true);
        $statistics_table = new TableStatistics($database, $sql_compatibility);
        $statistics_table->createTable(null, true);
        $scripts_table = new TableScripts($database, $sql_compatibility);
        $scripts_table->createTable(null, true);
        $global_recents_table = new TableGlobalRecents($database, $sql_compatibility);
        $global_recents_table->createTable(null, true);

        // The following tables rely on the settings table
        $settings_table = new TableSettings($database, $sql_compatibility);
        $settings_table->createTable(null, true);
        $setting_options_table = new TableSettingOptions($database, $sql_compatibility);
        $setting_options_table->createTable(null, true);
        $board_defaults_table = new TableBoardDefaults($database, $sql_compatibility);
        $board_defaults_table->createTable(null, true);
        $site_config_table = new TableSiteConfig($database, $sql_compatibility);
        $site_config_table->createTable(null, true);

        // The following tables rely on the filetype categories table
        $filetype_categories_table = new TableFiletypeCategories($database, $sql_compatibility);
        $filetype_categories_table->createTable(null, true);
        $filetypes_table = new TableFiletypes($database, $sql_compatibility);
        $filetypes_table->createTable(null, true);

        // The following tables rely on the roles and permissions tables
        $roles_table = new TableRoles($database, $sql_compatibility);
        $roles_table->createTable(null, true);
        $permissions_table = new TablePermissions($database, $sql_compatibility);
        $permissions_table->createTable(null, true);
        $role_permissions_table = new TableRolePermissions($database, $sql_compatibility);
        $role_permissions_table->createTable(null, true);

        // The following tables rely on the users table
        $users_table = new TableUsers($database, $sql_compatibility);
        $users_table->createTable(null, true);
        $ip_notes_table = new TableIPNotes($database, $sql_compatibility);
        $ip_notes_table->createTable(null, true);
        $news_table = new TableNews($database, $sql_compatibility);
        $news_table->createTable(null, true);
        $private_messages_table = new TablePrivateMessages($database, $sql_compatibility);
        $private_messages_table->createTable(null, true);
        $noticeboard_table = new TableNoticeboard($database, $sql_compatibility);
        $noticeboard_table->createTable(null, true);

        // The following tables rely on ip info and visitor id tables
        $ip_info_table = new TableIPInfo($database, $sql_compatibility);
        $ip_info_table->createTable(null, true);
        $visitor_info_table = new TableVisitorInfo($database, $sql_compatibility);
        $visitor_info_table->createTable(null, true);
        $reports_table = new TableReports($database, $sql_compatibility);
        $reports_table->createTable(null, true);
        $system_logs_table = new TableLogs($database, $sql_compatibility);
        $system_logs_table->tableName(NEL_SYSTEM_LOGS_TABLE);
        $system_logs_table->createTable(null, true);
        $public_logs_table = new TableLogs($database, $sql_compatibility);
        $public_logs_table->tableName(NEL_PUBLIC_LOGS_TABLE);
        $public_logs_table->createTable(null, true);

        // The following tables rely on the users and roles tables
        $user_roles_table = new TableUserRoles($database, $sql_compatibility);
        $user_roles_table->createTable(null, true);

        // The following tables rely on the users and bans table
        $bans_table = new TableBans($database, $sql_compatibility);
        $bans_table->createTable(null, true);
        $ban_appeals_table = new TableBanAppeals($database, $sql_compatibility);
        $ban_appeals_table->createTable(null, true);

        $plugins_table = new TablePlugins($database, $sql_compatibility);
        $plugins_table->createTable(null, true);

        // The following tables rely on the plugins and domain registry tables
        $plugin_configs_table = new TablePluginConfigs($database, $sql_compatibility);
        $plugin_configs_table->createTable(null, true);

        // The following tables are fully independent
        $image_sets_table = new TableImageSets($database, $sql_compatibility);
        $image_sets_table->createTable(null, true);
        $styles_table = new TableStyles($database, $sql_compatibility);
        $styles_table->createTable(null, true);
        $embeds_table = new TableEmbeds($database, $sql_compatibility);
        $embeds_table->createTable(null, true);
        $rate_limit_table = new TableRateLimit($database, $sql_compatibility);
        $rate_limit_table->createTable(null, true);
        $templates_table = new TableTemplates($database, $sql_compatibility);
        $templates_table->createTable(null, true);
        $blotter_table = new TableBlotter($database, $sql_compatibility);
        $blotter_table->createTable(null, true);
        $embeds_table = new TableEmbeds($database, $sql_compatibility);
        $embeds_table->createTable(null, true);
        $content_ops_table = new TableContentOps($database, $sql_compatibility);
        $content_ops_table->createTable(null, true);
        $capcodes_table = new TableCapcodes($database, $sql_compatibility);
        $capcodes_table->createTable(null, true);
        $markup_table = new TableMarkup($database, $sql_compatibility);
        $markup_table->createTable(null, true);

        echo __('Core database tables created.'), '<br>';
    }

    public function createCoreDirectories()
    {
        $this->file_handler->createDirectory(NEL_CACHE_FILES_PATH);
        $this->file_handler->createDirectory(NEL_GENERATED_FILES_PATH);
        $this->file_handler->createDirectory(NEL_GENERAL_FILES_PATH);
        $this->file_handler->createDirectory(NEL_CAPTCHA_FILES_PATH);
        $this->file_handler->createDirectory(NEL_BANNERS_FILES_PATH);
        $this->file_handler->createDirectory(NEL_BANNERS_FILES_PATH . 'site/');
        $this->file_handler->createDirectory(NEL_TEMP_FILES_BASE_PATH);
        $this->file_handler->createDirectory(NEL_STYLES_FILES_PATH . 'custom/');
        $this->file_handler->createDirectory(NEL_SCRIPTS_FILES_PATH . 'custom/');
        $this->file_handler->createDirectory(NEL_IMAGE_SETS_FILES_PATH . 'custom/');
        $this->file_handler->createDirectory(NEL_MEDIA_FILES_PATH . 'custom/');
        echo __('Core directories created.'), '<br>';
    }

    public function createBoardTables(NellielPDO $database, SQLCompatibility $sql_compatibility, string $board_id,
        string $db_prefix)
    {
        $domain = Domain::getDomainFromID($board_id, nel_database('core'));

        $archives_table = new TableThreadArchives($database, $sql_compatibility);
        $archives_table->tableName($domain->reference('archives_table'));
        $archives_table->createTable();

        // NOTE: Tables must be created in order of threads -> posts -> uploads
        $threads_table = new TableThreads($database, $sql_compatibility);
        $threads_table->tableName($domain->reference('threads_table'));
        $threads_table->createTable();
        $posts_table = new TablePosts($database, $sql_compatibility);
        $posts_table->tableName($domain->reference('posts_table'));
        $posts_table->createTable(['threads_table' => $domain->reference('threads_table')]);
        $uploads_table = new TableUploads($database, $sql_compatibility);
        $uploads_table->tableName($domain->reference('uploads_table'));
        $uploads_table->createTable(
            ['threads_table' => $domain->reference('threads_table'), 'posts_table' => $domain->reference('posts_table')]);
    }

    public function createBoardDirectories(string $board_id)
    {
        $domain = Domain::getDomainFromID($board_id, nel_database('core'));
        $this->file_handler->createDirectory($domain->reference('src_path'));
        $this->file_handler->createDirectory($domain->reference('preview_path'));
        $this->file_handler->createDirectory($domain->reference('page_path'));
        $this->file_handler->createDirectory($domain->reference('banners_path'));
        $this->file_handler->createDirectory($domain->reference('archive_src_path'));
        $this->file_handler->createDirectory($domain->reference('archive_preview_path'));
        $this->file_handler->createDirectory($domain->reference('archive_page_path'));
    }

    public function installCoreTemplates($overwrite = false): void
    {
        $front_end_data = new FrontEndData(nel_database('core'));
        $template_inis = $front_end_data->getTemplateInis();

        foreach ($template_inis as $ini) {
            $template_id = $ini['info']['id'];

            if (!$front_end_data->templateIsCore($template_id)) {
                continue;
            }

            $front_end_data->getTemplate($template_id)->install($overwrite);
            $front_end_data->getTemplate($template_id)->load();
        }

        echo __('Core templates installed.') . '<br>';
    }

    public function installCoreStyles(bool $overwrite = false): void
    {
        $front_end_data = new FrontEndData(nel_database('core'));
        $style_inis = $front_end_data->getStyleInis();

        foreach ($style_inis as $ini) {
            $style_id = $ini['info']['id'];

            if (!$front_end_data->styleIsCore($style_id)) {
                continue;
            }

            $front_end_data->getStyle($style_id)->install($overwrite);
            $front_end_data->getStyle($style_id)->load();
        }

        echo __('Core styles installed.') . '<br>';
    }

    public function installCoreImageSets(bool $overwrite = false): void
    {
        $front_end_data = new FrontEndData(nel_database('core'));
        $image_set_inis = $front_end_data->getImageSetInis();

        foreach ($image_set_inis as $ini) {
            $image_set_id = $ini['info']['id'];

            if (!$front_end_data->imageSetIsCore($image_set_id)) {
                continue;
            }

            $front_end_data->getImageSet($image_set_id)->install($overwrite);
            $front_end_data->getImageSet($image_set_id)->load();
        }

        echo __('Core image sets installed.') . '<br>';
    }

    private function output(string $template_file, array $render_data = array()): void
    {
        $render_data['base_stylesheet'] = NEL_STYLES_WEB_PATH . 'core/base_style.css';
        $html = $this->render_core->renderFromTemplateFile($template_file, $render_data);
        echo $this->translator->translateHTML($html);
        die();
    }
}