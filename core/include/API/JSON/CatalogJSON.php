<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\DomainBoard;

class CatalogJSON extends JSON
{
    private $board;
    private $page;

    function __construct(DomainBoard $board, int $page)
    {
        $this->board = $board;
        $this->page = $page;
    }

    protected function generate(): void
    {
        $raw_data = array();
        $threads = $this->board->activeThreads(true);
        $thread_list = array();
        // This allows for possible pagination in the future
        // Also 4Chan API compatible
        $thread_list['page'] = 1;

        foreach ($threads as $thread) {
            $thread_data = array();
            $thread_data = $thread->getJSON()->getRawData();
            unset($thread_data['posts']);
            $thread_data['op'] = $thread->firstPost()->getJSON()->getRawData();
            $thread_list['threads'][] = $thread_data;
        }

        $raw_data[] = $thread_list;
        $raw_data = nel_plugins()->processHook('nel-in-after-catalog-json', [$this->board], $raw_data);
        $this->raw_data = $raw_data;
        $this->needs_update = false;
    }
}