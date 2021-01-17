<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use PDO;

class OutputEmbed extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $post_data = $parameters['post_data'] ?? array();
        $file = $parameters['file_data'] ?? array();
        $multiple = $post_data['content_count'] > 1;
        $json_post = $parameters['json_instances']['post'];
        $json_content = $parameters['json_instances']['content'];
        $json_post->addContentData($json_content->prepareData($file));
        $file_content_id = new ContentID();
        $file_content_id->changeThreadID($post_data['parent_thread']);
        $file_content_id->changePostID($post_data['post_number']);
        $file_content_id->changeOrderID($file['content_order']);
        $this->render_data['is_embed'] = true;
        $this->render_data['embed_container_id'] = 'embed-container-' . $file_content_id->getIDString();
        $this->render_data['single_multiple'] = $multiple ? 'multiple' : 'single';
        $this->render_data['embed_content_id'] = $file_content_id->getIDString();
        $this->render_data['original_url'] = $file['embed_url'];
        $this->render_data['display_url'] = $file['embed_url'];
        $embed_regexes = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_EMBEDS_TABLE . '" WHERE "enabled" = 1', PDO::FETCH_ASSOC);

        if ($embed_regexes !== false)
        {
            foreach ($embed_regexes as $regex)
            {
                if (preg_match($regex['data_regex'], $file['embed_url']) === 1)
                {
                    $embed_url = preg_replace($regex['data_regex'], $regex['embed_url'], $file['embed_url']);
                    $this->render_data['embed_url'] = $embed_url;
                    break;
                }
            }
        }

        if ($this->session->inModmode($this->domain))
        {
            $this->render_data['in_modmode'] = true;
            $this->render_data['delete_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                    '&actions=delete&content-id=' . $file_content_id->getIDString() . '&modmode=true&goback=true';
        }

        $this->render_data['max_preview_width'] = ($multiple) ? $this->domain->setting('max_multi_display_width') : $this->domain->setting('max_display_width');
        $this->render_data['max_preview_height'] = ($multiple) ? $this->domain->setting('max_multi_display_height') : $this->domain->setting('max_display_height');
        $output = $this->output('thread/file_info', $data_only, true, $this->render_data);
        return $output;
    }
}