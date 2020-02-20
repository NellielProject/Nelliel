<?php

namespace Nelliel\Language;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class Translator
{
    private $domain;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->dom_render_core = new \Nelliel\RenderCoreDOM();
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
        $content_node_list = $dom->getElementsByAttributeName('data-i18n');
        $attribute_node_list = $dom->getElementsByAttributeName('data-i18n-attributes');

        foreach ($attribute_node_list as $node)
        {
            $split_attribute = explode('|', $node->getAttribute('data-i18n-attributes'), 2);

            if ($split_attribute[0] === 'gettext')
            {
                $this->gettextAttribute($node);
                $node->removeAttribute('data-i18n-attributes');
            }
        }

        foreach ($content_node_list as $node)
        {
            if ($node->getAttribute('data-i18n') === 'gettext')
            {
                $this->gettextContent($node);
                $node->removeAttribute('data-i18n');
            }
        }
    }

    private function gettextAttribute($node)
    {
        $split_attribute = explode('|', $node->getAttribute('data-i18n-attributes'), 2);
        $attribute_list = explode(',', $split_attribute[1]);
        $new_text = '';

        foreach ($attribute_list as $attribute_name)
        {
            $attribute_name = trim($attribute_name);
            $attribute_value = $node->getAttribute($attribute_name);
            $new_text = _gettext($attribute_value);
            $attribute_node = $node->ownerDocument->createFullAttribute($attribute_name, $new_text, 'none');
            //$attribute_node->value = $new_text;
            $node->setAttributeNode($attribute_node);
        }
    }

    private function gettextContent($node)
    {
        $text = $node->getContent();
        $node->setContent(_gettext($text));
    }
}