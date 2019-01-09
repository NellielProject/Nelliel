<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_board_header($domain, $dotdot = null, $treeline = null)
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $translator = new \Nelliel\Language\Translator();
    $session = new \Nelliel\Session($authorization);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'header.html');
    $dotdot = (!empty($dotdot)) ? $dotdot : '';
    $head_element = $dom->getElementsByTagName('head')->item(0);
    $link_elements = $head_element->getElementsByTagName('link');
    $dom->getElementById('js-main-file')->modifyAttribute('src', $dotdot, 'before');
    $dom->getElementById('js-onload')->setContent(
            'window.onload = function () {nelliel.setup.doImportantStuff(\'' . $domain->id() . '\', \'' .
            $session->inModmode($domain->id()) . '\');};');
    $dom->getElementById('js-style-set')->setContent('setStyle(nelliel.core.getCookie("style-' . $domain->id() . '"));');

    if ($domain->setting('use_honeypot'))
    {
        $honeypot_css = '#form-user-info-1{display: none !important;}#form-user-info-2{display: none !important;}#form-user-info-3{position: absolute; top: 3px; left: -9001px;}';
        $style_element = $dom->createElement('style', $honeypot_css);
        $dom->getElementsByTagName('head')->item(0)->appendChild($style_element);
    }

    foreach ($link_elements as $element)
    {
        $content = $element->getAttribute('title');
        $element->extSetAttribute('href', $dotdot . CSS_DIR . '/' . strtolower($content) . '.css');
    }

    $title_element = $head_element->getElementsByTagName('title')->item(0);
    $title_content = $domain->setting('board_name');

    if (isset($treeline[0]['subject']) && $treeline[0]['subject'] === '')
    {
        $title_content = $domain->setting('board_name') . ' > Thread #' . $treeline[0]['post_number'];
    }
    else
    {
        $title_content = $domain->setting('board_name') . ' > ' . $treeline[0]['subject'];
    }

    $title_element->setContent($title_content);
    $board_navigation = $dom->getElementById("board-navigation");
    $board_navigation->appendChild($dom->createTextNode('[ '));
    $board_data = $database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);
    $end = end($board_data);

    foreach ($board_data as $data)
    {
        $board_link = $dom->createElement('a');
        $board_link->extSetAttribute('class', 'board-navigation-link');
        $board_link->extSetAttribute('href', $dotdot . $data['board_directory']);
        $board_link->extSetAttribute('title', $domain->setting('board_name'));
        $board_link->setContent($data['board_directory']);
        $board_navigation->appendChild($board_link);

        if ($data !== $end)
        {
            $board_navigation->appendChild($dom->createTextNode(' / '));
        }
    }

    $board_navigation->appendChild($dom->createTextNode(' ]'));

    $logo_image = $dom->getElementById('top-logo-image');
    $logo_text = $dom->getElementById('top-logo-text');
    $logo_image->remove(); // TODO: Be able to use image for logo

    /*if ($board_settings['show_logo'])
     {
     $logo_image->extSetAttribute('src', $board_settings['board_logo']);
     $logo_image->extSetAttribute('alt', $board_settings['board_name']);
     }
     else
     {
     $logo_image->remove();
     $logo_text->remove();
     }*/

    if ($domain->setting('show_title'))
    {
        $logo_text->setContent($domain->setting('board_name'));
    }
    else
    {
        $logo_text->remove();
    }

    $top_admin_span = $dom->getElementById('top-admin-span');
    $a_elements = $top_admin_span->getElementsByTagName('a');
    $a_elements->item(1)->extSetAttribute('href', nel_parameters_and_data()->siteSettings('home_page'));

    if ($session->isActive() && !$domain->renderActive())
    {
        $a_elements->item(2)->extSetAttribute('href', $dotdot . PHP_SELF . '?module=main-panel');
    }
    else
    {
        $a_elements->item(2)->extSetAttribute('href', $dotdot . PHP_SELF . '?module=login');
    }

    $a_elements->item(3)->extSetAttribute('href', $dotdot . PHP_SELF . '?about_nelliel');

    $dom->getElementById('manage-board-header')->remove();
    $dom->getElementById('manage-sub-header')->remove();

    if ($session->inModmode($domain->id()) && !$domain->renderActive())
    {
        $dom->getElementById('manage-header-text')->setContent(_gettext('Mod Mode'));
        $a_elements->item(0)->extSetAttribute('href', $dotdot . PHP_SELF . '?module=logout');
    }
    else
    {
        $a_elements->item(0)->parentNode->remove();
        $dom->getElementById('manage-header')->remove();
    }

    $translator->translateDom($dom, $domain->setting('language'));

    $domain->renderInstance()->appendHTMLFromDOM($dom);
}

function nel_render_general_header($domain, $dotdot = null, $extra_data = array())
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $translator = new \Nelliel\Language\Translator();
    $session = new \Nelliel\Session($authorization);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'header.html');
    $head_element = $dom->getElementsByTagName('head')->item(0);
    $dotdot = (!empty($dotdot)) ? $dotdot : '';
    $link_elements = $head_element->getElementsByTagName('link');
    $dom->getElementById('js-main-file')->modifyAttribute('src', $dotdot, 'before');
    $dom->getElementById('js-onload')->setContent(
            'window.onload = function () {nelliel.setup.doImportantStuff(\'' . $domain->id() . '\', \'' .
            $session->inModmode($domain->id()) . '\');};');
    $dom->getElementById('js-style-set')->setContent('setStyle(nelliel.core.getCookie("style-' . $domain->id() . '"));');

    $dom->getElementById('top-logo-image')->remove();
    $dom->getElementById('top-logo-text')->remove();

    foreach ($link_elements as $element)
    {
        $content = $element->getAttribute('title');
        $element->extSetAttribute('href', $dotdot . CSS_DIR . '/' . strtolower($content) . '.css');
    }

    $title_element = $head_element->getElementsByTagName('title')->item(0);
    $title_element->setContent('Nelliel Imageboard');
    $top_admin_span = $dom->getElementById('top-admin-span');
    $a_elements = $top_admin_span->getElementsByTagName('a');
    $a_elements->item(1)->extSetAttribute('href', nel_parameters_and_data()->siteSettings('home_page'));

    if ($session->isActive())
    {
        $a_elements->item(2)->extSetAttribute('href', $dotdot . PHP_SELF . '?module=main-panel');
    }
    else
    {
        $a_elements->item(2)->extSetAttribute('href', $dotdot . PHP_SELF . '?module=login');
    }

    $a_elements->item(3)->extSetAttribute('href', $dotdot . PHP_SELF . '?about_nelliel');

    if (($session->isActive() || $session->inModmode($domain->id())))
    {
        if (isset($extra_data['header']))
        {
            $dom->getElementById('manage-header-text')->setContent($extra_data['header']);
        }

        if ($domain->id() !== '')
        {
            $board_data = _gettext('Current Board:') . ' ' . $domain->id();
            $dom->getElementById('manage-board-header-data')->setContent($board_data);
        }

        if (isset($extra_data['sub_header']))
        {
            $dom->getElementById('manage-sub-header-text')->setContent($extra_data['sub_header']);
        }

        $a_elements->item(0)->extSetAttribute('href', $dotdot . PHP_SELF . '?module=logout');
    }
    else
    {
        $top_admin_span->removeChild($a_elements->item(0)->parentNode);
        $dom->getElementById('manage-header')->remove();
        $dom->getElementById('manage-board-header')->remove();
        $dom->getElementById('manage-sub-header')->remove();
    }

    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
}