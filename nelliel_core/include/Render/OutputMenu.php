<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use PDO;

class OutputMenu extends Output
{
    protected $render_data = array();

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function styles(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $styles = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "asset_id" ASC',
                PDO::FETCH_ASSOC);

        foreach ($styles as $style)
        {
            $style_data = array();
            $info = json_decode($style['info'], true);
            $style_data['stylesheet'] = ($style['is_default']) ? 'stylesheet' : 'alternate stylesheet';
            $style_data['style_id'] = $style['asset_id'];
            $style_data['stylesheet_url'] = NEL_STYLES_WEB_PATH . $info['directory'] . '/' . $info['main_file'];
            $style_data['style_name'] = $info['name'];
            $render_data[] = $style_data;
        }

        return $render_data;
    }
}