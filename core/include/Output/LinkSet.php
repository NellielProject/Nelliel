<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class LinkSet
{
    private array $data = array();

    function __construct()
    {}

    public function addLink(string $link_id, array $data): void
    {
        $this->data[$link_id] = $data;
    }

    public function addData(string $link_id, string $key, $data): void
    {
        $this->data[$link_id][$key] = $data;
    }

    public function removeLink(string $link_id): void
    {
        unset($this->data[$link_id]);
    }

    public function removeData(string $link_id, string $key): void
    {
        unset($this->data[$link_id][$key]);
    }

    public function build(array $link_list): array
    {
        $link_set = array();

        foreach ($link_list as $link_id) {
            if (!isset($this->data[$link_id])) {
                continue;
            }

            $link_set[] = $this->data[$link_id];
        }

        return $link_set;
    }
}