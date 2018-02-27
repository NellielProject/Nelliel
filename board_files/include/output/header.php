<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_board_header($board_id, $dataforce, $render, $treeline = null)
{
    $dbh = nel_database();
    $board_settings = nel_board_settings($board_id);
    $references = nel_board_references($board_id);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'header.html');
    $dotdot = isset($dataforce['dotdot']) ? $dataforce['dotdot'] : '../';
    $head_element = $dom->getElementsByTagName('head')->item(0);
    $link_elements = $head_element->getElementsByTagName('link');
    $dom->getElementById('js-main-file')->modifyAttribute('src', $dotdot, 'before');
    $dom->getElementById('js-onload')->setContent('window.onload = function () {doImportantStuff(\'' . $board_id .
         '\');};');
    $dom->getElementById('js-style-set')->setContent('changeBoardStyle("' . $board_id . '", getCookie("style-' .
         $board_id . '"));');
    $html5shiv = '[if lt IE 9]><script src="' . $dotdot . JS_DIR . 'html5shiv-printshiv.js"></script><![endif]';
    $head_element->doXPathQuery('//comment()')->item(0)->data = $html5shiv;

    foreach ($link_elements as $element)
    {
        $content = $element->getAttribute('title');
        $element->extSetAttribute('href', $dotdot . CSS_DIR . strtolower($content) . '.css');
    }

    $title_element = $head_element->getElementsByTagName('title')->item(0);
    $title_content = $board_settings['board_name'];

    if (!is_null($treeline))
    {
        if ($treeline[0]['subject'] === '')
        {
            $title_content = $board_settings['board_name'] . ' > Thread #' . $treeline[0]['post_number'];
        }
        else
        {
            $title_content = $board_settings['board_name'] . ' > ' . $treeline[0]['subject'];
        }
    }

    $title_element->setContent($title_content);

    $board_navigation = $dom->getElementById("board-navigation");
    $board_navigation->appendChild($dom->createTextNode('[ '));
    $board_data = $dbh->executeFetchAll('SELECT * FROM "nelliel_board_data"', PDO::FETCH_ASSOC);
    $end = end($board_data);

    foreach ($board_data as $board)
    {
        $board_link = $dom->createElement('a');
        $board_link->extSetAttribute('class', 'board-navigation-link');
        $board_link->extSetAttribute('href', $dotdot . $board['board_directory']);
        $board_link->extSetAttribute('title', nel_board_settings($board['board_id'], 'board_name'));
        $board_link->setContent($board['board_directory']);
        $board_navigation->appendChild($board_link);

        if ($board !== $end)
        {
            $board_navigation->appendChild($dom->createTextNode(' / '));
        }
    }

    $board_navigation->appendChild($dom->createTextNode(' ]'));

    $logo_element = $dom->getElementById('logo');
    $logo_image = $dom->getElementById('top-logo-image');
    $logo_text = $dom->getElementById('top-logo-text');

    if ($board_settings['show_logo'])
    {
        $logo_image->extSetAttribute('src', $board_settings['board_logo']);
        $logo_image->extSetAttribute('alt', $board_settings['board_name']);
    }
    else
    {
        $logo_element->removeChild($logo_image);
    }

    if ($board_settings['show_title'])
    {
        $logo_text->setContent($board_settings['board_name']);
    }
    else
    {
        $logo_element->removeChild($logo_text);
    }

    $top_admin_span = $dom->getElementById('top-admin-span');
    $a_elements = $top_admin_span->getElementsByTagName('a');
    $a_elements->item(1)->extSetAttribute('href', nel_site_settings('home_page'));
    $a_elements->item(2)->extSetAttribute('href', $dotdot . PHP_SELF . '?manage=login');
    $a_elements->item(3)->extSetAttribute('href', $dotdot . PHP_SELF . '?about_nelliel');

    if (nel_session_is_ignored('render'))
    {
        $top_admin_span->removeChild($a_elements->item(0)->parentNode);
        $dom->getElementById('manage-header')->removeSelf();
        $dom->getElementById('manage-board-header')->removeSelf();
        $dom->getElementById('manage-sub-header')->removeSelf();
    }
    else
    {
        $a_elements->item(0)->extSetAttribute('href', $dotdot . PHP_SELF . '?manage=logout');
    }

    nel_process_i18n($dom, nel_board_settings($board_id, 'board_language'));

    $render->appendHTMLFromDOM($dom);
}

function nel_render_general_header($render, $dotdot = null, $board_id = null, $extra_data = array())
{
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'header.html');
    $head_element = $dom->getElementsByTagName('head')->item(0);
    $dotdot = (is_null($dotdot)) ? $dotdot : '';
    $link_elements = $head_element->getElementsByTagName('link');
    $dom->getElementById('js-main-file')->modifyAttribute('src', $dotdot, 'before');
    $dom->getElementById('js-onload')->setContent('window.onload = function () {doImportantStuff(\'' . INPUT_BOARD_ID .
         '\');};');
    $dom->getElementById('js-style-set')->setContent('changeBoardStyle("", getCookie("base-style"));');
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
    $top_admin_span = $dom->getElementById('top-admin-span');
    $a_elements = $top_admin_span->getElementsByTagName('a');
    $a_elements->item(1)->extSetAttribute('href', nel_site_settings('home_page'));
    $a_elements->item(2)->extSetAttribute('href', $dotdot . PHP_SELF . '?manage=login');
    $a_elements->item(3)->extSetAttribute('href', $dotdot . PHP_SELF . '?about_nelliel');

    if (nel_session_is_ignored('render'))
    {
        $top_admin_span->removeChild($a_elements->item(0)->parentNode);
        $dom->getElementById('manage-header')->removeSelf();
        $dom->getElementById('manage-board-header')->removeSelf();
        $dom->getElementById('manage-sub-header')->removeSelf();
    }
    else
    {
        if (isset($pextra_data['header']))
        {
            $dom->getElementById('manage-header-text')->setContent($extra_data['header']);
        }

        if (!is_null($board_id))
        {
            $board_data = nel_stext('MANAGE_CURRENT_BOARD') . ' ' . $board_id;
            $dom->getElementById('manage-board-header-data')->setContent($board_data);
        }

        if (isset($extra_data['sub_header']))
        {
            $dom->getElementById('manage-sub-header-text')->setContent($extra_data['sub_header']);
        }

        $a_elements->item(0)->extSetAttribute('href', $dotdot . PHP_SELF . '?manage=logout');
    }

    nel_process_i18n($dom);

    $render->appendHTMLFromDOM($dom);
}