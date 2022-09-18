<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelPermissions extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/permissions');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Permissions');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $permissions = $this->database->executeFetchAll(
            'SELECT * FROM "' . NEL_PERMISSIONS_TABLE . '" ORDER BY "permission" ASC', PDO::FETCH_ASSOC);
        $this->render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'permissions', 'new']);
        $bgclass = 'row1';

        foreach ($permissions as $permission) {
            $permission_data = array();
            $permission_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $permission_data['permission'] = $permission['permission'];
            $permission_data['description'] = _gettext($permission['description']);
            $permission_data['remove_url'] = nel_build_router_url(
                [$this->domain->id(), 'permissions', $permission['permission'], 'delete']);
            $this->render_data['permission_list'][] = $permission_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}