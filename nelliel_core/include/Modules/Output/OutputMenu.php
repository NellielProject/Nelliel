<?php

declare(strict_types=1);

namespace Nelliel\Modules\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use PDO;

class OutputMenu extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function styles(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $styles = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_ASSETS_TABLE . '" WHERE "type" = \'style\'',
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

        usort($render_data, [$this, 'sortByStyleName']);
        return $render_data;
    }

    private function sortByStyleName($a, $b)
    {
        if ($a['style_name'] == $b['style_name'])
        {
            return $a['style_name'] - $b['style_name'];
        }

        return ($a['style_name'] < $b['style_name']) ? -1 : 1;
    }
}