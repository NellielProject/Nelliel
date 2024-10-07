<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Filters\Wordfilter;
use Nelliel\Output\OutputPanelWordfilters;

class AdminWordfilters extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_WORDFILTERS_TABLE;
        $this->id_column = 'filter_id';
        $this->panel_name = _gettext('Wordfilters');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_wordfilters');
        $output_panel = new OutputPanelWordfilters($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_wordfilters');
        $output_panel = new OutputPanelWordfilters($this->domain, false);
        $output_panel->new(['editing' => false], false);
    }

    public function add(): void
    {
        $board_id = $_POST['board_id'] ?? $this->domain->uri();
        $domain = Domain::getDomainFromID($board_id);
        $this->verifyPermissions($domain, 'perm_manage_wordfilters');
        $wordfilter = new Wordfilter($this->database, 0);
        $wordfilter->changeData('board_id', $domain->id());
        $wordfilter->changeData('text_match', $_POST['text_match'] ?? '');
        $wordfilter->changeData('replacement', $_POST['replacement'] ?? '');
        $wordfilter->changeData('filter_action', $_POST['filter_action'] ?? null);
        $wordfilter->changeData('notes', $_POST['notes'] ?? null);
        $wordfilter->changeData('enabled', $_POST['enabled'] ?? 0);
        $wordfilter->update();
        $this->panel();
    }

    public function editor(int $filter_id): void
    {
        $wordfilter = new Wordfilter($this->database, $filter_id);
        $domain = Domain::getDomainFromID($wordfilter->getData('board_id'));
        $this->verifyPermissions($domain, 'perm_manage_wordfilters');
        $output_panel = new OutputPanelWordfilters($this->domain, false);
        $output_panel->edit(['editing' => true, 'filter_id' => $filter_id], false);
    }

    public function update(int $filter_id): void
    {
        $wordfilter = new Wordfilter($this->database, $filter_id);
        $domain = Domain::getDomainFromID($wordfilter->getData('board_id'));
        $this->verifyPermissions($domain, 'perm_manage_wordfilters');
        $wordfilter->changeData('board_id', $_POST['board_id'] ?? $wordfilter->getData('board_id'));
        $wordfilter->changeData('text_match', $_POST['text_match'] ?? $wordfilter->getData('text_match'));
        $wordfilter->changeData('replacement', $_POST['replacement'] ?? $wordfilter->getData('replacement'));
        $wordfilter->changeData('filter_action', $_POST['filter_action'] ?? $wordfilter->getData('filter_action'));
        $wordfilter->changeData('notes', $_POST['notes'] ?? $wordfilter->getData('notes'));
        $wordfilter->changeData('enabled', $_POST['enabled'] ?? $wordfilter->getData('enabled'));
        $wordfilter->update();
        $this->panel();
    }

    public function delete(int $filter_id): void
    {
        $wordfilter = new Wordfilter($this->database, $filter_id);
        $domain = Domain::getDomainFromID($wordfilter->getData('board_id'));
        $this->verifyPermissions($domain, 'perm_manage_wordfilters');
        $wordfilter->delete();
        $this->panel();
    }

    public function enable(int $filter_id)
    {
        $wordfilter = new Wordfilter($this->database, $filter_id);
        $domain = Domain::getDomainFromID($wordfilter->getData('board_id'));
        $this->verifyPermissions($domain, 'perm_manage_wordfilters');
        $wordfilter->changeData('enabled', 1);
        $wordfilter->update();
        $this->panel();
    }

    public function disable(int $filter_id)
    {
        $wordfilter = new Wordfilter($this->database, $filter_id);
        $domain = Domain::getDomainFromID($wordfilter->getData('board_id'));
        $this->verifyPermissions($domain, 'perm_manage_wordfilters');
        $wordfilter->changeData('enabled', 0);
        $wordfilter->update();
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_wordfilters':
                nel_derp(400, _gettext('You are not allowed to manage wordfilters.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
