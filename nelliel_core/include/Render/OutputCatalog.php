<?php
declare(strict_types = 1);

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use PDO;

class OutputCatalog extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('catalog');
        $cites = new \Nelliel\Cites($this->database);
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['catalog_title'] = _gettext('Catalog of ') . '/' . $this->domain->id() . '/';
        $threads = $this->database->executeFetchAll(
                'SELECT * FROM "' . $this->domain->reference('threads_table') .
                '" WHERE "archive_status" = 0 ORDER BY "sticky" DESC, "last_bump_time" DESC, "last_bump_time_milli" DESC',
                PDO::FETCH_ASSOC);
        $thread_count = 1;

        foreach ($threads as $thread)
        {
            $thread_data = array();
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "parent_thread" = ? AND "op" = 1');
            $first_post = $this->database->executePreparedFetch($prepared, [$thread['thread_id']], PDO::FETCH_ASSOC);

            if (empty($first_post))
            {
                continue;
            }

            $first_post['render_cache'] = json_decode($first_post['cache'], true);
            $post_content_id = new ContentId('cid_' . $thread['thread_id'] . '_' . $first_post['post_number']);
            $thread_content_id = new ContentId(ContentID::createIDString($thread['thread_id']));
            $thread_instance = $thread_content_id->getInstanceFromID($this->domain);
            $thread_instance->loadFromDatabase();
            $thread_data['open_url'] = $thread_instance->getURL();

            if (!empty($first_post['subject']))
            {
                $thread_data['first_post_subject'] = $first_post['subject'];
            }

            if (!empty($first_post['comment']))
            {
                $output_post = new OutputPost($this->domain, false);

                if (NEL_USE_RENDER_CACHE && isset($first_post['render_cache']['comment_data']))
                {
                    $thread_data['comment_markdown'] = $first_post['render_cache']['comment_data'];
                }
                else
                {
                    $thread_data['comment_markdown'] = $output_post->parseComment($first_post['comment'],
                            $post_content_id);
                }
            }

            $thread_data['mod-comment'] = $first_post['mod_comment'];
            $thread_data['reply_count'] = $thread['post_count'] - 1;
            $thread_data['content_count'] = $thread['content_count'];
            $index_page = ceil($thread_count / $this->domain->setting('threads_per_page'));
            $thread_data['index_page'] = $index_page;
            $thread_data['is_sticky'] = $thread['sticky'] == 1;
            $thread_data['is_locked'] = $thread['locked'] == 1;
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . $this->domain->reference('content_table') .
                    '" WHERE "post_ref" = ? AND "content_order" = 1');
            $first_file = $this->database->executePreparedFetch($prepared, [$first_post['post_number']],
                    PDO::FETCH_ASSOC);

            if (!empty($first_file) && !empty($first_file['preview_name']))
            {
                $thread_data['has_preview'] = true;
                $width = $first_file['preview_width'];
                $height = $first_file['preview_height'];

                if ($width > $this->domain->setting('max_catalog_display_width') ||
                        $height > $this->domain->setting('max_catalog_display_height'))
                {
                    $ratio = min(($this->domain->setting('max_catalog_display_height') / $height),
                            ($this->domain->setting('max_catalog_display_width') / $width));
                    $width = intval($ratio * $width);
                    $height = intval($ratio * $height);
                }

                $thread_data['preview_width'] = $width;
                $thread_data['preview_height'] = $height;
                $thread_preview_web_path = $this->domain->reference('preview_web_path') . $first_post['post_number'] .
                        '/';
                $thread_data['preview_url'] = $thread_preview_web_path . $first_file['preview_name'] . '.' .
                        $first_file['preview_extension'];
            }
            else
            {
                $thread_data['has_preview'] = false;
                $thread_data['open_text'] = _gettext('Open thread');
            }

            $thread_count ++;
            $this->render_data['catalog_entries'][] = $thread_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);

        if ($this->write_mode)
        {
            $file = $this->domain->reference('board_path') . 'catalog.html';
            $this->file_handler->writeFile($file, $output);
        }
        else
        {
            echo $output;
        }

        return $output;
    }
}