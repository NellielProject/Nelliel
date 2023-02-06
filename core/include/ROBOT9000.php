<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\DomainBoard;
use PDO;

class ROBOT9000
{

    function __construct()
    {}

    public function hashContent(DomainBoard $board, string $content): string
    {
        $modified_string = $content;

        if ($board->setting('r9k_strip_repeating')) {
            $modified_string = preg_replace('/(.)\1+/us', '$1', $modified_string);
        }

        if ($board->setting('r9k_include_unicode_letters')) {
            $modified_string = preg_replace('/[^\p{L}]/u', '', $modified_string);
        } else {
            $modified_string = preg_replace('/[^a-zA-Z]/u', '', $modified_string);
        }

        return hash('sha1', $modified_string);
    }

    public function checkForHash(DomainBoard $board, string $hash): bool
    {
        if ($board->setting('r9k_global_unoriginal_check')) {
            $prepared = nel_site_domain()->database()->prepare(
                'SELECT 1 FROM "' . NEL_R9K_CONTENT_TABLE . '" WHERE "content_hash" = :content_hash LIMIT 1');
        } else {
            $prepared = nel_site_domain()->database()->prepare(
                'SELECT 1 FROM "' . NEL_R9K_CONTENT_TABLE .
                '" WHERE "content_hash" = :content_hash AND "board_id" = :board_id LIMIT 1');
            $prepared->bindValue(':board_id', $board->id(), PDO::PARAM_STR);
        }

        $prepared->bindValue(':content_hash', $hash, PDO::PARAM_STR);
        $result = nel_site_domain()->database()->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        return $result !== false;
    }

    public function addHash(DomainBoard $board, string $hash, int $time): void
    {
        $prepared = nel_site_domain()->database()->prepare(
            'INSERT INTO "' . NEL_R9K_CONTENT_TABLE .
            '" ("board_id", "content_hash", "post_time") VALUES (:board_id, :content_hash, :post_time)');
        $prepared->bindValue(':board_id', $board->id(), PDO::PARAM_STR);
        $prepared->bindValue(':content_hash', $hash, PDO::PARAM_STR);
        $prepared->bindValue(':post_time', $time, PDO::PARAM_INT);
        nel_site_domain()->database()->executePrepared($prepared, null);
    }

    public function getLastMuteTime(DomainBoard $board, string $poster_hash): int
    {
        if ($board->setting('r9k_global_mute_check')) {
            $prepared = nel_site_domain()->database()->prepare(
                'SELECT "mute_time" FROM "' . NEL_R9K_MUTES_TABLE .
                '" WHERE "poster_hash" = :poster_hash AND "mute_time" >= :mute_time ORDER BY "mute_time" DESC LIMIT 1');
        } else {
            $prepared = nel_site_domain()->database()->prepare(
                'SELECT "mute_time" FROM "' . NEL_R9K_MUTES_TABLE .
                '" WHERE "poster_hash" = :poster_hash AND "board_id" = :board_id AND "mute_time" >= :mute_time ORDER BY "mute_time" DESC LIMIT 1');
            $prepared->bindValue(':board_id', $board->id(), PDO::PARAM_STR);
        }

        $prepared->bindValue(':poster_hash', $poster_hash, PDO::PARAM_STR);
        $time_range = time() - $board->setting('r9k_mute_time_range');
        $prepared->bindValue(':mute_time', $time_range, PDO::PARAM_INT);
        $result = nel_site_domain()->database()->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        return intval($result);
    }

    public function muteCount(DomainBoard $board, string $poster_hash): int
    {
        if ($board->setting('r9k_global_mute_check')) {
            $prepared = nel_site_domain()->database()->prepare(
                'SELECT COUNT(*) FROM "' . NEL_R9K_MUTES_TABLE .
                '" WHERE "poster_hash" = :poster_hash AND "mute_time" >= :mute_time');
        } else {
            $prepared = nel_site_domain()->database()->prepare(
                'SELECT COUNT(*) FROM "' . NEL_R9K_MUTES_TABLE .
                '" WHERE "poster_hash" = :poster_hash AND "board_id" = :board_id AND "mute_time" >= :mute_time');
            $prepared->bindValue(':board_id', $board->id(), PDO::PARAM_STR);
        }

        $prepared->bindValue(':poster_hash', $poster_hash, PDO::PARAM_STR);
        $time_range = time() - $board->setting('r9k_mute_time_range');
        $prepared->bindValue(':mute_time', $time_range, PDO::PARAM_INT);
        $result = nel_site_domain()->database()->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        return intval($result);
    }

    public function addMute(DomainBoard $board, string $poster_hash): void
    {
        $prepared = nel_site_domain()->database()->prepare(
            'INSERT INTO "' . NEL_R9K_MUTES_TABLE .
            '" ("board_id", "poster_hash", "mute_time") VALUES (:board_id, :poster_hash, :mute_time)');
        $prepared->bindValue(':board_id', $board->id(), PDO::PARAM_STR);
        $prepared->bindValue(':poster_hash', $poster_hash, PDO::PARAM_STR);
        $mute_time = time();
        $prepared->bindValue(':mute_time', $mute_time, PDO::PARAM_INT);
        nel_site_domain()->database()->executePrepared($prepared, null);
    }

    public function calculateMuteTime(DomainBoard $board, int $mute_count): int
    {
        $base = $board->setting('r9k_mute_base_number') ?? 2;
        return (int) pow($base, $mute_count);
    }
}
