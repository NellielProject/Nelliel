<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class Statistics
{

    function __construct()
    {}

    public function get(Domain $domain, string $statistic): int
    {
        $prepared = $domain->database()->prepare(
            'SELECT "value" FROM "' . NEL_STATISTICS_TABLE .
            '" WHERE "domain_id" = :domain_id AND "statistic" = :statistic');
        $prepared->bindValue(':domain_id', $domain->id(), PDO::PARAM_STR);
        $prepared->bindValue(':statistic', $statistic, PDO::PARAM_STR);
        $result = $domain->database()->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        return intval($result);
    }

    public function increment(Domain $domain, string $statistic, int $increment): void
    {
        if ($domain->database()->rowExists(NEL_STATISTICS_TABLE, ['domain_id', 'statistic'],
            [$domain->id(), $statistic], [PDO::PARAM_STR, PDO::PARAM_STR])) {
            $prepared = $domain->database()->prepare(
                'UPDATE "' . NEL_STATISTICS_TABLE .
                '" SET "value" = "value" + :increment WHERE "domain_id" = :domain_id AND "statistic" = :statistic');
            $prepared->bindValue(':increment', $increment, PDO::PARAM_INT);
            $prepared->bindValue(':domain_id', $domain->id(), PDO::PARAM_STR);
            $prepared->bindValue(':statistic', $statistic, PDO::PARAM_STR);
            $domain->database()->executePrepared($prepared);
        } else {
            $this->update($domain, $statistic, $increment);
        }
    }

    public function update(Domain $domain, string $statistic, int $value): void
    {
        if ($domain->database()->rowExists(NEL_STATISTICS_TABLE, ['domain_id', 'statistic'],
            [$domain->id(), $statistic], [PDO::PARAM_STR, PDO::PARAM_STR])) {
            $prepared = $domain->database()->prepare(
                'UPDATE "' . NEL_STATISTICS_TABLE .
                '" SET "value" = :value WHERE "domain_id" = :domain_id AND "statistic" = :statistic');
        } else {
            $prepared = $domain->database()->prepare(
                'INSERT INTO "' . NEL_STATISTICS_TABLE . '" ("domain_id", "statistic", "value") VALUES (:domain_id, :statistic, :value)');
        }

        $prepared->bindValue(':domain_id', $domain->id(), PDO::PARAM_STR);
        $prepared->bindValue(':statistic', $statistic, PDO::PARAM_STR);
        $prepared->bindValue(':value', $value, PDO::PARAM_INT);
        $domain->database()->executePrepared($prepared);
    }

    public function remove(Domain $domain, string $statistic): void
    {
        $prepared = $domain->database()->prepare(
            'DELETE FROM "' . NEL_STATISTICS_TABLE . '" WHERE "domain_id" = :domain_id AND "statistic" = :statistic');
        $prepared->bindValue(':domain_id', $domain->id(), PDO::PARAM_STR);
        $prepared->bindValue(':statistic', $statistic, PDO::PARAM_STR);
        $domain->database()->executePrepared($prepared);
    }
}