<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminBans extends AdminHandler
{
    private $ban_hammer;

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->ban_hammer = new \Nelliel\BanHammer($this->database);
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Account\Session();
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
    }

    public function renderPanel($user)
    {
        $output_panel = new \Nelliel\Output\OutputPanelBans($this->domain);
        $output_panel->render(['section' => 'panel', 'user' => $user], false);
    }

    public function creator($user)
    {
        $ip = $_GET['ban_ip'] ?? '';
        $type = $_GET['ban_type'] ?? 'GENERAL';
        $output_panel = new \Nelliel\Output\OutputPanelBans($this->domain);
        $output_panel->render(['section' => 'add', 'user' => $user, 'ip' => $ip, 'type' => $type], false);
    }

    public function add($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_manage_bans'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $ban_input = $this->ban_hammer->postToArray();
        $this->ban_hammer->addBan($ban_input);

        if (isset($_GET['post-id']))
        {
            if (isset($_POST['mod_post_comment']) && !empty($_POST['mod_post_comment']))
            {
                $post_table = $this->domain->reference('posts_table');
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
        $output_panel = new \Nelliel\Output\OutputPanelBans($this->domain);
        $output_panel->render(['section' => 'modify', 'user' => $user], false);
    }

    public function update($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_manage_bans'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $ban_input = $this->ban_hammer->postToArray();

        if ($ban_input['all_boards'] === 1 && !$user->domainPermission($this->domain, 'perm_manage_bans'))
        {
            nel_derp(322, _gettext('You are not allowed to ban from all boards.'));
        }

        $this->ban_hammer->modifyBan($ban_input);
        $this->renderPanel($user);
    }

    public function remove($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_manage_bans'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $ban_input = $this->ban_hammer->postToArray();
        $this->ban_hammer->removeBan($this->domain, $_GET['ban_id']);
        $this->renderPanel($user);
    }
}
