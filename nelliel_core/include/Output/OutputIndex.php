<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Content\ContentID;
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
        $session = new \Nelliel\Account\Session($this->domain);
        $write = $parameters['write'] ?? false;
        $thread_id = $parameters['thread_id'] ?? 0;
        $dotdot = ($write) ? '../' : '';
        $site_domain = new \Nelliel\DomainSite($this->database);
        $json_index = new \Nelliel\API\JSON\JSONIndex($this->domain, $this->file_handler);
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
        $threads_done = 0;
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
                $this->file_handler->writeFile($this->domain->reference('board_path') . NEL_MAIN_INDEX . NEL_PAGE_EXT, $output,
                        NEL_FILES_PERM);
                $json_index->writeStoredData($this->domain->reference('board_path'), 'index-1');
            }
            else
            {
                echo $output;
            }

            return $output;
        }

        $gen_data['index_rendering'] = true;
        $this->render_data['catalog_url'] = 'catalog.html';
        $this->render_data['form_action'] = $dotdot . NEL_MAIN_SCRIPT . '?module=threads&board_id=' . $this->domain->id();
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
            $thread_content_id = ContentID::createIDString(intval($thread_data['thread_id']));
            $thread_input = array();
            $thread_input['thread_id'] = $thread_content_id;
            $thread_input['thread_expand_id'] = 'thread-expand-' . $thread_content_id;
            $thread_input['thread_corral_id'] = 'thread-' . $thread_content_id;
            $thread_input['omitted_count'] = $thread_data['post_count'] - $this->domain->setting('abbreviate_thread');
            $gen_data['abbreviate'] = $thread_data['post_count'] > $this->domain->setting('abbreviate_thread');
            $thread_input['abbreviate'] = $gen_data['abbreviate'];
            $abbreviate_start = $thread_data['post_count'] - ($this->domain->setting('abbreviate_thread') - 1);
            $post_counter = 1;

            foreach ($treeline as $post_data)
            {
                $json_post = new \Nelliel\API\JSON\JSONPost($this->domain, $this->file_handler);
                $json_instances['post'] = $json_post;
                $parameters = ['thread_data' => $thread_data, 'dotdot' => $dotdot, 'post_data' => $post_data,
                    'gen_data' => $gen_data, 'json_instances' => $json_instances, 'in_thread_number' => $post_counter];

                if ($session->isActive() && $write)
                {
                    $parameters['ignore_session'] = true;
                }

                if ($post_data['op'] == 1)
                {
                    $thread_input['op_post'] = $output_post->render($parameters, true);
                    $json_thread->addPostData($json_post->retrieveData());
                }
                else
                {
                    if ($post_counter > $abbreviate_start)
                    {
                        $thread_input['thread_posts'][] = $output_post->render($parameters, true);
                        $json_thread->addPostData($json_post->retrieveData());
                    }
                }

                $post_counter ++;
            }

            $this->render_data['threads'][] = $thread_input;
            $threads_on_page ++;
            $threads_done++;

            if ($threads_on_page >= $this->domain->setting('threads_per_page') || $threads_done == $thread_count)
            {
                $json_index->addThreadData($json_thread->retrieveData());
                $this->render_data['pagination'] = $this->indexNavigation($page, $page_count, $index_format);
                $this->render_data['use_report_captcha'] = $this->domain->setting('use_report_captcha');
                $this->render_data['captcha_gen_url'] = $dotdot . NEL_MAIN_SCRIPT . '?module=captcha&action=get';
                $this->render_data['captcha_regen_url'] = $dotdot . NEL_MAIN_SCRIPT . '?module=captcha&action=generate&no-display';
                $this->render_data['use_report_recaptcha'] = $this->domain->setting('use_report_recaptcha');
                $this->render_data['recaptcha_sitekey'] = $this->site_domain->setting('recaptcha_site_key');
                $output_footer = new OutputFooter($this->domain);
                $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => true],
                        true);
                $output = $this->output('index/index_page', $data_only, true);
                $index_filename = ($page == 1) ? 'index' . NEL_PAGE_EXT : sprintf($index_format, ($page)) . NEL_PAGE_EXT;

                if ($write)
                {
                    $this->file_handler->writeFile($this->domain->reference('board_path') . $index_filename, $output,
                            NEL_FILES_PERM, true);
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


    private function indexNavigation(int $page, int $page_count, $page_format)
    {
        $pagination_object = new \Nelliel\Pagination();
        $pagination_object->setPrevious(_gettext('Previous'));
        $pagination_object->setNext(_gettext('Next'));
        $pagination_object->setPage('%d', $page_format);
        $pagination_object->setFirst('%d', 'index' . NEL_PAGE_EXT);
        $pagination_object->setLast('%d', $page_format);
        return $pagination_object->generateNumerical(1, $page_count, $page);
    }
}