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
    protected $id_string = 'nci_0_0_0';
    protected $is_thread = false;
    protected $is_post = false;
    protected $is_file = false;

    function __construct($id_string)
    {
        $this->id_string = $id_string;
        $id_array = self::parseIDString($id_string);
        $this->thread_id = $id_array['thread'];
        $this->post_id = $id_array['post'];
        $this->order_id = $id_array['order'];
        $this->is_file = $id_array['order'] > 0;
        $this->is_post = !$this->is_file && $id_array['post'] > 0;
        $this->is_thread = !$this->is_file && !$this->is_post && $id_array['thread'] > 0;
    }

    public static function isContentID($string)
    {
        return preg_match('/nci_[0-9]+_[0-9]+_[0-9]+/', $string) === 1;
    }

    public static function createIDString($thread_id = 0, $post_id = 0, $order_id = 0)
    {
        return 'nci_' . $thread_id . '_' . $post_id . '_' . $order_id;
    }

    public static function parseIDString($id_string)
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
        return $this->id_string;
    }

    public function isThread()
    {
        return $this->is_thread;
    }

    public function isPost()
    {
        return $this->is_post;
    }

    public function isFile()
    {
        return $this->is_file;
    }
}
