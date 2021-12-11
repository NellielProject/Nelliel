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

    public function configStyles(string $selected): array
    {
        $styles = $this->domain->frontEndData()->getAllStyles(true);
        $options = array();

        foreach ($styles as $style) {
            $option_data = array();
            $option_data['option_name'] = $style->id();
            $option_data['option_label'] = $style->info('name');

            if ($option_data['option_name'] === $selected) {
                $option_data['option_selected'] = 'selected';
            }

            $options[] = $option_data;
        }

        return $options;
    }

    public function configImageSets(string $selected): array
    {
        $sets = $this->domain->frontEndData()->getAllImageSets(true);
        $options = array();

        foreach ($sets as $set) {
            $option_data = array();
            $option_data['option_name'] = $set->id();
            $option_data['option_label'] = $set->info('name');

            if ($option_data['option_name'] === $selected) {
                $option_data['option_selected'] = 'selected';
            }

            $options[] = $option_data;
        }

        return $options;
    }

    public function configTemplates(string $selected): array
    {
        $templates = $this->domain->frontEndData()->getAllTemplates(true);
        $options = array();

        foreach ($templates as $template) {
            $option_data = array();
            $option_data['option_name'] = $template->id();
            $option_data['option_label'] = $template->info('name');

            if ($option_data['option_name'] === $selected) {
                $option_data['option_selected'] = 'selected';
            }

            $options[] = $option_data;
        }

        return $options;
    }

    public function styles(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $styles = $this->domain->frontEndData()->getAllStyles(true);
        $render_data = array();
        $enabled_styles = json_decode($this->domain->setting('enabled_styles') ?? '');

        foreach ($styles as $style) {
            if ($this->domain->id() !== Domain::SITE && !in_array($style->id(), $enabled_styles)) {
                continue;
            }

            $style_data = array();
            $style_data['stylesheet'] = ($this->domain->setting('default_style') === $style->id()) ? 'stylesheet' : 'alternate stylesheet';
            $style_data['style_id'] = $style->id();
            $style_data['stylesheet_url'] = $style->getMainFileWebPath();
            $style_data['style_name'] = $style->info('name');
            $render_data[] = $style_data;
        }

        usort($render_data, function ($a, $b) {
            return $a['style_name'] <=> $b['style_name'];
        });

        return $render_data;
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