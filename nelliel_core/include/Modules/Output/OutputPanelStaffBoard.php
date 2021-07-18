<?php

declare(strict_types=1);

namespace Nelliel\Modules\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelStaffBoard extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/staff_board');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Staff Board');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $board_posts = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_STAFF_BOARD_TABLE . '" ORDER BY "post_time" ASC', PDO::FETCH_ASSOC);
        $bgclass = 'row1';
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'staff-board', 'actions' => 'add']);

        foreach ($board_posts as $post)
        {
            $post_info = array();
            $post_info['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $post_info['user'] = $post['user_id'];
            $post_info['domain'] = $post['domain_id'];
            $post_info['subject'] = $post['subject'];
            $post_info['message'] = $post['message'];
            $post_info['time'] = date('Y/m/d (D) H:i:s', intval($post['post_time']));

            if($this->session->user()->checkPermission($this->domain, 'perm_staff_board_delete'))
            {
                $post_info['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                        ['module' => 'admin', 'section' => 'staff-board', 'actions' => 'remove',
                        'entry' => $post['entry']]);
            }

            $this->render_data['board_posts'][] = $post_info;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}