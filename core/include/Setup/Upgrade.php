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

    public function doUpgrades(): void
    {
        if (!$this->needsUpgrade()) {
            echo __('Already up to date!') . '<br>';
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

        switch ($this->installedVersion()) {
            case 'v0.9.25':
                $target_version = NELLIEL_VERSION;
                echo sprintf(__('Updating from v0.9.25 to %s...'), $target_version) . '<br>';
                $core_sqltype = nel_database('core')->config()['sqltype'];

                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . NEL_FILETYPES_TABLE . '" CHANGE mime mimetypes TEXT NOT NULL');
                } else {
                    nel_database('core')->exec('ALTER TABLE "' . NEL_FILETYPES_TABLE . '" RENAME mime TO mimetypes');
                    nel_database('core')->exec(
                        'ALTER TABLE "' . NEL_FILETYPES_TABLE . '" ALTER COLUMN mimetypes TYPE TEXT');
                    nel_database('core')->exec(
                        'ALTER TABLE "' . NEL_FILETYPES_TABLE . '" ALTER COLUMN mimetypes SET NOT NULL');
                }

                $old_data = nel_database('core')->executeFetchAll(
                    'SELECT "format", "mimetypes" FROM "' . NEL_FILETYPES_TABLE . '"', PDO::FETCH_ASSOC);

                $multiples = ['bmp' => '["image/bmp", "image/x-bmp"]', 'tgs' => '["image/targa", "image/x-tga"]',
                    'pict' => '["image/pict", "image/x-pict"]', 'aiff' => '["audio/aiff", "audio/x-aiff"]',
                    'm4a' => '["audio/mp4", "audio/x-m4a"]', 'flac' => '["audio/flac", "audio/x-flac"]',
                    'midi' => '["audio/midi", "audio/x-midi"]', 'rtf' => '["text/rtf", "application/rtf"]',
                    'doc' => '["application/vnd.ms-word", "application/msword"]',
                    'gzip' => '["application/gzip", "application/x-gzip"]',
                    'rar' => '["application/vnd.rar", "application/x-rar-compressed"]',
                    'stuffit' => '["application/x-stuffit", "application/x-sit"]',
                    'swf' => '["application/vnd.adobe.flash-movie", "application/x-shockwave-flash"]'];

                foreach ($old_data as $data) {
                    $new_value = '["' . $data['mimetypes'] . '"]';

                    if (array_key_exists($data['format'], $multiples)) {
                        $new_value = $multiples[$data['format']];
                    }

                    $prepared = nel_database('core')->prepare(
                        'UPDATE "' . NEL_FILETYPES_TABLE . '" SET "mimetypes" = :mimetypes WHERE "format" = :format');
                    $prepared->bindValue(':mimetypes', $new_value);
                    $prepared->bindValue(':format', $data['format']);
                    nel_database('core')->executePrepared($prepared, null);
                }

                $prepared = nel_database('core')->exec(
                    'UPDATE "' . NEL_FILETYPES_TABLE . '" SET "extensions" = \'["3gp", "3gpp"]\' WHERE "format" = \'3gp\'');
                echo __(' - Filetypes table updated.') . '<br>';
                $migration_count ++;
        }

        return $migration_count;
    }
}