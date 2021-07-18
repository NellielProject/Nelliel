<?php
declare(strict_types = 1);

namespace Nelliel\Modules\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelDNSBL extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/dnsbl_main');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('DNSBL');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $dnsbl_services = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_DNSBL_TABLE . '" ORDER BY "entry" DESC', PDO::FETCH_ASSOC);
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'dnsbl', 'actions' => 'add']);
        $this->render_data['new_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'dnsbl', 'actions' => 'new']);
        $bgclass = 'row1';

        foreach ($dnsbl_services as $service)
        {
            $service_data = array();
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $service_data['bgclass'] = $bgclass;
            $service_data['entry'] = $service['entry'];
            $service_data['service_domain'] = $service['service_domain'];
            $service_data['return_codes'] = $service['return_codes'];
            $service_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'dnsbl', 'actions' => 'remove',
                                'dnsbl-id' => $service['entry']]);
            $service_data['edit_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'dnsbl', 'actions' => 'edit',
                                'dnsbl-id' => $service['entry']]);

            if ($service['enabled'] == 1)
            {
                $service_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'admin', 'section' => 'dnsbl', 'actions' => 'disable',
                                    'dnsbl-id' => $service['entry']]);
                $service_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($service['enabled'] == 0)
            {
                $service_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'admin', 'section' => 'dnsbl', 'actions' => 'enable',
                                    'dnsbl-id' => $service['entry']]);
                $service_data['enable_disable_text'] = _gettext('Enable');
            }

            $service_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'dnsbl', 'actions' => 'remove',
                                'dnsbl-id' => $service['entry']]);
            $this->render_data['service_list'][] = $service_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function new(array $parameters, bool $data_only)
    {
        $parameters['section'] = $parameters['section'] ?? _gettext('New');
        $parameters['submit_add'] = true;
        return $this->edit($parameters, $data_only);
    }

    public function edit(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/dnsbl_edit');
        $editing = $parameters['editing'] ?? false;
        $this->render_data['submit_add'] = $parameters['submit_add'] ?? false;
        $this->render_data['submit_edit'] = $editing;
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('DNSBL');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        $form_action = '';

        if ($editing)
        {
            $entry = $parameters['entry'] ?? 0;
            $form_action = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'dnsbl', 'actions' => 'update', 'dnsbl-id' => $entry]);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_DNSBL_TABLE . '" WHERE "entry" = ?');
            $service_data = $this->database->executePreparedFetch($prepared, [$entry], PDO::FETCH_ASSOC);

            if ($service_data !== false)
            {
                $this->render_data['entry'] = $service_data['entry'];
                $this->render_data['service_domain'] = $service_data['service_domain'];
                $this->render_data['return_codes'] = $service_data['return_codes'];
                $this->render_data['enabled_checked'] = $service_data['enabled'] == 1 ? 'checked' : '';
            }
        }
        else
        {
            $form_action = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(['module' => 'admin', 'section' => 'dnsbl', 'actions' => 'add']);
        }

        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}