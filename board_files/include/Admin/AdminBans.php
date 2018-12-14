<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/ban_panel.php';

class AdminBans extends AdminBase
{
    private $domain;
    private $ban_hammer;

    function __construct($database, $authorization, $domain)
    {
        $this->database = $database;
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->ban_hammer = new \Nelliel\BanHammer($database);
    }

    // TODO: Separate this out more.
    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
        $user = $session->sessionUser();

        if ($inputs['action'] === 'modify')
        {
            $this->editor($user);
        }
        else if ($inputs['action'] === 'new' || $inputs['action'] === 'ban-delete')
        {
            $this->creator($user);
        }
        else if ($inputs['action'] === 'add')
        {
            $this->add($user);
        }
        else if ($inputs['action'] === 'remove')
        {
            $this->remove($user);
        }
        else if ($inputs['action'] === 'update')
        {
            $this->update($user);
        }
        else
        {
            $this->renderPanel($user);
        }

        $this->applyNewBan();
    }

    public function renderPanel($user)
    {

        nel_render_main_ban_panel($user, $this->domain);
    }

    public function creator($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $ip = (isset($_GET['ban_ip'])) ? $_GET['ban_ip'] : '';
        $type = (isset($_GET['ban_type'])) ? $_GET['ban_type'] : 'GENERAL';
        $snacks = new \Nelliel\Snacks(nel_database(), new \Nelliel\BanHammer(nel_database()));
        $this->applyNewBan();
        nel_render_ban_panel_add($this->domain, $ip, $type);
    }

    public function add($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $ban_input = $this->ban_hammer->postToArray();
        $this->ban_hammer->addBan($ban_input);

        if (isset($_GET['post-id']))
        {
            if (isset($_POST['mod_post_comment']) && !empty($_POST['mod_post_comment']))
            {
                $post_table = $this->domain->reference('post_table');
                $prepared = $this->database->prepare(
                        'UPDATE "' . $post_table . '" SET "mod_comment" = ? WHERE "post_number" = ?');

                $this->database->executePrepared($prepared, array($_POST['mod_post_comment'], $_GET['post-id']));
                $regen = new \Nelliel\Regen();
                $regen->threads($this->domain, true, array($_GET['post-id']));
                $regen->index($this->domain);
            }
        }
    }

    public function editor($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $this->applyNewBan();
        nel_render_ban_panel_modify($this->domain);
    }

    public function update($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $ban_input = $this->ban_hammer->postToArray();
        $this->ban_hammer->modifyBan($ban_input);
    }

    public function remove($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $ban_input = $this->ban_hammer->postToArray();
        $this->ban_hammer->removeBan($this->domain->id(), $_GET['ban_id']);
    }

    private function applyNewBan()
    {
        $snacks = new \Nelliel\Snacks($this->database, new \Nelliel\BanHammer($this->database));
        $snacks->applyBan(array(), $this->domain);
    }
}
