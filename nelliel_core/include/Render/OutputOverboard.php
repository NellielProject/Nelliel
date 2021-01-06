<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Timer;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Content\ContentID;
use PDO;

class OutputOverboard extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->render_data['page_language'] = $this->domain->locale();
        $timer = new Timer();
        $timer->start();
        $this->render_data['show_stats']['render_timer'] = $this->timerTotalFunction($timer);
        $sfw = $parameters['sfw'] ?? false;
        $prefix = ($sfw) ? 'sfw_' : '';
        $json_index = new \Nelliel\API\JSON\JSONIndex($this->site_domain, $this->file_handler);
        $output_head = new OutputHead($this->site_domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->site_domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_OVERBOARD_TABLE . '" ORDER BY "last_bump_time" DESC, "last_bump_time_milli" DESC');
        $thread_list = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
        $thread_count = count($thread_list);
        $threads_done = 0;
        $gen_data = array();
        $gen_data['index']['thread_count'] = $thread_count;
        $gen_data['index_rendering'] = true;
        $threads_on_page = 0;
        $timer_offset = $this->endTimer(false);

        for ($i = 0; $i <= $thread_count; ++ $i)
        {
            if ($threads_on_page >= $this->site_domain->setting('overboard_threads') || $i === $thread_count)
            {
                $this->render_data['index_navigation'] = false;
                $this->render_data['footer_form'] = true;
                $output_footer = new OutputFooter($this->site_domain, $this->write_mode);
                $this->render_data['footer'] = $output_footer->render(['show_styles' => true], true);
                $output = $this->output('overboard', $data_only, true, $this->render_data);
                $index_filename = 'index' . NEL_PAGE_EXT;

                if ($this->write_mode)
                {
                    $this->file_handler->writeFile(
                            NEL_BASE_PATH . $this->site_domain->setting($prefix . 'overboard_uri') . '/' .
                            $index_filename, $output, NEL_FILES_PERM, true);
                    $json_index->storeData($json_index->prepareData($gen_data['index']), 'index');
                    $json_index->writeStoredData(
                            NEL_BASE_PATH . $this->site_domain->setting($prefix . 'overboard_uri') . '/', 'index');
                }
                else
                {
                    echo $output;
                }

                return $output;
            }

            $thread = $thread_list[$i];

            if ($sfw && $thread['safety_level'] !== 'SFW')
            {
                continue;
            }

            $thread_domain = new DomainBoard($thread['board_id'], $this->database);
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . $thread_domain->reference('threads_table') . '" WHERE "thread_id" = ?');
            $thread_data = $this->database->executePreparedFetch($prepared, [$thread['thread_id']], PDO::FETCH_ASSOC);

            if (empty($thread_data))
            {
                continue;
            }

            $this->startTimer($timer_offset);
            $thread_input = array();
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . $thread_domain->reference('posts_table') .
                    '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
            $treeline = $this->database->executePreparedFetchAll($prepared, [$thread['thread_id']], PDO::FETCH_ASSOC);
            $output_post = new OutputPost($thread_domain, $this->write_mode);
            $json_thread = new \Nelliel\API\JSON\JSONThread($thread_domain, $this->file_handler);
            $thread_content_id = ContentID::createIDString(intval($thread_data['thread_id']));
            $thread_input = array();
            $thread_input['thread_id'] = $thread_content_id;
            $thread_input['thread_expand_id'] = 'thread-expand-' . $thread_content_id;
            $thread_input['thread_corral_id'] = 'thread-corral-' . $thread_content_id;
            $thread_input['omitted_count'] = $thread_data['post_count'] - 5;
            $gen_data['abbreviate'] = $thread_data['post_count'] > 5;
            $thread_input['abbreviate'] = $gen_data['abbreviate'];
            $abbreviate_start = $thread_data['post_count'] - (5 - 1);
            $post_counter = 1;

            foreach ($treeline as $post_data)
            {
                $json_post = new \Nelliel\API\JSON\JSONPost($thread_domain, $this->file_handler);
                $json_instances['post'] = $json_post;
                $parameters = ['thread_data' => $thread_data, 'post_data' => $post_data, 'gen_data' => $gen_data,
                    'json_instances' => $json_instances, 'in_thread_number' => $post_counter];

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

            $json_index->addThreadData($json_thread->retrieveData());
            $this->render_data['threads'][] = $thread_input;
            $threads_on_page ++;
            $threads_done ++;
        }
    }
}