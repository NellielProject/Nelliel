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
        $this->validateUser();
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelBans($this->domain, false);
        $output_panel->render(['section' => 'panel', 'user' => $this->session_user], false);
    }

    public function creator()
    {
        $ip_start = $_GET['ban_ip_start'] ?? '';
        $hashed_ip = $_GET['ban_hashed_ip'] ?? '';
        $ban_type = $_GET['ban_type'] ?? 'GENERAL';
        $output_panel = new \Nelliel\Output\OutputPanelBans($this->domain, false);
        $output_panel->render(
                ['section' => 'add', 'user' => $this->session_user, 'ip_start' => $ip_start, 'hashed_ip' => $hashed_ip,
                    'ban_type' => $ban_type], false);
        $this->outputMain(false);
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_bans'))
        {
            nel_derp(321, _gettext('You are not allowed to issue bans.'));
        }

        $this->ban_hammer->collectFromPOST();
        $this->ban_hammer->apply();

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
                $regen->overboard($this->domain);
            }
        }

        $this->outputMain(true);
    }

    public function editor()
    {
        $output_panel = new \Nelliel\Output\OutputPanelBans($this->domain, false);
        $output_panel->render(['section' => 'modify', 'user' => $this->session_user], false);
        $this->outputMain(false);
    }

    public function update()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_bans'))
        {
            nel_derp(322, _gettext('You are not allowed to modify bans.'));
        }

        $this->ban_hammer->collectFromPOST();

        // TODO: Update or remove this perm
        if ($this->ban_hammer->getData('all_boards') === 1 &&
                !$this->session_user->checkPermission($this->domain, 'perm_manage_bans'))
        {
            nel_derp(323, _gettext('You are not allowed to ban from all boards.'));
        }

        $this->ban_hammer->apply();
        $this->outputMain(true);
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_bans'))
        {
            nel_derp(324, _gettext('You are not allowed to modify bans.'));
        }

        $ban_id = $_GET['ban_id'] ?? '';
        $this->ban_hammer->loadFromID($ban_id);
        $this->ban_hammer->remove();
        $this->outputMain(true);
    }
}
