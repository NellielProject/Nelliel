<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPrivateMessages extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function messageList(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('private_messages/message_list');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_PRIVATE_MESSAGES_TABLE . '" WHERE "recipient" = ?');
        var_dump($this->session->user());
        $prepared->bindValue(1, $this->session->user()->id(), PDO::PARAM_STR);
        $list = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);

        foreach($list as $message)
        {
            $message_info = array();
            $message_info['message_read'] = ($message['message_read'] == 1) ? 'X' : null;
            $message_info['time'] = date('Y/m/d l H:i', intval($message['time_sent']));
            $message_info['sender'] = $message['sender'];
            $message_info['message'] = $message['message'];
            $message_info['mark_read_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
            http_build_query(['module' => 'account', 'section' => 'private-message', 'actions' => 'mark-read', 'message-id' => $message['entry']]);
            $message_info['delete_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
            http_build_query(['module' => 'account', 'section' => 'private-message', 'actions' => 'delete', 'message-id' => $message['entry']]);
            $this->render_data['private_messages'][] = $message_info;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;

    }
}