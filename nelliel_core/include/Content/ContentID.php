<?php

namespace Nelliel\Content;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;

class ContentID
{
    protected $thread_id = 0;
    protected $post_id = 0;
    protected $order_id = 0;
    protected $id_string = 'cid_0_0_0';

    function __construct(string $id_string = 'cid_0_0_0')
    {
        $this->id_string = $id_string;
        $id_array = self::parseIDString($id_string);
        $this->thread_id = intval($id_array['thread']);
        $this->post_id = intval($id_array['post']);
        $this->order_id = intval($id_array['order']);
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

    public function isContent() // TODO: Change to specific type (file, etc)
    {
        return $this->order_id > 0;
    }

    public function threadID()
    {
        return $this->thread_id;
    }

    public function postID()
    {
        return $this->post_id;
    }

    public function orderID()
    {
        return $this->order_id;
    }

    public function changeThreadID($thread_id)
    {
        $old_id = $this->thread_id;
        $this->thread_id = intval($thread_id);
        return $old_id;
    }

    public function changePostID($post_id)
    {
        $old_id = $this->post_id;
        $this->post_id = intval($post_id);
        return $old_id;
    }

    public function changeOrderID($order_id)
    {
        $old_id = $this->order_id;
        $this->order_id = intval($order_id);
        return $old_id;
    }

    public function getInstanceFromID(Domain $domain, bool $archived = false)
    {
        if($this->isThread())
        {
            return new ContentThread($this, $domain, $archived);
        }
        else if($this->isPost())
        {
            return new ContentPost($this, $domain, $archived);
        }
        else if($this->isContent())
        {
            return new ContentFile($this, $domain, $archived);
        }
    }
}
