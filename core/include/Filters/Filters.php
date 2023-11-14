<?php
declare(strict_types = 1);

namespace Nelliel\Filters;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use PDO;

class Filters
{
    private $database;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function applyFileFilters(string $hash, array $board_ids = array()): void
    {
        foreach ($this->getFileFilters($board_ids) as $filter) {
            if (!$filter->getData('enabled')) {
                continue;
            }

            if ($filter->getData('file_hash') === $hash) {
                switch ($filter->getData('filter_action')) {
                    case 'reject':
                    default:
                        nel_derp(24, _gettext('Rejected due to prohibited file.'));
                }
            }
        }
    }

    public function applyWordfilters(string $text, array $board_ids = array()): string
    {
        foreach ($this->getWordfilters($board_ids) as $filter) {
            if (!$filter->getData('enabled')) {
                continue;
            }

            switch ($filter->getData('filter_action')) {
                case 'reject':
                    if (preg_match('/' . $filter->getData('text_match') . '/u', $text) === 1) {
                        nel_derp(59, __('Rejected by wordfilter.'));
                    }

                    break;

                case 'replace':
                default:
                    $text = preg_replace('/' . $filter->getData('text_match') . '/u', $filter->getData('replacement'),
                        $text);
            }
        }

        return $text;
    }

    public function getWordfilters(array $board_ids = array(), bool $sort_by_domain = false): array
    {
        $wordfilters = array();

        if (nel_true_empty($board_ids)) {
            $filters = $this->getWordfiltersForDomain();
        } else {
            $filters = array();

            foreach ($board_ids as $board_id) {
                $filters = array_merge($filters, $this->getWordfiltersForDomain($board_id));
            }
        }

        if ($sort_by_domain) {
            foreach ($filters as $filter) {
                $board_id = $filter->getData('board_id');

                if (nel_true_empty($board_id)) {
                    $board_id = Domain::GLOBAL;
                }

                $wordfilters[$board_id][] = $filter;
            }
        } else {
            $wordfilters = $filters;
        }

        return $wordfilters;
    }

    private function getWordfiltersForDomain(string $board_id = null): array
    {
        $wordfilters = array();

        if ($board_id === Domain::GLOBAL) {
            $prepared = $this->database->prepare(
                'SELECT "filter_id" FROM "' . NEL_WORDFILTERS_TABLE .
                '" WHERE "board_id" = ? OR "board_id" = \'\' OR "board_id" IS NULL');
            $result = $this->database->executePreparedFetchAll($prepared, [$board_id], PDO::FETCH_COLUMN);
        } else if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT "filter_id" FROM "' . NEL_WORDFILTERS_TABLE . '" WHERE "board_id" = ?');
            $result = $this->database->executePreparedFetchAll($prepared, [$board_id], PDO::FETCH_COLUMN);
        } else {
            $query = 'SELECT "filter_id" FROM "' . NEL_WORDFILTERS_TABLE . '"';
            $result = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);
        }

        if (is_array($result)) {
            foreach ($result as $filter_id) {
                $wordfilters[] = new Wordfilter($this->database, (int) $filter_id);
            }
        }

        return $wordfilters;
    }

    public function getFileFilters(array $board_ids = array(), bool $sort_by_domain = false): array
    {
        $file_filters = array();

        if (nel_true_empty($board_ids)) {
            $filters = $this->getFileFiltersForDomain();
        } else {
            $filters = array();

            foreach ($board_ids as $board_id) {
                $filters = array_merge($filters, $this->getFileFiltersForDomain($board_id));
            }
        }

        if ($sort_by_domain) {
            foreach ($filters as $filter) {
                $board_id = $filter->getData('board_id');

                if (nel_true_empty($board_id)) {
                    $board_id = Domain::GLOBAL;
                }

                $file_filters[$board_id][] = $filter;
            }
        } else {
            $file_filters = $filters;
        }

        return $file_filters;
    }

    private function getFileFiltersForDomain(string $board_id = null): array
    {
        $file_filters = array();

        if ($board_id === Domain::GLOBAL) {
            $prepared = $this->database->prepare(
                'SELECT "filter_id" FROM "' . NEL_FILE_FILTERS_TABLE .
                '" WHERE "board_id" = ? OR "board_id" = \'\' OR "board_id" IS NULL');
            $result = $this->database->executePreparedFetchAll($prepared, [$board_id], PDO::FETCH_COLUMN);
        } else if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT "filter_id" FROM "' . NEL_FILE_FILTERS_TABLE . '" WHERE "board_id" = ?');
            $result = $this->database->executePreparedFetchAll($prepared, [$board_id], PDO::FETCH_COLUMN);
        } else {
            $query = 'SELECT "filter_id" FROM "' . NEL_FILE_FILTERS_TABLE . '"';
            $result = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);
        }

        if (is_array($result)) {
            foreach ($result as $filter_id) {
                $file_filters[] = new FileFilter($this->database, (int) $filter_id);
            }
        }

        return $file_filters;
    }
}
