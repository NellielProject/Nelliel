<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminNews extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_NEWS_TABLE;
        $this->id_field = 'entry';
        $this->id_column = 'entry';
        $this->panel_name = _gettext('News');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_news_manage');
        $output_panel = new \Nelliel\Output\OutputPanelNews($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_news_manage');
        $news_info = array();
        $news_info['user_id'] = $this->session_user->id();
        $news_info['name'] = $_POST['name'] ?? '';

        if ($news_info['name'] === '' || !$this->session_user->checkPermission($this->domain, 'perm_custom_name'))
        {
            $this->session_user->getData('display_name');
        }

        $news_info['headline'] = $_POST['headline'] ?? null;
        $news_info['time'] = time();
        $news_info['text'] = $_POST['text'] ?? null;
        $query = 'INSERT INTO "' . $this->data_table .
                '" ("user_id", "name", "headline", "time", "text") VALUES (?, ?, ?, ?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared,
                [$news_info['user_id'], $news_info['name'], $news_info['headline'], $news_info['time'],
                    $news_info['text']]);
        $regen = new \Nelliel\Regen();
        $regen->news($this->domain);
        $this->outputMain(true);
    }

    public function editor(): void
    {
    }

    public function update(): void
    {
    }

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_news_manage');
        $id = $_GET[$this->id_field] ?? 0;
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $regen = new \Nelliel\Regen();
        $regen->news($this->domain);
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm))
        {
            return;
        }

        switch ($perm)
        {
            case 'perm_news_manage':
                nel_derp(360, _gettext('You are not allowed to manage news entries.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
