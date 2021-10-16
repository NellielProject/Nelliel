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
        $prepared->bindValue(1, $this->session->user()
            ->id(), PDO::PARAM_STR);
        $list = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($list as $message) {
            $message_info = array();
            $message_info['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $message_info['message_read'] = ($message['message_read'] == 1) ? 'X' : null;
            $message_info['time'] = date('Y/m/d l H:i', intval($message['time_sent']));
            $message_info['sender'] = $message['sender'];
            $message_info['message'] = $message['message'];
            $message_info['view_url'] = nel_build_router_url(
                [Domain::SITE, 'account', 'private-messages', 'view', $message['entry']]);
            $message_info['mark_read_url'] = nel_build_router_url(
                [Domain::SITE, 'account', 'private-messages', 'mark-read', $message['entry']]);
            $message_info['delete_url'] = nel_build_router_url(
                [Domain::SITE, 'account', 'private-messages', 'delete', $message['entry']]);
            $this->render_data['private_messages'][] = $message_info;
        }

        $this->render_data['new_url'] = nel_build_router_url([Domain::SITE, 'account', 'private-messages', 'new']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function newMessage(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('private_messages/new_message');
        $reply_id = $parameters['reply_id'] ?? null;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['form_action'] = nel_build_router_url([Domain::SITE, 'account', 'private-messages', 'send']);

        if (!is_null($reply_id)) {
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_PRIVATE_MESSAGES_TABLE . '" WHERE "entry" = ?');
            $prepared->bindValue(1, $reply_id, PDO::PARAM_INT);
            $message = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

            if (!is_array($message)) {
                return;
            }

            $this->render_data['recipient'] = $message['sender'];
            $this->render_data['quoted_text'] = '>' . str_replace("\n", "\n" . '>', $message['message']);
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function viewMessage(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('private_messages/view_message');
        $message_id = $parameters['message_id'] ?? null;
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_PRIVATE_MESSAGES_TABLE . '" WHERE "entry" = ?');
        $prepared->bindValue(1, $message_id, PDO::PARAM_INT);
        $message = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if (!is_array($message)) {
            return;
        }

        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['form_action'] = nel_build_router_url(
            [Domain::SITE, 'account', 'private-messages', 'reply', $message['entry']]);
        $this->render_data['sender'] = $message['sender'];

        foreach ($this->output_filter->newlinesToArray($message['message']) as $line) {
            $this->render_data['message_lines'][]['text'] = $line;
        }

        $this->render_data['time_sent'] = $message['time_sent'];
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}