<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use PDO;

class Template
{
    private $database;
    private $template_id;
    private $data = array();
    private $info = array();
    private $front_end_data;

    function __construct(NellielPDO $database, FrontEndData $front_end_data, string $template_id)
    {
        $this->database = $database;
        $this->template_id = $template_id;
        $this->front_end_data = $front_end_data;
        $this->load();
    }

    public function id(): string
    {
        return $this->template_id ?? '';
    }

    public function info(string $key): string
    {
        return $this->info[$key] ?? '';
    }

    public function data(string $section, string $key): string
    {
        return $this->data[$section][$key] ?? '';
    }

    public function getDirectory(): string
    {
        return $this->info('directory');
    }

    public function getPath(): string
    {
        $directory = $this->getDirectory();

        if ($directory !== '')
        {
            return NEL_TEMPLATES_FILES_PATH . $directory . '/';
        }

        return $directory;
    }

    public function install(bool $overwrite = false): void
    {
        $template_inis = $this->front_end_data->gettemplateInis();

        foreach ($template_inis as $ini)
        {
            if ($ini['template-info']['id'] === $this->id())
            {
                $directory = $ini['template-info']['directory'];
                break;
            }
        }

        if ($this->database->rowExists(NEL_TEMPLATES_TABLE, ['template_id'], [$this->id()],
                [PDO::PARAM_STR, PDO::PARAM_STR]))
        {
            if (!$overwrite)
            {
                return;
            }

            $this->uninstall();
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_TEMPLATES_TABLE . '" ("template_id", "directory") VALUES (?, ?)');
        $this->database->executePrepared($prepared, [$this->id(), $directory]);
        $this->load();
    }

    public function uninstall(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_TEMPLATES_TABLE . '" WHERE "template_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function load(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_TEMPLATES_TABLE . '" WHERE "template_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);
        $directory = $data['directory'] ?? '';
        $file = NEL_TEMPLATES_FILES_PATH . $directory . '/template_info.ini';

        if (file_exists($file))
        {
            $ini = parse_ini_file($file, true);
            $this->data = $ini ?? array();
            $this->info = $ini['template-info'] ?? array();
        }
    }
}
