<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use DateTimeZone;
use PDO;

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
            $options[] = $this->createSelectOption($style->info('name'), $style->id(), $selected);
        }

        return $options;
    }

    public function configImageSets(string $selected): array
    {
        $sets = $this->domain->frontEndData()->getAllImageSets(true);
        $options = array();

        foreach ($sets as $set) {
            $options[] = $this->createSelectOption($set->info('name'), $set->id(), $selected);
        }

        return $options;
    }

    public function configTemplates(string $selected): array
    {
        $templates = $this->domain->frontEndData()->getAllTemplates(true);
        $options = array();

        foreach ($templates as $template) {
            $options[] = $this->createSelectOption($template->info('name'), $template->id(), $selected);
        }

        return $options;
    }

    public function timezones(string $selected): array
    {
        $timezones = DateTimeZone::listIdentifiers();
        $options = array();

        foreach ($timezones as $timezone) {
            $options[] = $this->createSelectOption($timezone, $timezone, $selected);
        }

        return $options;
    }

    public function styles(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $styles = $this->domain->frontEndData()->getAllStyles(true);
        $render_data = array();
        $enabled_styles = json_decode($this->domain->setting('enabled_styles') ?? '');
        $default_style = $this->domain->setting('default_style');

        foreach ($styles as $style) {
            if ($this->domain->id() !== Domain::SITE && $this->domain->id() !== Domain::GLOBAL &&
                !in_array($style->id(), $enabled_styles)) {
                continue;
            }

            $style_data = array();
            $style_data['stylesheet'] = ($default_style === $style->id()) ? 'stylesheet' : 'alternate stylesheet';
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

    public function wordfilterActions(string $selected): array
    {
        $actions = array();
        $actions['select_name'] = 'filter_action';
        $actions['options'][] = $this->createSelectOption(__('Replace'), 'replace', $selected);
        $actions['options'][] = $this->createSelectOption(__('Reject'), 'reject', $selected);
        return $actions;
    }

    public function fileFilterActions(string $selected): array
    {
        $actions = array();
        $actions['select_name'] = 'filter_action';
        $actions['options'][] = $this->createSelectOption(__('Reject'), 'reject', $selected);
        return $actions;
    }

    public function fgsfds(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $options = array();
        $options[] = $this->createSelectOption(__(''), '');
        $options[] = $this->createSelectOption(__('noko'), 'noko');
        $options[] = $this->createSelectOption(__('sage'), 'sage');
        $options[] = $this->createSelectOption(__('noko + sage'), 'noko sage');
        return $options;
    }

    public function boards(string $name, string $selected, bool $data_only): array
    {
        $board_data = $this->database->executeFetchAll(
            'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);
        $boards = array();
        $boards['select_name'] = $name;
        $boards['options'] = $this->createSelectOption('', '', $selected);

        foreach ($board_data as $board) {
            $domain = Domain::getDomainFromID($board, $this->database);
            $boards['options'] = $this->createSelectOption($domain->uri(), $domain->uri(), $selected);
        }

        return $boards;
    }

    public function markupOptions(string $selected, bool $html, bool $data_only): array
    {
        $this->renderSetup();
        $markup_options = array();
        $markup_options['select_name'] = 'markup_type';
        $markup_options['options'][] = $this->createSelectOption(__('None'), 'none', $selected);

        if ($html) {
            $markup_options['options'][] = $this->createSelectOption(__('HTML'), 'html', $selected);
        }

        $markup_options['options'][] = $this->createSelectOption(__('Imageboard'), 'imageboard', $selected);
        return $markup_options;
    }

    public function markupTypes(string $selected, bool $data_only): array
    {
        $this->renderSetup();
        $markup_types = array();
        $markup_types['select_name'] = 'type';
        $markup_types['options'][] = $this->createSelectOption(__('Block'), 'block', $selected);
        $markup_types['options'][] = $this->createSelectOption(__('Line'), 'line', $selected);
        $markup_types['options'][] = $this->createSelectOption(__('Simple'), 'simple', $selected);
        $markup_types['options'][] = $this->createSelectOption(__('Loop'), 'loop', $selected);
        return $markup_types;
    }

    private function createSelectOption(string $label, string $value, string $selected): array
    {
        $option = array();
        $option['option_label'] = $label;
        $option['option_value'] = $value;
        $option['option_selected'] = $selected === $value ? 'selected' : '';
        return $option;
    }
}