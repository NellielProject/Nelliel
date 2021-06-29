<?php
declare(strict_types = 1);

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Utility\FileHandler;

class Upgrade
{
    private $file_handler;
    private $original = 'v0.0';
    private $installed = 'v0.0';

    function __construct(FileHandler $file_handler)
    {
        $this->file_handler = $file_handler;
    }

    public function doUpgrades(): void
    {
        if (!$this->needsUpgrade())
        {
            echo _gettext('Already up to date!') . '<br>';
            return;
        }

        $migration_count = $this->doMigrations();

        if ($migration_count > 0)
        {
            echo sprintf(_gettext('%d migrations have been completed.'), $migration_count) . '<br>';
        }
        else
        {
            echo _gettext('No migrations were needed.') . '<br>';
        }

        $generate_files = new \Nelliel\Setup\GenerateFiles($this->file_handler);
        $versions_data = array();
        $versions_data['original'] = $this->originalVersion();
        $versions_data['installed'] = NELLIEL_VERSION;
        $generate_files->versions($versions_data, true);
        echo _gettext('Upgrades completed!');
    }

    public function needsUpgrade(): bool
    {
        return version_compare($this->installedVersion(), NELLIEL_VERSION, '<');
    }

    public function originalVersion(): string
    {
        if ($this->original === 'v0.0')
        {
            $versions_data = $this->versionsData();
            $this->original = $versions_data['original'] ?? $this->original;
        }

        return $this->original;
    }

    public function installedVersion(): string
    {
        if ($this->installed === 'v0.0')
        {
            $versions_data = $this->versionsData();
            $this->installed = $versions_data['installed'] ?? $this->installed;
        }

        return $this->installed;
    }

    public function versionsData(): array
    {
        $versions_data = array();

        if (file_exists(NEL_GENERATED_FILES_PATH . 'versions.php'))
        {
            include NEL_GENERATED_FILES_PATH . 'versions.php';
        }

        return $versions_data;
    }

    public function doMigrations(): int
    {
        $migration_count = 0;

        switch ($this->installedVersion())
        {
        }

        return $migration_count;
    }
}