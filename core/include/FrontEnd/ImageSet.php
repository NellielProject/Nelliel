<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use PDO;

class ImageSet
{
    private $database;
    private $image_set_id;
    private $data = array();
    private $info = array();
    private $front_end_data;

    function __construct(NellielPDO $database, FrontEndData $front_end_data, string $image_set_id)
    {
        $this->database = $database;
        $this->image_set_id = $image_set_id;
        $this->front_end_data = $front_end_data;
        $this->load();
    }

    public function id(): string
    {
        return $this->image_set_id ?? '';
    }

    public function info(string $key): string
    {
        return $this->info[$key] ?? '';
    }

    public function data(string $section, string $key): string
    {
        return $this->data[$section][$key] ?? '';
    }

    public function getFile(string $section, string $image, bool $fallback): string
    {
        if ($this->data($section, $image) === '' && $fallback)
        {
            return $this->front_end_data->getBaseImageSet()->getFile($section, $image, false);
        }

        return $this->info[$section][$image] ?? '';
    }

    public function getFilePath(string $section, string $image, bool $fallback): string
    {
        if ($this->getFile($section, $image, false) === '' && $fallback)
        {
            return $this->front_end_data->getBaseImageSet()->getFilePath($section, $image, false);
        }

        $image_file = $this->data($section, $image);

        if ($image_file !== '')
        {
            return NEL_IMAGE_SETS_FILES_PATH . $this->info('directory') . '/' . $section . '/' . $image_file;
        }

        return '';
    }

    public function getWebPath(string $section, string $image, bool $fallback): string
    {
        if ($this->getFile($section, $image, false) === '' && $fallback)
        {
            return $this->front_end_data->getBaseImageSet()->getWebPath($section, $image, false);
        }

        $image_file = $this->data($section, $image);

        if ($image_file !== '')
        {
            return NEL_IMAGE_SETS_WEB_PATH . $this->info('directory') . '/' . $section . '/' . $image_file;
        }

        return '';
    }

    public function install(bool $overwrite = false): void
    {
        $image_set_inis = $this->front_end_data->getImageSetInis();

        foreach ($image_set_inis as $ini)
        {
            if ($ini['set-info']['id'] === $this->id())
            {
                $directory = $ini['set-info']['directory'];
                break;
            }
        }

        if ($this->database->rowExists(NEL_IMAGE_SETS_TABLE, ['set_id'], [$this->id()],
                [PDO::PARAM_STR, PDO::PARAM_STR]))
        {
            if (!$overwrite)
            {
                return;
            }

            $this->uninstall();
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_IMAGE_SETS_TABLE . '" ("set_id", "directory") VALUES (?, ?)');
        $this->database->executePrepared($prepared, [$this->id(), $directory]);
        $this->load();
    }

    public function uninstall(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_IMAGE_SETS_TABLE . '" WHERE "set_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function load(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_IMAGE_SETS_TABLE . '" WHERE "set_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);
        $directory = $data['directory'] ?? '';
        $file = NEL_IMAGE_SETS_FILES_PATH . $directory . '/image_info.ini';

        if (file_exists($file))
        {
            $ini = parse_ini_file($file, true);
            $this->data = $ini ?? array();
            $this->info = $ini['set-info'] ?? array();
        }
    }
}
