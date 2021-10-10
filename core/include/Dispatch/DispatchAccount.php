<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\PrivateMessage;
use Nelliel\Account\Register;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputAccount;
use Nelliel\Output\OutputLoginPage;
use Nelliel\Output\OutputPrivateMessages;
use Nelliel\Output\OutputRegisterPage;

class DispatchAccount extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs)
    {
        switch ($inputs['section']) {
            case 'login':
                if ($inputs['method'] === 'POST') {
                    $this->session->login();
                    $this->session->loggedInOrError();
                    $output_account = new OutputAccount($this->domain, false);
                    $output_account->render([], false);
                }

                if ($inputs['method'] === 'GET') {
                    $output_login = new OutputLoginPage($this->domain, false);
                    $output_login->render([], false);
                }

                break;

            case 'logout':
                $this->session->logout();
                break;

            case 'register':
                if ($inputs['method'] === 'POST') {
                    $register = new Register($this->authorization, $this->domain);
                    $register->new();
                }

                if ($inputs['method'] === 'GET') {
                    $output_login = new OutputRegisterPage($this->domain, false);
                    $output_login->render(['section' => 'register'], false);
                }

                break;

            case 'private-message':
                $this->session->init(true);

                if (!$this->session->user()->checkPermission($this->domain, 'perm_private_messages_use')) {
                    nel_derp(511, _gettext('You cannot use the private message system.'));
                }

                $private_message = new PrivateMessage($this->domain->database(), $this->session, intval($inputs['message_id'] ?? 0));

                if ($inputs['method'] === 'GET') {
                    switch ($inputs['action']) {
                        case 'view':
                            $private_message->view();
                            break;

                        case 'mark-read':
                            $private_message->markRead();
                            break;

                        case 'delete':
                            $private_message->delete();
                            break;

                        case 'new':
                            $output_private_messages = new OutputPrivateMessages($this->domain, false);
                            $output_private_messages->newMessage([], false);
                            nel_clean_exit();
                            break;
                    }
                }

                if ($inputs['method'] === 'POST') {
                    switch ($inputs['action']) {
                        case 'send':
                            $private_message->collectFromPOST();
                            $private_message->send();
                            break;

                        case 'reply':
                            $private_message->reply();
                            break;
                    }
                }

                $output_private_messages = new OutputPrivateMessages($this->domain, false);
                $output_private_messages->messageList([], false);
                break;

            default:
                $this->session->init(true);

                if ($this->session->isActive()) {
                    $output_account = new OutputAccount($this->domain, false);
                    $output_account->render([], false);
                } else {
                    $output_login = new OutputLoginPage($this->domain, false);
                    $output_login->render([], false);
                }
        }
    }
}