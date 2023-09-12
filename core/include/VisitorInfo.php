<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class VisitorInfo
{
    private $visitor_id;
    private $info = array();
    private $database;
    private $unloaded = true;

    function __construct(string $visitor_id, bool $process = true)
    {
        $this->database = nel_site_domain()->database();
        $this->visitor_id = $visitor_id;

        if ($process) {
            $this->updateID($visitor_id);
        }

        $this->load();
    }

    private function load(): void
    {
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_VISITOR_INFO_TABLE . '" WHERE "visitor_ids" = :visitor_id');
        $prepared->bindValue(':visitor_id', $this->visitor_id, PDO::PARAM_STR);
        $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if ($result !== false) {
            $this->info = $result;
            return;
        }
    }

    private function store(): void
    {
        if ($this->inDatabase()) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_VISITOR_INFO_TABLE .
                '" SET "visitor_id" = :visitor_id, "last_activity" = :last_activity WHERE "visitor_id" = :visitor_id');
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_VISITOR_INFO_TABLE .
                '" ("visitor_id", "last_activity")
                VALUES (:visitor_id, :last_activity)');
        }

        $prepared->bindValue(':visitor_id', $this->visitor_id, PDO::PARAM_STR);
        $prepared->bindValue(':last_activity', $this->info['last_activity'] ?? 0, PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    private function inDatabase(): bool
    {
        if (!empty($this->visitor_id)) {
            return $this->database->rowExists(NEL_VISITOR_INFO_TABLE, ['visitor_id'], [$this->visitor_id],
                [PDO::PARAM_STR]);
        }

        return false;
    }

    public function updateID(string $new_visitor_id): void
    {
        $this->info['visitor_id'] = $new_visitor_id;
        $this->visitor_id = $new_visitor_id;
        $this->store();
    }

    public function updateLastActivity(int $time): void
    {
        $this->info['last_activity'] = $time;
        $this->store();
    }

    public function infoAvailable(): bool
    {
        return $this->inDatabase();
    }

    public function getInfo(string $key)
    {
        return $this->info[$key] ?? null;
    }
}