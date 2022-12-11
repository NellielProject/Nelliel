<?php
declare(strict_types = 1);

namespace Nelliel\Setup;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Domains\DomainSite;
use Nelliel\FrontEnd\FrontEndData;
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
        echo '
<!DOCTYPE html>
<html>
<head>
    <title>' . __('Installer') . '</title>
</head>
<body>
    <p>';

        if ($this->checkInstallDone()) {
            nel_derp(108, _gettext('Installation has already been completed!'));
        }

        if (isset($_POST['install_key'])) {
            if (!$this->verifyInstallKey()) {
                nel_derp(114, _gettext('Install key does not match or is invalid.'));
                die();
            }
        } else {
            $this->displayInstallCheck();
            die();
        }

        $this->checkPHP();
        $this->checkDBEngine();
        $this->mainDirWritable();
        $this->coreDirWritable();
        $file_handler = new FileHandler();
        $generate_files = new GenerateFiles($file_handler);
        $install_id = base64_encode(random_bytes(32));

        if ($generate_files->peppers(false)) {
            echo _gettext('Peppers file has been created.'), '<br>';
        } else {
            echo _gettext('Peppers file already present.'), '<br>';
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
            echo _gettext('Site owner account already created.'), '<br>';
            echo _gettext(
                'Install has finished with no apparent problems! When you\'re ready to continue, follow this link to the login page: '), '<br>';
            echo '<a href="' . NEL_BASE_WEB_PATH . 'imgboard.php?route=/' . Domain::SITE . '/account/login">' .
                _gettext('Login page') . '</a>';
            echo '</body></html>';
            die();
        } else {
            echo '
    </p>
    <p>
' .
                _gettext(
                    'No problems so far! To complete setup, a site owner account needs to be created. This account will have all permissions by default. It is also necessary to use the site settings control panel.') .
                '
    </p>
    <form accept-charset="utf-8" action="imgboard.php?route=/' . Domain::SITE .
                '/account/register" method="post">
        <input type="hidden" name="create_owner" value="' . $install_id . '"
        <div>
            <label for="register_username">' . __('Username:') .
                '</label>
            <input id="register_username" type="text" name="register_username" maxlength="255">
        </div>
        <div>
            <label for="register_super_sekrit">' . __('Password:') .
                '</label>
            <input id="register_super_sekrit" type="password" name="register_super_sekrit" maxlength="255">
        </div>
        <div>
            <label for="register_super_sekrit_confirm">' . __('Confirm password:') .
                '</label>
            <input id="register_super_sekrit_confirm" type="password" name="register_super_sekrit_confirm" maxlength="255">
        </div>
        <div>
            <input type="submit" value="' . __('Submit') . '">
        </div>
    </form>
</body></html>';
            $generate_files->ownerCreate($install_id);
            $generate_files->versions();
            die();
        }
    }

    public function displayInstallCheck(): void
    {
        echo '
<!DOCTYPE html>
<html>
<head>
    <title>' . __('Install key check') . '</title>
</head>
<body>
    <p>' . __('Enter the install key to continue.') .
            '</p>
    <form accept-charset="utf-8" action="imgboard.php?install" method="post">
        <input type="hidden" name="install_key" value="">
        <div>
            <label for="install_key">' . __('Install Key:') .
            '</label>
            <input id="install_key" type="text" name="install_key" maxlength="255">
        </div>
        <div>
            <input type="submit" value="' . __('Submit') . '">
        </div>
    </form>
</body></html>';
    }

    public function verifyInstallKey()
    {
        $install_key = $_POST['install_key'] ?? '';
        return !nel_true_empty(NEL_INSTALL_KEY) && $install_key === NEL_INSTALL_KEY;
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
        $config = $this->database->config();

        echo sprintf(__('Database type configured is: %s'), $config['sqltype']) . '<br>';

        if (($config['sqltype'] === 'MYSQL' || $config['sqltype'] === 'MARIADB') && !$this->checkForInnoDB()) {
            nel_derp(102,
                _gettext('InnoDB engine is required for MySQL or MariaDB support but that engine is not available.'));
        } else {
            echo _gettext('No database problems detected.'), '<br>';
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

    public function createCoreTables()
    {
        $versions_table = new TableVersions($this->database, $this->sql_compatibility);
        $versions_table->createTable();
        // Settings table must exist before any config tables
        $settings_table = new TableSettings($this->database, $this->sql_compatibility);
        $settings_table->createTable();
        $setting_options_table = new TableSettingOptions($this->database, $this->sql_compatibility);
        $setting_options_table->createTable();
        $board_defaults_table = new TableBoardDefaults($this->database, $this->sql_compatibility);
        $board_defaults_table->createTable();
        $site_config_table = new TableSiteConfig($this->database, $this->sql_compatibility);
        $site_config_table->createTable();
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

        // NOTE: The following tables rely on the filetype categories table
        // Filetype categories must be created first!
        $filetype_categories_table = new TableFiletypeCategories($this->database, $this->sql_compatibility);
        $filetype_categories_table->createTable();
        $filetypes_table = new TableFiletypes($this->database, $this->sql_compatibility);
        $filetypes_table->createTable();

        // NOTE: The following tables rely on the user, role and/or permission tables
        // User, role and permission tables must be created first!
        $roles_table = new TableRoles($this->database, $this->sql_compatibility);
        $roles_table->createTable();
        $permissions_table = new TablePermissions($this->database, $this->sql_compatibility);
        $permissions_table->createTable();
        $users_table = new TableUsers($this->database, $this->sql_compatibility);
        $users_table->createTable();
        $role_permissions_table = new TableRolePermissions($this->database, $this->sql_compatibility);
        $role_permissions_table->createTable();
        $news_table = new TableNews($this->database, $this->sql_compatibility);
        $news_table->createTable();
        $private_messages_table = new TablePrivateMessages($this->database, $this->sql_compatibility);
        $private_messages_table->createTable();
        $noticeboard_table = new TableNoticeboard($this->database, $this->sql_compatibility);
        $noticeboard_table->createTable();
        $ip_notes_table = new TableIPNotes($this->database, $this->sql_compatibility);
        $ip_notes_table->createTable();

        // NOTE: The following tables rely on the domain registry table
        // Domain registry table must be created first!
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
        $wordfilters_table = new TableWordfilters($this->database, $this->sql_compatibility);
        $wordfilters_table->createTable();
        $system_logs_table = new TableLogs($this->database, $this->sql_compatibility);
        $system_logs_table->tableName(NEL_SYSTEM_LOGS_TABLE);
        $system_logs_table->createTable();
        $public_logs_table = new TableLogs($this->database, $this->sql_compatibility);
        $public_logs_table->tableName(NEL_PUBLIC_LOGS_TABLE);
        $public_logs_table->createTable();
        $bans_table = new TableBans($this->database, $this->sql_compatibility);
        $bans_table->createTable();
        $ban_appeals_table = new TableBanAppeals($this->database, $this->sql_compatibility);
        $ban_appeals_table->createTable();
        $captcha_table = new TableCaptcha($this->database, $this->sql_compatibility);
        $captcha_table->createTable();
        $board_configs_table = new TableBoardConfigs($this->database, $this->sql_compatibility);
        $board_configs_table->createTable();
        $pages_table = new TablePages($this->database, $this->sql_compatibility);
        $pages_table->createTable();
        $cache_table = new TableCache($this->database, $this->sql_compatibility);
        $cache_table->createTable();
        $user_roles_table = new TableUserRoles($this->database, $this->sql_compatibility);
        $user_roles_table->createTable();
        $r9k_content_table = new TableR9KContent($this->database, $this->sql_compatibility);
        $r9k_content_table->createTable();
        $r9k_mutes_table = new TableR9KMutes($this->database, $this->sql_compatibility);
        $r9k_mutes_table->createTable();
        $statistics_table = new TableStatistics($this->database, $this->sql_compatibility);
        $statistics_table->createTable();
        $scripts_table = new TableScripts($this->database, $this->sql_compatibility);
        $scripts_table->createTable();

        echo _gettext('Core database tables created.'), '<br>';
    }

    public function createCoreDirectories()
    {
        $this->file_handler->createDirectory(NEL_CACHE_FILES_PATH);
        $this->file_handler->createDirectory(NEL_GENERATED_FILES_PATH);
        $this->file_handler->createDirectory(NEL_GENERAL_FILES_PATH);
        $this->file_handler->createDirectory(NEL_CAPTCHA_FILES_PATH);
        $this->file_handler->createDirectory(NEL_BANNERS_FILES_PATH);
        $this->file_handler->createDirectory(NEL_TEMP_FILES_BASE_PATH);
        $this->file_handler->createDirectory(NEL_STYLES_FILES_PATH . 'custom/');
        $this->file_handler->createDirectory(NEL_SCRIPTS_FILES_PATH . 'custom/');
        $this->file_handler->createDirectory(NEL_IMAGE_SETS_FILES_PATH . 'custom/');
        $this->file_handler->createDirectory(NEL_MEDIA_FILES_PATH . 'custom/');
        echo _gettext('Core directories created.'), '<br>';
    }

    public function createBoardTables(string $board_id, string $db_prefix)
    {
        $domain = new DomainBoard($board_id, nel_database('core'));

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

        echo _gettext('Core templates installed.') . '<br>';
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

        echo _gettext('Core styles installed.') . '<br>';
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

        echo _gettext('Core image sets installed.') . '<br>';
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