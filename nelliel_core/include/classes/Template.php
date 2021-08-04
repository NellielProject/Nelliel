<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

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
        $this->loadFromDB();
        $this->front_end_data = $front_end_data;
    }

    public function id(): string
    {
        return $this->template_id ?? '';
    }

    public function info(string $key): string
    {
        return $this->info['template-info'][$key] ?? '';
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
                $info = json_encode($ini);
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
                'INSERT INTO "' . NEL_TEMPLATES_TABLE . '" ("template_id", "info") VALUES (?, ?)');
        $this->database->executePrepared($prepared, [$this->id(), $info]);
        $this->loadFromDB();
    }

    public function uninstall(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_TEMPLATES_TABLE . '" WHERE "template_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function loadFromDB(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_TEMPLATES_TABLE . '" WHERE "template_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);
        $this->data = is_array($data) ? $data : array();
        $this->info = json_decode($data['info'] ?? '', true);
    }
}
