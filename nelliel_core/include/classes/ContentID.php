<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ContentID
{
    public $thread_id = 0;
    public $post_id = 0;
    public $order_id = 0;
    protected $id_string = 'cid_0_0_0';

    function __construct(string $id_string = 'cid_0_0_0')
    {
        $this->id_string = $id_string;
        $id_array = self::parseIDString($id_string);
        $this->thread_id = $id_array['thread'];
        $this->post_id = $id_array['post'];
        $this->order_id = $id_array['order'];
    }

    public static function isContentID(string $string)
    {
        return preg_match('/cid_[0-9]+_[0-9]+_[0-9]+/', $string) === 1;
    }

    public static function createIDString($thread_id = 0, $post_id = 0, $order_id = 0)
    {
        return 'cid_' . $thread_id . '_' . $post_id . '_' . $order_id;
    }

    public static function parseIDString(string $id_string)
    {
        $id_split = explode('_', $id_string);
        $id_array = array();
        $id_array['thread'] = (isset($id_split[1]) && is_numeric($id_split[1])) ? intval($id_split[1]) : 0;
        $id_array['post'] = (isset($id_split[2]) && is_numeric($id_split[2])) ? intval($id_split[2]) : 0;
        $id_array['order'] = (isset($id_split[3]) && is_numeric($id_split[3])) ? intval($id_split[3]) : 0;
        return $id_array;
    }

    public function getIDString()
    {
        return $this->createIDString($this->thread_id, $this->post_id, $this->order_id);
    }

    public function isThread()
    {
        return !$this->isPost() && !$this->isContent() && $this->thread_id > 0;
    }

    public function isPost()
    {
        return !$this->isContent() && $this->post_id > 0;
    }

    public function isContent()
    {
        return $this->order_id > 0;
    }
}
