<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminBoards;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputInterstitial;

class DispatchManageBoards extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $boards = new AdminBoards($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'POST') {
                    $boards->add();
                }

                break;

            case 'delete':
                if ($inputs['method'] === 'GET') {
                    $messages[] = sprintf(_gettext('You are about to delete the board: %s'), $inputs['id']);
                    $messages[] = _gettext(
                        'This will wipe out all posts, settings, files, everything. All the things get shoved into /dev/null. There is no undo or recovery.');
                    $messages[] = _gettext('Are you absolutely sure?');
                    $no_info['text'] = _gettext('NOPE. Get me out of here!');
                    $no_info['url'] = nel_build_router_url([$this->domain->id(), 'manage-boards']);
                    $yes_info['text'] = _gettext('Delete the board');
                    $yes_info['url'] = nel_build_router_url(
                        [$this->domain->id(), 'manage-boards', $inputs['id'], 'delete']);
                    $output_interstitial = new OutputInterstitial($this->domain, false);
                    echo $output_interstitial->confirm([], false, $messages, $yes_info, $no_info);
                }

                if ($inputs['method'] === 'POST') {
                    $boards->delete($inputs['id']);
                }

                break;

            case 'lock':
                $boards->lock($inputs['id']);
                break;

            case 'unlock':
                $boards->unlock($inputs['id']);
                break;

            case 'remove-confirmed':
                $boards->delete($inputs['id'], true);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $boards->panel();
                }
        }
    }
}