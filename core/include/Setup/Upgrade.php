<?php
declare(strict_types = 1);

namespace Nelliel\Setup;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Utility\FileHandler;
use PDO;

class Upgrade
{
    private $file_handler;
    private $original = 'v0.0';
    private $installed = 'v0.0';

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
    <title>' . __('Site owner login') . '</title>
</head>
<body>
    <p>' . __('Please log in with the site owner account to perform upgrades.') .
            '</p>
    <form accept-charset="utf-8" action="imgboard.php?upgrade" method="post">
        <input type="hidden" name="upgrade_login" value="">
        <div>
            <label for="username">' . __('Username:') .
            '</label>
            <input id="username" type="text" name="username" maxlength="255">
        </div>
        <div>
            <label for="super_sekrit">' . __('Password:') .
            '</label>
            <input id="super_sekrit" type="password" name="super_sekrit" maxlength="255">
        </div>
        <div>
            <input type="submit" value="' . __('Submit') . '">
        </div>
    </form>
</body></html>';
    }

    public function verifyLogin(): bool
    {
        if($this->installedVersion() < 'v0.9.25') {
            $username = $_POST['username'] ?? '';
        } else {
            $username = utf8_strtolower($_POST['username'] ?? '');
        }

        $form_password = $_POST['super_sekrit'] ?? '';
        $prepared = nel_database('core')->prepare(
            'SELECT * FROM "' . NEL_USERS_TABLE . '" WHERE "username" = :username AND "owner" = 1');
        $prepared->bindValue(':username', $username);
        $user_data = nel_database('core')->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);
        return is_array($user_data) &&
            nel_password_verify($form_password, $user_data['password'] ?? $user_data['user_password']);
    }

    public function doUpgrades(): void
    {
        if (!$this->needsUpgrade()) {
            echo __('Already up to date!');
            return;
        }

        if (isset($_POST['upgrade_login'])) {
            if (!$this->verifyLogin()) {
                echo __('Username or password is wrong or that user is not a site owner account.');
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
        $beta_migrations = new BetaMigrations($this->file_handler, $this);
        $migration_count += $beta_migrations->doMigrations();
        return $migration_count;
    }
}