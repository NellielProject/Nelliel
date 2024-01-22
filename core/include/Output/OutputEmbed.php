<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Post;
use Nelliel\Content\Upload;
use Nelliel\Domains\Domain;

class OutputEmbed extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(Upload $embed, Post $post, array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $catalog = $parameters['catalog'] ?? false;
        $first = $parameters['first'] ?? false;
        $multiple = $parameters['multiple'] ?? false;
        $this->render_data['is_embed'] = true;
        $this->render_data['embed_container_id'] = 'upload-container-' . $embed->contentID()->getIDString();
        $this->render_data['embed_content_id'] = $embed->contentID()->getIDString();
        $this->render_data['original_url'] = $embed->getData('embed_url');
        $this->render_data['display_url'] = $embed->getData('embed_url');
        $this->render_data['embed_url'] = $embed->parseEmbedURL($embed->getData('embed_url'), false);
        $this->render_data['in_modmode'] = $this->session->inModmode($this->domain) && !$this->write_mode;

        if (utf8_strlen($this->render_data['display_url']) > $this->domain->setting('embed_url_display_length')) {
            $this->render_data['display_url'] = utf8_substr($this->render_data['display_url'], 0,
                $this->domain->setting('embed_url_display_length')) . '...';
        }

        if ($this->session->inModmode($this->domain)) {
            $output_modmode_headers = new OutputModmodeHeaders($this->domain, $this->write_mode);
            $this->render_data['embed_modmode_options'] = $output_modmode_headers->upload($embed);
        }

        if ($catalog) {
            $first_full_size = $first && $this->domain->setting('catalog_first_preview_full_size');
            $max_width = ($multiple && !$first_full_size) ? $this->domain->setting(
                'catalog_max_multi_preview_display_width') : $this->domain->setting('catalog_max_preview_display_width');
            $max_height = ($multiple && !$first_full_size) ? $this->domain->setting(
                'catalog_max_multi_preview_display_height') : $this->domain->setting(
                'catalog_max_preview_display_height');
        } else {
            if ($post->getData('op')) {
                $max_width = ($multiple) ? $this->domain->setting('max_op_multi_display_width') : $this->domain->setting(
                    'max_op_embed_display_width');
                $max_height = ($multiple) ? $this->domain->setting('max_op_multi_display_height') : $this->domain->setting(
                    'max_op_embed_display_height');
            } else {
                $max_width = ($multiple) ? $this->domain->setting('max_reply_multi_display_width') : $this->domain->setting(
                    'max_reply_embed_display_width');
                $max_height = ($multiple) ? $this->domain->setting('max_reply_multi_display_height') : $this->domain->setting(
                    'max_reply_embed_display_height');
            }
        }

        $this->render_data['max_preview_width'] = $max_width;
        $this->render_data['max_preview_height'] = $max_height;

        $this->render_data['content_links_hide_embed']['content_id'] = $embed->contentID()->getIDString();
        $this->render_data['embed_options'][] = $this->render_data['content_links_hide_embed'];

        if ($embed->getData('deleted')) {
            if(nel_is_absolute_url($this->domain->setting('image_deleted_embed'))) {
                $this->render_data['deleted_url'] = $this->domain->setting('image_deleted_embed');
            } else {
                $this->render_data['deleted_url'] = NEL_ASSETS_WEB_PATH . $this->domain->setting('image_deleted_embed');
            }
        }

        $output = $this->output('thread/file_info', $data_only, true, $this->render_data);
        return $output;
    }
}