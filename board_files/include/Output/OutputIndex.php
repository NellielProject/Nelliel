<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputIndex extends OutputCore
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
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $session = new \Nelliel\Session();
        $write = ($parameters['write']) ?? false;
        $thread_id = ($parameters['thread_id']) ?? 0;
        $dotdot = ($write) ? '../' : '';
        $site_domain = new \Nelliel\DomainSite($this->database);
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);

        if ($session->isActive() && !$write)
        {
            $manage_headers['header'] = _gettext('Moderator Mode');
            $manage_headers['sub_header'] = _gettext('View Index');
            $this->render_data['header'] = $output_header->render(
                    ['header_type' => 'board', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        }
        else
        {
            $this->render_data['header'] = $output_header->render(
                    ['header_type' => 'board', 'dotdot' => $dotdot, 'ignore_session' => true], true);
        }

        $result = $this->database->query(
                'SELECT * FROM "' . $this->domain->reference('threads_table') .
                '" WHERE "archive_status" = 0 ORDER BY "sticky" DESC, "last_bump_time" DESC, "last_bump_time_milli" DESC');
        $thread_list = $result->fetchAll(PDO::FETCH_ASSOC);
        $thread_count = count($thread_list);
        $gen_data['index']['thread_count'] = $thread_count;
        $output_posting_form = new OutputPostingForm($this->domain);
        $this->render_data['posting_form'] = $output_posting_form->render(['dotdot' => $dotdot, 'response_to' => 0],
                true);

        if (empty($thread_list))
        {
            $this->render_data['catalog_url'] = 'catalog.html';
            $output_footer = new OutputFooter($this->domain);
            $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => true], true);
            $output = $this->output('/index/index_page', $data_only, true);

            if ($write)
            {
                $this->file_handler->writeFile($this->domain->reference('board_path') . MAIN_INDEX . PAGE_EXT, $output,
                        FILE_PERM);
                $json_index->writeStoredData($this->domain->reference('board_path'), sprintf('index-%d', $page + 1));
            }
            else
            {
                echo $output;
            }

            return $output;
        }

        $post_counter = 0;
        $gen_data['index_rendering'] = true;
        $json_index = new \Nelliel\API\JSON\JSONIndex($this->domain, $this->file_handler);
        $this->render_data['catalog_url'] = 'catalog.html';
        $this->render_data['form_action'] = $dotdot . MAIN_SCRIPT . '?module=threads&board_id=' . $this->domain->id();
        $index_format = $site_domain->setting('index_filename_format');
        $page = 1;
        $page_count = (int) ceil($thread_count / $this->domain->setting('threads_per_page'));
        $threads_on_page = 0;
        $timer_offset = $this->endTimer(false);

        foreach ($thread_list as $thread_data)
        {
            $this->startTimer($timer_offset);
            $thread_input = array();
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
            $treeline = $this->database->executePreparedFetchAll($prepared, [$thread_data['thread_id']],
                    PDO::FETCH_ASSOC);

            $output_post = new OutputPost($this->domain);
            $json_thread = new \Nelliel\API\JSON\JSONThread($this->domain, $this->file_handler);
            $thread_content_id = \Nelliel\ContentID::createIDString($thread_data['thread_id']);
            $thread_input['op_post'] = '';
            $thread_input['thread_posts'] = '';
            $thread_input['thread_id'] = $thread_content_id;
            $thread_input['thread_expand_id'] = 'thread-expand-' . $thread_content_id;
            $thread_input['thread_corral_id'] = 'thread-' . $thread_content_id;
            $thread_input['omitted_count'] = $thread_data['post_count'] - $this->domain->setting('abbreviate_thread');
            $gen_data['abbreviate'] = $thread_data['post_count'] > $this->domain->setting('abbreviate_thread');
            $thread_input['abbreviate'] = $gen_data['abbreviate'];
            $abbreviate_start = $thread_data['post_count'] - ($this->domain->setting('abbreviate_thread') - 1);
            $post_counter = 0;

            foreach ($treeline as $post_data)
            {
                $json_post = new \Nelliel\API\JSON\JSONPost($this->domain, $this->file_handler);
                $json_instances['post'] = $json_post;
                $parameters = ['thread_data' => $thread_data, 'dotdot' => $dotdot, 'post_data' => $post_data,
                    'gen_data' => $gen_data, 'json_instances' => $json_instances];

                if ($post_data['op'] == 1)
                {
                    $thread_input['op_post'] = $output_post->render($parameters, true);
                    $json_thread->addPostData($json_post->retrieveData());
                }
                else
                {
                    if ($post_counter >= $abbreviate_start)
                    {
                        $thread_input['thread_posts'][] = $output_post->render($parameters, true);
                        $json_thread->addPostData($json_post->retrieveData());
                    }
                }

                $post_counter ++;
            }

            $this->render_data['threads'][] = $thread_input;
            $threads_on_page ++;

            if ($threads_on_page >= $this->domain->setting('threads_per_page'))
            {
                $json_index->addThreadData($json_thread->retrieveData());
                $output_menu = new OutputMenu($this->domain);
                $this->render_data['nav_elements'] = $output_menu->render(
                        ['menu' => 'index_navigation', 'page' => $page, 'index_format' => $index_format,
                            'page_count' => $page_count], true);
                $output_footer = new OutputFooter($this->domain);
                $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => true],
                        true);
                $output = $this->output('index/index_page', $data_only, true);
                $index_filename = ($page == 1) ? 'index' . PAGE_EXT : sprintf($index_format, ($page)) . PAGE_EXT;

                if ($write)
                {
                    $this->file_handler->writeFile($this->domain->reference('board_path') . $index_filename, $output,
                            FILE_PERM, true);
                    $json_index->storeData($json_index->prepareData($gen_data['index']), 'index');
                    $json_index->writeStoredData($this->domain->reference('board_path'), sprintf('index-%d', $page));
                }
                else
                {
                    echo $output;
                    return $output;
                }

                $threads_on_page = 0;
                $this->render_data['threads'] = array();
                $page ++;
            }
        }
    }
}