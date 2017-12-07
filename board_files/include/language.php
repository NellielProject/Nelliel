<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Handles language functions
//
function nel_get_language($language, $form, $text)
{
    static $lang_arrays;

    if (!isset($lang_arrays[$language]))
    {
        include LANGUAGE_PATH . 'lang.' . $language . '.php';
        $lang_arrays[$language]['singular'] = $lang_singular;
        $lang_arrays[$language]['plural'] = $lang_plural;
    }

    if (!isset($lang_arrays[$language][$form][$text]))
    {
        return '???';
    }

    return $lang_arrays[$language][$form][$text];
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

function nel_process_neltext($dom, $node, $attribute = null, $variable = null)
{
    if (!is_null($attribute))
    {
        if ($node->hasAttribute('data-' . $attribute . '-plural'))
        {
            $text = $node->getAttribute('data-' . $attribute . '-plural');
            $new_text = nel_ptext($text, $$variable);
            $node->removeAttribute('data-' . $attribute . '-plural');
        }
        else
        {
            $text = $node->getAttribute($attribute);
            $new_text = nel_stext($text);
        }

        $node->extSetAttribute($attribute, $new_text);
        $node->removeAttribute('data-i18n-attributes');
    }
    else
    {
        $plural_data = $dom->doXPathQuery('(.//*[@data-plural])[1]', $node)->item(0);

        if (!is_null($plural_data))
        {
            $text = $plural_data->getContent();
            $variable = $plural_data->getAttribute('data-plural');
            $new_text = nel_ptext($text, $$variable);
        }
        else
        {
            $text = $node->getContent();
            $new_text = nel_stext($text);
        }

        $node->setContent($new_text, 'replace');
        $node->removeAttribute('data-i18n');
    }
}

function nel_process_i18n($dom)
{
    $content_node_list = $dom->getElementsByAttributeName('data-i18n');
    $attribute_node_list = $dom->getElementsByAttributeName('data-i18n-attributes');

    foreach ($content_node_list as $node)
    {
        if ($node->getAttribute('data-i18n') === 'neltext')
        {
            nel_process_neltext($dom, $node);
        }
    }

    foreach ($attribute_node_list as $node)
    {
        $attribute_list = $node->getAttribute('data-i18n-attributes');
        $attributes = explode(',', $attribute_list);

        foreach ($attributes as $attribute)
        {
            $parts = explode('|', $attribute);
            $attribute = trim($parts[0]);
            $variable = null;

            if (!$node->hasAttribute($attribute))
            {
                continue;
            }

            if ($node->hasAttribute('data-' . $attribute . '-plural'))
            {
                $variable = $parts[1];
            }

            nel_process_neltext($dom, $node, $attribute, $variable);
        }
    }
}