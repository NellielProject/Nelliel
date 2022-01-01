<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\ContentID;
use Nelliel\Domains\DomainBoard;
use PDO;

class OutputCatalog extends Output
{

    function __construct(DomainBoard $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('catalog');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['catalog_title'] = _gettext('Catalog of ') . '/' . $this->domain->id() . '/';
        $threads = $this->domain->activeThreads(true);
        $thread_count = 1;

        foreach ($threads as $thread)
        {
            if (is_null($thread) || !$thread->exists())
            {
                continue;
            }

            $thread_data = array();
            $prepared = $this->database->prepare(
                    'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "parent_thread" = ? AND "op" = 1');
            $op_id = $this->database->executePreparedFetch($prepared, [$thread->contentID()->threadID()],
                    PDO::FETCH_COLUMN);

            if (empty($op_id))
            {
                continue;
            }

            $post_content_id = new ContentId('cid_' . $thread->contentID()->threadID() . '_' . $op_id . '_0');
            $post = $post_content_id->getInstanceFromID($this->domain);
            $thread_data['open_url'] = $thread->getURL($this->session->inModmode($this->domain));

            if ($this->session->inModmode($this->domain) && !$this->writeMode())
            {
                $thread_data['open_url'] .= '&modmode=true';
            }

            $thread_data['first_post_subject'] = $post->data('subject');

            if (!nel_true_empty($post->data('comment')))
            {
                $output_post = new OutputPost($this->domain, false);

                if (NEL_USE_RENDER_CACHE && isset($post->getCache()['comment_data']))
                {
                    $thread_data['comment_markdown'] = $post->getCache()['comment_data'];
                }
                else
                {
                    $thread_data['comment_markdown'] = $output_post->parseComment($post->data('comment'),
                            $post_content_id);
                }
            }

            $thread_data['mod-comment'] = $post->data('mod_comment');
            $thread_data['reply_count'] = $thread->data('post_count') - 1;
            $thread_data['total_uploads'] = $thread->data('total_uploads');
            $index_page = ceil($thread_count / $this->domain->setting('threads_per_page'));
            $thread_data['index_page'] = $index_page;
            $ui_image_set = $this->domain->frontEndData()->getImageSet($this->domain->setting('ui_image_set'));
            $thread_data['is_sticky'] = $thread->data('sticky');
            $thread_data['sticky'] = $ui_image_set->getWebPath('ui', 'sticky', true);
            $thread_data['is_locked'] = $thread->data('locked');
            $thread_data['locked'] = $ui_image_set->getWebPath('ui', 'locked', true);
            $thread_data['is_cyclic'] = $thread->data('cyclic');
            $thread_data['cyclic'] = $ui_image_set->getWebPath('ui', 'cyclic', true);
            $uploads = $post->getUploads();

            if (count($uploads) > 0)
            {
                $output_file_info = new OutputFile($this->domain, $this->write_mode);
                $output_embed_info = new OutputEmbed($this->domain, $this->write_mode);
                $thread_data['single_file'] = true;
                $thread_data['multi_file'] = false;
                $thread_data['single_multiple'] = 'single';
                $upload = $uploads[0];

                if (nel_true_empty($upload->data('embed_url')))
                {
                    $file_data = $output_file_info->render($upload, $post, ['catalog' => true], true);
                }
                else
                {
                    $file_data = $output_embed_info->render($upload, $post, ['catalog' => true], true);
                }

                $thread_data['preview'] = $file_data;
                $thread_data['has_preview'] = true;
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
            $file = $this->domain->reference('base_path') . 'catalog.html';
            $this->file_handler->writeFile($file, $output);
        }
        else
        {
            echo $output;
        }

        return $output;
    }
}