<?php

declare(strict_types=1);

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminNews extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function renderPanel()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Render\OutputPanelNews($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
        $this->verifyAccess($this->domain);
    }

    public function add()
    {
        $this->verifyAction($this->domain);
        $news_info = array();
        $news_info['poster_id'] = $this->session_user->id();
        $news_info['headline'] = $_POST['headline'] ?? null;
        $news_info['time'] = time();
        $news_info['text'] = $_POST['news_text'] ?? null;
        $query = 'INSERT INTO "' . NEL_NEWS_TABLE . '" ("poster_id", "headline", "time", "text") VALUES (?, ?, ?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared,
                [$news_info['poster_id'], $news_info['headline'], $news_info['time'], $news_info['text']]);
        $this->regenNews();
        $this->outputMain(true);
    }

    public function editor()
    {
        $this->verifyAccess($this->domain);
    }

    public function update()
    {
        $this->verifyAction($this->domain);
    }

    public function remove()
    {
        $this->verifyAction($this->domain);
        $entry = $_GET['entry'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_NEWS_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$entry]);
        $this->regenNews();
        $this->outputMain(true);
    }

    public function enable()
    {
        $this->verifyAction($this->domain);
    }

    public function disable()
    {
        $this->verifyAction($this->domain);
    }

    public function makeDefault()
    {
        $this->verifyAction($this->domain);
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
