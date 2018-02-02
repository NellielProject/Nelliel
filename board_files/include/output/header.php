<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_board_header($board_id, $dataforce, $render, $treeline = null) // TODO:Separate functions for admin and post/thread headers
{
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'header.html');
    $dotdot = isset($dataforce['dotdot']) ? $dataforce['dotdot'] : '../';
    $head_element = $dom->getElementsByTagName('head')->item(0);
    $link_elements = $head_element->getElementsByTagName('link');
    $dom->getElementById('js-main-file')->modifyAttribute('src', $dotdot, 'before');
    $dom->getElementById('js-onload')->setContent('window.onload = function () {doImportantStuff(\'' . $board_id .
         '\');};');
    $dom->getElementById('js-style-set')->setContent('processCookie("style-' . BOARD_DIR . '");');
    $html5shiv = '[if lt IE 9]><script src="' . $dotdot . JS_DIR . 'html5shiv-printshiv.js"></script><![endif]';
    $head_element->doXPathQuery('//comment()')->item(0)->data = $html5shiv;

    foreach ($link_elements as $element)
    {
        $content = $element->getAttribute('title');
        $element->extSetAttribute('href', $dotdot . CSS_DIR . strtolower($content) . '.css');
    }

    $title_element = $head_element->getElementsByTagName('title')->item(0);
    $title_content = BS_BOARD_NAME;

            if (!is_null($treeline))
            {
                if ($treeline[0]['subject'] === '')
                {
                    $title_content = BS_BOARD_NAME . ' > Thread #' . $treeline[0]['post_number'];
                }
                else
                {
                    $title_content = BS_BOARD_NAME . ' > ' . $treeline[0]['subject'];
                }
            }

    $title_element->setContent($title_content);

    $logo_element = $dom->getElementById('logo');
    $logo_image = $dom->getElementById('top-logo-image');
    $logo_text = $dom->getElementById('top-logo-text');

    if (BS_SHOW_LOGO)
    {
        $logo_image->extSetAttribute('src', BS_BOARD_LOGO);
        $logo_image->extSetAttribute('alt', BS_BOARD_NAME);
    }
    else
    {
        $logo_element->removeChild($logo_image);
    }

    if (BS_SHOW_TITLE)
    {
        $logo_text->setContent(BS_BOARD_NAME);
    }
    else
    {
        $logo_element->removeChild($logo_text);
    }

    $a_elements = $dom->getElementById('top-styles-span')->getElementsByTagName('a');

    foreach ($a_elements as $element)
    {
        $content = $element->getContent();
    }

    $top_admin_span = $dom->getElementById('top-admin-span');
    $a_elements = $top_admin_span->getElementsByTagName('a');
    $a_elements->item(1)->extSetAttribute('href', $dotdot . HOME);
    $a_elements->item(2)->extSetAttribute('href', $dotdot . PHP_SELF . '?mode=admin');
    $a_elements->item(3)->extSetAttribute('href', $dotdot . PHP_SELF . '?about_nelliel');

    if (nel_session_is_ignored('render'))
    {
        $top_admin_span->removeChild($a_elements->item(0)->parentNode);
    }
    else
    {
        $a_elements->item(0)->extSetAttribute('href', $dotdot . PHP_SELF . '?mode=log_out');
    }

    nel_process_i18n($dom, nel_board_settings($board_id, 'board_language'));

    $render->appendHTMLFromDOM($dom);
}

function nel_render_general_header($dataforce, $render)
{
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'header.html');
    $dotdot = isset($dataforce['dotdot']) ? $dataforce['dotdot'] : '../';
    $head_element = $dom->getElementsByTagName('head')->item(0);
    $link_elements = $head_element->getElementsByTagName('link');
    $dom->getElementById('js-main-file')->modifyAttribute('src', $dotdot, 'before');
    $dom->getElementById('js-onload')->setContent('window.onload = function () {doImportantStuff(\'' . $board_id .
    '\');};');
    $dom->getElementById('js-style-set')->setContent('processCookie("style-' . BOARD_DIR . '");');
    $html5shiv = '[if lt IE 9]><script src="' . $dotdot . JS_DIR . 'html5shiv-printshiv.js"></script><![endif]';
    $head_element->doXPathQuery('//comment()')->item(0)->data = $html5shiv;

    foreach ($link_elements as $element)
    {
        $content = $element->getAttribute('title');
        $element->extSetAttribute('href', $dotdot . CSS_DIR . strtolower($content) . '.css');
    }

    $title_element = $head_element->getElementsByTagName('title')->item(0);
    $title_element->setContent('Nelliel Imageboard');
    $dom->getElementById('logo')->removeSelf();

    $a_elements = $dom->getElementById('top-styles-span')->getElementsByTagName('a');

    foreach ($a_elements as $element)
    {
        $content = $element->getContent();
    }

    $top_admin_span = $dom->getElementById('top-admin-span');
    $a_elements = $top_admin_span->getElementsByTagName('a');
    $a_elements->item(1)->extSetAttribute('href', $dotdot . HOME);
    $a_elements->item(2)->extSetAttribute('href', $dotdot . PHP_SELF . '?mode=admin');
    $a_elements->item(3)->extSetAttribute('href', $dotdot . PHP_SELF . '?about_nelliel');

    if (nel_session_is_ignored('render'))
    {
        $top_admin_span->removeChild($a_elements->item(0)->parentNode);
    }
    else
    {
        $a_elements->item(0)->extSetAttribute('href', $dotdot . PHP_SELF . '?mode=log_out');
    }

    nel_process_i18n($dom, nel_board_settings($board_id, 'board_language'));

    $render->appendHTMLFromDOM($dom);
}