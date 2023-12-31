<?php
declare(strict_types = 1);

namespace Nelliel\Setup;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\DatabaseConfig;
use Nelliel\Regen;
use Nelliel\Database\DatabaseConnector;
use Nelliel\Utility\FileHandler;
use PDO;

class Upgrade
{
    private $file_handler;
    private $original = 'v0.0';
    private $installed = 'v0.0';
    private $core_database_connection;

    function __construct(FileHandler $file_handler)
    {
        $this->file_handler = $file_handler;
    }

    public function displayLogin(): void
    {
        echo '
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type"  content="text/html;charset=utf-8">
    <title>' . __('Site owner login') . '</title>
    <link rel="stylesheet" type="text/css" href="' . NEL_STYLES_WEB_PATH . 'core/base_style.css' .
            '">
</head>
<body>
    <p>' . __('Please log in with the site owner account to perform upgrades.') .
            '</p>
    <form accept-charset="utf-8" action="imgboard.php?upgrade" method="post" class="display-table">
        <input type="hidden" name="upgrade_login" value="">
        <div class="display-row">
            <label for="username" class="display-cell form-label">' . __('Username') .
            '</label>
            <input id="username" class="display-cell form-input" type="text" name="username" maxlength="255">
        </div>
        <div class="display-row">
            <label for="super_sekrit" class="display-cell form-label">' . __('Password') .
            '</label>
            <input id="super_sekrit" class="display-cell form-input" type="password" name="super_sekrit" maxlength="255">
        </div>
        <div class="display-row">
            <input type="submit" class="display-cell form-input" value="' . __('Submit') . '">
        </div>
    </form>
</body></html>';
    }

    public function verifyLogin(): bool
    {
        if (version_compare($this->installedVersion(), 'v0.9.25', '<=')) {
            $username = $_POST['username'] ?? '';
        } else {
            $username = utf8_strtolower($_POST['username'] ?? '');
        }

        $form_password = $_POST['super_sekrit'] ?? '';
        $prepared = $this->core_database_connection->prepare(
            'SELECT * FROM "nelliel_users" WHERE "username" = :username AND "owner" = 1 LIMIT 1');
        $prepared->bindValue(':username', $username);
        $user_data = $this->core_database_connection->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);
        return is_array($user_data) &&
            password_verify($form_password, $user_data['password'] ?? $user_data['user_password']);
    }

    public function doUpgrades(): void
    {
        if (!$this->needsUpgrade()) {
            echo __('Already up to date!');
            return;
        }

        if (version_compare($this->installedVersion(), 'v0.9.25', '<')) {
            echo __('Versions older than v0.9.25 do not have an upgrade path.');
            return;
        }

        if (version_compare($this->installedVersion(), 'v0.9.31', '<')) {
            $db_config = array();
            include NEL_CONFIG_FILES_PATH . 'config.php';
            $database_connector = new DatabaseConnector(new DatabaseConfig($db_config));
            $this->core_database_connection = $database_connector->getConnection('core');
        } else {
            $this->core_database_connection = nel_database('core');
        }

        if (isset($_POST['upgrade_login'])) {
            if (!$this->verifyLogin()) {
                echo __('Username or password is wrong or that user is not a site owner.');
                return;
            }
        } else {
            $this->displayLogin();
            return;
        }

        $migration_count = $this->doMigrations();

        if ($migration_count > 0) {
            echo sprintf(__('%d migrations were completed.'), $migration_count) . '<br>';
        } else {
            echo __('No migrations were needed.') . '<br>';
        }

        $generate_files = new GenerateFiles($this->file_handler);
        $versions_data = array();
        $versions_data['original'] = $this->originalVersion();
        $versions_data['installed'] = NELLIEL_VERSION;
        $generate_files->versions($versions_data, true);

        echo __('Regenerating caches and pages.') . '<br>';
        $regen = new Regen();
        nel_site_domain()->regenCache();
        nel_site_domain(true);
        $regen->sitePages(nel_site_domain());
        $regen->allBoards(true, true);
        $regen->overboard(nel_site_domain());

        echo __('Upgrades completed!');
    }

    public function needsUpgrade(): bool
    {
        return version_compare($this->installedVersion(), NELLIEL_VERSION, '<');
    }

    public function originalVersion(): string
    {
        if ($this->original === 'v0.0') {
            $versions_data = $this->versionsData();
            $this->original = $versions_data['original'] ?? $this->original;
        }

        return $this->original;
    }

    public function installedVersion(): string
    {
        if ($this->installed === 'v0.0') {
            $versions_data = $this->versionsData();
            $this->installed = $versions_data['installed'] ?? $this->installed;
        }

        return $this->installed;
    }

    public function versionsData(): array
    {
        $versions_data = array();

        if (file_exists(NEL_GENERATED_FILES_PATH . 'versions.php')) {
            include NEL_GENERATED_FILES_PATH . 'versions.php';
        }

        return $versions_data;
    }

    public function doMigrations(): int
    {
        $migration_count = 0;
        $config_migrations = new ConfigMigrations($this->file_handler, $this);
        $migration_count += $config_migrations->doMigrations();
        $beta_migrations = new BetaMigrations($this->file_handler, $this);
        $migration_count += $beta_migrations->doMigrations();
        return $migration_count;
    }
}