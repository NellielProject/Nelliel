<?php

declare(strict_types=1);

namespace Nelliel\Modules\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Modules\Account\Session;
use Nelliel\Auth\Authorization;

class AdminNews extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_NEWS_TABLE;
        $this->id_field = 'entry';
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Modules\Output\OutputPanelNews($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        $this->verifyAction($this->domain);
        $news_info = array();
        $news_info['poster_id'] = $this->session_user->id();
        $news_info['headline'] = $_POST['headline'] ?? null;
        $news_info['time'] = time();
        $news_info['text'] = $_POST['news_text'] ?? null;
        $query = 'INSERT INTO "' . $this->data_table . '" ("poster_id", "headline", "time", "text") VALUES (?, ?, ?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared,
                [$news_info['poster_id'], $news_info['headline'], $news_info['time'], $news_info['text']]);
        $this->regenNews();
        $this->outputMain(true);
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->regenNews();
        $this->outputMain(true);
    }

    private function regenNews()
    {
        $regen = new \Nelliel\Regen();
        $regen->news($this->domain);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_news'))
        {
            nel_derp(440, _gettext('You do not have access to the News panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_news'))
        {
            nel_derp(441, _gettext('You are not allowed to manage news articles.'));
        }
    }
}
