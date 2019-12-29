<?php

namespace Nelliel;

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

    public function generateNumerical(int $start, int $end, int $current, bool $link_current = false)
    {
        $pagination = array();

        for ($i = $start; $i <= $end; $i ++)
        {
            $is_current = $i === $current;
            $entry = array();

            if ($i === $start)
            {
                $entry['text'] = sprintf($this->first_text, $i);
                $entry['url'] = sprintf($this->first_url_format, $i) . PAGE_EXT;
            }
            else
            {
                $entry['text'] = sprintf($this->page_text, $i);
                $entry['url'] = sprintf($this->page_url_format, $i) . PAGE_EXT;
            }

            $entry['linked'] = true;

            if ($is_current)
            {
                if (!$link_current)
                {
                    $entry['url'] = '';
                    $entry['linked'] = false;
                }
            }

            $pagination[] = $entry;

            if ($i === $start)
            {
                $entry['text'] = sprintf($this->previous_text, $this->previous_url_format);

                if ($is_current)
                {
                    $previous = ($current - 1 >= $start) ? $current - 1 : $start;
                    $entry['url'] = sprintf($this->page_url_format, $previous) . PAGE_EXT;
                }

                array_unshift($pagination, $entry);
            }

            if ($i === $end)
            {
                $entry['text'] = sprintf($this->next_text, $this->next_url_format);

                if ($is_current && $link_current)
                {
                    $next = ($i + 1 <= $end) ? $i + 1 : $end;
                    $entry['url'] = sprintf($this->page_url_format, $next) . PAGE_EXT;
                }

                $pagination[] = $entry;
            }
        }

        return $pagination;
    }
}