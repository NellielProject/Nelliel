<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class Wordfilters
{
    protected $database;
    protected static $wordfilters = array();

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
        $this->loadWordfilters();
    }

    public function apply(string $text, Domain $domain = null): string
    {
        $domain_filters = self::$wordfilters[$domain->id()] ?? array();
        $global_filters = self::$wordfilters[Domain::GLOBAL] ?? array();
        $combined_filters = array_merge($domain_filters, $global_filters);

        foreach ($combined_filters as $filter) {
            if ($filter['is_regex']) {
                $text = preg_replace($filter['text_match'] . 'u', $filter['replacement'], $text);
            } else {
                $text = utf8_str_replace($filter['text_match'], $filter['replacement'], $text);
            }
        }

        return $text;
    }

    protected function loadWordfilters(string $board_id = null): void
    {
        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_WORD_FILTERS_TABLE .
                '" WHERE ("board_id" = ? OR "board_id" IS NULL) AND "enabled" = 1');
            $result = $this->database->executePreparedFetchAll($prepared, [$board_id, Domain::GLOBAL], PDO::FETCH_ASSOC);
        } else {
            $query = 'SELECT * FROM "' . NEL_WORD_FILTERS_TABLE . '" WHERE "enabled" = 1';
            $result = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);
        }

        if (is_array($result)) {
            foreach ($result as $filter) {
                self::$wordfilters[$filter['board_id']][] = $filter;
            }
        }
    }
}
