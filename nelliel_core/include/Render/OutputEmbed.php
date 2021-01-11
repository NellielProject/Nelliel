<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;

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
        $session = new \Nelliel\Account\Session();
        $file_content_id = new ContentID();
        $file_content_id->changeThreadID($post_data['parent_thread']);
        $file_content_id->changePostID($post_data['post_number']);
        $file_content_id->changeOrderID($file['content_order']);
        $this->render_data['is_embed'] = true;
        $this->render_data['file_container_id'] = 'file-container-' . $file_content_id->getIDString();
        $this->render_data['single_multiple'] = $multiple ? 'multiple' : 'single';
        $this->render_data['file_content_id'] = $file_content_id->getIDString();

        if ($session->inModmode($this->domain))
        {
            $this->render_data['in_modmode'] = true;
            $this->render_data['delete_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                    '&actions=delete&content-id=' . $file_content_id->getIDString() . '&modmode=true&goback=true';
        }

        $this->render_data['embed_url'] = $file['embed_url'];
        //$this->render_data['embed_width'] = 560; // NOTE temp for testing
        //$this->render_data['embed_height'] = 315;
        $output = $this->output('thread/file_info', $data_only, true, $this->render_data);
        return $output;
    }
}