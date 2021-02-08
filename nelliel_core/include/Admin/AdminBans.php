<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;

class AdminBans extends Admin
{
    private $ban_hammer;

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
        $this->ban_hammer = new \Nelliel\BanHammer($this->database);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelBans($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator()
    {
        $this->verifyAccess();
        $ban_ip = $_GET['ban-ip'] ?? '';
        $output_panel = new \Nelliel\Render\OutputPanelBans($this->domain, false);
        $output_panel->new(['ban_ip' => $ban_ip], false);
        $this->outputMain(false);
    }

    public function add()
    {
        $this->verifyAction();
        $this->ban_hammer->collectFromPOST();
        $this->ban_hammer->apply();

        if (isset($_GET['content-id']))
        {
            $content_id = new ContentID($_GET['content-id']);
            $mod_post_comment = $_POST['mod_post_comment'] ?? null;

            if ($content_id->isPost() && !is_null($mod_post_comment))
            {
                $content_post = $content_id->getInstanceFromID($this->domain);
                $content_post->loadFromDatabase();
                $content_post->changeData('mod_comment', $mod_post_comment);
                $content_post->writeToDatabase();
                $regen = new \Nelliel\Regen();
                $regen->threads($this->domain, true, [$content_id->postID()]);
                $regen->index($this->domain);
                $regen->overboard($this->domain);
            }
        }

        $this->outputMain(true);
    }

    public function editor()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelBans($this->domain, false);
        $output_panel->modify([], false);
        $this->outputMain(false);
    }

    public function update()
    {
        $this->verifyAction();
        $this->ban_hammer->loadFromID($_POST['ban_id']);
        $this->ban_hammer->collectFromPOST();
        $this->ban_hammer->apply();
        $this->outputMain(true);
    }

    public function remove()
    {
        $this->verifyAction();
        $ban_id = $_GET['ban_id'] ?? '';
        $this->ban_hammer->loadFromID($ban_id);
        $this->ban_hammer->remove();
        $this->outputMain(true);
    }

    public function enable()
    {
        $this->verifyAction();
    }

    public function disable()
    {
        $this->verifyAction();
    }

    public function makeDefault()
    {
        $this->verifyAction();
    }

    public function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_bans'))
        {
            nel_derp(320, _gettext('You do not have access to the Bans panel.'));
        }
    }

    public function verifyAction()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_bans'))
        {
            nel_derp(321, _gettext('You are not allowed to manage bans.'));
        }
    }
}
