<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use PDO;
use Nelliel\Interfaces\SelfPersisting;
use Nelliel\Tables\TableNews;
use Nelliel\Interfaces\MutableData;

class NewsArticle implements MutableData, SelfPersisting
{
    private NellielPDO $database;
    private array $data = array();
    private int $id;

    function __construct(NellielPDO $database, int $id = 0)
    {
        $this->database = $database;
        $this->id = $id;

        if ($id > 0) {
            $this->load();
        }
    }

    public function getData(string $key = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }

    public function changeData(string $key, $new_data): void
    {
        $this->data[$key] = TableNews::typeCastValue($key, $new_data);

        if ($key === 'id') {
            $this->id = $this->data[$key];
        }
    }

    public function load(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_NEWS_TABLE . '" WHERE "article_id" = :article_id');
        $prepared->bindValue(':article_id', $this->id, PDO::PARAM_STR);
        $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if ($result === false) {
            return;
        }

        $this->data = TableNews::typeCastData($result);
    }

    public function save(): void
    {
        if ($this->database->rowExists(NEL_NEWS_TABLE, ['article_id'], [$this->id], [PDO::PARAM_STR, PDO::PARAM_STR])) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_NEWS_TABLE .
                '" SET "username" = :username, "name" = :name, "time" = :time, "headline" = :headline, "text" = :text WHERE "article_id" = :article_id');
            $prepared->bindValue(':article_id', $this->id, PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_NEWS_TABLE .
                '" ("username", "name", "time", "headline", "text") VALUES (:username, :name, :time, :headline, :text)');
        }

        $prepared->bindValue(':username', $this->getData('username'), PDO::PARAM_STR);
        $prepared->bindValue(':name', $this->getData('name') ?? '', PDO::PARAM_STR);
        $prepared->bindValue(':time', $this->getData('time') ?? time(), PDO::PARAM_INT);
        $prepared->bindValue(':headline', $this->getData('headline') ?? '', PDO::PARAM_STR);
        $prepared->bindValue(':text', $this->getData('text') ?? '', PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }

    public function delete(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_NEWS_TABLE . '" WHERE "article_id" = :article_id');
        $prepared->bindValue(':article_id', $this->id, PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }
}