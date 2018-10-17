<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/thread_panel.php';

class PanelThreads extends PanelBase
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

        if (!$user->boardPerm($this->board_id, 'perm_post_access'))
        {
            nel_derp(350, _gettext('You are not allowed to access the threads panel.'));
        }

        if($inputs['action'] === 'update')
        {
            if (!$user->boardPerm($this->board_id, 'perm_post_modify'))
            {
                nel_derp(351, _gettext('You are not allowed to modify threads or posts.'));
            }

            $this->update();
            $this->renderPanel();
        }
        else
        {
            $this->renderPanel();
        }
    }

    public function renderPanel()
    {
        if (isset($_POST['expand_thread']))
        {
            $expand_data = explode(' ', $_POST['expand_thread']);
            nel_render_thread_panel_expand($this->board_id, $expand_data[1]);
        }
        else
        {
            nel_render_thread_panel_main($this->board_id);
        }
    }

    public function add()
    {
    }

    public function edit()
    {
    }

    public function update()
    {
        $thread_handler = new \Nelliel\ThreadHandler($this->database, $this->board_id);
        $updates = $thread_handler->processContentDeletes();
        $regen = new \Nelliel\Regen();
        $regen->threads($this->board_id, true, $updates);
        $regen->index($this->board_id);
    }

    public function remove()
    {
    }


}
