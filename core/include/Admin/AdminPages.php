<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelPages;
use PDO;

class AdminPages extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_PAGES_TABLE;
        $this->id_field = 'page-id';
        $this->id_column = 'entry';
        $this->panel_name = _gettext('Pages');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_pages_manage');
        $output_panel = new OutputPanelPages($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_pages_manage');
        $output_panel = new OutputPanelPages($this->domain, false);
        $output_panel->new([], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_pages_manage');
        $this->checkLimit($this->domain);
        $page_info = array();
        $page_info['domain_id'] = $this->domain->id();
        $page_info['uri'] = $_POST['uri'] ?? '';
        $page_info['title'] = $_POST['title'] ?? '';
        $page_info['text'] = $_POST['text'] ?? '';
        $page_info['markup_type'] = 'html'; // TODO: Other types
        $query = 'INSERT INTO "' . $this->data_table .
            '" ("domain_id", "uri", "title", "text", "markup_type") VALUES (?, ?, ?, ?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared,
            [$page_info['domain_id'], $page_info['uri'], $page_info['title'], $page_info['text'],
                $page_info['markup_type']]);
        $regen = new Regen();
        $regen->page($this->domain, $_POST['uri']);
        $this->outputMain(true);
    }

    public function editor(): void
    {
        $this->verifyPermissions($this->domain, 'perm_pages_manage');
        $output_panel = new OutputPanelPages($this->domain, false);
        $output_panel->edit(['entry' => $_GET['page-id'] ?? 0], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $id = $_GET[$this->id_field] ?? 0;
        $prepared = $this->domain->database()->prepare(
            'SELECT "domain_id" FROM "' . NEL_PAGES_TABLE . '" WHERE "entry" = :id');
        $prepared->bindValue(':id', $id);
        $domain_id = $this->domain->database()->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

        if ($domain_id === false) {
            return;
        }

        $domain = Domain::getDomainFromID($domain_id, $this->database);
        $this->verifyPermissions($domain, 'perm_pages_manage');
        $page_info = array();
        $page_info['uri'] = $_POST['uri'] ?? '';
        $page_info['title'] = $_POST['title'] ?? '';
        $page_info['text'] = $_POST['text'] ?? '';
        $page_info['markup_type'] = 'html';
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_PAGES_TABLE .
            '" SET "uri" = ?, "title" = ?, "text" = ?, "markup_type" = ? WHERE "entry" = ?');
        $this->database->executePrepared($prepared,
            [$page_info['uri'], $page_info['title'], $page_info['text'], $page_info['markup_type'], $id]);
        $regen = new Regen();
        $regen->page($domain, $page_info['uri']);
        $this->outputMain(true);
    }

    public function remove(): void
    {
        $id = $_GET[$this->id_field] ?? 0;
        $prepared = $this->domain->database()->prepare(
            'SELECT "uri", "domain_id" FROM "' . NEL_PAGES_TABLE . '" WHERE "entry" = :id');
        $prepared->bindValue(':id', $id);
        $info = $this->domain->database()->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if ($info === false) {
            return;
        }

        $domain_id = $info['domain_id'];
        $domain = Domain::getDomainFromID($domain_id, $this->database);
        $this->verifyPermissions($domain, 'perm_pages_manage');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        nel_utilities()->fileHandler()->eraserGun($domain->reference('base_path'), $info['uri'] . '.html');
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_pages_manage':
                nel_derp(360, _gettext('You are not allowed to manage static pages.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    private function checkLimit(Domain $domain): void
    {
        if ($domain->id() !== Domain::SITE) {
            $prepared = $this->domain->database()->prepare(
                'SELECT COUNT("entry") FROM "' . NEL_PAGES_TABLE . '" WHERE "domain_id" = :domain_id');
            $prepared->bindValue(':domain_id', $domain->id());
            $page_count = $this->domain->database()->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

            if ($page_count >= nel_site_domain()->setting('max_board_pages')) {
                nel_derp(250, _gettext('The maximum number of static pages for this board has been reached.'));
            }
        }
    }
}
