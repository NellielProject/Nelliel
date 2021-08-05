<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputMenu extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function styles(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $styles = $this->domain->frontEndData()->getAllStyles();
        $render_data = array();

        foreach ($styles as $style)
        {
            $style_data = array();
            $style_data['stylesheet'] = ($this->domain->setting('default_style') === $style->id()) ? 'stylesheet' : 'alternate stylesheet';
            $style_data['style_id'] = $style->id();
            $style_data['stylesheet_url'] = $style->getMainFileWebPath();
            $style_data['style_name'] = $style->info('name');
            $render_data[] = $style_data;
        }

        usort($render_data, [$this, 'sortByStyleName']);
        return $render_data;
    }

    private function sortByStyleName($a, $b)
    {
        if ($a['style_name'] == $b['style_name'])
        {
            return $a['style_name'] - $b['style_name'];
        }

        return ($a['style_name'] < $b['style_name']) ? -1 : 1;
    }
}