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

        $this->render_data['is_op'] = $post->data('op');
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
        $this->postHeaders($response, $thread, $post, $gen_data, $in_thread_number);

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
                    $file_data = $output_file_info->render($upload, $post,
                        ['multiple' => $post->data('file_count') > 1], true);
                } else {
                    $file_data = $output_embed_info->render($upload, $post,
                        ['multiple' => $post->data('file_count') > 1], true);
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

        if ($thread->data('shadow') && $post->data('op')) {
            $markup = new Markup($this->database);
            $dynamic_urls = $this->session->inModmode($this->domain) && !$this->write_mode;
            $cite_text = '>>>/' . $thread->getMoar()->get('shadow_board_id') . '/' .
                $thread->getMoar()->get('shadow_thread_id');
            $shadow_cite = $markup->parseCites($cite_text, $post, $dynamic_urls);
            $this->render_data['is_shadow'] = true;
            $shadow_type = $thread->getMoar()->get('shadow_type');
            $shadow_message = '';

            if ($shadow_type === 'moved') {
                $shadow_message = $this->domain->setting('shadow_message_moved');
            }

            if ($shadow_type === 'merged') {
                $shadow_message = $this->domain->setting('shadow_message_merged');
            }

            $this->render_data['shadow_message'] = sprintf(htmlspecialchars($shadow_message), $shadow_cite);
        }

        $dice_roll = $post->getMoar()->get('dice_roll');

        if (!is_null($dice_roll)) {
            $this->render_data['show_dice_roll'] = true;
            $modifier = $dice_roll['modifier'] > 0 ? '+' . $dice_roll['modifier'] : $dice_roll['modifier'];

            if ($this->domain->setting('list_all_dice_rolls')) {
                $rolls = implode(', ', $dice_roll['rolls']);
                $this->render_data['dice_roll_results'] = sprintf(__('Rolled %d %d-sided dice with modifier of %s'),
                    $dice_roll['dice'], $dice_roll['sides'], $modifier);
                $this->render_data['dice_roll_list'] = sprintf('%s %s = %d', $rolls, $modifier, $dice_roll['total']);
            } else {
                $this->render_data['dice_roll_results'] = sprintf(
                    __('Rolled %d %d-sided dice with modifier of %s = %d'), $dice_roll['dice'], $dice_roll['sides'],
                    $modifier, $dice_roll['total']);
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
        $this->render_data['post_options'] = array();
        $this->render_data['headers']['response'] = $response;
        $post_content_id = $post->contentID();
        $this->render_data['show_poster_name'] = $this->domain->setting('show_poster_name');
        $this->render_data['show_tripcodes'] = $this->domain->setting('show_tripcodes');
        $this->render_data['show_capcode'] = $this->domain->setting('show_capcode');
        $this->render_data['show_post_subject'] = $this->domain->setting('show_post_subject');
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
            $this->render_data['headers']['thread_headers'] = $thread_headers;
        }

        $post_headers['in_thread_number'] = $in_thread_number;

        if (!nel_true_empty($post->data('email'))) {
            $post_headers['mailto']['mailto_url'] = 'mailto:' . $post->data('email');
        }

        $post_headers['subject'] = $post->data('subject');
        $post_headers['name'] = $post->data('name');

        if ($this->domain->setting('show_poster_id')) {
            $raw_poster_id = hash_hmac('sha256', $thread->data('salt') . $post->data('hashed_ip_address'),
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

        $this->render_data['content_links_hide_post']['content_id'] = $post->contentID()->getIDString();
        $this->render_data['post_options'][] = $this->render_data['content_links_hide_post'];
        $this->render_data['content_links_cite_post']['content_id'] = $post->contentID()->getIDString();
        $this->render_data['post_options'][] = $this->render_data['content_links_cite_post'];

        $post_headers['post_time'] = $this->domain->domainDateTime(intval($post->data('post_time')))->format(
            $this->domain->setting('post_time_format'));
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
            $comment_data['comment_markup'] = $this->domain->setting('no_comment_text');
            return $comment_data;
        }

        // TODO: Do cache check/fetch better
        if (NEL_USE_RENDER_CACHE && isset($post->getCache()['comment_markup'])) {
            $comment_markup = $post->getCache()['comment_markup'];
        } else {
            $comment_markup = $this->parseComment($comment, $post);
        }

        if ($gen_data['index_rendering']) {
            $comment_lines = $this->output_filter->newlinesToArray($comment_markup);
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

                $comment_markup = implode("\n", $reduced_lines);
            }
        }

        $comment_data['comment_markup'] = $comment_markup;
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

        if ($post->getMoar()->get('no_markup') === true) {
            return htmlspecialchars($comment, ENT_QUOTES, 'UTF-8', false);
        }

        $dynamic_urls = $this->session->inModmode($this->domain) && !$this->write_mode;
        $engine = new Markup($this->database);
        $escaped_comment = htmlspecialchars($comment, ENT_NOQUOTES, 'UTF-8');
        $parsed_markup = $engine->parsePostComments($escaped_comment, $post, $dynamic_urls);
        return $parsed_markup;
    }
}