<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;

class AdminBans extends Admin
{
    private $ban_hammer;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->ban_hammer = new \Nelliel\BanHammer($this->database);
        $this->data_table = NEL_BANS_TABLE;
        $this->id_field = 'ban-id';
        $this->id_column = 'ban_id';
        $this->panel_name = _gettext('Bans');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_bans_view');
        $output_panel = new \Nelliel\Output\OutputPanelBans($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_bans_add');
        $ban_ip = $_GET['ban-ip'] ?? '';
        $output_panel = new \Nelliel\Output\OutputPanelBans($this->domain, false);
        $output_panel->new(['ban_ip' => $ban_ip], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_bans_add');
        $this->ban_hammer->collectFromPOST();
        $this->ban_hammer->apply();

        if (isset($_GET['content-id']))
        {
            $content_id = new ContentID($_GET['content-id']);
            $mod_post_comment = $_POST['mod_post_comment'] ?? null;

            if ($content_id->isPost() && !is_null($mod_post_comment))
            {
                $content_post = $content_id->getInstanceFromID($this->domain);
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

    public function editor(): void
    {
        $this->verifyPermissions($this->domain, 'perm_bans_modify');
        $output_panel = new \Nelliel\Output\OutputPanelBans($this->domain, false);
        $output_panel->modify([], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_bans_modify');
        $this->ban_hammer->loadFromID($_POST['ban_id']);
        $this->ban_hammer->collectFromPOST();
        $this->ban_hammer->apply();
        $this->outputMain(true);
    }

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_bans_delete');
        $ban_id = $_GET['ban_id'] ?? '';
        $this->ban_hammer->loadFromID($ban_id);
        $this->ban_hammer->remove();
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm))
        {
            return;
        }

        switch ($perm)
        {
            case 'perm_bans_view':
                nel_derp(310, sprintf(_gettext('You do not have access to the %s control panel.'), $this->panel_name));
                break;

            case 'perm_bans_add':
                nel_derp(311, _gettext('You cannot add new bans.'));
                break;

            case 'perm_bans_modify':
                nel_derp(312, _gettext('You cannot modify existing bans.'));
                break;

            case 'perm_bans_delete':
                nel_derp(313, _gettext('You cannot delete existing bans.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
