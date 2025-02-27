<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\DomainBoard;

class OutputCatalog extends Output
{

    function __construct(DomainBoard $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('catalog/catalog');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['catalog_navigation_top'] = $this->domain->setting('catalog_nav_top');
        $this->render_data['catalog_navigation_bottom'] = $this->domain->setting('catalog_nav_bottom');
        $this->render_data['in_modmode'] = $this->session->inModmode($this->domain) && !$this->write_mode;

        if ($this->render_data['in_modmode']) {
            $manage_headers['header'] = _gettext('Moderator Mode');
            $manage_headers['sub_header'] = _gettext('View Catalog');
            $this->render_data['header'] = $output_header->board(['manage_headers' => $manage_headers], true);
        } else {
            $this->render_data['header'] = $output_header->board([], true);
        }

        $this->render_data['header']['name'] = _gettext('Catalog of') . ' ' . $this->render_data['header']['name'];
        $output_navigation = new OutputNavigationLinks($this->domain, $this->write_mode);
        $this->render_data['page_navigation'] = $output_navigation->boardPages(
            ['in_modmode' => $this->render_data['in_modmode'], 'display' => 'catalog'], $data_only);
        $thread_count = 1;

        foreach ($this->domain->getThreads(true, false) as $thread) {
            if (is_null($thread) || !$thread->exists()) {
                continue;
            }

            $thread_data = array();
            $post = $thread->firstPost();

            if ($this->session->inModmode($this->domain) && !$this->writeMode()) {
                $thread_data['open_url'] = $thread->getRoute(false, 'modmode');
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
        $this->render_data['footer'] = $output_footer->board([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);

        if ($this->write_mode) {
            $file = $this->domain->reference('base_path') . 'catalog.html';
            $this->file_handler->writeFile($file, $output);
        } else {
            echo $output;
        }

        return $output;
    }
}