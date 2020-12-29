<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

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

            $post_content_id = new \Nelliel\Content\ContentId(
                    'cid_' . $thread['thread_id'] . '_' . $first_post['post_number']);
            $thread_page_web_path = $this->domain->reference('page_web_path') . $thread['thread_id'] . '/thread-' .
                    $thread['thread_id'] . '.html';
            $thread_data['open_url'] = $thread_page_web_path;

            if (!empty($first_post['subject']))
            {
                $thread_data['first_post_subject'] = $first_post['subject'];
            }

            if (!empty($first_post['comment']))
            {
                $this->output_filter->clearWhitespace($first_post['comment']);

                foreach ($this->output_filter->newlinesToArray($first_post['comment']) as $line)
                {
                    $line_parts = array();
                    $segments = preg_split('#(>>[0-9]+)|(>>>\/.+\/[0-9]+)#', $line, null, PREG_SPLIT_DELIM_CAPTURE);
                    $line_final = '';

                    foreach ($segments as $segment)
                    {
                        $link_url = $cites->createPostLinkURL($this->domain, $post_content_id, $segment);

                        if (!empty($link_url))
                        {
                            if (preg_match('#^\s*>#', $segment) === 1)
                            {
                                $link = array();
                                $link['link_url'] = $link_url;
                                $link['link_text'] = $segment;
                                $line_parts[]['link'] = $link;
                            }
                        }
                        else
                        {
                            $line_parts[]['text'] = $segment;
                        }
                    }

                    $thread_data['comment_lines'][]['line'] = $line_parts;
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

                if ($width > $this->domain->setting('max_catalog_width') ||
                        $height > $this->domain->setting('max_catalog_height'))
                {
                    $ratio = min(($this->domain->setting('max_catalog_height') / $height),
                            ($this->domain->setting('max_catalog_width') / $width));
                    $width = intval($ratio * $width);
                    $height = intval($ratio * $height);
                }

                $thread_data['preview_width'] = $width;
                $thread_data['preview_height'] = $height;
                $thread_preview_web_path = $this->domain->reference('preview_web_path') . $thread['thread_id'] . '/' .
                        $first_post['post_number'] . '/';
                $thread_data['preview_url'] = $thread_preview_web_path . $first_file['preview_name'] . '.' .
                        $first_file['preview_extension'];
            }
            else
            {
                $thread_data['has_preview'] = false;
                $thread_data['open_text'] = _gettext('Open thread');
            }

            ++ $thread_count;
            $this->render_data['catalog_entries'][] = $thread_data;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('catalog', $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);

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