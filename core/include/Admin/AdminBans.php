<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Bans\BanHammer;
use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelBans;
use Nelliel\Bans\BansAccess;

class AdminBans extends Admin
{
    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_BANS_TABLE;
        $this->id_column = 'ban_id';
        $this->panel_name = _gettext('Bans');
    }

    public function panel(int $page = 1): void
    {
        $this->verifyPermissions($this->domain, 'perm_view_bans');
        $output_panel = new OutputPanelBans($this->domain, false);
        $output_panel->main(['page' => $page], false);
    }

    public function creator(ContentID $content_id = null): void
    {
        $this->verifyPermissions($this->domain, 'perm_add_bans');
        $parameters = array();

        if (!is_null($content_id)) {
            $parameters = ['content_id' => $content_id];
        }

        $output_panel = new OutputPanelBans($this->domain, false);
        $output_panel->new($parameters, false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_add_bans');
        $ban_hammer = new BanHammer($this->database);
        $ban_hammer->collectFromPOST();

        if ($ban_hammer->getData('ban_type') === BansAccess::RANGE ||
            $ban_hammer->getData('ban_type') === BansAccess::HASHED_SUBNET) {
            $this->verifyPermissions($this->domain, 'perm_add_range_bans');
        }

        $ban_hammer->apply();

        if (isset($_GET['content-id'])) {
            $content_id = new ContentID($_GET['content-id']);
            $mod_post_comment = $_POST['mod_post_comment'] ?? null;

            if ($content_id->isPost() && !is_null($mod_post_comment)) {
                $content_post = $content_id->getInstanceFromID($this->domain);
                $content_post->changeData('mod_comment', $mod_post_comment);
                $content_post->writeToDatabase();
                $regen = new Regen();
                $regen->threads($this->domain, [$content_id->postID()]);
                $regen->index($this->domain);
                $regen->overboard($this->domain);
            }
        }

        $this->panel();
    }

    public function editor(string $ban_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_bans');
        $output_panel = new OutputPanelBans($this->domain, false);
        $output_panel->modify(['ban_id' => $ban_id], false);
    }

    public function update(string $ban_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_bans');
        $ban_hammer = new BanHammer($this->database, (int) $ban_id);
        $ban_hammer->collectFromPOST();
        $ban_hammer->apply();
        $this->panel();
    }

    public function delete(string $ban_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_delete_bans');
        $ban_hammer = new BanHammer($this->database, (int) $ban_id);
        $ban_hammer->delete();
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_view_bans':
                nel_derp(310, sprintf(_gettext('You do not have access to the %s control panel.'), $this->panel_name), 403);
                break;

            case 'perm_add_bans':
                nel_derp(311, _gettext('You cannot add new bans.'), 403);
                break;

            case 'perm_modify_bans':
                nel_derp(312, _gettext('You cannot modify existing bans.'), 403);
                break;

            case 'perm_delete_bans':
                nel_derp(313, _gettext('You cannot delete existing bans.'), 403);
                break;

            case 'perm_add_range_bans':
                nel_derp(314, _gettext('You cannot add new range bans.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
