<?php
declare(strict_types = 1);

namespace Nelliel\Language;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Render\RenderCoreDOM;
use Nelliel\Utility\FileHandler;

class Translator
{

    function __construct(FileHandler $file_handler)
    {
        $this->dom_render_core = new RenderCoreDOM($file_handler);
    }

    public function translateHTML(string $html, bool $return_dom = false)
    {
        $dom_document = $this->dom_render_core->newDOMDocument();
        $template_id = md5(random_bytes(8));
        $template_contents = $this->dom_render_core->loadTemplateFromString($template_id, $html);
        $this->dom_render_core->loadDOMFromTemplate($dom_document, $template_contents);
        $this->translateDOM($dom_document);

        if ($return_dom) {
            return $dom_document;
        }

        return $this->dom_render_core->renderFromDOM($dom_document, $template_id);
    }

    public function translateDOM($dom)
    {
        $content_node_list = $dom->getElementsByAttributeName('data-i18n');
        $attribute_node_list = $dom->getElementsByAttributeName('data-i18n-attributes');

        foreach ($content_node_list as $node) {
            $this->gettextContent($node);
            $node->removeAttribute('data-i18n');
        }

        foreach ($attribute_node_list as $node) {
            $this->gettextAttribute($node);
            $node->removeAttribute('data-i18n-attributes');
        }
    }

    private function gettextAttribute($node)
    {
        $attribute_list = explode('|', $node->getAttribute('data-i18n-attributes'));

        foreach ($attribute_list as $attribute_name) {
            $attribute_name = trim($attribute_name);
            $attribute_value = $node->getAttribute($attribute_name);
            $node->modifyAttribute($attribute_name, _gettext($attribute_value));
        }
    }

    private function gettextContent($node)
    {
        $text = $node->getContent();
        $node->setContent(_gettext($text));
    }
}