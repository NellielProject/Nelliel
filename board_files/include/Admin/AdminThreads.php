<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/thread_panel.php';

class AdminThreads extends AdminBase
{
    private $domain;

    function __construct($database, $authorization, $domain)
    {
        $this->database = $database;
        $this->authorization = $authorization;
        $this->domain = $domain;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
        $user = $session->sessionUser();

        if($inputs['action'] === 'update')
        {
            $this->update($user);
            $this->renderPanel($user);
        }
        else
        {
            $this->renderPanel($user);
        }
    }

    public function renderPanel($user)
    {
        if (isset($_POST['expand_thread']))
        {
            $expand_data = explode(' ', $_POST['expand_thread']);
            nel_render_thread_panel_expand($user, $this->domain, $expand_data[1]);
        }
        else
        {
            nel_render_thread_panel_main($user, $this->domain);
        }
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
    }

    public function editor($user)
    {
    }

    public function update($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_threads_modify'))
        {
            nel_derp(351, _gettext('You are not allowed to modify threads or posts.'));
        }

        $thread_handler = new \Nelliel\ThreadHandler($this->database, $this->domain->id());
        $thread_handler->processContentDeletes();
    }

    public function remove($user)
    {
    }
}
