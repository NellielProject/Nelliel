<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminNews extends AdminHandler
{
    private $defaults = false;

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->validateUser();
    }

    public function actionDispatch($inputs)
    {
        if ($inputs['action'] === 'add')
        {
            $this->add();
        }
        else if ($inputs['action'] === 'remove')
        {
            $this->remove();
        }

        $this->renderPanel();
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelNews($this->domain);
        $output_panel->render(['user' => $this->session_user], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_news'))
        {
            nel_derp(471, _gettext('You are not allowed to modify news.'));
        }

        $news_info = array();
        $news_info['poster_id'] = $this->session_user->id();
        $news_info['headline'] = $_POST['headline'] ?? null;
        $news_info['time'] = time();
        $news_info['text'] = $_POST['news_text'] ?? null;
        $query = 'INSERT INTO "' . NEWS_TABLE . '" ("poster_id", "headline", "time", "text") VALUES (?, ?, ?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared,
                [$news_info['poster_id'], $news_info['headline'], $news_info['time'], $news_info['text']]);
        $this->regenNews();
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_news'))
        {
            nel_derp(471, _gettext('You are not allowed to modify news.'));
        }

        $entry = $_GET['entry'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEWS_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$entry]);
        $this->regenNews();
    }

    private function regenNews()
    {
        $regen = new \Nelliel\Regen();
        $regen->news($this->domain);
    }
}
