<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/ban_panel.php';

class AdminBans extends AdminHandler
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
        if (!$user->boardPerm($this->domain->id(), 'perm_ban_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the bans panel.'));
        }

        nel_render_main_ban_panel($user, $this->domain);
    }

    public function creator($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $ip = $_GET['ban_ip'] ?? '';
        $type = $_GET['ban_type'] ?? 'GENERAL';
        $snacks = new \Nelliel\Snacks(nel_database(), new \Nelliel\BanHammer(nel_database()));
        $this->applyNewBan();
        nel_render_ban_panel_add($user, $this->domain, $ip, $type);
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

                $this->database->executePrepared($prepared, [$_POST['mod_post_comment'], $_GET['post-id']]);
                $regen = new \Nelliel\Regen();
                $regen->threads($this->domain, true, [$_GET['post-id']]);
                $regen->index($this->domain);
            }
        }

        $this->renderPanel($user);
    }

    public function editor($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $this->applyNewBan();
        nel_render_ban_panel_modify($user, $this->domain);
    }

    public function update($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $ban_input = $this->ban_hammer->postToArray();

        if ($ban_input['all_boards'] === 1 && !$user->boardPerm('', 'perm_ban_modify'))
        {
            nel_derp(322, _gettext('You are not allowed to ban from all boards.'));
        }

        $this->ban_hammer->modifyBan($ban_input);
        $this->renderPanel($user);
    }

    public function remove($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $ban_input = $this->ban_hammer->postToArray();
        $this->ban_hammer->removeBan($this->domain->id(), $_GET['ban_id']);
        $this->renderPanel($user);
    }

    private function applyNewBan()
    {
        $snacks = new \Nelliel\Snacks($this->database, new \Nelliel\BanHammer($this->database));
        $snacks->applyBan($this->domain);
    }
}
