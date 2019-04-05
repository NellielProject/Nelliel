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

    public function render(array $parameters = array())
    {
        $write = ($parameters['write']) ?? false;
        $thread_id = ($parameters['thread_id']) ?? 0;
        $dotdot = ($write) ? '../' : '';
        $site_domain = new \Nelliel\DomainSite($this->database);

        $this->render_core->startTimer();
        $result = $this->database->query(
                'SELECT * FROM "' . $this->domain->reference('threads_table') .
                '" WHERE "archive_status" = 0 ORDER BY "sticky" DESC, "last_bump_time" DESC, "last_bump_time_milli" DESC');
        $thread_list = $result->fetchAll(PDO::FETCH_ASSOC);
        $thread_count = count($thread_list);
        $gen_data['index']['thread_count'] = $thread_count;
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $this->render_core->appendToOutput($output_header->render(['header_type' => 'board', 'dotdot' => $dotdot]),
                'header');
        $output_posting_form = new \Nelliel\Output\OutputPostingForm($this->domain);
        $this->render_core->appendToOutput($output_posting_form->render(['dotdot' => $dotdot, 'response_to' => 0]),
                'header');

        if (empty($thread_list))
        {
            $render_input['catalog_url'] = 'catalog.html';
            $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
            $this->render_core->appendToOutput(
                    $output_footer->render(['dotdot' => $dotdot, 'generate_styles' => true]));

            if ($write)
            {
                $this->file_handler->writeFile($this->domain->reference('board_path') . MAIN_INDEX . PAGE_EXT,
                        $this->render_core->getOutput(), FILE_PERM);
                $json_index->writeStoredData($this->domain->reference('board_path'), sprintf('index-%d', $page + 1));
            }
            else
            {
                echo $this->domain->renderInstance()->getOutput();
            }

            return;
        }

        $post_counter = 0;
        $gen_data['index_rendering'] = true;
        $json_index = new \Nelliel\API\JSON\JSONIndex($this->domain, $this->file_handler);
        $render_input['catalog_url'] = 'catalog.html';
        $render_input['form_action'] = $dotdot . MAIN_SCRIPT . '?module=threads&board_id=' . $this->domain->id();
        $index_format = $site_domain->setting('index_filename_format');
        $page = 1;
        $page_count = (int) ceil($thread_count / $this->domain->setting('threads_per_page'));
        $threads_on_page = 0;

        foreach ($thread_list as $thread_data)
        {
            $thread_input = array();
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
            $treeline = $this->database->executePreparedFetchAll($prepared, [$thread_data['thread_id']],
                    PDO::FETCH_ASSOC);

            $output_post = new \Nelliel\Output\OutputPost($this->domain);
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
                    $thread_input['op_post'] = $output_post->render($parameters);
                    $json_thread->addPostData($json_post->retrieveData());
                }
                else
                {
                    if ($post_counter >= $abbreviate_start)
                    {
                        $thread_input['thread_posts'] .= $output_post->render($parameters);
                        $json_thread->addPostData($json_post->retrieveData());
                    }
                }

                $post_counter ++;
            }

            $render_input['threads'][] = $thread_input;
            $threads_on_page ++;

            if ($threads_on_page >= $this->domain->setting('threads_per_page'))
            {
                $json_index->addThreadData($json_thread->retrieveData());

                // Set up the array of navigation elements
                $nav_elements = array();
                $previous = array();
                $previous['link_text'] = _gettext('Previous');
                $prev_filename = ($page - 1 == 1) ? 'index' : $index_format;
                $previous['linked'] = ($page != 1);
                $previous['index_url'] = ($page != 1) ? sprintf($prev_filename, ($page - 1)) . PAGE_EXT : '';
                $nav_elements[] = $previous;

                for ($i = 1; $i <= $page_count; $i ++)
                {
                    $index_entry = array();
                    $link_filename = ($i === 1) ? 'index' : $index_format;
                    $index_entry['linked'] = ($i != $page);
                    $index_entry['index_url'] = ($i != $page) ? sprintf($link_filename, $i) . PAGE_EXT : '';
                    $index_entry['link_text'] = $i;
                    $nav_elements[] = $index_entry;
                }

                $next = array();
                $next['linked'] = ($page != $page_count);
                $next['link_text'] = _gettext('Next');
                $next['index_url'] = ($page != $page_count) ? sprintf($index_format, ($page + 1)) . PAGE_EXT : '';
                $nav_elements[] = $next;
                $render_input['nav_elements'] = $nav_elements;

                $this->render_core->appendToOutput($this->render_core->getOutput('header'));
                $this->render_core->appendToOutput($this->render_core->renderFromTemplateFile('index', $render_input));
                $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
                $this->render_core->appendToOutput(
                        $output_footer->render(['dotdot' => $dotdot, 'generate_styles' => true]));
                $index_filename = ($page == 1) ? 'index' . PAGE_EXT : sprintf($index_format, ($page)) . PAGE_EXT;

                if ($write)
                {
                    $this->file_handler->writeFile($this->domain->reference('board_path') . $index_filename,
                            $this->render_core->getOutput(), FILE_PERM, true);
                    $json_index->storeData($json_index->prepareData($gen_data['index']), 'index');
                    $json_index->writeStoredData($this->domain->reference('board_path'), sprintf('index-%d', $page));
                }
                else
                {
                    $this->render_core->getOutput();
                    nel_clean_exit();
                }

                $threads_on_page = 0;
                $this->render_core->clearOutput();
                $render_input['threads'] = array();
                $page ++;
            }
        }
    }
}