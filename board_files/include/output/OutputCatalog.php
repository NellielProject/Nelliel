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
    private $sdatabase;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $this->prepare('catalog.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $write = ($parameters['write']) ?? false;
        $cites = new \Nelliel\Cites($this->database);
        $dotdot = ($write) ? '../' : '';
        $output_header->render(['header_type' => 'board', 'dotdot' => $dotdot]);
        $catalog_container = $this->dom->getElementById('catalog-container');
        $catalog_container_nodes = $catalog_container->getElementsByAttributeName('data-parse-id', true);
        $this->dom->getElementById('catalog-title')->setContent(_gettext('Catalog of ') . '/' . $this->domain->id() . '/');

        $base_domain_path = BASE_DOMAIN . BASE_WEB_PATH;
        $board_web_path = '//' . $base_domain_path . rawurlencode($this->domain->reference('board_directory')) . '/';
        $pages_web_path = $board_web_path . rawurlencode($this->domain->reference('page_dir')) . '/';
        $preview_web_path = $board_web_path . rawurlencode($this->domain->reference('preview_dir')) . '/';

        $threads = $this->database->executeFetchAll('SELECT * FROM "' . $this->domain->reference('threads_table') . '"',
                PDO::FETCH_ASSOC);
        $thread_count = 1;

        foreach ($threads as $thread)
        {
            $catalog_entry = $this->dom->copyNode($catalog_container_nodes['catalog-entry'], $catalog_container, 'append');
            $catalog_entry_nodes = $catalog_entry->getElementsByAttributeName('data-parse-id', true);
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . $this->domain->reference('posts_table') . '" WHERE "parent_thread" = ? AND "op" = 1');
            $first_post = $this->database->executePreparedFetch($prepared, [$thread['thread_id']], PDO::FETCH_ASSOC);

            if(empty($first_post))
            {
                continue;
            }

            $post_content_id = new \Nelliel\ContentId('cid_' . $thread['thread_id'] . '_' . $first_post['post_number']);
            $thread_page_web_path = $pages_web_path . $thread['thread_id'] . '/thread-' . $thread['thread_id'] . '.html';
            $catalog_entry_nodes['open-link']->extSetAttribute('href', $thread_page_web_path);

            if (!empty($first_post['subject']))
            {
                $catalog_entry_nodes['subject']->setContent($first_post['subject']);
            }
            else
            {
                $catalog_entry_nodes['subject']->remove();
            }

            $this->output_filter->clearWhitespace($first_post['comment']);

            foreach ($this->output_filter->newlinesToArray($first_post['comment']) as $line)
            {
                $segments = preg_split('#(>>[0-9]+)|(>>>\/.+\/[0-9]+)#', $line, null, PREG_SPLIT_DELIM_CAPTURE);

                foreach ($segments as $segment)
                {
                    $post_link = $cites->createPostLinkElement($this->domain, $catalog_entry_nodes['post-comment'],
                            $post_content_id, $segment);

                    if (!$post_link->hasAttribute('href'))
                    {
                        if (preg_match('#^\s*>#', $segment) === 1)
                        {
                            $post_link = $this->output_filter->postQuote($catalog_entry_nodes['post-comment'], $segment);
                        }
                        else
                        {
                            $post_link = $catalog_entry_nodes['post-comment']->ownerDocument->createTextNode($segment);
                        }
                    }

                    $catalog_entry_nodes['post-comment']->appendChild($post_link);
                }

                $catalog_entry_nodes['post-comment']->appendChild($this->dom->createElement('br'));
            }

            $catalog_entry_nodes['post-comment']->setContent($first_post['comment']);
            $catalog_entry_nodes['mod-comment']->setContent($first_post['mod_comment']);
            $catalog_entry_nodes['reply-count']->setContent($thread['post_count'] - 1);
            $catalog_entry_nodes['file-count']->setContent($thread['total_files']);
            $index_page = ceil($thread_count / $this->domain->setting('threads_per_page'));
            $catalog_entry_nodes['index-page']->setContent($index_page);

            if($thread['sticky'] != 1)
            {
                $catalog_entry_nodes['sticky-icon']->remove();
            }

            if($thread['locked'] != 1)
            {
                $catalog_entry_nodes['locked-icon']->remove();
            }

            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . $this->domain->reference('content_table') .
                    '" WHERE "post_ref" = ? AND "content_order" = 1');
            $first_file = $this->database->executePreparedFetch($prepared, [$first_post['post_number']], PDO::FETCH_ASSOC);

            if (!empty($first_file))
            {
                $width = $first_file['preview_width'];
                $height = $first_file['preview_height'];

                if ($width > $this->domain->setting('max_catalog_width') || $height > $this->domain->setting('max_catalog_height'))
                {
                    $ratio = min(($this->domain->setting('max_catalog_height') / $height),
                            ($this->domain->setting('max_catalog_width') / $width));
                    $width = intval($ratio * $width);
                    $height = intval($ratio * $height);
                }

                $catalog_entry_nodes['file-preview']->extSetAttribute('width', $width);
                $catalog_entry_nodes['file-preview']->extSetAttribute('height', $height);
                $thread_preview_web_path = $preview_web_path . $thread['thread_id'] . '/' . $first_post['post_number'] . '/';
                $catalog_entry_nodes['file-preview']->extSetAttribute('src',
                        $thread_preview_web_path . $first_file['preview_name'] . '.' . $first_file['preview_extension']);
            }
            else
            {
                $catalog_entry_nodes['file-preview']->remove();
                $catalog_entry_nodes['open-link']->setContent(_gettext('Open thread'));
            }

            ++$thread_count;
        }

        $catalog_container_nodes['catalog-entry']->remove();
        $this->domain->translator()->translateDom($this->dom, $this->domain->setting('language'));
        $this->domain->renderInstance()->appendHTMLFromDOM($this->sdom);
        nel_render_general_footer($this->domain, null, false);

        if($write)
        {
            $this->file_handler->writeFile(BASE_PATH . $this->domain->reference('board_directory') . '/catalog.html', $this->domain->renderInstance()->outputRenderSet());
        }
        else
        {
            echo $this->domain->renderInstance()->outputRenderSet();
        }
    }
}