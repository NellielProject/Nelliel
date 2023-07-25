<?php
declare(strict_types = 1);

namespace Nelliel\Setup\Installer;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Domains\DomainSite;
use Nelliel\FrontEnd\FrontEndData;
use Nelliel\Language\Translator;
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
use Nelliel\Tables\TableWordFilters;
use Nelliel\Utility\FileHandler;
use Nelliel\Render\RenderCoreSimple;

class Installer
{
    private $database;
    private $sql_compatibility;
    private $file_handler;
    private $translator;
    private $render_core;

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

        if ($step === 'verify-install-key') {
            $this->installKeyCheck();
        }

        if (!file_exists(NEL_CONFIG_FILES_PATH . 'dnsbl.php')) {
            copy(NEL_CONFIG_FILES_PATH . 'dnsbl.php.example', NEL_CONFIG_FILES_PATH . 'dnsbl.php');
        }

        if (!file_exists(NEL_CONFIG_FILES_PATH . 'if_thens.php')) {
            copy(NEL_CONFIG_FILES_PATH . 'if_thens.php.example', NEL_CONFIG_FILES_PATH . 'if_thens.php');
        }

        $environment_check = new EnvironmentCheck($this->translator);
        $environment_check->check($step);

        $database_setup = new DatabaseSetup($this->file_handler, $this->translator);
        $database_setup->setup($step);

        $crypt_setup = new CryptSetup($this->file_handler, $this->translator);
        $crypt_setup->setup($step);

        $this->database = nel_database('core');
        $this->sql_compatibility = nel_utilities()->sqlCompatibility();

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

        echo sprintf(__('Database type chosen is: %s'), $this->database->config()['sqltype']) . '<br>';
        echo __('No database problems detected.'), '<br>';

        $generate_files = new GenerateFiles($this->file_handler);
        $install_id = base64_encode(random_bytes(32));

        if ($generate_files->peppers(false)) {
            echo __('Peppers file has been created.'), '<br>';
        } else {
            echo __('Peppers file already present.'), '<br>';
        }

        $this->createCoreTables();
        $this->createCoreDirectories();
        $this->installCoreTemplates();
        $this->installCoreStyles();
        $this->installCoreImageSets();
        $site_domain = new DomainSite($this->database);
        $regen = new Regen();
        $site_domain->regenCache();
        $regen->news($site_domain);
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
        <div class="installer-form">
            <div class="installer-form-row">
                <label for="register_username" class="installer-form-label">' . __('Username:') .
                '</label>
                <input id="register_username" class="installer-form-input" type="text" name="register_username" maxlength="255">
            </div>
            <div class="installer-form-row">
                <label for="register_super_sekrit" class="installer-form-label">' . __('Password:') .
                '</label>
                <input id="register_super_sekrit" class="installer-form-input" type="password" name="register_super_sekrit" maxlength="255">
            </div>
            <div class="installer-form-row">
                <label for="register_super_sekrit_confirm" class="installer-form-label">' . __('Confirm password:') .
                '</label>
                <input id="register_super_sekrit_confirm" class="installer-form-input" type="password" name="register_super_sekrit_confirm" maxlength="255">
            </div>
            <div class="installer-form-row">
                <input class="installer-form-input" type="submit" value="' . __('Submit') .
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
        $install_key = '';
        include NEL_CONFIG_FILES_PATH . 'install_key.php';
        $given_install_key = $_POST['install_key'] ?? '';

        if ($install_key === '' || $given_install_key !== $install_key) {
            nel_derp(114, __('Install key does not match or is invalid.'));
        }
    }

    public function ownerCreated()
    {
        return file_exists(NEL_GENERATED_FILES_PATH . 'create_owner.php');
    }

    public function checkInstallDone()
    {
        return file_exists(NEL_GENERATED_FILES_PATH . 'install_done.php');
    }

    public function createCoreTables()
    {
        // Versions is first as all table creation relies on it
        $versions_table = new TableVersions($this->database, $this->sql_compatibility);
        $versions_table->createTable();

        // Domain registry is second as many tables rely on it
        $domain_registry_table = new TableDomainRegistry($this->database, $this->sql_compatibility);
        $domain_registry_table->createTable();

        // The following tables rely on the domain registry table
        $board_data_table = new TableBoardData($this->database, $this->sql_compatibility);
        $board_data_table->createTable();
        $file_filters_table = new TableFileFilters($this->database, $this->sql_compatibility);
        $file_filters_table->createTable();
        $overboard_table = new TableOverboard($this->database, $this->sql_compatibility);
        $overboard_table->createTable();
        $cites_table = new TableCites($this->database, $this->sql_compatibility);
        $cites_table->createTable();
        $wordfilters_table = new TableWordfilters($this->database, $this->sql_compatibility);
        $wordfilters_table->createTable();
        $captcha_table = new TableCaptcha($this->database, $this->sql_compatibility);
        $captcha_table->createTable();
        $board_configs_table = new TableBoardConfigs($this->database, $this->sql_compatibility);
        $board_configs_table->createTable();
        $pages_table = new TablePages($this->database, $this->sql_compatibility);
        $pages_table->createTable();
        $cache_table = new TableCache($this->database, $this->sql_compatibility);
        $cache_table->createTable();
        $r9k_content_table = new TableR9KContent($this->database, $this->sql_compatibility);
        $r9k_content_table->createTable();
        $r9k_mutes_table = new TableR9KMutes($this->database, $this->sql_compatibility);
        $r9k_mutes_table->createTable();
        $statistics_table = new TableStatistics($this->database, $this->sql_compatibility);
        $statistics_table->createTable();
        $scripts_table = new TableScripts($this->database, $this->sql_compatibility);
        $scripts_table->createTable();
        $global_recents_table = new TableGlobalRecents($this->database, $this->sql_compatibility);
        $global_recents_table->createTable();

        // The following tables rely on the settings table
        $settings_table = new TableSettings($this->database, $this->sql_compatibility);
        $settings_table->createTable();
        $setting_options_table = new TableSettingOptions($this->database, $this->sql_compatibility);
        $setting_options_table->createTable();
        $board_defaults_table = new TableBoardDefaults($this->database, $this->sql_compatibility);
        $board_defaults_table->createTable();
        $site_config_table = new TableSiteConfig($this->database, $this->sql_compatibility);
        $site_config_table->createTable();

        // The following tables rely on the filetype categories table
        $filetype_categories_table = new TableFiletypeCategories($this->database, $this->sql_compatibility);
        $filetype_categories_table->createTable();
        $filetypes_table = new TableFiletypes($this->database, $this->sql_compatibility);
        $filetypes_table->createTable();

        // The following tables rely on the roles and permissions tables
        $roles_table = new TableRoles($this->database, $this->sql_compatibility);
        $roles_table->createTable();
        $permissions_table = new TablePermissions($this->database, $this->sql_compatibility);
        $permissions_table->createTable();
        $role_permissions_table = new TableRolePermissions($this->database, $this->sql_compatibility);
        $role_permissions_table->createTable();

        // The following tables rely on the users table
        $users_table = new TableUsers($this->database, $this->sql_compatibility);
        $users_table->createTable();
        $ip_notes_table = new TableIPNotes($this->database, $this->sql_compatibility);
        $ip_notes_table->createTable();
        $news_table = new TableNews($this->database, $this->sql_compatibility);
        $news_table->createTable();
        $private_messages_table = new TablePrivateMessages($this->database, $this->sql_compatibility);
        $private_messages_table->createTable();
        $noticeboard_table = new TableNoticeboard($this->database, $this->sql_compatibility);
        $noticeboard_table->createTable();

        // The following tables rely on ip info tables
        $ip_info_table = new TableIPInfo($this->database, $this->sql_compatibility);
        $ip_info_table->createTable();
        $reports_table = new TableReports($this->database, $this->sql_compatibility);
        $reports_table->createTable();
        $system_logs_table = new TableLogs($this->database, $this->sql_compatibility);
        $system_logs_table->tableName(NEL_SYSTEM_LOGS_TABLE);
        $system_logs_table->createTable();
        $public_logs_table = new TableLogs($this->database, $this->sql_compatibility);
        $public_logs_table->tableName(NEL_PUBLIC_LOGS_TABLE);
        $public_logs_table->createTable();

        // The following tables rely on the users and roles tables
        $user_roles_table = new TableUserRoles($this->database, $this->sql_compatibility);
        $user_roles_table->createTable();

        // The following tables rely on the users and bans table
        $bans_table = new TableBans($this->database, $this->sql_compatibility);
        $bans_table->createTable();
        $ban_appeals_table = new TableBanAppeals($this->database, $this->sql_compatibility);
        $ban_appeals_table->createTable();

        // The following tables are fully independent
        $image_sets_table = new TableImageSets($this->database, $this->sql_compatibility);
        $image_sets_table->createTable();
        $styles_table = new TableStyles($this->database, $this->sql_compatibility);
        $styles_table->createTable();
        $embeds_table = new TableEmbeds($this->database, $this->sql_compatibility);
        $embeds_table->createTable();
        $rate_limit_table = new TableRateLimit($this->database, $this->sql_compatibility);
        $rate_limit_table->createTable();
        $templates_table = new TableTemplates($this->database, $this->sql_compatibility);
        $templates_table->createTable();
        $plugins_table = new TablePlugins($this->database, $this->sql_compatibility);
        $plugins_table->createTable();
        $blotter_table = new TableBlotter($this->database, $this->sql_compatibility);
        $blotter_table->createTable();
        $embeds_table = new TableEmbeds($this->database, $this->sql_compatibility);
        $embeds_table->createTable();
        $content_ops_table = new TableContentOps($this->database, $this->sql_compatibility);
        $content_ops_table->createTable();
        $capcodes_table = new TableCapcodes($this->database, $this->sql_compatibility);
        $capcodes_table->createTable();
        $markup_table = new TableMarkup($this->database, $this->sql_compatibility);
        $markup_table->createTable();

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

    public function createBoardTables(string $board_id, string $db_prefix)
    {
        $domain = new DomainBoard($board_id, nel_database('core'));

        $archives_table = new TableThreadArchives($this->database, $this->sql_compatibility);
        $archives_table->tableName($domain->reference('archives_table'));
        $archives_table->createTable();

        // NOTE: Tables must be created in order of threads -> posts -> uploads
        $threads_table = new TableThreads($this->database, $this->sql_compatibility);
        $threads_table->tableName($domain->reference('threads_table'));
        $threads_table->createTable();
        $posts_table = new TablePosts($this->database, $this->sql_compatibility);
        $posts_table->tableName($domain->reference('posts_table'));
        $posts_table->createTable(['threads_table' => $domain->reference('threads_table')]);
        $uploads_table = new TableUploads($this->database, $this->sql_compatibility);
        $uploads_table->tableName($domain->reference('uploads_table'));
        $uploads_table->createTable(
            ['threads_table' => $domain->reference('threads_table'), 'posts_table' => $domain->reference('posts_table')]);
    }

    public function createBoardDirectories(string $board_id)
    {
        $domain = new DomainBoard($board_id, nel_database('core'));
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
        $front_end_data = new FrontEndData($this->database);
        $template_inis = $front_end_data->getTemplateInis();

        foreach ($template_inis as $ini) {
            $template_id = $ini['info']['id'];

            if (!$front_end_data->templateIsCore($template_id)) {
                continue;
            }

            $front_end_data->getTemplate($template_id)->install($overwrite);
        }

        echo __('Core templates installed.') . '<br>';
    }

    public function installCoreStyles(bool $overwrite = false): void
    {
        $front_end_data = new FrontEndData($this->database);
        $style_inis = $front_end_data->getStyleInis();

        foreach ($style_inis as $ini) {
            $style_id = $ini['info']['id'];

            if (!$front_end_data->styleIsCore($style_id)) {
                continue;
            }

            $front_end_data->getStyle($style_id)->install($overwrite);
        }

        echo __('Core styles installed.') . '<br>';
    }

    public function installCoreImageSets(bool $overwrite = false): void
    {
        $front_end_data = new FrontEndData($this->database);
        $image_set_inis = $front_end_data->getImageSetInis();

        foreach ($image_set_inis as $ini) {
            $image_set_id = $ini['info']['id'];

            if (!$front_end_data->imageSetIsCore($image_set_id)) {
                continue;
            }

            $front_end_data->getImageSet($image_set_id)->install($overwrite);
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