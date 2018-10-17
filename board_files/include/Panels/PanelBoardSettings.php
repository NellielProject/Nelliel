<?php

namespace Nelliel\Panels;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/board_settings_panel.php';

class PanelBoardSettings extends PanelBase
{
    private $board_id = '';
    private $defaults = false;

    function __construct($database, $authorize, $board_id = null)
    {
        $this->database = $database;
        $this->authorize = $authorize;

        if(is_null($board_id) || $board_id === '')
        {
            $this->defaults = true;
        }
        else
        {
            $this->board_id = $board_id;
        }
    }

    public function actionDispatch($inputs)
    {
        $user = $this->authorize->getUser($_SESSION['username']);

        if ($this->defaults && !$user->boardPerm($this->board_id, 'perm_manage_board_defaults'))
        {
            nel_derp(332, _gettext('You are not allowed to modify the default board settings.'));
        }

        if (!$user->boardPerm($this->board_id, 'perm_manage_board_config'))
        {
            nel_derp(330, _gettext('You are not allowed to modify the board settings.'));
        }

        if($inputs['action'] === 'update')
        {
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
        nel_render_board_settings_panel($this->board_id, $this->defaults);
    }

    public function add()
    {
    }

    public function edit()
    {
    }

    public function update()
    {
        $references = nel_parameters_and_data()->boardReferences($this->board_id);
        $config_table = ($this->defaults) ? BOARD_DEFAULTS_TABLE : $references['config_table'];

        while ($item = each($_POST))
        {
            if ($item[0] === 'jpeg_quality' && $item[1] > 100)
            {
                $item[0] = 100;
            }

            $prepared = $this->database->prepare('UPDATE "' . $config_table . '" SET "setting" = ? WHERE "config_name" = ?');
            $this->database->executePrepared($prepared, array($item[1], $item[0]), true);
        }

        if (!$this->defaults)
        {
            $regen = new \Nelliel\Regen();
            $regen->boardCache($this->board_id);
            $regen->allPages($this->board_id);
        }
    }

    public function remove()
    {
    }


}
