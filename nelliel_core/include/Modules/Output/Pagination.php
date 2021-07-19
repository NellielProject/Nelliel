<?php

declare(strict_types=1);

namespace Nelliel\Modules\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Pagination
{
    private $previous_text;
    private $previous_url_format;
    private $next_text;
    private $next_url_format;
    private $page_text;
    private $page_url_format;
    private $first_text;
    private $first_url_format;
    private $last_text;
    private $last_url_format;

    function __construct()
    {
        $this->previous_text = '<<';
        $this->previous_url_format = '';
        $this->next_text = '>>';
        $this->next_url_format = '';
        $this->page_text = '%d';
        $this->page_url_format = '';
        $this->first_text = '%d';
        $this->first_url_format = '';
        $this->last_text = '%d';
        $this->last_url_format = '';
    }

    public function setPrevious(string $text = null, string $url_format = null)
    {
        $this->previous_text = ($text) ?? $this->previous_text;
        $this->previous_url_format = ($url_format) ?? $this->previous_url_format;
    }

    public function setNext(string $text = null, string $url_format = null)
    {
        $this->next_text = ($text) ?? $this->next_text;
        $this->next_url_format = ($url_format) ?? $this->next_url_format;
    }

    public function setPage(string $text = null, string $url_format = null)
    {
        $this->page_text = ($text) ?? $this->page_text;
        $this->page_url_format = ($url_format) ?? $this->page_url_format;
    }

    public function setFirst(string $text = null, string $url_format = null)
    {
        $this->first_text = ($text) ?? $this->first_text;
        $this->first_url_format = ($url_format) ?? $this->first_url_format;
    }

    public function setLast(string $text = null, string $url_format = null)
    {
        $this->last_text = ($text) ?? $this->last_text;
        $this->last_url_format = ($url_format) ?? $this->last_url_format;
    }

    public function generateNumerical(int $start, int $end, int $current, bool $link_current = false)
    {
        $pagination = array();
        $previous = ($current - 1 >= $start) ? $current - 1 : $current;
        $pagination[] = $this->numericalEntry($start, $end, $current, $link_current, $this->previous_text, $previous);

        for ($i = $start; $i <= $end; $i ++)
        {
            $pagination[] = $this->numericalEntry($start, $end, $current, $link_current, sprintf($this->page_text, $i),
                    $i);
        }

        $next = ($current + 1 <= $end) ? $current + 1 : $current;
        $pagination[] = $this->numericalEntry($start, $end, $current, $link_current, $this->next_text, $next);
        return $pagination;
    }

    private function numericalEntry(int $start, int $end, int $current, bool $link_current, string $text, int $i)
    {
        $entry = array();
        $entry['text'] = $text;

        if ($i === $current && !$link_current)
        {
            $entry['url'] = '';
            $entry['linked'] = false;
        }
        else
        {
            if ($i === $start)
            {
                $entry['url'] = sprintf($this->first_url_format, $i);
            }
            else if ($i === $end)
            {
                $entry['url'] = sprintf($this->last_url_format, $i);
            }
            else
            {
                $entry['url'] = sprintf($this->page_url_format, $i);
            }

            $entry['linked'] = true;
        }

        return $entry;
    }
}