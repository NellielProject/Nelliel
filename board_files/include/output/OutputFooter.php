<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputFooter extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function render(array $parameters = array())
    {
        $this->prepare('footer.html');
        $dotdot = (!empty($parameters['dotdot'])) ? $parameters['dotdot'] : '';

        if (!$parameters['styles'])
        {
            $this->dom->getElementById('bottom-styles')->remove();
        }
        else
        {
            $this->buildStyles($dotdot);
        }

        $this->dom->getElementById('nelliel-version')->setContent(NELLIEL_VERSION);
        $this->dom->getElementById('js-ui')->extSetAttribute('src', $dotdot . SCRIPTS_WEB_PATH . 'ui.js');
        $this->domain->translator()->translateDom($this->dom, $domain->setting('language'));

        if($domain->setting('display_render_timer'))
        {
            $timer_out = sprintf(_gettext('This page was created in %s seconds.'), round($domain->renderInstance()->endRenderTimer(), 4));
            $this->dom->getElementById('footer-timer')->setContent($timer_out);
        }

        $domain->renderInstance()->appendHTMLFromDOM($this->dom);
    }

    private function buildStyles($dotdot)
    {
        $database = nel_database();
        $bottom_styles_menu = $this->dom->getElementById('bottom-styles-menu');
        $styles = $database->executeFetchAll('SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry", "is_default" DESC', PDO::FETCH_ASSOC);

        foreach ($styles as $style)
        {
            $info = json_decode($style['info'], true);
            $style_option = $this->dom->createElement('option', $info['name']);
            $style_option->extSetAttribute('data-command', 'change-style');
            $style_option->extSetAttribute('data-id', $style['id']);
            $style_option->extSetAttribute('value', $style['id']);
            $bottom_styles_menu->appendChild($style_option);
        }
    }
}