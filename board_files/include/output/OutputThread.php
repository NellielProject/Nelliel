<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputThread extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->database = $domain->database();
        $this->domain = $domain;
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $write = ($parameters['write']) ?? false;
        $thread_id = ($parameters['thread_id']) ?? 0;
        $dotdot = ($write) ? '../../../' : '';
        $command = ($parameters['command']) ?? 'view-thread';
        $thread_content_id = \Nelliel\ContentID::createIDString($thread_id);

        // Temp
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startRenderTimer();

        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('thread');
        $render_input['form_action'] = $dotdot . MAIN_SCRIPT . '?module=threads&board_id=' . $this->domain->id();
        $prepared = $this->database->prepare('SELECT * FROM "' . $this->domain->reference('threads_table') . '" WHERE "thread_id" = ?');
        $thread_data = $this->database->executePreparedFetch($prepared, [$thread_id], PDO::FETCH_ASSOC);

        if(empty($thread_data))
        {
            return;
        }

        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
        $treeline = $this->database->executePreparedFetchAll($prepared, [$thread_id], PDO::FETCH_ASSOC);

        if (empty($treeline))
        {
            return;
        }

        $json_thread = new \Nelliel\API\JSON\JSONThread($this->domain, $this->file_handler);
        $json_thread->storeData($json_thread->prepareData($thread_data), 'thread');
        $json_content = new \Nelliel\API\JSON\JSONContent($this->domain, $this->file_handler);
        $json_instances = ['thread' => $json_thread, 'content' => $json_content];
        $post_counter = 0;
        $gen_data['posts_ending'] = false;
        $gen_data['index_rendering'] = false;
        $gen_data['abbreviate'] = false;
        $hr_added = false;
        $total_posts = $thread_data['post_count'];
        $render_input['abbreviate'] = $total_posts > $this->domain->setting('abbreviate_thread');
        $thread_render = '';
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $output_header->render(['header_type' => 'board', 'dotdot' => $dotdot]);
        $output_posting_form = new \Nelliel\Output\OutputPostingForm($this->domain);
        $output_posting_form->render(['dotdot' => $dotdot, 'response_to' => $thread_id]);
        $thread_render .= $this->render_instance->outputRenderSet();
        $this->render_instance->clearRenderSet();
        $output_post = new \Nelliel\Output\OutputPost($this->domain);
        $render_input['thread_posts'] = '';
        $render_input['thread_id'] = $thread_content_id;
        $render_input['thread_expand_id'] = 'thread-expand-' . $thread_content_id;
        $render_input['thread_corral_id'] = 'thread-' . $thread_content_id;
        $render_input['board_id'] = $this->domain->id();

        foreach($treeline as $post_data)
        {
            $json_post = new \Nelliel\API\JSON\JSONPost($this->domain, $this->file_handler);
            $json_instances['post'] = $json_post;
            $parameters = ['thread_data' => $thread_data, 'dotdot' => $dotdot, 'post_data' => $post_data, 'gen_data' => $gen_data, 'json_instances' => $json_instances];
            $render_input['thread_posts'] .= $output_post->render($parameters);
            $json_thread->addPostData($json_post->retrieveData());
        }

        $thread_render .= $render_instance->render('thread/thread', $render_input);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $output_footer->render(['dotdot' => $dotdot, 'styles' => true]);
        $thread_render .= $this->render_instance->outputRenderSet();

        if ($write)
        {
            $this->file_handler->writeFile($this->domain->reference('page_path') . $thread_id . '/thread-' . $thread_id . '.html',
                    $thread_render, FILE_PERM, true);
            /*$file_handler->writeFile(
                    $domain->reference('page_path') . $thread_id . '/thread-' . $thread_id . '-expand.html',
                    $domain->renderInstance()->outputRenderSet('expand'), FILE_PERM, true);*/
            /*$file_handler->writeFile(
                    $domain->reference('page_path') . $thread_id . '/thread-' . $thread_id . '-collapse.html',
                    $domain->renderInstance()->outputRenderSet('collapse'), FILE_PERM, true);*/
            $json_thread->writeStoredData($this->domain->reference('page_path') . $thread_id . '/',
                    sprintf('thread-%d', $thread_id));
        }
        else
        {
            switch ($command)
            {
                case 'view-thread':
                    echo $thread_render;
                    break;

                case 'expand-thread':
                    //echo $domain->renderInstance()->outputRenderSet('expand');
                    break;

                case 'collapse-thread':
                    //echo $domain->renderInstance()->outputRenderSet('collapse');
                    break;

                default:
                    echo $thread_render;
            }

            nel_clean_exit();
        }
    }
}