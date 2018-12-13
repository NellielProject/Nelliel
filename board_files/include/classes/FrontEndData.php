<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class FrontEndData
{
    private $database;
    private $css_styles = array();
    private $templates = array();

    function __construct($database)
    {
        $this->database = $database;
    }

    private function loadData()
    {
        $all_data = $this->database->executeFetchAll('SELECT * FROM "' . FRONT_END_TABLE . '"', PDO::FETCH_ASSOC);

        foreach ($all_data as $data)
        {
            if ($data['resource_type'] == 'css')
            {
                $this->css_styles[$data['id']] = $data;
            }
            else if ($data['resource_type'] == 'template')
            {
                $this->templates[$data['id']] = $data;
            }
        }
    }

    public function cssStyle($style = null)
    {
        if (empty($this->css_styles))
        {
            $this->loadData();
        }

        if (is_null($style))
        {
            return $this->css_styles;
        }

        return $this->css_styles[$style];
    }

    public function template($template = null)
    {
        if (empty($this->templates))
        {
            $this->loadData();
        }

        if (is_null($template))
        {
            return $this->templates;
        }

        return $this->templates[$template];
    }
}