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
        $multiple = $post->data('embed_count') > 1;
        $this->render_data['is_embed'] = true;
        $this->render_data['embed_container_id'] = 'embed-container-' . $embed->contentID()->getIDString();
        $this->render_data['embed_content_id'] = $embed->contentID()->getIDString();
        $this->render_data['original_url'] = $embed->data('embed_url');
        $this->render_data['display_url'] = $embed->data('embed_url');
        $this->render_data['embed_url'] = $embed->parseEmbedURL($embed->data('embed_url'), false);

        if (utf8_strlen($this->render_data['display_url']) > $this->domain->setting('embed_url_display_length')) {
            $this->render_data['display_url'] = utf8_substr($this->render_data['display_url'], 0,
                $this->domain->setting('embed_url_display_length')) . '...';
        }

        if ($this->session->inModmode($this->domain)) {
            $this->render_data['in_modmode'] = true;
            $this->render_data['delete_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                '&actions=delete&content-id=' . $embed->ContentID()->getIDString() . '&modmode=true&goback=true';
        }

        if ($catalog) {
            $this->render_data['max_preview_width'] = $this->domain->setting('max_catalog_display_width');
            $this->render_data['max_preview_height'] = $this->domain->setting('max_catalog_display_height');
            $multiple = false;
        } else {
            $this->render_data['max_preview_width'] = ($multiple) ? $this->domain->setting('max_multi_display_width') : $this->domain->setting(
                'max_embed_display_width');
            $this->render_data['max_preview_height'] = ($multiple) ? $this->domain->setting('max_multi_display_height') : $this->domain->setting(
                'max_embed_display_height');
        }

        if ($embed->data('deleted')) {
            $this->render_data['deleted_url'] = NEL_ASSETS_WEB_PATH . $this->domain->setting('image_deleted_embed');
        }

        $output = $this->output('thread/file_info', $data_only, true, $this->render_data);
        return $output;
    }
}