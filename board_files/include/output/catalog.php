<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_catalog(\Nelliel\Domain $domain, $write)
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $translator = new \Nelliel\Language\Translator();
    $session = new \Nelliel\Session($authorization);
    $output_filter = new \Nelliel\OutputFilter();
    $cites = new \Nelliel\Cites($database);
    $file_handler = new \Nelliel\FileHandler();
    $dotdot = ($write) ? '../' : '';
    $domain->renderInstance()->startRenderTimer();
    $output_header = new \Nelliel\Output\OutputHeader($domain, $database);
    $output_header->render(['header_type' => 'board', 'dotdot' => $dotdot]);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'catalog.html');
    $catalog_container = $dom->getElementById('catalog-container');
    $catalog_container_nodes = $catalog_container->getElementsByAttributeName('data-parse-id', true);
    $dom->getElementById('catalog-title')->setContent(_gettext('Catalog of ') . '/' . $domain->id() . '/');

    $base_domain_path = BASE_DOMAIN . BASE_WEB_PATH;
    $board_web_path = '//' . $base_domain_path . rawurlencode($domain->reference('board_directory')) . '/';
    $pages_web_path = $board_web_path . rawurlencode($domain->reference('page_dir')) . '/';
    $preview_web_path = $board_web_path . rawurlencode($domain->reference('preview_dir')) . '/';

    $threads = $database->executeFetchAll('SELECT * FROM "' . $domain->reference('threads_table') . '"',
            PDO::FETCH_ASSOC);
    $thread_count = 1;

    foreach ($threads as $thread)
    {
        $catalog_entry = $dom->copyNode($catalog_container_nodes['catalog-entry'], $catalog_container, 'append');
        $catalog_entry_nodes = $catalog_entry->getElementsByAttributeName('data-parse-id', true);
        $prepared = $database->prepare(
                'SELECT * FROM "' . $domain->reference('posts_table') . '" WHERE "parent_thread" = ? AND "op" = 1');
        $first_post = $database->executePreparedFetch($prepared, [$thread['thread_id']], PDO::FETCH_ASSOC);

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

        $output_filter->clearWhitespace($first_post['comment']);

        foreach ($output_filter->newlinesToArray($first_post['comment']) as $line)
        {
            $segments = preg_split('#(>>[0-9]+)|(>>>\/.+\/[0-9]+)#', $line, null, PREG_SPLIT_DELIM_CAPTURE);

            foreach ($segments as $segment)
            {
                $post_link = $cites->createPostLinkElement($domain, $catalog_entry_nodes['post-comment'],
                        $post_content_id, $segment);

                if (!$post_link->hasAttribute('href'))
                {
                    if (preg_match('#^\s*>#', $segment) === 1)
                    {
                        $post_link = $output_filter->postQuote($catalog_entry_nodes['post-comment'], $segment);
                    }
                    else
                    {
                        $post_link = $catalog_entry_nodes['post-comment']->ownerDocument->createTextNode($segment);
                    }
                }

                $catalog_entry_nodes['post-comment']->appendChild($post_link);
            }

            $catalog_entry_nodes['post-comment']->appendChild($dom->createElement('br'));
        }

        $catalog_entry_nodes['post-comment']->setContent($first_post['comment']);
        $catalog_entry_nodes['mod-comment']->setContent($first_post['mod_comment']);
        $catalog_entry_nodes['reply-count']->setContent($thread['post_count'] - 1);
        $catalog_entry_nodes['file-count']->setContent($thread['total_files']);
        $index_page = ceil($thread_count / $domain->setting('threads_per_page'));
        $catalog_entry_nodes['index-page']->setContent($index_page);

        if($thread['sticky'] != 1)
        {
            $catalog_entry_nodes['sticky-icon']->remove();
        }

        if($thread['locked'] != 1)
        {
            $catalog_entry_nodes['locked-icon']->remove();
        }

        $prepared = $database->prepare(
                'SELECT * FROM "' . $domain->reference('content_table') .
                '" WHERE "post_ref" = ? AND "content_order" = 1');
        $first_file = $database->executePreparedFetch($prepared, [$first_post['post_number']], PDO::FETCH_ASSOC);

        if (!empty($first_file))
        {
            $width = $first_file['preview_width'];
            $height = $first_file['preview_height'];

            if ($width > $domain->setting('max_catalog_width') || $height > $domain->setting('max_catalog_height'))
            {
                $ratio = min(($domain->setting('max_catalog_height') / $height),
                        ($domain->setting('max_catalog_width') / $width));
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
    $translator->translateDom($dom, $domain->setting('language'));
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain, null, false);

    if($write)
    {
        $file_handler->writeFile(BASE_PATH . $domain->reference('board_directory') . '/catalog.html', $domain->renderInstance()->outputRenderSet());
    }
    else
    {
        echo $domain->renderInstance()->outputRenderSet();
    }
}
