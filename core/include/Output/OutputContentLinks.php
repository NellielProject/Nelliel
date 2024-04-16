<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Post;
use Nelliel\Content\Thread;
use Nelliel\Content\Upload;
use Nelliel\Domains\Domain;

class OutputContentLinks extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function thread(Thread $thread, array $gen_data = array()): array
    {
        $options_keys = (array) json_decode($this->domain->setting('thread_options_link_set'));
        $context = $gen_data['context'] ?? '';
        $link_set = new LinkSet();
        $base_data = array();
        $base_data['left_bracket'] = $this->getUIText('content_links_left_bracket');
        $base_data['right_bracket'] = $this->getUIText('content_links_right_bracket');

        if ($context === 'index') {
            if ($this->session->inModmode($this->domain) && !$this->write_mode) {
                $thread_url = $thread->getRoute(false, 'modmode');
            } else {
                $thread_url = $thread->getURL();
            }

            $index_replies = $gen_data['index_replies'] ?? 0;
            $abbreviate = $gen_data['abbreviate'] ?? false;

            $link_set->addLink('content_links_reply', $base_data);
            $link_set->addData('content_links_reply', 'url', $thread_url);
            $link_set->addData('content_links_reply', 'text', $this->getUIText('content_links_reply'));
            $link_set->addData('content_links_reply', 'query_class', 'js-hide-thread');
            $link_set->addData('content_links_reply', 'content_id', $thread->contentID()->getIDString());

            if ($abbreviate) {
                $link_set->addLink('content_links_expand_thread', $base_data);
                $link_set->addData('content_links_expand_thread', 'text',
                    $this->getUIText('content_links_expand_thread'));
                $link_set->addData('content_links_expand_thread', 'command', 'expand-thread');
                $link_set->addData('content_links_expand_thread', 'alt_text',
                    $this->getUIText('content_links_collapse_thread'));
                $link_set->addData('content_links_expand_thread', 'alt_command', 'collapse-thread');
                $link_set->addData('content_links_expand_thread', 'url', $thread_url);

                if ($this->session->inModmode($this->domain)) {
                    $link_set->addData('content_links_expand_thread', 'url', $thread->getRoute(false, 'expand&modmode'));
                    $link_set->addData('content_links_expand_thread', 'alt_url',
                        $thread->getRoute(false, 'collapse&modmode'));
                } else {
                    $link_set->addData('content_links_expand_thread', 'url', $thread->getRoute(false, 'expand'));
                    $link_set->addData('content_links_expand_thread', 'alt_url', $thread->getRoute(false, 'collapse'));
                }

                $link_set->addData('content_links_collapse_thread', 'content_id', $thread->contentID()->getIDString());

                $link_set->addLink('content_links_collapse_thread', $base_data);
                $link_set->addData('content_links_collapse_thread', 'text',
                    $this->getUIText('content_links_collapse_thread'));
                $link_set->addData('content_links_collapse_thread', 'command', 'collapse-thread');
                $link_set->addData('content_links_collapse_thread', 'alt_text',
                    $this->getUIText('content_links_expand_thread'));
                $link_set->addData('content_links_collapse_thread', 'alt_command', 'expand-thread');
                $link_set->addData('content_links_collapse_thread', 'url', $thread_url);

                if ($this->session->inModmode($this->domain)) {
                    $link_set->addData('content_links_collapse_thread', 'url',
                        $thread->getRoute(false, 'collapse&modmode'));
                    $link_set->addData('content_links_collapse_thread', 'alt_url',
                        $thread->getRoute(false, 'expand&modmode'));
                } else {
                    $link_set->addData('content_links_collapse_thread', 'url', $thread->getRoute(false, 'collapse'));
                    $link_set->addData('content_links_collapse_thread', 'alt_url', $thread->getRoute(false, 'expand'));
                }

                $link_set->addData('content_links_collapse_thread', 'content_id', $thread->contentID()->getIDString());
            }

            $thread->getData('post_count') - $index_replies - 1; // Subtract 1 to account for OP

            $first_posts_increments = json_decode($this->domain->setting('first_posts_increments'));
            $first_posts_format = $thread->pageBasename() . $this->site_domain->setting('first_posts_filename_format');

            if (is_array($first_posts_increments) &&
                $thread->getData('post_count') > $this->domain->setting('first_posts_threshold')) {
                foreach ($first_posts_increments as $increment) {
                    if ($thread->getData('post_count') >= $increment) {
                        $id = 'content_links_first_posts' . $increment;
                        $options_keys[] = $id;
                        $link_set->addLink($id, $base_data);
                        $link_set->addData($id, 'url',
                            $this->domain->reference('page_web_path') . $thread->contentID()->threadID() . '/' .
                            sprintf($first_posts_format, $increment) . NEL_PAGE_EXT);
                        $link_set->addData($id, 'text',
                            sprintf($this->getUIText('content_links_first_posts'), $increment));
                        $link_set->addData($id, 'query_class', 'js-hide-thread');
                    }
                }
            }

            $last_posts_increments = json_decode($this->domain->setting('last_posts_increments'));
            $last_posts_format = $thread->pageBasename() . $this->site_domain->setting('last_posts_filename_format');

            if (is_array($last_posts_increments) &&
                $thread->getData('post_count') > $this->domain->setting('last_posts_threshold')) {
                foreach ($last_posts_increments as $increment) {
                    if ($thread->getData('post_count') >= $increment) {
                        $id = 'content_links_last_posts' . $increment;
                        $options_keys[] = $id;
                        $link_set->addLink($id, $base_data);
                        $link_set->addData($id, 'url',
                            $this->domain->reference('page_web_path') . $thread->contentID()->threadID() . '/' .
                            sprintf($last_posts_format, $increment) . NEL_PAGE_EXT);
                        $link_set->addData($id, 'text',
                            sprintf($this->getUIText('content_links_last_posts'), $increment));
                        $link_set->addData($id, 'query_class', 'js-hide-thread');
                    }
                }
            }
        }

        $link_set->addLink('content_links_hide_thread', $base_data);
        $link_set->addData('content_links_hide_thread', 'content_id', $thread->firstPost()->contentID()->getIDString());
        $link_set->addData('content_links_hide_thread', 'text', $this->getUIText('content_links_hide_thread'));
        $link_set->addData('content_links_hide_thread', 'command', 'hide-thread');
        $link_set->addData('content_links_hide_thread', 'alt_text', $this->getUIText('content_links_show_thread'));
        $link_set->addData('content_links_hide_thread', 'alt_command', 'show-thread');

        return $link_set->build($options_keys);
    }

    public function post(Post $post): array
    {
        $options_keys = (array) json_decode($this->domain->setting('post_options_link_set'));
        $link_set = new LinkSet();
        $base_data = array();
        $base_data['left_bracket'] = $this->getUIText('content_links_left_bracket');
        $base_data['right_bracket'] = $this->getUIText('content_links_right_bracket');

        $link_set->addLink('content_links_hide_post', $base_data);
        $link_set->addData('content_links_hide_post', 'content_id', $post->contentID()->getIDString());
        $link_set->addData('content_links_hide_post', 'text', $this->getUIText('content_links_hide_post'));
        $link_set->addData('content_links_hide_post', 'command', 'hide-post');
        $link_set->addData('content_links_hide_post', 'alt_text', $this->getUIText('content_links_show_post'));
        $link_set->addData('content_links_hide_post', 'alt_command', 'show-post');

        $link_set->addLink('content_links_show_post', $base_data);
        $link_set->addData('content_links_show_post', 'content_id', $post->contentID()->getIDString());
        $link_set->addData('content_links_show_post', 'text', $this->getUIText('content_links_show_post'));
        $link_set->addData('content_links_show_post', 'command', 'show-post');
        $link_set->addData('content_links_show_post', 'alt_text', $this->getUIText('content_links_hide_post'));
        $link_set->addData('content_links_show_post', 'alt_command', 'hide-post');

        $link_set->addLink('content_links_cite_post', $base_data);
        $link_set->addData('content_links_cite_post', 'content_id', $post->contentID()->getIDString());
        $link_set->addData('content_links_cite_post', 'text', $this->getUIText('content_links_cite_post'));
        $link_set->addData('content_links_cite_post', 'command', 'cite-post');

        return $link_set->build($options_keys);
    }

    public function upload(Upload $upload): array
    {
        $is_file = nel_true_empty($upload->getData('embed_url'));
        $options_keys = (array) json_decode($this->domain->setting('upload_options_link_set'));
        $link_set = new LinkSet();
        $base_data = array();
        $base_data['left_bracket'] = $this->getUIText('mod_links_left_bracket');
        $base_data['right_bracket'] = $this->getUIText('mod_links_right_bracket');

        if ($is_file) {
            $link_set->addLink('content_links_hide_file', $base_data);
            $link_set->addData('content_links_hide_file', 'content_id', $upload->contentID()->getIDString());
            $link_set->addData('content_links_hide_file', 'text', $this->getUIText('content_links_hide_file'));
            $link_set->addData('content_links_hide_file', 'command', 'hide-file');
            $link_set->addData('content_links_hide_file', 'alt_text', $this->getUIText('content_links_show_file'));
            $link_set->addData('content_links_hide_file', 'alt_command', 'show-file');

            $link_set->addLink('content_links_show_file', $base_data);
            $link_set->addData('content_links_show_file', 'content_id', $upload->contentID()->getIDString());
            $link_set->addData('content_links_show_file', 'text', $this->getUIText('content_links_show_file'));
            $link_set->addData('content_links_show_file', 'command', 'show-file');
            $link_set->addData('content_links_show_file', 'alt_text', $this->getUIText('content_links_hide_file'));
            $link_set->addData('content_links_show_file', 'alt_command', 'hide-file');

            $link_set->addLink('content_links_show_upload_meta', $base_data);
            $link_set->addData('content_links_show_upload_meta', 'content_id', $upload->contentID()->getIDString());
            $link_set->addData('content_links_show_upload_meta', 'text',
                $this->getUIText('content_links_show_upload_meta'));
            $link_set->addData('content_links_show_upload_meta', 'command', 'show-upload-meta');
            $link_set->addData('content_links_show_upload_meta', 'alt_text',
                $this->getUIText('content_links_hide_upload_meta'));
            $link_set->addData('content_links_show_upload_meta', 'alt_command', 'hide-upload-meta');

            $link_set->addLink('content_links_hide_upload_meta', $base_data);
            $link_set->addData('content_links_hide_upload_meta', 'content_id', $upload->contentID()->getIDString());
            $link_set->addData('content_links_hide_upload_meta', 'text',
                $this->getUIText('content_links_hide_upload_meta'));
            $link_set->addData('content_links_hide_upload_meta', 'command', 'hide-upload-meta');
            $link_set->addData('content_links_hide_upload_meta', 'alt_text',
                $this->getUIText('content_links_show_upload_meta'));
            $link_set->addData('content_links_hide_upload_meta', 'alt_command', 'show-upload-meta');
        } else {
            $link_set->addLink('content_links_hide_embed', $base_data);
            $link_set->addData('content_links_hide_embed', 'content_id', $upload->contentID()->getIDString());
            $link_set->addData('content_links_hide_embed', 'text', $this->getUIText('content_links_hide_embed'));
            $link_set->addData('content_links_hide_embed', 'command', 'hide-embed');
            $link_set->addData('content_links_hide_embed', 'alt_text', $this->getUIText('content_links_show_embed'));
            $link_set->addData('content_links_hide_embed', 'alt_command', 'show-embed');

            $link_set->addLink('content_links_show_embed', $base_data);
            $link_set->addData('content_links_show_embed', 'content_id', $upload->contentID()->getIDString());
            $link_set->addData('content_links_show_embed', 'text', $this->getUIText('content_links_show_embed'));
            $link_set->addData('content_links_show_embed', 'command', 'show-embed');
            $link_set->addData('content_links_show_embed', 'alt_text', $this->getUIText('content_links_hide_embed'));
            $link_set->addData('content_links_show_embed', 'alt_command', 'hide-embed');
        }

        return $link_set->build($options_keys);
    }

    private function getUIText(string $id)
    {
        $ui_text = strval($this->domain->setting($id));

        if (!$this->domain->setting('translate_content_links') || $ui_text === '') {
            return $ui_text;
        }

        return __($ui_text);
    }
}