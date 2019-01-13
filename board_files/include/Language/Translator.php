<?php

namespace Nelliel\Language;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Translator
{
    function __construct()
    {
    }

    public function translateDom($dom, $language = 'en_US')
    {
        $content_node_list = $dom->getElementsByAttributeName('data-i18n');
        $attribute_node_list = $dom->getElementsByAttributeName('data-i18n-attributes');

        foreach ($attribute_node_list as $node)
        {
            if ($node->getAttribute('data-i18n') === 'gettext')
            {
                $this->gettextAttribute($node);
            }

            $node->removeAttribute('data-i18n-attributes');
        }

        foreach ($content_node_list as $node)
        {
            if ($node->getAttribute('data-i18n') === 'gettext')
            {
                $this->gettextContent($node);
            }

            $node->removeAttribute('data-i18n');
        }
    }

    private function gettextAttribute($node)
    {
        $attribute_list = explode(',', $node->getAttribute('data-i18n-attributes'));
        $new_text = '';

        foreach ($attribute_list as $attribute_name)
        {
            $attribute_name = trim($attribute_name);
            $attribute_value = $node->getAttribute($attribute_name);
            $new_text = _gettext($attribute_value);
            $attribute_node = $node->ownerDocument->createAttribute($attribute_name);
            $attribute_node->value = $new_text;
            $node->setAttributeNode($attribute_node);
        }
    }

    private function gettextContent($node)
    {
        $new_text = '';
        $text = $node->getContent();
        $new_text = _gettext($text);
        $node->setContent($new_text);
    }
}