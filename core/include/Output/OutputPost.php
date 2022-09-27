<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Cites;
use Nelliel\Content\Post;
use Nelliel\Content\Thread;
use Nelliel\Domains\Domain;
use Nelliel\FrontEnd\Capcode;

class OutputPost extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(Post $post, array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $thread = $post->getParent();
        $gen_data = $parameters['gen_data'] ?? array();
        $in_thread_number = $parameters['in_thread_number'] ?? 0;
        $response = !$post->data('op');

        if (NEL_USE_RENDER_CACHE) {
            if ($post->data('regen_cache')) {
                $post->storeCache();
            }
        }

        $this->render_data['post_corral_id'] = 'post-corral-' . $post->contentID()->getIDString();
        $this->render_data['post_container_id'] = 'post-container-' . $post->contentID()->getIDString();

        if ($response) {
            $this->render_data['op_reply'] = 'reply';
            $this->render_data['indents_marker'] = $this->domain->setting('indent_marker');
        } else {
            $this->render_data['op_reply'] = 'op';
            $this->render_data['indents_marker'] = '';
        }

        if ($this->domain->setting('post_backlinks_header') || $this->domain->setting('post_backlinks_footer')) {
            $this->render_data['post_backlinks_header'] = $this->domain->setting('post_backlinks_header');
            $this->render_data['post_backlinks_footer'] = $this->domain->setting('post_backlinks_footer');
            $this->render_data['post_backlinks_label'] = $this->domain->setting('post_backlinks_label');

            // TODO: Do cache check/fetch better
            if (NEL_USE_RENDER_CACHE && isset($post->getCache()['backlink_data'])) {
                $this->render_data['backlinks'] = $post->getCache()['backlink_data'];
            } else {
                $this->render_data['backlinks'] = $this->generateBacklinks($post);
            }

            $this->render_data['has_backlinks'] = count($this->render_data['backlinks']) > 0;
        }

        $this->render_data['post_anchor_id'] = 't' . $post->contentID()->threadID() . 'p' . $post->contentID()->postID();
        $this->render_data['headers1'] = $this->postHeaders($response, $thread, $post, $gen_data, $in_thread_number);

        if ($post->data('total_uploads') > 0) {
            $uploads = $post->getUploads();
            $output_file_info = new OutputFile($this->domain, $this->write_mode);
            $output_embed_info = new OutputEmbed($this->domain, $this->write_mode);
            $upload_row = array();
            $this->render_data['single_file'] = count($uploads) === 1;
            $this->render_data['multi_file'] = count($uploads) > 1;
            $this->render_data['single_multiple'] = (count($uploads) > 1) ? 'multiple' : 'single';

            foreach ($uploads as $upload) {
                if ($upload->data('deleted') && !$this->domain->setting('display_deleted_placeholder')) {
                    continue;
                }

                $file_data = array();

                if (nel_true_empty($upload->data('embed_url'))) {
                    $file_data = $output_file_info->render($upload, $post, [], true);
                } else {
                    $file_data = $output_embed_info->render($upload, $post, [], true);
                }

                $upload_row[] = $file_data;

                if (count($upload_row) == $this->domain->setting('max_uploads_row')) {
                    $this->render_data['upload_rows'][]['row'] = $upload_row;
                    $upload_row = array();
                }
            }

            if (!empty($upload_row)) {
                $this->render_data['upload_rows'][]['row'] = $upload_row;
            }
        }

        $this->render_data['post_comments'] = $this->postComments($post, $gen_data, $thread);

        if (!nel_true_empty($this->site_domain->setting('site_content_disclaimer'))) {
            foreach ($this->output_filter->newlinesToArray($this->site_domain->setting('site_content_disclaimer')) as $line) {
                $this->render_data['site_content_disclaimer_lines'][]['text'] = htmlspecialchars($line);
            }
        }

        if (!nel_true_empty($this->domain->setting('board_content_disclaimer'))) {
            foreach ($this->output_filter->newlinesToArray($this->domain->setting('board_content_disclaimer')) as $line) {
                $this->render_data['board_content_disclaimer_lines'][]['text'] = htmlspecialchars($line);
            }
        }

        $output = $this->output('thread/post', $data_only, true, $this->render_data);
        return $output;
    }

    private function postHeaders(bool $response, Thread $thread, Post $post, array $gen_data, int $in_thread_number)
    {
        $ui_image_set = $this->domain->frontEndData()->getImageSet($this->domain->setting('ui_image_set'));
        $header_data = array();
        $thread_headers = array();
        $post_headers = array();
        $this->render_data['headers']['response'] = $response;
        $post_content_id = $post->contentID();
        $this->render_data['show_poster_name'] = $this->domain->setting('show_poster_name');
        $this->render_data['show_tripcodes'] = $this->domain->setting('show_tripcodes');
        $this->render_data['show_capcode'] = $this->domain->setting('show_capcode');
        $this->render_data['show_post_subject'] = $this->domain->setting('show_post_subject');

        if ($this->session->inModmode($this->domain) && !$this->write_mode) {
            if ($this->session->user()->checkPermission($this->domain, 'perm_view_unhashed_ip') &&
                !empty($post->data('ip_address'))) {
                $ip = $post->data('ip_address');
            } else {
                $ip = $post->data('hashed_ip_address');
            }

            $this->render_data['mod_ip_address'] = $ip;
            $this->render_data['in_modmode'] = true;

            if (!$response) {
                $lock_button = $thread->data('locked') ? 'mod_links_unlock' : 'mod_links_lock';
                $lock_action = $thread->data('locked') ? 'unlock' : 'lock';
                $this->render_data['mod_lock_option'] = $this->render_data[$lock_button];
                $this->render_data['mod_lock_url'] = nel_build_router_url(
                    [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), $lock_action]);

                $sticky_button = $thread->data('sticky') ? 'mod_links_unsticky' : 'mod_links_sticky';
                $sticky_action = $thread->data('sticky') ? 'unsticky' : 'sticky';
                $this->render_data['mod_sticky_option'] = $this->render_data[$sticky_button];
                $this->render_data['mod_sticky_url'] = nel_build_router_url(
                    [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), $sticky_action]);

                $sage_button = $thread->data('permasage') ? 'mod_links_unpermasage' : 'mod_links_permasage';
                $sage_action = $thread->data('permasage') ? 'unsage' : 'sage';
                $this->render_data['mod_sage_option'] = $this->render_data[$sage_button];
                $this->render_data['mod_sage_url'] = nel_build_router_url(
                    [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), $sage_action]);

                $cyclic_button = $thread->data('cyclic') ? 'mod_links_non_cyclic' : 'mod_links_cyclic';
                $cyclic_action = $thread->data('cyclic') ? 'non-cyclic' : 'cyclic';
                $this->render_data['mod_cyclic_option'] = $this->render_data[$cyclic_button];
                $this->render_data['mod_cyclic_url'] = nel_build_router_url(
                    [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), $cyclic_action]);
            }

            $this->render_data['mod_ban_url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $post_content_id->getIDString(), 'ban']);
            $this->render_data['mod_delete_url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $post_content_id->getIDString(), 'delete']);
            $this->render_data['mod_delete_by_ip_url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $post_content_id->getIDString(), 'delete-by-ip']);
            $this->render_data['mod_global_delete_by_ip_url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $post_content_id->getIDString(), 'global-delete-by-ip']);
            $this->render_data['mod_ban_delete_url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $post_content_id->getIDString(), 'ban-delete']);
            $this->render_data['mod_edit_url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $post_content_id->getIDString(), 'edit']);
        }

        $this->render_data['headers']['thread_url'] = $thread->getURL(!$this->write_mode);
        $thread_headers['thread_content_id'] = $thread->contentID()->getIDString();
        $thread_headers['post_content_id'] = $post_content_id->getIDString();
        $post_headers['thread_content_id'] = $thread->contentID()->getIDString();
        $post_headers['post_content_id'] = $post_content_id->getIDString();
        $post_headers['is_op'] = $post->data('op');

        if (!$response) {
            $thread_headers['is_sticky'] = $thread->data('sticky');
            $thread_headers['status_sticky'] = $ui_image_set->getWebPath('ui', 'status_sticky', true);
            $thread_headers['is_locked'] = $thread->data('locked');
            $thread_headers['status_locked'] = $ui_image_set->getWebPath('ui', 'status_locked', true);
            $thread_headers['is_cyclic'] = $thread->data('cyclic');
            $thread_headers['status_cyclic'] = $ui_image_set->getWebPath('ui', 'status_cyclic', true);

            if ($gen_data['index_rendering']) {
                $thread_headers['index_render'] = true;

                if (!$response && $gen_data['abbreviate']) {
                    $thread_headers['abbreviate'] = true;
                }
            }

            if (!$this->write_mode) {
                $thread_headers['reply_to_url'] = $thread->getURL(true);
                $thread_headers['output'] = '-render';

                if ($this->session->inModmode($this->domain)) {
                    $thread_headers['reply_to_url'] .= '?modmode';
                }
            } else {
                $thread_headers['reply_to_url'] = $thread->getURL(false);
            }

            $first_posts_increments = json_decode($this->domain->setting('first_posts_increments'));
            $first_posts_format = $thread->pageBasename() . $this->site_domain->setting('first_posts_filename_format');

            if (is_array($first_posts_increments) &&
                $thread->data('post_count') > $this->domain->setting('first_posts_threshold')) {
                foreach ($first_posts_increments as $increment) {
                    if ($thread->data('post_count') >= $increment) {
                        $options = array();
                        $options['first_posts_url'] = $this->domain->reference('page_web_path') .
                            $thread->contentID()->threadID() . '/' . sprintf($first_posts_format, $increment) .
                            NEL_PAGE_EXT;
                        $options['first_posts_label'] = sprintf(_gettext('First %d Posts'), $increment);
                        $thread_headers['first_posts'][] = $options;
                    }
                }
            }

            $last_posts_increments = json_decode($this->domain->setting('last_posts_increments'));
            $last_posts_format = $thread->pageBasename() . $this->site_domain->setting('last_posts_filename_format');

            if (is_array($last_posts_increments) &&
                $thread->data('post_count') > $this->domain->setting('last_posts_threshold')) {
                foreach ($last_posts_increments as $increment) {
                    if ($thread->data('post_count') >= $increment) {
                        $options = array();
                        $options['last_posts_url'] = $this->domain->reference('page_web_path') .
                            $thread->contentID()->threadID() . '/' . sprintf($last_posts_format, $increment) .
                            NEL_PAGE_EXT;
                        $options['last_posts_label'] = sprintf(_gettext('Last %d Posts'), $increment);
                        $thread_headers['last_posts'][] = $options;
                    }
                }
            }

            $this->render_data['headers']['thread_headers'] = $thread_headers;
        }

        $post_headers['in_thread_number'] = $in_thread_number;

        if (!nel_true_empty($post->data('email'))) {
            $post_headers['mailto']['mailto_url'] = 'mailto:' . $post->data('email');
        }

        $post_headers['subject'] = $post->data('subject');
        $post_headers['name'] = $post->data('name');

        if ($this->domain->setting('show_poster_id')) {
            $raw_poster_id = hash_hmac('sha256', $post->data('hashed_ip_address'),
                NEL_POSTER_ID_PEPPER . $this->domain->id() . $thread->contentID()->threadID());
            $poster_id = utf8_substr($raw_poster_id, 0, $this->domain->setting('poster_id_length'));
            $post_headers['id_color_code'] = '#' . utf8_substr($raw_poster_id, 0, 6);
            $post_headers['poster_id'] = $poster_id;
            $post_headers['show_poster_id'] = true;

            if ($this->domain->setting('poster_id_colors')) {
                $post_headers['id_colors'] = true;
            }
        }

        $tripcode = (!empty($post->data('tripcode'))) ? $this->domain->setting('tripcode_marker') .
            $post->data('tripcode') : '';
        $secure_tripcode = (!empty($post->data('secure_tripcode'))) ? $this->domain->setting('tripcode_marker') .
            $this->domain->setting('tripcode_marker') . $post->data('secure_tripcode') : '';
        $post_headers['tripline'] = $tripcode . $secure_tripcode;

        if (!nel_true_empty($post->data('capcode'))) {
            $capcode = new Capcode($this->database, $this->domain->frontEndData(), $post->data('capcode'));
            $capcode->load();

            // Most likely no matching capcode so assume it was custom
            if (nel_true_empty($capcode->data('output'))) {
                $capcode = new Capcode($this->database, $this->domain->frontEndData(), '');
                $capcode->load();
            }

            if ($capcode->data('enabled')) {
                $post_headers['capcode_output'] = sprintf($capcode->data('output'), $post->data('capcode'));
            }
        }

        $post_headers['post_time'] = date($this->domain->setting('post_date_format'), intval($post->data('post_time')));
        $post_headers['post_number'] = $post->contentID()->postID();
        $post_headers['post_number_url'] = $post->getURL($this->session->inModmode($this->domain) && !$this->write_mode);
        $post_headers['post_number_url_cite'] = $post_headers['post_number_url'] . 'cite';
        $this->render_data['headers']['post_headers'] = $post_headers;
        return $header_data;
    }

    private function postComments(Post $post, array $gen_data, Thread $thread)
    {
        $comment_data = array();
        $comment_data['post_contents_id'] = 'post-contents-' . $post->contentID()->getIDString();
        $comment_data['show_mod_comments'] = $this->domain->setting('show_mod_comments');
        $comment_data['mod_comments'] = $post->data('mod_comment') ?? null;
        $comment_data['show_user_comments'] = $this->domain->setting('show_user_comments');
        $comment_data['nofollow_external_links'] = $this->site_domain->setting('nofollow_external_links');
        $comment = $post->data('comment');

        if (nel_true_empty($comment)) {
            $comment_data['comment_markdown'] = $this->domain->setting('no_comment_text');
            return $comment_data;
        }

        // TODO: Do cache check/fetch better
        if (NEL_USE_RENDER_CACHE && isset($post->getCache()['comment_data'])) {
            $comment_markdown = $post->getCache()['comment_data'];
        } else {
            $comment_markdown = $this->parseComment($comment, $post);
        }

        if ($gen_data['index_rendering']) {
            $comment_lines = $this->output_filter->newlinesToArray($comment_markdown);
            $line_count = count($comment_lines);

            if ($line_count > $this->domain->setting('max_index_comment_lines')) {
                $comment_data['long_comment'] = true;
                $comment_data['long_comment_url'] = $post->getURL($this->session->inModmode($this->domain));
                $comment_data['comment_lines'] = array();
                $i = 0;
                $reduced_lines = array();
                $limit = $this->domain->setting('max_index_comment_lines');

                for (; $i < $limit; $i ++) {
                    $reduced_lines[] = $comment_lines[$i];
                }

                $comment_markdown = implode("\n", $reduced_lines);
            }
        }

        $comment_data['comment_markdown'] = $comment_markdown;
        return $comment_data;
    }

    public function generateBacklinks(Post $post): array
    {
        $cites = new Cites($this->database);
        $cite_list = $cites->getForPost($post);
        $post_content_id = $post->contentID();
        $backlinks = array();

        foreach ($cite_list['sources'] as $cite) {
            $backlink_data = array();

            if ($cite['source_board'] == $this->domain->id()) {
                $backlink_data['backlink_text'] = '>>' . $cite['source_post'];
            } else {
                $backlink_data['backlink_text'] = '>>>/' . $cite['source_board'] . '/' . $cite['source_post'];
            }

            $cite_data = $cites->getCiteData($backlink_data['backlink_text'], $this->domain, $post_content_id);
            $cite_url = '';

            if ($cite_data['exists']) {
                $cite_url = $cites->generateCiteURL($cite_data,
                    $this->session->inModmode($this->domain) && !$this->write_mode);

                if (!empty($cite_url)) {
                    $backlink_data['backlink_url'] = $cite_url;
                    $backlinks[] = $backlink_data;
                }
            }
        }

        return $backlinks;
    }

    public function parseComment(?string $comment_text, Post $post): string
    {
        if (nel_true_empty($comment_text)) {
            return '';
        }

        if ($post->getMoar()->get('raw_html') === true) {
            return $comment_text;
        }

        $comment = $comment_text;

        if ($this->domain->setting('trim_comment_newlines_start')) {
            $comment = ltrim($comment, "\n\r");
        }

        if ($this->domain->setting('trim_comment_newlines_end')) {
            $comment = rtrim($comment, "\n\r");
        }

        if ($this->domain->setting('filter_zalgo')) {
            $comment = $this->output_filter->filterZalgo($comment);
        }

        if ($post->getMoar()->get('no_markdown') === true) {
            return htmlspecialchars($comment, ENT_QUOTES, 'UTF-8', false);
        }

        $dynamic_urls = $this->session->inModmode($this->domain) && !$this->write_mode;
        $engine = new Markdown();
        $escaped_comment = htmlspecialchars($comment, ENT_NOQUOTES, 'UTF-8');
        $parsed_markdown = $engine->parsePostComments($escaped_comment, $post, $dynamic_urls);
        return $parsed_markdown;
    }
}