<?php
declare(strict_types = 1);

namespace Nelliel\Setup;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use Nelliel\Tables\TableBans;
use Nelliel\Tables\TableBlotter;
use Nelliel\Tables\TableBoardConfigs;
use Nelliel\Tables\TableBoardData;
use Nelliel\Tables\TableBoardDefaults;
use Nelliel\Tables\TableCache;
use Nelliel\Tables\TableCaptcha;
use Nelliel\Tables\TableCites;
use Nelliel\Tables\TableDomainRegistry;
use Nelliel\Tables\TableEmbeds;
use Nelliel\Tables\TableFileFilters;
use Nelliel\Tables\TableFiletypes;
use Nelliel\Tables\TableIPNotes;
use Nelliel\Tables\TableIconSets;
use Nelliel\Tables\TableLogs;
use Nelliel\Tables\TableNews;
use Nelliel\Tables\TableNoticeboard;
use Nelliel\Tables\TableOverboard;
use Nelliel\Tables\TablePages;
use Nelliel\Tables\TablePermissions;
use Nelliel\Tables\TablePlugins;
use Nelliel\Tables\TablePosts;
use Nelliel\Tables\TablePrivateMessages;
use Nelliel\Tables\TableRateLimit;
use Nelliel\Tables\TableReports;
use Nelliel\Tables\TableRolePermissions;
use Nelliel\Tables\TableRoles;
use Nelliel\Tables\TableSettings;
use Nelliel\Tables\TableSiteConfig;
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
use Nelliel\Utility\SQLCompatibility;
use PDO;

class Setup
{
    protected $database;
    protected $sql_compatibility;
    protected $file_handler;

    function __construct(NellielPDO $database, SQLCompatibility $sql_compatibility, FileHandler $file_handler)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->file_handler = $file_handler;
    }

    public function install()
    {
        echo '<!DOCTYPE html><html><body>';

        if ($this->checkInstallDone()) {
            nel_derp(108, _gettext('Installation has already been completed!'));
        }

        $this->checkPHP();
        $this->checkDBEngine();
        $this->mainDirWritable();
        $this->coreDirWritable();
        // $this->configDirWritable();

        $file_handler = new \Nelliel\Utility\FileHandler();
        $generate_files = new \Nelliel\Setup\GenerateFiles($file_handler);
        $install_id = base64_encode(random_bytes(33));

        if ($generate_files->peppers(false)) {
            echo _gettext('Peppers file has been created.'), '<br>';
        } else {
            echo _gettext('Peppers file already present.'), '<br>';
        }

        $this->createCoreTables();
        $this->createCoreDirectories();
        $this->installCoreTemplates();
        $this->installCoreStyles();
        $this->installCoreIconSets();
        $site_domain = new \Nelliel\Domains\DomainSite($this->database);
        $regen = new \Nelliel\Regen();
        $site_domain->regenCache();
        $regen->news($site_domain);
        $generate_files->installDone();

        if ($this->ownerCreated()) {
            echo _gettext('Site owner account already created.'), '<br>';
            echo _gettext(
                'Install has finished with no apparent problems! When you\'re ready to continue, follow this link to the login page: '), '<br>';
            echo '<a href="' . NEL_BASE_WEB_PATH . 'imgboard.php?module=account&amp;actions=login">' .
                _gettext('Login page') . '</a>';
            echo '</body></html>';
            die();
        } else {
            echo '<p>';
            echo _gettext(
                'No problems so far! To complete setup, a site owner account needs to be created. This account will have all permissions by default. It is also necessary to use the site settings control panel.');
            echo '</p>';
            echo '<form accept-charset="utf-8" action="imgboard.php?route=/' . $site_domain->id() . '/account/register" method="post">';
            echo '
<input type="hidden" name="create_owner" value="' . $install_id . '"';
            echo '
<div>
    <span data-i18n="gettext">User ID: </span><input type="text" name="register_user_id" size="25" maxlength="255">
</div>';
            echo '
<div>
    <span data-i18n="gettext">Password: </span><input type="password" name="register_super_sekrit" size="25" maxlength="255">
</div>';
            echo '
<div>
    <span data-i18n="gettext">Confirm password: </span><input type="password" name="register_super_sekrit_confirm" size="25" maxlength="255">
</div>';
            echo '
<div>
    <input type="submit" value="Submit" data-i18n-attributes="gettext|value">
</div>';
            echo '</form></body></html>';
            $generate_files->ownerCreate($install_id);
            $generate_files->versions();
            die();
        }
    }

    public function checkPHP()
    {
        echo _gettext('Minimum PHP version required: ' . NELLIEL_PHP_MINIMUM), '<br>';
        echo _gettext('PHP version detected: ' . PHP_VERSION), '<br>';

        if (version_compare(PHP_VERSION, NELLIEL_PHP_MINIMUM, '<=')) {
            nel_derp(109, _gettext('This version of PHP is too old! Upgrade to a version supported by Nelliel.'));
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

    public function checkDBEngine()
    {
        if ((NEL_SQLTYPE === 'MYSQL' || NEL_SQLTYPE === 'MARIADB') && !$this->checkForInnoDB()) {
            nel_derp(102,
                _gettext('InnoDB engine is required for MySQL or MariaDB support but that engine is not available.'));
        } else {
            echo _gettext('DB engine ok.'), '<br>';
        }
    }

    public function coreDirWritable()
    {
        if (!is_writable(NEL_CORE_PATH)) {
            nel_derp(104, _gettext('The core directory not writable.'));
        } else {
            echo _gettext('The core directory is writable.'), '<br>';
        }
    }

    public function mainDirWritable()
    {
        if (!is_writable(NEL_BASE_PATH)) {
            nel_derp(105, _gettext('Nelliel project directory is not writable.'));
        } else {
            echo _gettext('Main directory is writable.'), '<br>';
        }
    }

    public function configDirWritable()
    {
        if (!is_writable(NEL_CONFIG_FILES_PATH)) {
            nel_derp(106, _gettext('Configuration directory is missing or not writable. Admin should check this out.'));
        } else {
            echo _gettext('The configuration directory is writable.'), '<br>';
        }
    }

    public function createCoreTables()
    {
        $versions_table = new TableVersions($this->database, $this->sql_compatibility);
        $versions_table->createTable();
        $icon_sets_table = new TableIconSets($this->database, $this->sql_compatibility);
        $icon_sets_table->createTable();
        $board_defaults_table = new TableBoardDefaults($this->database, $this->sql_compatibility);
        $board_defaults_table->createTable();
        $styles_table = new TableStyles($this->database, $this->sql_compatibility);
        $styles_table->createTable();
        $settings_table = new TableSettings($this->database, $this->sql_compatibility);
        $settings_table->createTable();
        $embeds_table = new TableEmbeds($this->database, $this->sql_compatibility);
        $embeds_table->createTable();
        $filetypes_table = new TableFiletypes($this->database, $this->sql_compatibility);
        $filetypes_table->createTable();
        $news_table = new TableNews($this->database, $this->sql_compatibility);
        $news_table->createTable();
        $rate_limit_table = new TableRateLimit($this->database, $this->sql_compatibility);
        $rate_limit_table->createTable();
        $site_config_table = new TableSiteConfig($this->database, $this->sql_compatibility);
        $site_config_table->createTable();
        $templates_table = new TableTemplates($this->database, $this->sql_compatibility);
        $templates_table->createTable();
        $plugins_table = new TablePlugins($this->database, $this->sql_compatibility);
        $plugins_table->createTable();
        $pms_table = new TablePrivateMessages($this->database, $this->sql_compatibility);
        $pms_table->createTable();
        $blotter_table = new TableBlotter($this->database, $this->sql_compatibility);
        $blotter_table->createTable();
        $noticeboard_table = new TableNoticeboard($this->database, $this->sql_compatibility);
        $noticeboard_table->createTable();
        $ip_notes_table = new TableIPNotes($this->database, $this->sql_compatibility);
        $ip_notes_table->createTable();

        // NOTE: The following tables rely on the domain registry table
        // Domain registry must be created first!
        $domain_registry_table = new TableDomainRegistry($this->database, $this->sql_compatibility);
        $domain_registry_table->createTable();
        $board_data_table = new TableBoardData($this->database, $this->sql_compatibility);
        $board_data_table->createTable();
        $file_filters_table = new TableFileFilters($this->database, $this->sql_compatibility);
        $file_filters_table->createTable();
        $overboard_table = new TableOverboard($this->database, $this->sql_compatibility);
        $overboard_table->createTable();
        $reports_table = new TableReports($this->database, $this->sql_compatibility);
        $reports_table->createTable();
        $cites_table = new TableCites($this->database, $this->sql_compatibility);
        $cites_table->createTable();
        $word_filters_table = new TableWordFilters($this->database, $this->sql_compatibility);
        $word_filters_table->createTable();
        $logs_table = new TableLogs($this->database, $this->sql_compatibility);
        $logs_table->createTable();
        $bans_table = new TableBans($this->database, $this->sql_compatibility);
        $bans_table->createTable();
        $captcha_table = new TableCaptcha($this->database, $this->sql_compatibility);
        $captcha_table->createTable();
        $board_configs_table = new TableBoardConfigs($this->database, $this->sql_compatibility);
        $board_configs_table->createTable();
        $pages_table = new TablePages($this->database, $this->sql_compatibility);
        $pages_table->createTable();
        $cache_table = new TableCache($this->database, $this->sql_compatibility);
        $cache_table->createTable();

        // NOTE: Tables must be created in order of:
        // roles -> permissions -> role permissions -> users -> user roles
        $roles_table = new TableRoles($this->database, $this->sql_compatibility);
        $roles_table->createTable();
        $permissions_table = new TablePermissions($this->database, $this->sql_compatibility);
        $permissions_table->createTable();
        $role_permissions_table = new TableRolePermissions($this->database, $this->sql_compatibility);
        $role_permissions_table->createTable();
        $users_table = new TableUsers($this->database, $this->sql_compatibility);
        $users_table->createTable();
        $user_roles_table = new TableUserRoles($this->database, $this->sql_compatibility);
        $user_roles_table->createTable();
        echo _gettext('Core database tables created.'), '<br>';
    }

    public function createCoreDirectories()
    {
        $this->file_handler->createDirectory(NEL_CACHE_FILES_PATH, NEL_DIRECTORY_PERM, true);
        $this->file_handler->createDirectory(NEL_GENERATED_FILES_PATH, NEL_DIRECTORY_PERM, true);
        $this->file_handler->createDirectory(NEL_GENERAL_FILES_PATH, NEL_DIRECTORY_PERM, true);
        $this->file_handler->createDirectory(NEL_CAPTCHA_FILES_PATH, NEL_DIRECTORY_PERM, true);
        $this->file_handler->createDirectory(NEL_BANNERS_FILES_PATH, NEL_DIRECTORY_PERM, true);
        echo _gettext('Core directories created.'), '<br>';
    }

    public function createBoardTables(string $board_id, string $db_prefix)
    {
        $domain = new \Nelliel\Domains\DomainBoard($board_id, nel_database());

        $archives_table = new TableThreadArchives($this->database, $this->sql_compatibility);
        $archives_table->tableName($domain->reference('archives_table'));
        $archives_table->createTable();

        // NOTE: Tables must be created in order of
        // threads -> posts -> uploads
        $threads_table = new TableThreads($this->database, $this->sql_compatibility);
        $threads_table->tableName($domain->reference('threads_table'));
        $threads_table->createTable();
        $posts_table = new TablePosts($this->database, $this->sql_compatibility);
        $posts_table->tableName($domain->reference('posts_table'));
        $posts_table->createTable(['threads_table' => $domain->reference('threads_table')]);
        $uploads_table = new TableUploads($this->database, $this->sql_compatibility);
        $uploads_table->tableName($domain->reference('uploads_table'));
        $uploads_table->createTable(['posts_table' => $domain->reference('posts_table')]);
    }

    public function createBoardDirectories(string $board_id)
    {
        $domain = new \Nelliel\Domains\DomainBoard($board_id, nel_database());
        $this->file_handler->createDirectory($domain->reference('src_path'), NEL_DIRECTORY_PERM, true);
        $this->file_handler->createDirectory($domain->reference('preview_path'), NEL_DIRECTORY_PERM, true);
        $this->file_handler->createDirectory($domain->reference('page_path'), NEL_DIRECTORY_PERM, true);
        $this->file_handler->createDirectory($domain->reference('banners_path'), NEL_DIRECTORY_PERM, true);
        $this->file_handler->createDirectory($domain->reference('archive_src_path'), NEL_DIRECTORY_PERM, true);
        $this->file_handler->createDirectory($domain->reference('archive_preview_path'), NEL_DIRECTORY_PERM, true);
        $this->file_handler->createDirectory($domain->reference('archive_page_path'), NEL_DIRECTORY_PERM, true);
    }

    public function installCoreTemplates($overwrite = false): void
    {
        $front_end_data = new \Nelliel\FrontEnd\FrontEndData($this->database);
        $template_inis = $front_end_data->getTemplateInis();

        foreach ($template_inis as $ini) {
            $template_id = $ini['template-info']['id'];

            if (!$front_end_data->templateIsCore($template_id)) {
                continue;
            }

            $front_end_data->getTemplate($template_id)->install($overwrite);
        }

        echo _gettext('Core templates installed.') . '<br>';
    }

    public function installCoreStyles(bool $overwrite = false): void
    {
        $front_end_data = new \Nelliel\FrontEnd\FrontEndData($this->database);
        $style_inis = $front_end_data->getStyleInis();

        foreach ($style_inis as $ini) {
            $style_id = $ini['style-info']['id'];

            if (!$front_end_data->styleIsCore($style_id)) {
                continue;
            }

            $front_end_data->getStyle($style_id)->install($overwrite);
        }

        echo _gettext('Core styles installed.') . '<br>';
    }

    public function installCoreIconSets(bool $overwrite = false): void
    {
        $front_end_data = new \Nelliel\FrontEnd\FrontEndData($this->database);
        $icon_set_inis = $front_end_data->getIconSetInis();

        foreach ($icon_set_inis as $ini) {
            $icon_set_id = $ini['set-info']['id'];

            if (!$front_end_data->iconSetIsCore($icon_set_id)) {
                continue;
            }

            $front_end_data->getIconSet($icon_set_id)->install($overwrite);
        }

        echo _gettext('Core icon sets installed.') . '<br>';
    }

    private function checkForInnoDB()
    {
        $result = $this->database->query("SHOW ENGINES");
        $list = $result->fetchAll(PDO::FETCH_ASSOC);

        foreach ($list as $entry) {
            if ($entry['Engine'] === 'InnoDB' && ($entry['Support'] === 'DEFAULT' || $entry['Support'] === 'YES')) {
                return true;
            }
        }

        return false;
    }
}