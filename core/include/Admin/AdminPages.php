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
        $this->id_column = 'page_id';
        $this->panel_name = _gettext('Pages');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_pages');
        $output_panel = new OutputPanelPages($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_pages');
        $output_panel = new OutputPanelPages($this->domain, false);
        $output_panel->new([], false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_pages');
        $this->checkLimit($this->domain);
        $page_info = array();
        $page_info['domain_id'] = $this->domain->id();
        $page_info['uri'] = $_POST['uri'] ?? '';
        $page_info['title'] = $_POST['title'] ?? '';
        $page_info['text'] = $_POST['text'] ?? '';
        $page_info['markup_type'] = $_POST['markup_type'] ?? 'none';

        if ($page_info['markup_type'] === 'html' && $this->session_user->checkPermission($this->domain, 'perm_raw_html')) {
            $page_info['markup_type'] === 'none';
        }

        $query = 'INSERT INTO "' . $this->data_table .
            '" ("domain_id", "uri", "title", "text", "markup_type") VALUES (?, ?, ?, ?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared,
            [$page_info['domain_id'], $page_info['uri'], $page_info['title'], $page_info['text'],
                $page_info['markup_type']]);
        $regen = new Regen();
        $regen->page($this->domain, $_POST['uri']);
        $this->panel();
    }

    public function editor(string $page_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_pages');
        $output_panel = new OutputPanelPages($this->domain, false);
        $output_panel->edit(['page_id' => $page_id], false);
    }

    public function update(string $page_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_pages');
        $prepared = $this->domain->database()->prepare(
            'SELECT "domain_id" FROM "' . NEL_PAGES_TABLE . '" WHERE "page_id" = :page_id');
        $prepared->bindValue(':page_id', $page_id);
        $domain_id = $this->domain->database()->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

        if ($domain_id === false) {
            return;
        }

        $domain = Domain::getDomainFromID($domain_id);
        $this->verifyPermissions($domain, 'perm_manage_pages');
        $page_info = array();
        $page_info['uri'] = $_POST['uri'] ?? '';
        $page_info['title'] = $_POST['title'] ?? '';
        $page_info['text'] = $_POST['text'] ?? '';
        $page_info['markup_type'] = $_POST['markup_type'] ?? 'none';

        if ($page_info['markup_type'] === 'html' && $this->session_user->checkPermission($this->domain, 'perm_raw_html')) {
            $page_info['markup_type'] === 'none';
        }

        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_PAGES_TABLE .
            '" SET "uri" = ?, "title" = ?, "text" = ?, "markup_type" = ? WHERE "page_id" = ?');
        $this->database->executePrepared($prepared,
            [$page_info['uri'], $page_info['title'], $page_info['text'], $page_info['markup_type'], $page_id]);
        $regen = new Regen();
        $regen->page($domain, $page_info['uri']);
        $this->panel();
    }

    public function remove(string $page_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_pages');
        $prepared = $this->domain->database()->prepare(
            'SELECT "uri", "domain_id" FROM "' . NEL_PAGES_TABLE . '" WHERE "page_id" = :page_id');
        $prepared->bindValue(':page_id', $page_id);
        $info = $this->domain->database()->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if ($info === false) {
            return;
        }

        $domain_id = $info['domain_id'];
        $domain = Domain::getDomainFromID($domain_id);
        $this->verifyPermissions($domain, 'perm_manage_pages');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "page_id" = ?');
        $this->database->executePrepared($prepared, [$page_id]);
        nel_utilities()->fileHandler()->eraserGun($domain->reference('base_path'), $info['uri'] . '.html');
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_pages':
                nel_derp(360, _gettext('You are not allowed to manage static pages.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    private function checkLimit(Domain $domain): void
    {
        if ($domain->id() !== Domain::SITE) {
            $prepared = $this->domain->database()->prepare(
                'SELECT COUNT("page_id") FROM "' . NEL_PAGES_TABLE . '" WHERE "domain_id" = :domain_id');
            $prepared->bindValue(':domain_id', $domain->id());
            $page_count = $this->domain->database()->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

            if ($page_count >= nel_get_cached_domain(Domain::SITE)->setting('max_board_pages')) {
                nel_derp(270, _gettext('The maximum number of static pages for this board has been reached.'));
            }
        }
    }
}
