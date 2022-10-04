<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelNews;

class AdminNews extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_NEWS_TABLE;
        $this->id_column = 'article_id';
        $this->panel_name = _gettext('News');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_news');
        $output_panel = new OutputPanelNews($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {}

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_news');
        $news_info = array();
        $news_info['username'] = $this->session_user->id();
        $news_info['name'] = $_POST['name'] ?? '';

        if ($news_info['name'] === '' || !$this->session_user->checkPermission($this->domain, 'perm_custom_name')) {
            $this->session_user->id();
        }

        $news_info['headline'] = $_POST['headline'] ?? null;
        $news_info['time'] = time();
        $news_info['text'] = $_POST['text'] ?? null;
        $query = 'INSERT INTO "' . $this->data_table .
            '" ("username", "name", "headline", "time", "text") VALUES (?, ?, ?, ?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared,
            [$news_info['username'], $news_info['name'], $news_info['headline'], $news_info['time'], $news_info['text']]);
        $regen = new Regen();
        $regen->news($this->domain);
        $this->panel();
    }

    public function editor(): void
    {}

    public function update(): void
    {}

    public function delete(string $article_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_news');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "article_id" = ?');
        $this->database->executePrepared($prepared, [$article_id]);
        $regen = new Regen();
        $regen->news($this->domain);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_news':
                nel_derp(360, _gettext('You are not allowed to manage news entries.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
