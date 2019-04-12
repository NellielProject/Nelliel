<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\ContentID;
use Nelliel\Domain;
use PDO;

class OutputPost extends OutputCore
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
        $thread_data = $parameters['thread_data'] ?? array();
        $gen_data = $parameters['gen_data'] ?? array();
        $post_id = $parameters['post_id'] ?? 0;
        $dotdot = $parameters['dotdot'] ?? '';
        $json_post = $parameters['json_instances']['post'];
        $post_data = $parameters['post_data'] ?? $this->getPostFromDatabase($post_id);

        $this->startTimer();
        $json_post->storeData($json_post->prepareData($post_data), 'post');
        $response = $post_data['op'] != 1;
        $thread_content_id = new ContentID(ContentID::createIDString($post_data['parent_thread']));
        $post_content_id = new ContentID(
                ContentID::createIDString($post_data['parent_thread'], $post_data['post_number']));

        $web_paths['base_domain'] = BASE_DOMAIN . BASE_WEB_PATH;
        $web_paths['board'] = '//' . $web_paths['base_domain'] .
                rawurlencode($this->domain->reference('board_directory')) . '/';
        $web_paths['pages'] = $web_paths['board'] . rawurlencode($this->domain->reference('page_dir')) . '/';
        $web_paths['thread_page'] = $web_paths['pages'] . $thread_content_id->thread_id . '/thread-' .
                $thread_content_id->thread_id . '.html';
        $web_paths['thread_src'] = $web_paths['board'] . rawurlencode($this->domain->reference('src_dir')) . '/' .
                $thread_content_id->thread_id . '/';
        $web_paths['thread_preview'] = $web_paths['board'] . rawurlencode($this->domain->reference('preview_dir')) . '/' .
                $thread_content_id->thread_id . '/';
        $this->render_data['post_corral_id'] = 'post-id-' . $post_content_id->getIDString();
        $this->render_data['post_container_id'] = 'post-container-' . $post_content_id->getIDString();
        $this->render_data['header_id'] = 'header-' . $post_content_id->getIDString();
        $this->render_data['content_container_id'] = 'content-' . $post_content_id->getIDString();
        $this->render_data['comments_id'] = 'post-comments-' . $post_content_id->getIDString();

        if ($response)
        {
            $this->render_data['indents_marker'] = $this->domain->setting('indent_marker');
            $this->render_data['post_container_class'] = 'reply-post';
            $this->render_data['header_class'] = 'reply-post-header';
            $this->render_data['content_container_class'] = 'reply-content-container';
            $this->render_data['comments_class'] = 'reply-post-comments';
        }
        else
        {
            $this->render_data['indents_marker'] = '';
            $this->render_data['post_container_class'] = 'op-post';
            $this->render_data['header_class'] = 'op-post-header';
            $this->render_data['content_container_class'] = 'op-content-container';
            $this->render_data['comments_class'] = 'op-post-comments';
        }

        $this->render_data['post_anchor_id'] = 't' . $post_content_id->thread_id . 'p' . $post_content_id->post_id;
        $this->render_data['headers'] = $this->postHeaders($response, $thread_data, $post_data, $thread_content_id,
                $post_content_id, $web_paths, $gen_data);

        // TODO: Change to has_content
        if ($post_data['has_file'] == 1)
        {
            $query = 'SELECT * FROM "' . $this->domain->reference('content_table') .
                    '" WHERE "post_ref" = ? ORDER BY "content_order" ASC';
            $prepared = $this->database->prepare($query);
            $file_list = $this->database->executePreparedFetchAll($prepared, [$post_data['post_number']],
                    PDO::FETCH_ASSOC);
            $output_file_info = new \Nelliel\Output\OutputFile($this->domain);
            $content_count = count($file_list);
            $content_row = array();

            foreach ($file_list as $file)
            {
                if (count($content_row) >= $this->domain->setting('max_files_row'))
                {
                    $this->render_data['content_rows'][]['row'] = $content_row;
                    $content_row = array();
                }

                $json_content = new \Nelliel\API\JSON\JSONContent($this->domain, $this->file_handler);
                $parameters['json_instances']['content'] = $json_content;
                $file_data = $output_file_info->render(
                        ['file_data' => $file, 'content_order' => $file['content_order'], 'post_data' => $post_data,
                            'web_paths' => $web_paths, 'json_instances' => $parameters['json_instances'],
                            'dotdot' => $dotdot], true);
                $content_row[] = $file_data;
            }

            if (!empty($content_row))
            {
                $this->render_data['content_rows'][]['row'] = $content_row;
            }
        }

        $this->render_data['post_comments'] = $this->postComments($post_data, $post_content_id, $gen_data, $web_paths);
        $output = $this->output('thread/post', $data_only, true);
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
            ContentID $post_content_id, array $web_paths, array $gen_data)
    {
        $authorization = new \Nelliel\Auth\Authorization($this->database);
        $session = new \Nelliel\Session();
        $cites = new \Nelliel\Cites($this->domain->database());
        $header_data['response'] = $response;
        $header_data = array();
        $modmode_headers = array();
        $thread_headers = array();

        // TODO: Convert to passed $web_paths values
        $base_domain_path = BASE_DOMAIN . BASE_WEB_PATH;
        $board_web_path = '//' . $base_domain_path . rawurlencode($this->domain->reference('board_directory')) . '/';
        $pages_web_path = $board_web_path . rawurlencode($this->domain->reference('page_dir')) . '/';
        $thread_page_web_path = $pages_web_path . $thread_content_id->thread_id . '/thread-' .
                $thread_content_id->thread_id . '.html';
        $src_web_path = $board_web_path . rawurlencode($this->domain->reference('src_dir')) . '/';
        $thread_src_web_path = $src_web_path . $thread_content_id->thread_id . '/';
        $preview_web_path = $board_web_path . rawurlencode($this->domain->reference('preview_dir')) . '/';
        $thread_preview_web_path = $preview_web_path . $thread_content_id->thread_id . '/';

        if ($session->inModmode($this->domain))
        {

            $ip = @inet_ntop($post_data['ip_address']);
            $modmode_headers['ip_address'] = $ip;

            // TODO: Change display according to user perms
            if ($response)
            {
                $temp_content_id = $post_content_id;
            }
            else
            {
                $temp_content_id = $thread_content_id;
                $locked = ($thread_data['locked'] == 1);
                $modmode_headers['lock_text'] = ($locked) ? _gettext('Unlock Thread') : _gettext('Lock Thread');
                $lock_action = ($locked) ? 'unlock' : 'lock';
                $modmode_headers['lock_url'] = '?module=threads-admin&board_id=' . $this->domain->id() . '&action=' .
                        $lock_action . '&content-id=' . $thread_content_id->getIDString() . '&modmode=true';
                $temp_content_id = $thread_content_id;
                $sticky = ($thread_data['sticky'] == 1);
                $modmode_headers['sticky_text'] = ($locked) ? _gettext('Unsticky Thread') : _gettext('Sticky Thread');
                $sticky_action = ($sticky) ? 'unsticky' : 'sticky';
                $modmode_headers['sticky_url'] = '?module=threads-admin&board_id=' . $this->domain->id() . '&action=' .
                        $sticky_action . '&content-id=' . $thread_content_id->getIDString() . '&modmode=true';
            }

            $modmode_headers['ban_url'] = '?module=bans&board_id=' . $this->domain->id() .
                    '&action=new&ban_type=POST&content-id=' . $temp_content_id->getIDString() . '&ban_ip=' .
                    rawurlencode($ip) . '&modmode=true';
            $modmode_headers['delete_url'] = '?module=threads-admin&board_id=' . $this->domain->id() .
                    '&action=delete&content-id=' . $temp_content_id->getIDString() . '&modmode=true';
            $modmode_headerss['ban_delete_irl'] = '?module=threads-admin&board_id=' . $this->domain->id() .
                    '&action=ban-delete&content-id=' . $temp_content_id->getIDString() . '&ban_type=POST&ban_ip=' .
                    rawurlencode($ip) . '&modmode=true';
            $header_data['modmode_headers'] = $modmode_headers;
        }

        // If we're working with op post, generate the thread headers
        if (!$response)
        {
            $thread_headers['hide_thread_id'] = 'hide-thread-' . $thread_content_id->getIDString();
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

            if ($session->inModmode($this->domain))
            {
                $thread_headers['render'] = '-render';
                $thread_headers['reply_to_url'] = MAIN_SCRIPT . '?module=render&action=view-thread&content-id=' .
                        $thread_content_id->getIDString() . '&thread=' . $thread_content_id->thread_id . '&board_id=' .
                        $this->domain->id() . '&modmode=true';
            }

            $header_data['thread_headers'] = $thread_headers;
        }

        $post_headers['post_content_id'] = $post_content_id->getIDString();
        $post_headers['hide_post_id'] = 'hide-post-' . $post_content_id->getIDString();
        $post_headers['post_header_info_id'] = 'post-header-info-' . $post_content_id->getIDString();
        $post_headers['post_header_options_id'] = 'post-header-options-' . $post_content_id->getIDString();

        if ($response)
        {
            $post_headers['header_options_class'] = 'reply-post-header-options';
            $post_headers['post_select_class'] = 'reply-post-select';
            $post_headers['subject_class'] = 'reply-subject';
            $post_headers['poster_name_class'] = 'reply-poster-name';
            $post_headers['mailto_class'] = 'reply-mailto';
            $post_headers['tripline_class'] = 'reply-trip-line';
            $post_headers['post_time_class'] = 'reply-post-time';
            $post_headers['post_link_class'] = 'reply-post-link';
            $post_headers['post_number_class'] = 'reply-post-number-link';
        }
        else
        {
            $post_headers['header_options_class'] = 'op-post-header-options';
            $post_headers['post_select_class'] = 'op-post-select';
            $post_headers['subject_class'] = 'op-subject';
            $post_headers['poster_name_class'] = 'op-poster-name';
            $post_headers['mailto_class'] = 'op-mailto';
            $post_headers['tripline_class'] = 'op-trip-line';
            $post_headers['post_time_class'] = 'op-post-time';
            $post_headers['post_link_class'] = 'op-post-link';
            $post_headers['post_number_class'] = 'op-post-number-link';
        }

        if (!nel_true_empty($post_data['email']))
        {
            $post_headers['mailto']['mailto_url'] = 'mailto:' . $post_data['email'];
        }

        $post_headers['subject'] = $post_data['subject'];
        $post_headers['poster_name'] = $post_data['poster_name'];

        if ($this->domain->setting('display_poster_id'))
        {
            $raw_poster_id = hash('sha256',
                    @inet_ntop($post_data['ip_address']) . $thread_data['thread_id'] . TRIPCODE_PEPPER);
            $poster_id = substr($raw_poster_id, 0, $this->domain->setting('poster_id_length'));
            $post_headers['poster_id'] = 'ID: ' . $poster_id;
        }

        $tripcode = (!empty($post_data['tripcode'])) ? $this->domain->setting('tripkey_marker') . $post_data['tripcode'] : '';
        $secure_tripcode = (!empty($post_data['secure_tripcode'])) ? $this->domain->setting('tripkey_marker') .
                $this->domain->setting('tripkey_marker') . $post_data['secure_tripcode'] : '';
        $capcode_text = ($post_data['mod_post_id']) ? $authorization->getRole($post_data['mod_post_id'])->auth_data['capcode_text'] : '';
        $trip_line = $tripcode . $secure_tripcode;
        $post_headers['tripline'] = $trip_line;
        $post_headers['capcode'] = $capcode_text;
        $post_headers['post_time'] = date($this->domain->setting('date_format'), $post_data['post_time']);
        $post_headers['post_number'] = $post_data['post_number'];
        $post_headers['post_number_url'] = $thread_page_web_path . '#t' . $post_content_id->thread_id . 'p' .
                $post_content_id->post_id;

        if ($this->domain->setting('display_post_backlinks'))
        {
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . CITES_TABLE . '" WHERE "target_board" = ? AND "target_post" = ?');
            $cite_list = $this->database->executePreparedFetchAll($prepared,
                    [$this->domain->id(), $post_content_id->post_id], PDO::FETCH_ASSOC);

            foreach ($cite_list as $cite)
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

                $link_url = $cites->createPostLinkURL($this->domain, $post_content_id, $backlink_data['backlink_text']);

                if (!empty($link_url))
                {
                    $backlink_data['backlink_url'] = $link_url;
                    $post_headers['backlinks'][] = $backlink_data;
                }
            }
        }

        $header_data['post_headers'] = $post_headers;
        return $header_data;
    }

    private function postComments(array $post_data, ContentID $post_content_id, array $gen_data, array $web_paths)
    {
        $cites = new \Nelliel\Cites($this->domain->database());
        $comment_data = array();
        $post_type_class = $post_data['op'] == 1 ? 'op-' : 'reply-';
        $comment_data['post_contents_id'] = 'post-contents-' . $post_content_id->getIDString();
        $comment_data['post_contents_class'] = $post_type_class . 'post-contents';

        if (!nel_true_empty($post_data['mod_comment']))
        {
            $comment_data['mod_comment'] = $post_data['mod_comment'];
        }

        $this->output_filter->clearWhitespace($post_data['comment']);
        $comment_data['post_comment_class'] = $post_type_class . 'post-comment';

        if (nel_true_empty($post_data['comment']))
        {
            $comment_data['mod_comment'] = $this->domain->setting('no_comment_text');
        }
        else
        {
            $line_count = 0;

            foreach ($this->output_filter->newlinesToArray($post_data['comment']) as $line)
            {
                $final_line = '';

                if ($gen_data['index_rendering'] && $line_count == $this->domain->setting('comment_display_lines'))
                {
                    $comment_data['post_url'] = $web_paths['thread_page'] . '#t' . $post_content_id->thread_id . 'p' .
                            $post_content_id->post_id;
                    break;
                }

                $segments = preg_split('#(>>[0-9]+)|(>>>\/.+\/[0-9]+)#', $line, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

                foreach ($segments as $segment)
                {
                    $link_url = $cites->createPostLinkURL($this->domain, $post_content_id, $segment);

                    if (!empty($link_url))
                    {
                        if (preg_match('#^\s*>#', $segment) === 1)
                        {
                            $segment = '<a href="' . $link_url . '" class="post-link" data-command="show-linked-post">' . $segment . '</a>';
                        }
                    }

                    $final_line .= $segment;
                }

                $comment_data['comment_lines'][]['line'] = $final_line;
                ++ $line_count;
            }
        }

        return $comment_data;
    }
}