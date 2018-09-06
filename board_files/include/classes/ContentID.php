<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ContentID
{
    private $id_string;
    private $thread_id;
    private $post_id;
    private $order_id;
    private $is_thread;
    private $is_post;
    private $is_file;

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

    public function threadID($new_value = null)
    {
        if(!is_null(new_value))
        {
            $this->thread_id = $new_value;
        }

        return $this->thread_id;
    }

    public function postID($new_value = null)
    {
        if(!is_null(new_value))
        {
            $this->post_id = $new_value;
        }

        return $this->post_id;
    }

    public function orderID($new_value = null)
    {
        if(!is_null(new_value))
        {
            $this->order_id = $new_value;
        }

        return $this->order_id;
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
