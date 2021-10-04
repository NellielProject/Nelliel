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
        $enabled_styles = json_decode($this->domain->setting('enabled_styles') ?? '');

        foreach ($styles as $style)
        {
            if ($this->domain->id() !== Domain::SITE && !in_array($style->id(), $enabled_styles))
            {
                continue;
            }

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

    public function fgsfds(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $options = array();
        $option_none = array();
        $option_none['option_label'] = '';
        $option_none['option_value'] = '';
        $options[] = $option_none;
        $option_noko = array();
        $option_noko['option_label'] = 'noko';
        $option_noko['option_value'] = 'noko';
        $options[] = $option_noko;
        $option_sage = array();
        $option_sage['option_label'] = 'sage';
        $option_sage['option_value'] = 'sage';
        $options[] = $option_sage;
        $option_noko_sage = array();
        $option_noko_sage['option_label'] = 'noko + sage';
        $option_noko_sage['option_value'] = 'noko sage';
        $options[] = $option_noko_sage;
        return $options;
    }
}