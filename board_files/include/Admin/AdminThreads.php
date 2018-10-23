<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/thread_panel.php';

class PanelThreads extends AdminBase
{
    private $board_id;

    function __construct($database, $authorize, $board_id = null)
    {
        $this->database = $database;
        $this->authorize = $authorize;
        $this->board_id = (is_null($board_id)) ? '' : $board_id;
    }

    public function actionDispatch($inputs)
    {
        $user = $this->authorize->getUser($_SESSION['username']);

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
            nel_render_thread_panel_expand($user, $this->board_id, $expand_data[1]);
        }
        else
        {
            nel_render_thread_panel_main($user, $this->board_id);
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
        if (!$user->boardPerm($this->board_id, 'perm_threads_modify'))
        {
            nel_derp(351, _gettext('You are not allowed to modify threads or posts.'));
        }

        $thread_handler = new \Nelliel\ThreadHandler($this->database, $this->board_id);
        $thread_handler->processContentDeletes();
    }

    public function remove($user)
    {
    }
}
