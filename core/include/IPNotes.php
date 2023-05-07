<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class IPNotes
{

    function __construct()
    {}

    public function getForIP(Domain $domain, string $ip): array
    {
        $prepared = $domain->database()->prepare(
            'SELECT * FROM "' . NEL_IP_NOTES_TABLE . '" WHERE "ip_address" = :ip_address OR "hashed_ip_address" = :hashed_ip_address');
        $prepared->bindValue(':ip_address', nel_prepare_ip_for_storage($ip), PDO::PARAM_LOB);
        $prepared->bindValue(':hashed_ip_address', $ip, PDO::PARAM_STR);
        $result = $domain->database()->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
        return $result;
    }

    public function create(Domain $domain, string $username, string $ip_address, string $hashed_ip_address,
        string $notes): void
    {
        $prepared = $domain->database()->prepare(
            'INSERT INTO "' . NEL_IP_NOTES_TABLE .
            '" ("username", "ip_address", "hashed_ip_address", "time", "notes") VALUES (:username, :ip_address, :hashed_ip_address, :time, :notes)');
        $prepared->bindValue(':username', $domain->id(), PDO::PARAM_STR);
        $prepared->bindValue(':ip_address', nel_prepare_ip_for_storage($ip_address), PDO::PARAM_LOB);
        $prepared->bindValue(':hashed_ip_address', $hashed_ip_address, PDO::PARAM_STR);
        $prepared->bindValue(':time', time(), PDO::PARAM_INT);
        $prepared->bindValue(':notes', $notes, PDO::PARAM_STR);
        $domain->database()->executePrepared($prepared);
    }

    public function removeByID(Domain $domain, int $note_id): void
    {
        $prepared = $domain->database()->prepare('DELETE FROM "' . NEL_IP_NOTES_TABLE . '" WHERE "note_id" = :note_id');
        $prepared->bindValue(':note_id', $note_id, PDO::PARAM_INT);
        $domain->database()->executePrepared($prepared);
    }

    public function removeByIP(Domain $domain, string $ip): void
    {
        $prepared = $domain->database()->prepare(
            'DELETE FROM "' . NEL_IP_NOTES_TABLE . '" WHERE "ip_address" = :ip_address OR "hashed_ip_address" = :hashed_ip_address');
        $prepared->bindValue(':ip_address', nel_prepare_ip_for_storage($ip), PDO::PARAM_LOB);
        $prepared->bindValue(':hashed_ip_address', $ip, PDO::PARAM_STR);
        $domain->database()->executePrepared($prepared);
    }
}