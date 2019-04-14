<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputCatalog extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $write = ($parameters['write']) ?? false;
        $cites = new \Nelliel\Cites($this->database);
        $dotdot = ($write) ? '../' : '';
        $this->startTimer();
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $this->render_data['header'] = $output_header->render(['header_type' => 'general', 'dotdot' => $dotdot],
                true);
        $this->render_data['catalog_title'] = _gettext('Catalog of ') . '/' . $this->domain->id() . '/';
        $base_domain_path = BASE_DOMAIN . BASE_WEB_PATH;
        $board_web_path = '//' . $base_domain_path . rawurlencode($this->domain->reference('board_directory')) . '/';
        $pages_web_path = $board_web_path . rawurlencode($this->domain->reference('page_dir')) . '/';
        $preview_web_path = $board_web_path . rawurlencode($this->domain->reference('preview_dir')) . '/';
        
        $threads = $this->database->executeFetchAll('SELECT * FROM "' . $this->domain->reference('threads_table') . '"',
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
            
            $post_content_id = new \Nelliel\ContentId('cid_' . $thread['thread_id'] . '_' . $first_post['post_number']);
            $thread_page_web_path = $pages_web_path . $thread['thread_id'] . '/thread-' . $thread['thread_id'] . '.html';
            $thread_data['open_url'] = $thread_page_web_path;
            
            if (!empty($first_post['subject']))
            {
                $thread_data['first_post_subject'] = $first_post['subject'];
            }
            
            $this->output_filter->clearWhitespace($first_post['comment']);
            
            foreach ($this->output_filter->newlinesToArray($first_post['comment']) as $line)
            {
                $segments = preg_split('#(>>[0-9]+)|(>>>\/.+\/[0-9]+)#', $line, null, PREG_SPLIT_DELIM_CAPTURE);
                $line_final = '';
                
                foreach ($segments as $segment)
                {
                    $link_url = $cites->createPostLinkURL($this->domain, $post_content_id, $segment);
                    
                    if (empty($link_url))
                    {
                        if (preg_match('#^\s*>#', $segment) === 1)
                        {
                            $line_final .= $this->output_filter->postQuote($catalog_entry_nodes['post-comment'],
                                    $segment, true);
                        }
                        else
                        {
                            $line_final .= $segment;
                        }
                    }
                    else
                    {
                        $link_element = '<a href="' . $link_url . '" class="post-link" data-command="show-linked-post">' .
                                $segment . '</a>';
                        $line_final .= $link_element;
                    }
                }
                
                //$line_final .= '<br>';
                $thread_data['comment_lines'][]['line'] = $line_final;
            }
            
            $thread_data['mod-comment'] = $first_post['mod_comment'];
            $thread_data['reply_count'] = $thread['post_count'] - 1;
            $thread_data['file_count'] = $thread['total_files'];
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
                $thread_preview_web_path = $preview_web_path . $thread['thread_id'] . '/' . $first_post['post_number'] .
                        '/';
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
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        
        if ($write)
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