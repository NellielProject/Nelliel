<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Cites;
use Nelliel\Content\ContentID;
use Nelliel\Content\Post;
use Nelliel\Content\Thread;
use Nelliel\Domains\Domain;
use Nelliel\Markdown\ImageboardMarkdown;
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
                $post->getJSON()->addUpload($upload->getJSON());

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
        $this->render_data['headers']['response'] = $response;
        $post_content_id = $post->contentID();

        if ($this->session->inModmode($this->domain) && !$this->write_mode) {
            if ($this->session->user()->checkPermission($this->domain, 'perm_view_unhashed_ip') &&
                !empty($post->data('ip_address'))) {
                $ip = $post->data('ip_address');
            } else {
                $ip = $post->data('hashed_ip_address');
            }

            $this->render_data['mod_ip_address'] = $ip;

            if (!$response) {
                $lock_button = $thread->data('locked') ? 'mod_unlock_label' : 'mod_lock_label';
                $this->render_data['mod_lock_option_label'] = $this->render_data[$lock_button];
                $this->render_data['mod_lock_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                    '&actions=lock&content-id=' . $thread->contentID()->getIDString() . '&modmode=true&goback=true';

                $sticky_button = $thread->data('sticky') ? 'mod_unsticky_label' : 'mod_sticky_label';
                $this->render_data['mod_sticky_option_label'] = $this->render_data[$sticky_button];
                $this->render_data['mod_sticky_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                    '&actions=sticky&content-id=' . $thread->contentID()->getIDString() . '&modmode=true&goback=true';

                $permasage_button = $thread->data('permasage') ? 'mod_unpermasage_label' : 'mod_permasage_label';
                $this->render_data['mod_permasage_option_label'] = $this->render_data[$permasage_button];
                $this->render_data['mod_permasage_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                    '&actions=permasage&content-id=' . $thread->contentID()->getIDString() . '&modmode=true&goback=true';

                $cyclic_button = $thread->data('cyclic') ? 'mod_non_cyclic_label' : 'mod_cyclic_label';
                $this->render_data['mod_cyclic_option_label'] = $this->render_data[$cyclic_button];
                $this->render_data['mod_cyclic_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                    '&actions=cyclic&content-id=' . $thread->contentID()->getIDString() . '&modmode=true&goback=true';
            }

            $this->render_data['mod_ban_url'] = '?module=admin&section=bans&board-id=' . $this->domain->id() .
                '&actions=new&ban-ip=' . $ip . '&modmode=true&goback=false';
            $this->render_data['mod_delete_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                '&actions=delete&content-id=' . $post_content_id->getIDString() . '&modmode=true&goback=true';
            $this->render_data['mod_delete_by_ip_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                '&actions=delete-by-ip&content-id=' . $post_content_id->getIDString() . '&modmode=true&goback=true';
            $this->render_data['mod_global_delete_by_ip_url'] = '?module=admin&section=threads&board-id=' .
                $this->domain->id() . '&actions=global-delete-by-ip&content-id=' . $post_content_id->getIDString() .
                '&modmode=true&goback=true';
            $this->render_data['mod_ban_delete_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                '&actions=bandelete&content-id=' . $post_content_id->getIDString() . '&ban-ip=' . $ip .
                '&modmode=true&goback=false';
            $this->render_data['mod_edit_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                '&actions=edit&content-id=' . $post_content_id->getIDString();
        }

        $this->render_data['headers']['thread_page'] = sprintf($this->site_domain->setting('thread_filename_format'),
            $thread->contentID()->threadID()) . NEL_PAGE_EXT;

        $thread_headers['thread_content_id'] = $thread->contentID()->getIDString();
        $post_headers['thread_content_id'] = $thread->contentID()->getIDString();
        $post_headers['post_content_id'] = $post_content_id->getIDString();
        $post_headers['is_op'] = $post->data('op');

        if (!$response) {
            $thread_headers['is_sticky'] = $thread->data('sticky');
            $thread_headers['sticky'] = $ui_image_set->getWebPath('ui', 'sticky', true);
            $thread_headers['is_locked'] = $thread->data('locked');
            $thread_headers['locked'] = $ui_image_set->getWebPath('ui', 'locked', true);
            $thread_headers['is_cyclic'] = $thread->data('cyclic');
            $thread_headers['cyclic'] = $ui_image_set->getWebPath('ui', 'cyclic', true);

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

        if ($this->domain->setting('display_poster_id')) {
            $raw_poster_id = hash_hmac('sha256',
                nel_convert_ip_from_storage($post->data('ip_address'), NEL_POSTER_ID_PEPPER) . $this->domain->id() .
                $thread->contentID()->threadID());
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

        $post_headers['post_time'] = date($this->domain->setting('date_format'), intval($post->data('post_time')));
        $post_headers['post_number'] = $post->contentID()->postID();
        $post_headers['post_number_url'] = $thread->getURL(
            $this->session->inModmode($this->domain) && !$this->write_mode) . '#t' . $post_content_id->threadID() . 'p' .
            $post_content_id->postID();
        $post_headers['post_number_url_cite'] = $post_headers['post_number_url'] . 'cite';

        if ($this->domain->setting('display_post_backlinks')) {
            // TODO: Do cache check/fetch better
            if (NEL_USE_RENDER_CACHE && isset($post->getCache()['backlink_data'])) {
                $post_headers['backlinks'] = $post->getCache()['backlink_data'];
            } else {
                $post_headers['backlinks'] = $this->generateBacklinks($post);
            }
        }

        $this->render_data['headers']['post_headers'] = $post_headers;
        return $header_data;
    }

    private function postComments(Post $post, array $gen_data, Thread $thread)
    {
        $comment_data = array();
        $comment_data['post_contents_id'] = 'post-contents-' . $post->contentID()->getIDString();
        $comment_data['mod_comment'] = $post->data('mod_comment') ?? null;
        $comment_data['nofollow_external_links'] = $this->site_domain->setting('nofollow_external_links');
        $comment = $post->data('comment');

        if ($post->getMoar()->get('raw_html')) {
            $comment_data['comment_markdown'] = $comment;
            return $comment_data;
        }

        if (nel_true_empty($comment)) {
            $comment_data['comment_markdown'] = $this->domain->setting('no_comment_text');
            return $comment_data;
        }

        // TODO: Do cache check/fetch better
        if (NEL_USE_RENDER_CACHE && isset($post->getCache()['comment_data'])) {
            $comment_markdown = $post->getCache()['comment_data'];
        } else {
            $comment_markdown = $this->parseComment($comment, $post->contentID());
        }

        if ($gen_data['index_rendering']) {
            $comment_lines = $this->output_filter->newlinesToArray($comment_markdown);
            $line_count = count($comment_lines);

            if ($line_count > $this->domain->setting('max_index_comment_lines')) {
                $comment_data['long_comment'] = true;
                $comment_data['long_comment_url'] = $thread->getURL($this->session->inModmode($this->domain)) . '#t' .
                    $post->contentID()->threadID() . 'p' . $post->contentID()->postID();
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

    public function postSuccess(array $parameters, bool $data_only)
    {
        $messages[] = _gettext('Post success!');
        $link['url'] = $parameters['forward_url'] ?? '';
        $link['text'] = _gettext('Click here if you are not automatically redirected');
        $parameters['page_title'] = $this->domain->reference('title');
        $output_interstitial = new OutputInterstitial($this->domain, $this->write_mode);
        return $output_interstitial->render($parameters, $data_only, $messages, [$link]);
    }

    public function contentDeleted(array $parameters, bool $data_only)
    {
        $messages[] = _gettext('The selected items have been deleted!');
        $link['url'] = $parameters['forward_url'] ?? '';
        $link['text'] = _gettext('Click here if you are not automatically redirected');
        $parameters['page_title'] = $this->domain->reference('title');
        $output_interstitial = new OutputInterstitial($this->domain, $this->write_mode);
        return $output_interstitial->render($parameters, $data_only, $messages, [$link]);
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
                $cite_url = $cites->createPostLinkURL($cite_data, $this->domain,
                    $this->session->inModmode($this->domain) && !$this->write_mode);

                if (!empty($cite_url)) {
                    $backlink_data['backlink_url'] = $cite_url;
                    $backlinks[] = $backlink_data;
                }
            }
        }

        return $backlinks;
    }

    public function parseComment(?string $comment_text, ContentID $post_content_id): string
    {
        if (nel_true_empty($comment_text)) {
            return '';
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

        $imageboard_markdown = new ImageboardMarkdown($this->domain, $post_content_id);

        if ($this->session->inModmode($this->domain) && !$this->write_mode) {
            $parsed_markdown = $imageboard_markdown->parseDynamic($comment);
        } else {
            $parsed_markdown = $imageboard_markdown->parse($comment);
        }

        return $parsed_markdown;
    }
}