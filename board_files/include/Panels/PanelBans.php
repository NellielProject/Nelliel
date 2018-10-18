<?php

namespace Nelliel\Panels;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/ban_panel.php';

class PanelBans extends PanelBase
{
    private $board_id;
    private $ban_hammer;

    function __construct($database, $authorize, $board_id = null)
    {
        $this->database = $database;
        $this->authorize = $authorize;
        $this->board_id = (is_null($board_id)) ? '' : $board_id;
        $this->ban_hammer = new \Nelliel\BanHammer(nel_database(), nel_authorize());
    }

    // TODO: Separate this out more.
    public function actionDispatch($inputs)
    {
        $user = $this->authorize->getUser($_SESSION['username']);

        if ($inputs['action'] === 'modify')
        {
            if (!$user->boardPerm($this->board_id, 'perm_ban_modify'))
            {
                nel_derp(322, _gettext('You are not allowed to modify bans.'));
            }

            nel_render_ban_panel_modify($this->board_id);
        }
        else if ($inputs['action'] === 'new')
        {
            if (!$user->boardPerm($this->board_id, 'perm_ban_add'))
            {
                nel_derp(321, _gettext('You are not allowed to add new bans.'));
            }

            $ip = (isset($_GET['ban_ip'])) ? $_GET['ban_ip'] : '';
            $type = (isset($_GET['ban_type'])) ? $_GET['ban_type'] : 'GENERAL';
            nel_render_ban_panel_add($this->board_id, $ip, $type);
        }
        else if ($inputs['action'] === 'add')
        {
            if (!$user->boardPerm($this->board_id, 'perm_ban_add'))
            {
                nel_derp(321, _gettext('You are not allowed to add new bans.'));
            }

            $this->add();
            $this->renderPanel();
        }
        else if ($inputs['action'] === 'remove')
        {
            if (!$user->boardPerm($this->board_id, 'perm_ban_modify'))
            {
                nel_derp(323, _gettext('You are not allowed to delete bans.'));
            }

            $this->remove();
            $this->renderPanel();
        }
        else if ($inputs['action'] === 'update')
        {
            if (!$user->boardPerm($this->board_id, 'perm_ban_modify'))
            {
                nel_derp(322, _gettext('You are not allowed to modify bans.'));
            }

            $this->update();
            $this->renderPanel();
        }
        else
        {
            if (!$user->boardPerm($this->board_id, 'perm_ban_access'))
            {
                nel_derp(320, _gettext('You are not allowed to access the bans panel.'));
            }

            $this->renderPanel();
        }
    }

    public function renderPanel()
    {
        nel_render_main_ban_panel($this->board_id);
    }

    public function add()
    {
        $ban_input = $this->ban_hammer->postToArray();
        $this->ban_hammer->addBan($ban_input);

        if (isset($_GET['post-id']))
        {
            if (isset($_POST['mod_post_comment']) && !empty($_POST['mod_post_comment']))
            {
                $post_table = nel_parameters_and_data()->boardReferences($this->board_id, 'post_table');
                $prepared = $this->database->prepare(
                        'UPDATE "' . $post_table . '" SET "mod_comment" = ? WHERE "post_number" = ?');

                $this->database->executePrepared($prepared, array($_POST['mod_post_comment'], $_GET['post-id']));
                $regen = new \Nelliel\Regen();
                $regen->threads($this->board_id, true, array($_GET['post-id']));
                $regen->index($this->board_id);
            }
        }
    }

    public function edit()
    {
    }

    public function update()
    {
        $ban_input = $this->ban_hammer->postToArray();
        $this->ban_hammer->modifyBan($ban_input);
    }

    public function remove()
    {
        $ban_input = $this->ban_hammer->postToArray();
        $this->ban_hammer->removeBan($this->board_id, $_GET['ban_id']);
    }


}
