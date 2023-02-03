<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputAccount extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('account/account_main');
        $parameters['area'] = $parameters['area'] ?? _gettext('Account Management');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Account');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['username'] = $this->session->user()->id();
        $this->render_data['display_name'] = $this->session->user()->getData('display_name');
        $this->render_data['last_login'] = $this->session->user()->getData('last_login');

        $notices = $this->database->executeFetchAll(
            'SELECT * FROM "' . NEL_NOTICEBOARD_TABLE . '" ORDER BY "time" DESC', PDO::FETCH_ASSOC);

        foreach ($notices as $notice) {
            $info = array();
            $info['notice_id'] = $notice['notice_id'];
            $info['user'] = $notice['username'];
            $info['subject'] = $notice['subject'];
            $info['time'] = $this->domain->domainDateTime(intval($notice['time']))->format('Y/m/d');
            $info['message'] = $notice['message'];
            $info['url'] = nel_build_router_url([$this->domain->id(), 'noticeboard', $notice['notice_id']]);
            $this->render_data['notices'][] = $info;
        }

        $this->render_data['private_messages_url'] = nel_build_router_url([Domain::SITE, 'account', 'private-messages']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}