<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Overboard;
use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;

class OutputOverboard extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function index(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('index/index');
        $sfw = $parameters['sfw'] ?? false;
        $overboard_id = $parameters['overboard_id'] ?? 'all';
        $this->render_data['show_catalog_link'] = $parameters['catalog'] ?? false;
        $uri = $parameters['uri'] ?? $this->site_domain->setting('overboard_uri');
        $overboard_name = $parameters['name'] ?? __('Overboard');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general(['name' => $overboard_name], true);
        $overboard = new Overboard($this->database);
        $threads = $overboard->getThreads($overboard_id);
        $thread_count = count($threads);
        $threads_done = 0;
        $gen_data = array();
        $gen_data['index']['thread_count'] = $thread_count;
        $gen_data['index_rendering'] = true;
        $this->render_data['hide_post_select'] = true;
        $this->render_data['hide_file_select'] = true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['styles'] = $output_menu->styles([], true);
        $this->render_data['overboard'] = true;
        $this->render_data['index_navigation_top'] = true;
        $this->render_data['show_catalog_link'] = true;
        $this->render_data['catalog_url'] = 'catalog.html';

        if ($sfw) {
            $index_replies = $this->site_domain->setting('sfw_overboard_thread_replies');
            $threads_per_page = $this->site_domain->setting('sfw_overboard_threads');
        } else {
            $index_replies = $this->site_domain->setting('overboard_thread_replies');
            $threads_per_page = $this->site_domain->setting('overboard_threads');
        }

        $threads_on_page = 0;

        for ($i = 0; $i <= $thread_count; $i ++) {
            if ($threads_on_page >= $threads_per_page || $i === $thread_count) {
                $this->render_data['index_navigation'] = false;
                $output_footer = new OutputFooter($this->site_domain, $this->write_mode);
                $this->render_data['footer'] = $output_footer->general([], true);
                $output = $this->output('basic_page', $data_only, true, $this->render_data);
                $index_filename = 'index' . NEL_PAGE_EXT;

                if ($this->write_mode) {
                    $this->file_handler->writeFile(NEL_PUBLIC_PATH . $uri . '/' . $index_filename, $output, true);
                } else {
                    echo $output;
                }

                return $output;
            }

            $thread = $threads[$i];
            $thread_domain = new DomainBoard($thread->domain()->id(), $this->database);
            $thread_input = array();
            $output_post = new OutputPost($thread_domain, $this->write_mode);
            $thread_input = array();
            $thread_input['board_uri'] = $thread->domain()->uri(true);
            $thread_input['board_url'] = NEL_BASE_WEB_PATH . $thread->domain()->uri() . '/';
            $thread_input['board_safety'] = $thread_domain->setting('safety_level');
            $thread_input['thread_id'] = $thread->getData('thread_id');
            $thread_input['thread_expand_id'] = 'thread-expand-' . $thread->contentID()->getIDString();
            $thread_input['thread_corral_id'] = 'thread-corral-' . $thread->contentID()->getIDString();

            $thread_input['omitted_count'] = $thread->getData('post_count') - $index_replies - 1; // Subtract 1 to account for OP
            $gen_data['abbreviate'] = $thread_input['omitted_count'] > 0;
            $thread_input['abbreviate'] = $gen_data['abbreviate'];
            $abbreviate_start = $thread->getData('post_count') - $index_replies;
            $post_counter = 1;

            foreach ($thread->getPosts() as $post) {
                $post_content_id = new ContentID(
                    ContentID::createIDString($thread->getData('thread_id'), $post->getData('post_number')));
                $post = $post_content_id->getInstanceFromID($thread_domain);
                $parameters = ['gen_data' => $gen_data, 'in_thread_number' => $post_counter];

                if ($post->getData('op') == 1) {
                    $thread_input['op_post'] = $output_post->render($post, $parameters, true);
                } else {
                    if ($post_counter > $abbreviate_start) {
                        $thread_input['thread_posts'][] = $output_post->render($post, $parameters, true);
                    }
                }

                $post_counter ++;
            }

            $this->render_data['threads'][] = $thread_input;
            $threads_on_page ++;
            $threads_done ++;
        }
    }

    public function catalog(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('catalog/catalog');
        $overboard_id = $parameters['overboard_id'] ?? 'all';
        $uri = $parameters['uri'] ?? 'overboard';
        $overboard_name = $parameters['name'] ?? __('Overboard');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['catalog_title'] = __('Catalog of ') . $overboard_name;
        $this->render_data['overboard'] = true;
        $overboard = new Overboard($this->database);
        $thread_count = 1;

        foreach ($overboard->getThreads($overboard_id) as $thread) {
            if (is_null($thread) || !$thread->exists()) {
                continue;
            }

            $thread_data = array();
            $post = $thread->firstPost();

            if ($this->session->inModmode($this->domain) && !$this->writeMode()) {
                $thread_data['open_url'] = $thread->getRoute(true, '&modmode=true');
            } else {
                $thread_data['open_url'] = $thread->getURL();
            }

            $thread_data['first_post_subject'] = $post->getData('subject');
            $output_post = new OutputPost($this->domain, false);

            if (NEL_USE_RENDER_CACHE && isset($post->getCache()['comment_markup'])) {
                $thread_data['comment_markup'] = $post->getCache()['comment_markup'];
            } else {
                $thread_data['comment_markup'] = $output_post->parseComment($post->getData('comment'), $post);
            }

            $thread_data['mod-comment'] = $post->getData('mod_comment');
            $thread_data['reply_count'] = $thread->getData('post_count') - 1;
            $thread_data['total_uploads'] = $thread->getData('total_uploads');
            $thread_data['index_page'] = ceil($thread_count / $thread->domain()->setting('threads_per_page'));
            $ui_image_set = $this->domain->frontEndData()->getImageSet($this->domain->setting('ui_image_set'));
            $thread_data['is_sticky'] = $thread->getData('sticky');
            $thread_data['status_sticky'] = $ui_image_set->getWebPath('ui', 'status_sticky', true);
            $thread_data['is_locked'] = $thread->getData('locked');
            $thread_data['status_locked'] = $ui_image_set->getWebPath('ui', 'status_locked', true);
            $thread_data['is_cyclic'] = $thread->getData('cyclic');
            $thread_data['status_cyclic'] = $ui_image_set->getWebPath('ui', 'status_cyclic', true);
            $thread_data['board_id'] = $thread->domain()->id();
            $thread_data['board_url'] = NEL_BASE_WEB_PATH . $thread->domain()->id() . '/';
            $thread_data['board_safety'] = $thread->domain()->setting('safety_level');
            $uploads = $post->getUploads();
            $upload_count = count($uploads);

            if ($upload_count > 0) {
                $output_file_info = new OutputFile($this->domain, $this->write_mode);
                $output_embed_info = new OutputEmbed($this->domain, $this->write_mode);
                $thread_data['single_file'] = true;
                $thread_data['multi_file'] = false;
                $thread_data['single_multiple'] = 'single';

                if (!nel_true_empty($post->getData('subject'))) {
                    $thread_data['subject'] = $post->getData('subject');
                } else {
                    $thread_data['subject'] = '#' . $post->getData('post_number');
                }

                $upload = $uploads[0];

                if (nel_true_empty($upload->getData('embed_url'))) {
                    $file_data = $output_file_info->render($upload, $post, ['catalog' => true], true);
                } else {
                    $file_data = $output_embed_info->render($upload, $post, ['catalog' => true], true);
                }

                $upload_row = array();
                $first = true;
                $multiple = $upload_count > 1 && $this->domain->setting('catalog_show_multiple_uploads');

                foreach ($uploads as $upload) {
                    if ($upload->getData('deleted') && !$this->domain->setting('display_deleted_placeholder')) {
                        continue;
                    }

                    $file_data = array();

                    if (nel_true_empty($upload->getData('embed_url'))) {
                        $file_data = $output_file_info->render($upload, $post,
                            ['catalog' => true, 'first' => $first, 'multiple' => $multiple], true);
                    } else {
                        $file_data = $output_embed_info->render($upload, $post,
                            ['catalog' => true, 'first' => $first, 'multiple' => $multiple], true);
                    }

                    $upload_row[] = $file_data;

                    if (!$this->domain->setting('catalog_show_multiple_uploads')) {
                        break;
                    }

                    if (($first && $this->domain->setting('catalog_first_preview_own_row')) ||
                        count($upload_row) == $this->domain->setting('catalog_max_uploads_row')) {
                        $thread_data['upload_rows'][]['row'] = $upload_row;
                        $upload_row = array();
                    }

                    $first = false;
                }

                if (!empty($upload_row)) {
                    $thread_data['upload_rows'][]['row'] = $upload_row;
                }
            } else {
                $thread_data['open_text'] = _gettext('Open thread');
            }

            $thread_count ++;
            $this->render_data['catalog_entries'][] = $thread_data;
        }

        $this->render_data['tile_width'] = $this->domain->setting('catalog_tile_width');
        $this->render_data['tile_height'] = $this->domain->setting('catalog_tile_height');
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->general([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);

        if ($this->write_mode) {
            $file = NEL_PUBLIC_PATH . $uri . '/catalog.html';
            $this->file_handler->writeFile($file, $output, true);
        } else {
            echo $output;
        }

        return $output;
    }
}