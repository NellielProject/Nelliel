<?php

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\DomainSite;

function nel_about_page(DomainSite $domain)
{
    $template_loader = new \Mustache_Loader_FilesystemLoader(__DIR__, ['extension' => '.html']);
    $mustache_engine = new \Mustache_Engine(['loader' => $template_loader]);
    $output_header = new \Nelliel\Output\OutputHeader($domain);
    $output = '';
    $output .= $output_header->render(['header_type' => 'general', 'dotdot' => '']);
    $render_data['nelliel_version'] = _gettext('Version: ') . NELLIEL_VERSION;
    $render_data['disclaimer_image_url'] = IMAGES_WEB_PATH . 'wat/luna_canterlot_disclaimer.png';
    $output .= $mustache_engine->render('about_nelliel', $render_data);
    $output_footer = new \Nelliel\Output\OutputFooter($domain);
    $output .= $output_footer->render(['dotdot' => '', 'generate_styles' => false, 'show_timer' => false]);
    echo $output;
    nel_clean_exit();
}