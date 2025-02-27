<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\CatalogJSON;
use Nelliel\API\JSON\IndexJSON;
use Nelliel\API\JSON\ThreadlistJSON;
use Nelliel\Content\Post;
use Nelliel\Content\Thread;
use Nelliel\Domains\DomainBoard;
use PDO;

class OutputIndex extends Output
{

    function __construct(DomainBoard $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('index/index');
        $page = intval($parameters['page'] ?? 1);
        $page_title = $this->domain->reference('title');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['page_title' => $page_title], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['in_modmode'] = $this->session->inModmode($this->domain) && !$this->write_mode;

        if ($this->render_data['in_modmode']) {
            $manage_headers['header'] = _gettext('Moderator Mode');
            $manage_headers['sub_header'] = _gettext('View Index');
            $this->render_data['header'] = $output_header->board(['manage_headers' => $manage_headers], true);
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->uri(), 'threads'], false,
                'modmode');
        } else {
            $this->render_data['header'] = $output_header->board([], true);
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->uri(), 'threads']);
        }

        $output_navigation = new OutputNavigationLinks($this->domain, $this->write_mode);
        $this->render_data['page_navigation'] = $output_navigation->boardPages(
            ['in_modmode' => $this->render_data['in_modmode'], 'display' => 'index'], $data_only);

        $threads = $this->domain->getThreads(true, false);
        $thread_count = count($threads);
        $threads_done = 0;
        $gen_data = array();
        $gen_data['index']['thread_count'] = $thread_count;
        $output_new_post_form = new OutputNewPostForm($this->domain, $this->write_mode);
        $this->render_data['new_post_form'] = $output_new_post_form->render(['thread_id' => 'cid_0_0_0', 'reply_to' => 0], true);

        if ($thread_count === 0) {
            $page_count = 1;
        } else {
            $page_count = (int) ceil($thread_count / $this->domain->setting('threads_per_page'));
        }

        $this->render_data['show_global_announcement'] = !nel_true_empty(
            $this->site_domain->setting('global_announcement'));
        $this->render_data['global_announcement_text'] = $this->site_domain->setting('global_announcement');

        $blotter_limit = $this->site_domain->setting('small_blotter_limit');
        $query = 'SELECT * FROM "' . NEL_BLOTTER_TABLE . '" ORDER BY "time" DESC LIMIT ' . $blotter_limit;
        $blotter_entries = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);

        if ($this->site_domain->setting('show_blotter') && !empty($blotter_entries)) {
            foreach ($blotter_entries as $entry) {
                $blotter_data = array();
                $blotter_data['time'] = $this->domain->domainDateTime(intval($entry['time']))->format(
                    $this->site_domain->setting('blotter_time_format'));
                $blotter_data['text'] = $entry['text'];
                $this->render_data['blotter_entries'][] = $blotter_data;
            }

            $this->render_data['show_blotter'] = isset($this->render_data['blotter_entries']) &&
                !empty($this->render_data['blotter_entries']);
            $this->render_data['blotter_url'] = NEL_BASE_WEB_PATH . 'blotter.html';
        }

        $this->render_data['index_navigation_top'] = $this->domain->setting('index_nav_top');
        $this->render_data['index_navigation_bottom'] = $this->domain->setting('index_nav_bottom');
        $this->render_data['footer_form'] = true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['styles'] = $output_menu->styles([], true);

        if ($this->site_domain->setting('enable_captchas') && $this->domain->setting('use_report_captcha')) {
            $this->render_data['use_report_captcha'] = true;
            $output_native_captchas = new OutputCAPTCHA($this->domain, $this->write_mode);
            $this->render_data['report_captchas'] = $output_native_captchas->render(['area' => 'report'], false);
        }

        if (empty($threads)) {
            $index_format = $this->site_domain->setting('first_index_filename_format');
            $output = $this->doOutput($gen_data, sprintf($index_format, ($page)), $page, $data_only);

            if (!$this->write_mode) {
                return $output;
            }
        }

        $gen_data['index_rendering'] = true;
        $gen_data['context'] = 'index';
        $threads_on_page = 0;
        $this->render_data['threads'] = array();

        if ($page > 1) {
            $thread_offset = $this->domain->setting('threads_per_page') * ($page - 1);
        } else {
            $thread_offset = 0;
        }

        foreach ($threads as $thread) {
            if (is_null($thread) || !$thread->exists()) {
                continue;
            }

            if ($threads_done < $thread_offset) {
                $threads_done ++;
                continue;
            }

            $thread_input = array();
            $posts = $thread->getPosts();

            if (empty($posts)) {
                $threads_done ++;
                continue;
            }

            $output_post = new OutputPost($this->domain, $this->write_mode);
            $thread_input = array();
            $thread_input['thread_id'] = $thread->contentID()->getIDString();
            $thread_input['thread_expand_id'] = 'thread-expand-' . $thread->contentID()->getIDString();
            $thread_input['thread_corral_id'] = 'thread-corral-' . $thread->contentID()->getIDString();
            $index_replies = $this->domain->setting('index_thread_replies');

            if ($thread->getData('sticky')) {
                $index_replies = $this->domain->setting('index_sticky_replies');
            }

            $thread_input['omitted_count'] = $thread->getData('post_count') - $index_replies - 1; // Subtract 1 to account for OP
            $gen_data['abbreviate'] = $thread_input['omitted_count'] > 0;
            $thread_input['abbreviate'] = $gen_data['abbreviate'];
            $abbreviate_start = $thread->getData('post_count') - $index_replies;

            if ($this->session->inModmode($this->domain) && !$this->write_mode) {
                $output_modmode_headers = new OutputModmodeLinks($this->domain, $this->write_mode);
                $thread_input['thread_modmode_options'] = $output_modmode_headers->thread($thread);
                $thread_input['post_modmode_options'] = $output_modmode_headers->post($thread->firstPost());
            }

            $options = $this->threadHeaders($thread, $thread->firstPost(), $gen_data, $thread->contentID()->threadID());
            $thread_input['thread_options'] = $options['thread'];
            $post_counter = 1;

            foreach ($posts as $post) {
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

            if ($threads_on_page === $this->domain->setting('threads_per_page') || $threads_done === $thread_count) {
                $index_format = ($page === 1) ? $this->site_domain->setting('first_index_filename_format') : $this->site_domain->setting(
                    'index_filename_format');
                $this->render_data['pagination'] = $this->indexNavigation($page, $page_count);
                $output = $this->doOutput($gen_data, sprintf($index_format, ($page)), $page, $data_only);

                if (!$this->write_mode) {
                    return $output;
                }

                $threads_on_page = 0;
                $this->render_data['threads'] = array();
                $this->timer->reset();
                $this->timer->start();
                $page ++;
            }
        }

        if (NEL_ENABLE_JSON_API) {
            $catalog_json = new CatalogJSON($this->domain, $page);
            $json_filename = 'catalog' . NEL_JSON_EXT;
            $this->file_handler->writeFile($this->domain->reference('base_path') . $json_filename,
                $catalog_json->getJSON(true));
        }

        return $output;
    }

    private function indexNavigation(int $page, int $page_count)
    {
        if (!$this->write_mode) {
            $query_string = $this->session->inModmode($this->domain) ? '?modmode' : '';
            $first_index = nel_build_router_url([$this->domain->uri()], true) . $query_string;
            $index = nel_build_router_url([$this->domain->uri()], true) . '%d' . $query_string;
        } else {
            $first_index = $this->site_domain->setting('first_index_filename_format') . NEL_PAGE_EXT;
            $index = $this->site_domain->setting('index_filename_format') . NEL_PAGE_EXT;
        }

        $pagination_object = new Pagination();
        $pagination_object->setPrevious(_gettext('Previous'));
        $pagination_object->setNext(_gettext('Next'));
        $pagination_object->setPage('%d', $index);
        $pagination_object->setFirst('%d', $first_index);
        $pagination_object->setLast('%d', $index);
        return $pagination_object->generateNumerical(1, $page_count, $page);
    }

    private function doOutput(array $gen_data, string $index_basename, int $page, bool $data_only)
    {
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->board([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);

        if ($this->write_mode) {
            $this->file_handler->writeFile($this->domain->reference('base_path') . $index_basename . NEL_PAGE_EXT,
                $output);

            if (NEL_ENABLE_JSON_API) {
                $index_json = new IndexJSON($this->domain, $page);
                $json_filename = $page . NEL_JSON_EXT;
                $this->file_handler->writeFile($this->domain->reference('base_path') . $json_filename,
                    $index_json->getJSON(true));

                $threadlist_json = new ThreadlistJSON($this->domain);
                $json_filename = 'threads' . NEL_JSON_EXT;
                $this->file_handler->writeFile($this->domain->reference('base_path') . $json_filename,
                    $threadlist_json->getJSON(true));
            }
        } else {
            echo $output;
        }

        return $output;
    }

    private function threadHeaders(Thread $thread, Post $post, array $gen_data, int $in_thread_number): array
    {
        $thread_headers = array();
        $options = array();
        $this->render_data['headers'] = array();
        $post_content_id = $post->contentID();
        $thread_headers['thread_content_id'] = $thread->contentID()->getIDString();
        $thread_headers['post_content_id'] = $post_content_id->getIDString();
        $thread_headers['index_render'] = true;
        $output_content_links = new OutputContentLinks($this->domain, $this->write_mode);
        $options['thread'] = $output_content_links->thread($thread, $gen_data);
        $this->render_data['headers']['thread_headers'] = $thread_headers;
        return $options;
    }
}