<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelFiletypes extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/filetypes_main');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Filetypes');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $categories = $this->database->executeFetchAll(
            'SELECT * FROM "' . NEL_FILETYPE_CATEGORIES_TABLE . '" ORDER BY "category" ASC', PDO::FETCH_ASSOC);
        $filetypes = $this->database->executeFetchAll(
            'SELECT * FROM "' . NEL_FILETYPES_TABLE . '" ORDER BY "category" ASC, "format" ASC', PDO::FETCH_ASSOC);
        $this->render_data['new_category_url'] = nel_build_router_url(
            [$this->domain->uri(), 'filetype-categories', 'new']);
        $this->render_data['new_filetype_url'] = nel_build_router_url([$this->domain->uri(), 'filetypes', 'new']);
        $bgclass = 'row1';

        foreach ($categories as $category) {
            $category_data = array();
            $category_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $category_data['category'] = $category['category'];
            $category_data['label'] = $category['label'];
            $category_data['enabled'] = $category['enabled'];
            $category_data['edit_url'] = nel_build_router_url(
                [$this->domain->uri(), 'filetype-categories', $category['category'], 'modify']);

            if ($category['enabled'] == 1) {
                $category_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'filetype-categories', $category['category'], 'disable']);
                $category_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($category['enabled'] == 0) {
                $category_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'filetype-categories', $category['category'], 'enable']);
                $category_data['enable_disable_text'] = _gettext('Enable');
            }

            $category_data['delete_url'] = nel_build_router_url(
                [$this->domain->uri(), 'filetype-categories', $category['category'], 'delete']);
            $this->render_data['category_list'][] = $category_data;
        }

        $bgclass = 'row1';

        foreach ($filetypes as $filetype) {
            $filetype_data = array();
            $filetype_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $filetype_data['format'] = $filetype['format'];
            $filetype_data['category'] = $filetype['category'];
            $filetype_data['mimetypes'] = $filetype['mimetypes'];
            $filetype_data['enabled'] = $filetype['enabled'];
            $sub_extensions = '';

            if (!empty($filetype['extensions'])) {
                foreach (json_decode($filetype['extensions'], true) as $sub_extension) {
                    $sub_extensions .= $sub_extension . ', ';
                }
            }

            $filetype_data['extensions'] = utf8_substr($sub_extensions, 0, -2);
            $mimetypes = '';

            if (!empty($filetype['mimetypes'])) {
                foreach (json_decode($filetype['mimetypes'], true) as $mime) {
                    $mimetypes .= $mime . ', ';
                }
            }
            $filetype_data['mimetypes'] = utf8_substr($mimetypes, 0, -2);
            $filetype_data['magic_regex'] = $filetype['magic_regex'];
            $filetype_data['label'] = $filetype['label'];
            $filetype_data['edit_url'] = nel_build_router_url(
                [$this->domain->uri(), 'filetypes', $filetype['format'], 'modify']);

            if ($filetype['enabled'] == 1) {
                $filetype_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'filetypes', $filetype['format'], 'disable']);
                $filetype_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($filetype['enabled'] == 0) {
                $filetype_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'filetypes', $filetype['format'], 'enable']);
                $filetype_data['enable_disable_text'] = _gettext('Enable');
            }

            $filetype_data['delete_url'] = nel_build_router_url(
                [$this->domain->uri(), 'filetypes', $filetype['format'], 'delete']);
            $this->render_data['filetype_list'][] = $filetype_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function newFiletype(array $parameters, bool $data_only)
    {
        $parameters['section'] = $parameters['section'] ?? _gettext('New');
        $parameters['editing'] = false;
        return $this->editFiletype($parameters, $data_only);
    }

    public function newCategory(array $parameters, bool $data_only)
    {
        $parameters['section'] = $parameters['section'] ?? _gettext('New');
        $parameters['editing'] = false;
        return $this->editCategory($parameters, $data_only);
    }

    public function editFiletype(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/filetypes_filetype_edit');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Filetypes');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $editing = $parameters['editing'] ?? true;

        if ($editing) {
            $format = $parameters['format'] ?? '';
            $form_action = nel_build_router_url([$this->domain->uri(), 'filetypes', $format, 'modify']);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_FILETYPES_TABLE . '" WHERE "format" = ?');
            $filetype_data = $this->database->executePreparedFetch($prepared, [$format], PDO::FETCH_ASSOC);

            if ($filetype_data !== false) {
                $this->render_data['format'] = $filetype_data['format'];
                $this->render_data['extensions'] = $filetype_data['extensions'];
                $this->render_data['category'] = $filetype_data['category'];
                $this->render_data['mimetypes'] = $filetype_data['mimetypes'];
                $this->render_data['magic_regex'] = $filetype_data['magic_regex'];
                $this->render_data['label'] = $filetype_data['label'];
                $this->render_data['enabled_checked'] = $filetype_data['enabled'] == 1 ? 'checked' : '';
            }
        } else {
            $this->render_data['new_filetype'] = true;
            $form_action = nel_build_router_url([$this->domain->uri(), 'filetypes', 'new']);
        }

        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function editCategory(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/filetypes_category_edit');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Filetypes');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $editing = $parameters['editing'] ?? true;

        if ($editing) {
            $category = $parameters['category'] ?? '';
            $form_action = nel_build_router_url([$this->domain->uri(), 'filetype-categories', $category, 'modify']);
            $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_FILETYPE_CATEGORIES_TABLE . '" WHERE "category" = ?');
            $category_data = $this->database->executePreparedFetch($prepared, [$category], PDO::FETCH_ASSOC);

            if ($category_data !== false) {
                $this->render_data['category'] = $category_data['category'];
                $this->render_data['label'] = $category_data['label'];
                $this->render_data['enabled_checked'] = $category_data['enabled'] == 1 ? 'checked' : '';
            }
        } else {
            $this->render_data['new_category'] = true;
            $form_action = nel_build_router_url([$this->domain->uri(), 'filetype-categories', 'new']);
        }

        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}