<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/board_settings_panel.php';

class AdminBoardSettings extends AdminBase
{
    private $domain;
    private $defaults = false;

    function __construct($database, $authorization, $domain)
    {
        $this->database = $database;
        $this->authorization = $authorization;
        $this->board = $domain;
        $this->defaults = ($this->board->id() === '') ? true : false;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
        $user = $session->sessionUser();

        if ($inputs['action'] === 'update')
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
        if (!$user->boardPerm($this->board->id(), 'perm_board_config_access'))
        {
            nel_derp(330, _gettext('You are not allowed to modify the board settings.'));
        }

        if ($this->defaults && !$user->boardPerm('', 'perm_board_defaults_access'))
        {
            nel_derp(332, _gettext('You are not allowed to modify the default board settings.'));
        }

        nel_render_board_settings_panel($this->board, $this->defaults);
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
        if (!$user->boardPerm($this->board->id(), 'perm_board_config_modify'))
        {
            nel_derp(330, _gettext('You are not allowed to modify the board settings.'));
        }

        if ($this->defaults && !$user->boardPerm('', 'perm_board_defaults_modify'))
        {
            nel_derp(332, _gettext('You are not allowed to modify the default board settings.'));
        }

        $config_table = ($this->defaults) ? BOARD_DEFAULTS_TABLE : $this->board->reference('config_table');

        while ($item = each($_POST))
        {
            if ($item[0] === 'jpeg_quality' && $item[1] > 100)
            {
                $item[0] = 100;
            }

            $prepared = $this->database->prepare(
                    'UPDATE "' . $config_table . '" SET "setting" = ? WHERE "config_name" = ?');
            $this->database->executePrepared($prepared, array($item[1], $item[0]), true);
        }

        if (!$this->defaults)
        {
            $regen = new \Nelliel\Regen();
            $regen->boardCache($this->board);
            $regen->allPages($this->board);
        }
    }

    public function remove($user)
    {
    }
}
