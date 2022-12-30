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

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('index/index');
        $this->render_data['page_language'] = $this->domain->locale();
        $sfw = $parameters['sfw'] ?? false;
        $uri = $sfw ? $this->site_domain->setting('sfw_overboard_uri') : $this->site_domain->setting('overboard_uri');
        $allow_nsfl = $this->site_domain->setting('nsfl_on_overboard');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->overboard(['uri' => $uri, 'sfw' => $sfw], true);
        $overboard = new Overboard($this->database);
        $threads = $overboard->getThreads($sfw ? 'sfw' : 'all');
        $thread_count = count($threads);
        $threads_done = 0;
        $gen_data = array();
        $gen_data['index']['thread_count'] = $thread_count;
        $gen_data['index_rendering'] = true;
        $this->render_data['hide_post_select'] = true;
        $this->render_data['hide_file_select'] = true;
        $this->render_data['show_styles'] = true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['styles'] = $output_menu->styles([], true);
        $this->render_data['overboard'] = true;
        $threads_on_page = 0;

        for ($i = 0; $i <= $thread_count; $i ++) {
            if ($threads_on_page >= $this->site_domain->setting('overboard_threads') || $i === $thread_count) {
                $this->render_data['index_navigation'] = false;
                $output_footer = new OutputFooter($this->site_domain, $this->write_mode);
                $this->render_data['footer'] = $output_footer->render([], true);
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
            $board_safety_level = $thread->domain()->setting('safety_level');

            if ($sfw && $board_safety_level !== 'SFW') {
                continue;
            }

            if ($board_safety_level === 'NSFL' && !$allow_nsfl) {
                continue;
            }

            $thread_input = array();
            $output_post = new OutputPost($thread_domain, $this->write_mode);
            $thread_input = array();
            $thread_input['board_id'] = $thread->domain()->id();
            $thread_input['board_url'] = NEL_BASE_WEB_PATH . $thread_domain->id() . '/';
            $thread_input['board_safety'] = $thread_domain->setting('safety_level');
            $thread_input['thread_id'] = $thread->data('thread_id');
            $thread_input['thread_expand_id'] = 'thread-expand-' . $thread->contentID()->getIDString();
            $thread_input['thread_corral_id'] = 'thread-corral-' . $thread->contentID()->getIDString();

            if ($sfw) {
                $index_replies = $this->site_domain->setting('sfw_overboard_thread_replies');
            } else {
                $index_replies = $this->site_domain->setting('overboard_thread_replies');
            }

            $thread_input['omitted_count'] = $thread->data('post_count') - $index_replies - 1; // Subtract 1 to account for OP
            $gen_data['abbreviate'] = $thread_input['omitted_count'] > 0;
            $thread_input['abbreviate'] = $gen_data['abbreviate'];
            $abbreviate_start = $thread->data('post_count') - $index_replies;
            $post_counter = 1;

            foreach ($thread->getPosts() as $post) {
                $post_content_id = new ContentID(
                    ContentID::createIDString($thread->data('thread_id'), $post->data('post_number')));
                $post = $post_content_id->getInstanceFromID($thread_domain);
                $parameters = ['gen_data' => $gen_data, 'in_thread_number' => $post_counter];

                if ($post->data('op') == 1) {
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
}