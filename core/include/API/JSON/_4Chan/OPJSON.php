<?php

namespace Nelliel\API\JSON\_4Chan;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\JSON;
use Nelliel\Content\Thread;
use PDO;

class OPJSON extends JSON
{
    private $thread;

    function __construct(Thread $thread)
    {
        $this->thread = $thread;
    }

    protected function generate(): void
    {
        // TODO: implement (and related functions if needed)
        // unique_ips (how many unique posters in a thread
        // archived
        // archived_on (timestamp of when archived)
        $post = $this->thread->firstPost();
        $this->raw_data = array();

        if ($this->thread->data('sticky')) {
            $this->raw_data['sticky'] = 1;
        }

        if ($this->thread->data('locked')) {
            $this->raw_data['closed'] = 1;
        }

        $this->raw_data['replies'] = $this->thread->data('post_count') - 1;
        $prepared = $this->thread->domain()->database()->prepare(
            'SELECT COUNT(*) FROM "' . $this->thread->domain()->reference('uploads_table') .
            '" WHERE "parent_thread" = ? AND "category" = \'graphics\' AND "upload_order" = 1');
        $image_posts = $this->thread->domain()->database()->executePreparedFetch($prepared,
            [$this->thread->contentID()->threadID()], PDO::FETCH_COLUMN);
        $this->raw_data['images'] = intval($image_posts !== false ? $image_posts : 0);

        if ($this->thread->domain()->setting('limit_bump_count') &&
            ($this->thread->data('post_count') >= $this->thread->domain()->setting('max_posts'))) {
            $this->raw_data['bumplimit'] = 1;
        }

        if ($this->thread->domain()->setting('limit_thread_uploads') &&
            ($this->thread->data('total_uploads') >= $this->thread->domain()->setting('max_thread_uploads'))) {
            $this->raw_data['imagelimit'] = 1;
        }

        $this->raw_data['semantic_url'] = $this->thread->generateSlug($post);
        $this->raw_data = $this->raw_data + $post->getJSON()->getRawData();
        $this->json = json_encode($this->raw_data);
        $this->needs_update = false;
    }
}