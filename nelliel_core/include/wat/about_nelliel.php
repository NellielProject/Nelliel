<?php

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

// This about page must be present and accessible in all installations and distributions of Nelliel!
// If a fork makes alterations such as adding or removing libraries the page can be updated accordingly.
// However the page itself cannot be removed!

use Nelliel\DomainSite;

function nel_about_page(DomainSite $domain)
{
    $render_engine = new Mustache_Engine();
    $render_data = array();
    $dotdot = '';
    $render_data['page_language'] = str_replace('_', '-', $domain->locale());
    $output_head = new \Nelliel\Output\OutputHead($domain);
    $render_data['head'] = $output_head->render(['dotdot' => $dotdot], false);
    $output_header = new \Nelliel\Output\OutputHeader($domain);
    $render_data['header'] = $output_header->render(['header_type' => 'general', 'dotdot' => $dotdot],
            false);
    $render_data['nelliel_version'] = _gettext('Version: ') . NELLIEL_VERSION;
    $render_data['disclaimer_image_url'] = NEL_CORE_IMAGES_WEB_PATH . 'wat/luna_canterlot_disclaimer.png';
    $render_data['disclaimer_alt_text'] = 'Luna Canterlot Voice';
    $output_footer = new \Nelliel\Output\OutputFooter($domain);
    $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], false);
    echo $render_engine->render(file_get_contents(NEL_WAT_FILES_PATH . 'about_nelliel.html'), $render_data);
    nel_clean_exit();
}