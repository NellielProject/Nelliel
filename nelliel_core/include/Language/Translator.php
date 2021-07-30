<?php
declare(strict_types = 1);

namespace Nelliel\Language;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Render\RenderCoreDOM;

class Translator
{
    private $domain;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->dom_render_core = new RenderCoreDOM();
    }

    public function translateHTML(string $html, bool $return_dom = false)
    {
        $dom_document = $this->dom_render_core->newDOMDocument();
        $template_id = md5(random_bytes(8));
        $template_contents = $this->dom_render_core->loadTemplateFromString($template_id, $html);
        $this->dom_render_core->loadDOMFromTemplate($dom_document, $template_contents);
        $this->translateDOM($dom_document);

        if ($return_dom)
        {
            return $dom_document;
        }

        return $this->dom_render_core->renderFromDOM($dom_document, $template_id);
    }

    public function translateDOM($dom)
    {
        $content_node_list = $dom->getElementsByAttributeName('data-gettext');

        foreach ($content_node_list as $node)
        {
            if ($node->getAttribute('data-gettext') === '')
            {
                $this->gettextContent($node);
            }
            else
            {
                $this->gettextAttribute($node);
            }

            $node->removeAttribute('data-gettext');
        }
    }

    private function gettextAttribute($node)
    {
        $attribute_list = explode('|', $node->getAttribute('data-gettext'));

        foreach ($attribute_list as $attribute_name)
        {
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