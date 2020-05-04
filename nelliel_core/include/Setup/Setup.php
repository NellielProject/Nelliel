<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;
use Nelliel\SQLCompatibility;

class Setup
{

    function __construct()
    {
    }

    public function install()
    {
        echo '<!DOCTYPE html><html><body>';

        if ($this->checkInstallDone())
        {
            nel_derp(108, _gettext('Installation has already been completed!'));
        }

        $this->checkDBEngine();
        $this->mainDirWritable();
        $this->coreDirWritable();
        $this->configDirWritable();

        $file_handler = new \Nelliel\FileHandler();
        $generate_files = new \Nelliel\Setup\GenerateFiles($file_handler);
        $install_id = base64_encode(random_bytes(33));

        if ($generate_files->peppers(false))
        {
            echo _gettext('Peppers file has been created.'), '<br>';
        }
        else
        {
            echo _gettext('Peppers file already present.'), '<br>';
        }

        $this->createCoreTables();
        $this->createCoreDirectories();
        $site_domain = new \Nelliel\DomainSite(nel_database());
        $regen = new \Nelliel\Regen();
        $regen->siteCache($site_domain);
        //$regen->news($site_domain);
        $generate_files->installDone(false);


        if ($this->ownerCreated())
        {
            echo _gettext('Site owner account already created.'), '<br>';
            echo _gettext(
                    'Install has finished with no apparent problems! When you\'re ready to continue, follow this link to the login page: '), '<br>';
            echo '<a href="' . BASE_WEB_PATH . 'imgboard.php?module=account&amp;action=login">' . _gettext('Login page') . '</a>';
            echo '</body></html>';
            die();
        }
        else
        {
            echo '<p>';
            echo _gettext(
                    'No problems so far! To complete setup, a site owner account needs to be created. This account will have all permissions by default. It is also necessary to use the site settings control panel.');
            echo '</p>';
            echo '<form accept-charset="utf-8" action="imgboard.php?module=account&amp;action=register&amp;create_owner=' . rawurlencode($install_id) . '" method="post">';
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
            $generate_files->ownerCreate($install_id, false);
            die();
        }
    }

    public function ownerCreated()
    {
        return file_exists(GENERATED_FILE_PATH . 'create_owner.php');
    }

    public function checkInstallDone()
    {
        return file_exists(GENERATED_FILE_PATH . 'install_done.php');
    }

    public function checkDBEngine()
    {
        if ((SQLTYPE === 'MYSQL' || SQLTYPE === 'MARIADB') && !$this->checkForInnoDB())
        {
            nel_derp(102,
                    _gettext(
                            'InnoDB engine is required for MySQL or MariaDB support. However the engine is not available for some reason.'));
        }
        else
        {
            echo _gettext('DB engine ok.'), '<br>';
        }
    }

    public function coreDirWritable()
    {
        if (!is_writable(NELLIEL_CORE_PATH))
        {
            nel_derp(104, _gettext('The core directory not writable.'));
        }
        else
        {
            echo _gettext('The core directory is writable.'), '<br>';
        }
    }

    public function mainDirWritable()
    {
        if (!is_writable(BASE_PATH))
        {
            nel_derp(105, _gettext('Nelliel main directory is not writable.'));
        }
        else
        {
            echo _gettext('Main directory is writable.'), '<br>';
        }
    }

    public function configDirWritable()
    {
        if (!is_writable(CONFIG_FILE_PATH))
        {
            nel_derp(106, _gettext('Configuration directory is missing or not writable. Admin should check this out.'));
        }
        else
        {
            echo _gettext('The configutation directory is writable.'), '<br>';
        }
    }

    public function createCoreTables()
    {
        $database = nel_database();
        $sql_compatibility = new SQLCompatibility($database);
        $versions_table = new TableVersions($database, $sql_compatibility);
        $versions_table->createTable();
        $assets_table = new TableAssets($database, $sql_compatibility);
        $assets_table->createTable();
        $bans_table = new TableBans($database, $sql_compatibility);
        $bans_table->createTable();
        $board_data_table = new TableBoardData($database, $sql_compatibility);
        $board_data_table->createTable();
        $captcha_table = new TableCaptcha($database, $sql_compatibility);
        $captcha_table->createTable();
        $cites_table = new TableCites($database, $sql_compatibility);
        $cites_table->createTable();
        $board_defaults_table = new TableBoardConfig($database, $sql_compatibility);
        $board_defaults_table->tableName(BOARD_DEFAULTS_TABLE);
        $board_defaults_table->createTable();
        $file_filters_table = new TableFileFilters($database, $sql_compatibility);
        $file_filters_table->createTable();
        $filetypes_table = new TableFiletypes($database, $sql_compatibility);
        $filetypes_table->createTable();
        $login_attempts_table = new TableLoginAttempts($database, $sql_compatibility);
        $login_attempts_table->createTable();
        $news_table = new TableNews($database, $sql_compatibility);
        $news_table->createTable();
        $permissions_table = new TablePermissions($database, $sql_compatibility);
        $permissions_table->createTable();
        $reports_table = new TableReports($database, $sql_compatibility);
        $reports_table->createTable();
        $role_permissions_table = new TableRolePermissions($database, $sql_compatibility);
        $role_permissions_table->createTable();
        $roles_table = new TableRoles($database, $sql_compatibility);
        $roles_table->createTable();
        $site_config_table = new TableSiteConfig($database, $sql_compatibility);
        $site_config_table->createTable();
        $staff_logs_table = new TableLogs($database, $sql_compatibility);
        $staff_logs_table->tableName(STAFF_LOGS_TABLE);
        $staff_logs_table->createTable();
        $system_logs_table = new TableLogs($database, $sql_compatibility);
        $system_logs_table->tableName(SYSTEM_LOGS_TABLE);
        $system_logs_table->createTable();
        $templates_table = new TableTemplates($database, $sql_compatibility);
        $templates_table->createTable();
        $user_roles_table = new TableUserRoles($database, $sql_compatibility);
        $user_roles_table->createTable();
        $users_table = new TableUsers($database, $sql_compatibility);
        $users_table->createTable();
        echo _gettext('Core database tables created.'), '<br>';
    }

    public function createCoreDirectories()
    {
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->createDirectory(CACHE_FILE_PATH, DIRECTORY_PERM, true);
        $file_handler->createDirectory(GENERATED_FILE_PATH, DIRECTORY_PERM, true);
        echo _gettext('Core directories created.'), '<br>';
    }

    public function createBoardTables(string $board_id, string $db_prefix)
    {
        $database = nel_database();
        $sql_compatibility = new SQLCompatibility($database);

        // Domain and such doesn't function without config table
        $config_table = new TableBoardConfig($database, $sql_compatibility);
        $config_table->tableName($db_prefix . '_config');
        $config_table->createTable();
        $config_table->copyFrom(BOARD_DEFAULTS_TABLE);

        $domain = new \Nelliel\DomainBoard($board_id, nel_database());
        $references = $domain->reference();
        $threads_table = new TableThreads($database, $sql_compatibility);
        $threads_table->tableName($domain->reference('threads_table'));
        $threads_table->createTable();
        $threads_table->tableName($domain->reference('archive_threads_table'));
        $threads_table->createTable();
        $posts_table = new TablePosts($database, $sql_compatibility);
        $posts_table->tableName($domain->reference('posts_table'));
        $posts_table->createTable(['threads_table' => $domain->reference('threads_table')]);
        $posts_table->tableName($domain->reference('archive_posts_table'));
        $posts_table->createTable(['threads_table' => $domain->reference('archive_threads_table')]);
        $content_table = new TableContent($database, $sql_compatibility);
        $content_table->tableName($domain->reference('content_table'));
        $content_table->createTable(['posts_table' => $domain->reference('posts_table')]);
        $content_table->tableName($domain->reference('archive_content_table'));
        $content_table->createTable(['posts_table' => $domain->reference('archive_posts_table')]);
    }

    public function createBoardDirectories(string $board_id)
    {
        $file_handler = new \Nelliel\FileHandler();
        $domain = new \Nelliel\DomainBoard($board_id, nel_database());
        $references = $domain->reference();
        $file_handler->createDirectory($references['src_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['preview_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['page_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_src_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_preview_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_page_path'], DIRECTORY_PERM, true);
    }

    private function checkForInnoDB()
    {
        $database = nel_database();
        $result = $database->query("SHOW ENGINES");
        $list = $result->fetchAll(PDO::FETCH_ASSOC);

        foreach ($list as $entry)
        {
            if ($entry['Engine'] === 'InnoDB' && ($entry['Support'] === 'DEFAULT' || $entry['Support'] === 'YES'))
            {
                return true;
            }
        }

        return false;
    }
}