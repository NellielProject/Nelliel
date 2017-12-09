<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_neltext_variable($name, $value = null)
{
    static $variables = array();

    if(!is_null($value))
    {
        $variables[$name] = $value;
    }

    return $variables[$name];
}

function nel_stext($text)
{
    return nel_get_language(BOARD_LANGUAGE, 'singular', $text);
}

function nel_ptext($text, $num)
{
    if ($num <= 1)
    {
        return nel_get_language(BOARD_LANGUAGE, 'singular', $text);
    }
    else if ($num > 1)
    {
        return nel_get_language(BOARD_LANGUAGE, 'plural', $text);
    }
}

function nel_process_neltext_attribute($node)
{
    $attribute_list = explode(',', $node->getAttribute('data-i18n-attributes'));
    $new_text = '';

    foreach ($attribute_list as $attribute_name)
    {
        $attribute_name = trim($attribute_name);
        $attribute_value = $node->getAttribute($attribute_name);
        $matches = array();
        $has_plural = preg_match('#{\+[\s.*]?plural:(.*?)[\s.*]?\+}(.*?)$#u', $attribute_value, $matches);

        if($has_plural === 1)
        {
            $variable = nel_neltext_variable($matches[1]);
            $new_text = nel_ptext($matches[2], $variable);
            $new_text = preg_replace('#%count%#u', $variable, $new_text);
        }
        else
        {
            $new_text = nel_stext($attribute_value);
        }

        $attribute_node = $node->ownerDocument->createAttribute($attribute_name);
        $attribute_node->value = $new_text;
        $node->setAttributeNode($attribute_node);
    }
}

function nel_process_neltext_content($node)
{
    $new_text = '';
    $xpath = new DOMXPath($node->ownerDocument);
    $plural_element = $xpath->query('.//*[@data-plural]', $node)->item(0);

    if (!is_null($plural_element))
    {
        $variable_name = $plural_element->getAttribute('data-plural');
        $variable = nel_neltext_variable($variable_name);
        $new_text = nel_ptext($plural_element->getContent(), $variable);
        $new_text = preg_replace('#%count%#u', $variable, $new_text);
    }
    else
    {
        $text = $node->getContent();
        $new_text = nel_stext($text);
    }

    $node->setContent($new_text, 'replace');
}