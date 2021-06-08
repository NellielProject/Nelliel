<?php
declare(strict_types = 1);

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Cites;
use Nelliel\Content\ContentID;
use Nelliel\Content\ContentPost;
use Nelliel\Content\ContentThread;
use Nelliel\Domains\Domain;
use Nelliel\Render\Markdown\ImageboardMarkdown;
use PDO;

class OutputPost extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $thread_data = $parameters['thread_data'] ?? array();
        $gen_data = $parameters['gen_data'] ?? array();
        $post_id = $parameters['post_id'] ?? 0;
        $json_post = $parameters['json_instances']['post'];
        $post_data = $parameters['post_data'] ?? $this->getPostFromDatabase($post_id);
        $in_thread_number = $parameters['in_thread_number'] ?? 0;
        $json_post->storeData($json_post->prepareData($post_data), 'post');
        $response = $post_data['op'] != 1;
        $thread_content_id = new ContentID(ContentID::createIDString($post_data['parent_thread']));
        $post_content_id = new ContentID(
                ContentID::createIDString($post_data['parent_thread'], $post_data['post_number']));
        $post = new ContentPost($post_content_id, $this->domain);
        $thread = new ContentThread($thread_content_id, $this->domain);
        $thread->loadFromDatabase();
        $post->loadFromDatabase();

        if (NEL_USE_RENDER_CACHE)
        {
            if ($post_data['regen_cache'] || empty($post_data['cache']))
            {
                $post->storeCache();
                $post_data['render_cache'] = $post->getCache();
            }
            else
            {
                $post_data['render_cache'] = json_decode($post_data['cache'], true);
            }
        }

        $web_paths['thread_page'] = $thread->getURL();
        $web_paths['thread_src'] = $this->domain->reference('src_web_path') . $thread_content_id->threadID() . '/';
        $web_paths['thread_preview'] = $this->domain->reference('preview_web_path') . $thread_content_id->threadID() .
                '/';
        $this->render_data['post_corral_id'] = 'post-corral-' . $post_content_id->getIDString();
        $this->render_data['post_container_id'] = 'post-container-' . $post_content_id->getIDString();

        if ($response)
        {
            $this->render_data['op_reply'] = 'reply';
            $this->render_data['indents_marker'] = $this->domain->setting('indent_marker');
        }
        else
        {
            $this->render_data['op_reply'] = 'op';
            $this->render_data['indents_marker'] = '';
        }

        $this->render_data['post_anchor_id'] = 't' . $post_content_id->threadID() . 'p' . $post_content_id->postID();
        $this->render_data['headers'] = $this->postHeaders($response, $thread_data, $post_data, $thread_content_id,
                $post, $web_paths, $gen_data, $in_thread_number);

        if ($post_data['has_content'] == 1)
        {
            $query = 'SELECT * FROM "' . $this->domain->reference('content_table') .
                    '" WHERE "post_ref" = ? ORDER BY "content_order" ASC';
            $prepared = $this->database->prepare($query);
            $file_list = $this->database->executePreparedFetchAll($prepared, [$post_data['post_number']],
                    PDO::FETCH_ASSOC);
            $output_file_info = new OutputFile($this->domain, $this->write_mode);
            $output_embed_info = new OutputEmbed($this->domain, $this->write_mode);
            $content_row = array();
            $this->render_data['has_file'] = count($file_list) === 1;
            $this->render_data['multi_file'] = count($file_list) > 1;

            foreach ($file_list as $file)
            {
                $json_content = new \Nelliel\API\JSON\JSONContent($this->domain, $this->file_handler);
                $parameters['json_instances']['content'] = $json_content;

                if (nel_true_empty($file['embed_url']))
                {
                    $file_data = $output_file_info->render(
                            ['file_data' => $file, 'content_order' => $file['content_order'], 'post_data' => $post_data,
                                'web_paths' => $web_paths, 'json_instances' => $parameters['json_instances']], true);
                }
                else
                {
                    $file_data = $output_embed_info->render(
                            ['file_data' => $file, 'content_order' => $file['content_order'], 'post_data' => $post_data,
                                'web_paths' => $web_paths, 'json_instances' => $parameters['json_instances']], true);
                }

                $content_row[]['content_data'] = $file_data;

                if ($this->render_data['multi_file'])
                {
                    if (count($content_row) == $this->domain->setting('max_uploads_row'))
                    {
                        $this->render_data['content_rows'][]['row'] = $content_row;
                        $content_row = array();
                    }
                }
                else
                {
                    $this->render_data['content_data'] = $file_data;
                }
            }

            if ($this->render_data['multi_file'] && !empty($content_row))
            {
                $this->render_data['content_rows'][]['row'] = $content_row;
            }
        }

        $this->render_data['post_comments'] = $this->postComments($post_data, $post_content_id, $gen_data, $web_paths);
        $this->render_data['site_content_disclaimer'] = nel_site_domain()->setting('site_content_disclaimer');
        $this->render_data['board_content_disclaimer'] = $this->domain->setting('board_content_disclaimer');
        $output = $this->output('thread/post', $data_only, true, $this->render_data);
        return $output;
    }

    public function getPostFromDatabase($post_id)
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->domain->reference('posts_table') . '" WHERE "post_number" = ?');
        $post_data = $this->database->executePreparedFetch($prepared, [$post_id], PDO::FETCH_ASSOC);

        if (empty($post_data))
        {
            $post_data = array();
        }

        return $post_data;
    }

    private function postHeaders(bool $response, array $thread_data, array $post_data, ContentID $thread_content_id,
            ContentPost $post, array $web_paths, array $gen_data, int $in_thread_number)
    {
        $header_data = array();
        $modmode_headers = array();
        $thread_headers = array();
        $header_data['response'] = $response;
        $post_content_id = $post->contentID();

        if ($this->session->inModmode($this->domain) && !$this->write_mode)
        {
            if ($this->session->user()->checkPermission($this->domain, 'perm_view_unhashed_ip') &&
                    !empty($post_data['ip_address']))
            {
                $ip = @inet_ntop($post_data['ip_address']);
            }
            else
            {
                $ip = bin2hex($post_data['hashed_ip_address']);
            }

            $modmode_headers['ip_address'] = $ip;

            // TODO: Change display according to user perms
            if (!$response)
            {
                $locked = $thread_data['locked'] == 1;
                $modmode_headers['lock_text'] = ($locked) ? _gettext('Unlock') : _gettext('Lock');
                $modmode_headers['lock_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                        '&actions=lock&content-id=' . $thread_content_id->getIDString() . '&modmode=true&goback=true';
                $sticky = $thread_data['sticky'] == 1;
                $modmode_headers['sticky_text'] = ($sticky) ? _gettext('Unsticky') : _gettext('Sticky');
                $modmode_headers['sticky_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                        '&actions=sticky&content-id=' . $thread_content_id->getIDString() . '&modmode=true&goback=true';
                $permasage = $thread_data['permasage'] == 1;
                $modmode_headers['permasage_text'] = ($permasage) ? _gettext('Unsage') : _gettext('Sage');
                $modmode_headers['permasage_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                        '&actions=sage&content-id=' . $thread_content_id->getIDString() . '&modmode=true&goback=true';
                $cyclic = $thread_data['cyclic'] == 1;
                $modmode_headers['cyclic_text'] = ($cyclic) ? _gettext('Non-cyclic') : _gettext('Cyclic');
                $modmode_headers['cyclic_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                        '&actions=cyclic&content-id=' . $thread_content_id->getIDString() . '&modmode=true&goback=true';
            }

            $modmode_headers['ban_url'] = '?module=admin&section=bans&board-id=' . $this->domain->id() .
                    '&actions=new&ban-ip=' . $ip . '&modmode=true&goback=false';
            $modmode_headers['delete_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                    '&actions=delete&content-id=' . $post_content_id->getIDString() . '&modmode=true&goback=true';
            $modmode_headers['delete_by_ip_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
            '&actions=delete-by-ip&content-id=' . $post_content_id->getIDString() . '&modmode=true&goback=true';
            $modmode_headers['ban_delete_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                    '&actions=bandelete&content-id=' . $post_content_id->getIDString() . '&ban-ip=' . $ip .
                    '&modmode=true&goback=false';
            $header_data['modmode_headers'] = $modmode_headers;
        }

        $header_data['thread_page'] = sprintf($this->site_domain->setting('thread_filename_format'), $thread_content_id->threadID()) . NEL_PAGE_EXT;

        if (!$response)
        {
            $thread_headers['thread_content_id'] = $thread_content_id->getIDString();
            $thread_headers['post_content_id'] = $post_content_id->getIDString();
            $thread_headers['sticky'] = $thread_data['sticky'];
            $thread_headers['locked'] = $thread_data['locked'];

            if ($gen_data['index_rendering'])
            {
                $thread_headers['index_render'] = true;

                if (!$response && $gen_data['abbreviate'])
                {
                    $thread_headers['abbreviate'] = true;
                }
            }

            $thread_headers['reply_to_url'] = $web_paths['thread_page'];

            if ($this->session->inModmode($this->domain) && !$this->write_mode)
            {
                $thread_headers['render'] = '-render';
                $thread_headers['reply_to_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        'module=render&actions=view-thread&content-id=' . $thread_content_id->getIDString() . '&thread=' .
                        $thread_content_id->threadID() . '&board-id=' . $this->domain->id() . '&modmode=true';
            }

            $header_data['thread_headers'] = $thread_headers;
        }

        $post_headers['in_thread_number'] = $in_thread_number;
        $post_headers['post_content_id'] = $post_content_id->getIDString();

        if (!nel_true_empty($post_data['email']))
        {
            $post_headers['mailto']['mailto_url'] = 'mailto:' . $post_data['email'];
        }

        $post_headers['subject'] = $post_data['subject'];
        $post_headers['poster_name'] = $post_data['poster_name'];

        if ($this->domain->setting('display_poster_id'))
        {
            $raw_poster_id = hash('sha256',
                    NEL_POSTER_ID_PEPPER . @inet_ntop($post_data['ip_address']) . $this->domain->id() .
                    $thread_data['thread_id']);
            $poster_id = substr($raw_poster_id, 0, $this->domain->setting('poster_id_length'));
            $post_headers['id_color_code'] = '#' . substr($raw_poster_id, 0, 6);
            $post_headers['poster_id'] = $poster_id;
            $post_headers['show_poster_id'] = true;

            if ($this->domain->setting('poster_id_colors'))
            {
                $post_headers['id_colors'] = true;
            }
        }

        $tripcode = (!empty($post_data['tripcode'])) ? $this->domain->setting('tripcode_marker') . $post_data['tripcode'] : '';
        $secure_tripcode = (!empty($post_data['secure_tripcode'])) ? $this->domain->setting('tripcode_marker') .
                $this->domain->setting('tripcode_marker') . $post_data['secure_tripcode'] : '';
        $post_headers['tripline'] = $tripcode . $secure_tripcode;

        if (!nel_true_empty($post_data['capcode']))
        {
            $post_headers['capcode'] = ' ## ' . $post_data['capcode'];
        }

        $post_headers['post_time'] = date($this->domain->setting('date_format'), (int) $post_data['post_time']);
        $post_headers['post_number'] = $post_data['post_number'];
        $post_headers['post_number_url'] = $web_paths['thread_page'] . '#t' . $post_content_id->threadID() . 'p' .
                $post_content_id->postID();
        $post_headers['post_number_url_cite'] = $post_headers['post_number_url'] . 'cite';

        if ($this->domain->setting('display_post_backlinks'))
        {
            if (NEL_USE_RENDER_CACHE && isset($post_data['render_cache']['backlink_data']))
            {
                $post_headers['backlinks'] = $post_data['render_cache']['backlink_data'];
            }
            else
            {
                $post_headers['backlinks'] = $this->generateBacklinks($post);
            }
        }

        $header_data['post_headers'] = $post_headers;
        return $header_data;
    }

    private function postComments(array $post_data, ContentID $post_content_id, array $gen_data, array $web_paths)
    {
        $comment_data = array();
        $comment_data['post_contents_id'] = 'post-contents-' . $post_content_id->getIDString();
        $comment_data['mod_comment'] = $post_data['mod_comment'] ?? null;
        $comment_data['noreferrer_nofollow'] = $this->site_domain->setting('noreferrer_nofollow');
        $comment = $post_data['comment'];

        if (nel_true_empty($comment))
        {
            $comment_data['comment_lines'][]['line']['text'] = $this->domain->setting('no_comment_text');
        }
        else
        {
            if (NEL_USE_RENDER_CACHE && isset($post_data['render_cache']['comment_data']))
            {
                $markdown = new ImageboardMarkdown($this->domain, $post_content_id);
                $comment_data['comment_markdown'] = $markdown->parse($comment);
                //$parsed_comment = $post_data['render_cache']['comment_data'];
                $parsed_comment = array();
            }
            else
            {
                $markdown = new ImageboardMarkdown($this->domain, $post_content_id);
                $comment_data['comment_markdown'] = $markdown->parse($comment);
                //$parsed_comment = $this->parseComment($comment, $post_content_id);
                $parsed_comment = array();
            }

            $comment_data['comment_lines'] = $parsed_comment;
            $line_count = count($parsed_comment);

            if ($gen_data['index_rendering'])
            {
                if ($line_count > $this->domain->setting('comment_display_lines'))
                {
                    $comment_data['long_comment'] = true;
                    $comment_data['long_comment_url'] = $web_paths['thread_page'] . '#t' . $post_content_id->threadID() .
                            'p' . $post_content_id->postID();
                    $comment_data['comment_lines'] = array();
                    $i = 0;
                    $limit = $this->domain->setting('comment_display_lines');

                    for (; $i < $limit; $i ++)
                    {
                        $comment_data['comment_lines'][] = $parsed_comment[$i];
                    }
                }
            }
        }

        return $comment_data;
    }

    public function postSuccess(array $parameters, bool $data_only)
    {
        $messages[] = _gettext('Post success!');
        $link['url'] = $parameters['forward_url'] ?? '';
        $link['text'] = _gettext('Click here if you are not automatically redirected');
        $parameters['page_title'] = $this->domain->reference('board_uri') . ' - ' . $this->domain->reference('title');
        $output_interstitial = new OutputInterstitial($this->domain, $this->write_mode);
        return $output_interstitial->render($parameters, $data_only, $messages, [$link]);
    }

    public function contentDeleted(array $parameters, bool $data_only)
    {
        $messages[] = _gettext('The selected items have been deleted!');
        $link['url'] = $parameters['forward_url'] ?? '';
        $link['text'] = _gettext('Click here if you are not automatically redirected');
        $parameters['page_title'] = $this->domain->reference('board_uri') . ' - ' . $this->domain->reference('title');
        $output_interstitial = new OutputInterstitial($this->domain, $this->write_mode);
        return $output_interstitial->render($parameters, $data_only, $messages, [$link]);
    }

    public function generateBacklinks(ContentPost $post): array
    {
        $cites = new Cites($this->database);
        $cite_list = $cites->getForPost($post);
        $post_content_id = $post->contentID();
        $backlinks = array();

        foreach ($cite_list['sources'] as $cite)
        {
            $backlink_data = array();

            if ($cite['source_board'] == $this->domain->id())
            {
                $backlink_data['backlink_text'] = '>>' . $cite['source_post'];
            }
            else
            {
                $backlink_data['backlink_text'] = '>>>/' . $cite['source_board'] . '/' . $cite['source_post'];
            }

            $cite_data = $cites->getCiteData($backlink_data['backlink_text'], $this->domain, $post_content_id);
            $cite_url = '';

            if ($cite_data['exists'])
            {
                $cite_url = $cites->createPostLinkURL($cite_data, $this->domain);
                $cites->addCite($cite_data);

                if (!empty($cite_url))
                {
                    $backlink_data['backlink_url'] = $cite_url;
                    $backlinks[] = $backlink_data;
                }

                $cites->addCite($cite_data);
            }
        }

        return $backlinks;
    }

    public function parseComment(?string $comment_text, ContentID $post_content_id): array
    {
        $comment_data = array();

        if (nel_true_empty($comment_text))
        {
            return $comment_data;
        }

        $comment = $comment_text;

        if ($this->domain->setting('trim_comment_newlines_start'))
        {
            $comment = ltrim($comment, "\n\r");
        }

        if ($this->domain->setting('trim_comment_newlines_end'))
        {
            $comment = rtrim($comment, "\n\r");
        }

        if ($this->domain->setting('filter_combining_characters'))
        {
            $comment = $this->output_filter->filterUnicodeCombiningCharacters($comment);
        }

        $greentext_regex = '#^\s*>[^>]#';
        $url_protocols = $this->domain->setting('url_protocols');
        $url_split_regex = '#(' . $url_protocols . ')(:\/\/)#';
        $line_split_regex = '#((?:' . $url_protocols . '):\/\/[^\s]+)|(>>[\d]+)|(>>>\/.+?\/[\d]?+)|(\s)#';
        $cites = new \Nelliel\Cites($this->database);
        $create_url_links = $this->domain->setting('create_url_links');
        $comment_lines = $this->output_filter->newlinesToArray($comment);
        $line_count = count($comment_lines);
        $last_i = $line_count - 1;
        $i = 0;

        for (; $i < $line_count; $i ++)
        {
            $line = $comment_lines[$i];
            $line_break = $i !== $last_i;

            // Split the line on spaces or embedded post cites, preserving the delimiters
            // URLs and greentext match until a line split point or line break so they don't need to be in this part
            $segment_chunks = preg_split($line_split_regex, $line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $line_parts = array();
            $plaintext_chunk = ''; // Plain text chunks can be recombined, only special cases need be separate

            foreach ($segment_chunks as $chunk)
            {
                $entry = array();
                $cite_type = $cites->citeType($chunk);

                if (!empty($cite_type['type']))
                {
                    $cite_data = $cites->getCiteData($chunk, $this->domain, $post_content_id);
                    $cite_url = '';

                    if ($cite_data['exists'])
                    {
                        $cite_url = $cites->createPostLinkURL($cite_data, $this->domain);
                        $cites->addCite($cite_data);
                    }

                    $entry['cite'] = true;
                    $entry['valid'] = !empty($cite_url);
                    $entry['url'] = $cite_url;
                    $entry['text'] = $chunk;
                }
                else if ($create_url_links && preg_match($url_split_regex, $chunk) === 1)
                {
                    $entry['link'] = true;
                    $entry['url'] = $chunk;
                    $entry['text'] = $chunk;
                }
                else if (preg_match($greentext_regex, $chunk) === 1)
                {
                    $entry['styled'] = true;
                    $entry['text'] = $chunk;
                    $entry['class'] = 'greentext';
                }
                else
                {
                    $plaintext_chunk .= $chunk;
                    continue;
                }

                $line_parts[] = array('plain' => true, 'text' => $plaintext_chunk);
                $line_parts[] = $entry;
                $plaintext_chunk = '';
            }

            if (!nel_true_empty($plaintext_chunk))
            {
                $line_parts[] = array('plain' => true, 'text' => $plaintext_chunk);
            }

            $line_parts[] = array('line_break' => $line_break);
            $comment_data[]['line_parts'] = $line_parts;
        }

        return $comment_data;
    }
}