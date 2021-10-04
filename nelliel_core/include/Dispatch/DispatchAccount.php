<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Register;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputAccount;
use Nelliel\Output\OutputLoginPage;
use Nelliel\Output\OutputRegisterPage;
use Nelliel\PrivateMessage;
use Nelliel\Output\OutputPrivateMessages;

class DispatchAccount extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs)
    {
        switch ($inputs['section'])
        {
            case 'login':
                if ($inputs['actions'][0] === 'submit')
                {
                    $this->session->login();
                    $this->session->loggedInOrError();
                    $output_account = new OutputAccount($this->domain, false);
                    $output_account->render([], false);
                }
                else
                {
                    $output_login = new OutputLoginPage($this->domain, false);
                    $output_login->render([], false);
                }

                break;

            case 'logout':
                $this->session->logout();
                break;

            case 'register':
                if ($inputs['actions'][0] === 'submit')
                {
                    $register = new Register($this->authorization, $this->domain);
                    $register->new();
                }
                else
                {
                    $output_login = new OutputRegisterPage($this->domain, false);
                    $output_login->render(['section' => 'register'], false);
                }

                break;

            case 'private-message':
                $this->session->init(true);

                if (!$this->session->user()->checkPermission($this->domain, 'perm_private_messages_use'))
                {
                    nel_derp(511, _gettext('You cannot use the private message system.'));
                }

                $message_id = intval($_GET['message-id'] ?? 0);
                $private_message = new PrivateMessage($this->domain->database(), $this->session, $message_id);

                if ($inputs['actions'][0] === 'send')
                {
                    $private_message->collectFromPOST();
                    $private_message->send();
                }
                else if ($inputs['actions'][0] === 'mark-read')
                {
                    $private_message->markRead();
                }
                else if ($inputs['actions'][0] === 'delete')
                {
                    $private_message->delete();
                }
                else if ($inputs['actions'][0] === 'view')
                {
                    $private_message->view();
                    break;
                }
                else if ($inputs['actions'][0] === 'new')
                {
                    $output_private_messages = new OutputPrivateMessages($this->domain, false);
                    $output_private_messages->newMessage([], false);
                    break;
                }
                else if ($inputs['actions'][0] === 'reply')
                {
                    $private_message->reply();
                    break;
                }

                $output_private_messages = new OutputPrivateMessages($this->domain, false);
                $output_private_messages->messageList([], false);
                break;

            default:
                $this->session->init(true);

                if ($this->session->isActive())
                {
                    $output_account = new OutputAccount($this->domain, false);
                    $output_account->render([], false);
                }
                else
                {
                    $output_login = new OutputLoginPage($this->domain, false);
                    $output_login->render([], false);
                }
        }
    }
}