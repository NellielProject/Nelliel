<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelPermissions extends OutputCore
{
    private $database;

    function __construct(Domain $domain)
    {
        $this->database = $domain->database();
        $this->domain = $domain;
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_permissions_access'))
        {
            nel_derp(450, _gettext('You are not allowed to access the Permissions panel.'));
        }

        $this->prepare('management/panels/permissions_panel.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Permissions')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $permissions = $this->database->executeFetchAll('SELECT * FROM "' . PERMISSIONS_TABLE . '" ORDER BY "entry" ASC',
                PDO::FETCH_ASSOC);
        $form_action = $this->url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'permissions', 'action' => 'add']);
        $this->dom->getElementById('add-permission-form')->extSetAttribute('action', $form_action);

        $permission_list = $this->dom->getElementById('permission-list');
        $permission_list_nodes = $permission_list->getElementsByAttributeName('data-parse-id', true);
        $bgclass = 'row1';

        foreach ($permissions as $permission)
        {
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $permission_row = $this->dom->copyNode($permission_list_nodes['permission-row'], $permission_list, 'append');
            $permission_row_nodes = $permission_row->getElementsByAttributeName('data-parse-id', true);
            $permission_row->extSetAttribute('class', $bgclass);
            $permission_row_nodes['permission']->setContent($permission['permission']);
            $permission_row_nodes['description']->setContent($permission['description']);
            $remove_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'permissions', 'action' => 'remove', 'permission' => $permission['permission']]);
            $permission_row_nodes['permission-remove-link']->extSetAttribute('href', $remove_link);
        }

        $permission_list_nodes['permission-row']->remove();
        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}