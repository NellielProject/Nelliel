<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class URLConstructor
{

    function __construct()
    {
    }

    public function dynamic(string $base_file, array $parameters)
    {
        $new_url = $base_file . '?';

        foreach ($parameters as $parameter_name => $parameter_value)
        {
            $this->appendDynamicParameter($new_url, $parameter_name, $parameter_value);
        }
        return $new_url;
    }

    private function appendDynamicParameter(&$url, string $parameter_name, $parameter_value, bool $encode = true)
    {
        $new_parameter = '';

        if (is_null($parameter_value))
        {
            return;
        }

        if (substr($url, -1) !== '?')
        {
            $new_parameter .= '&';
        }

        if ($encode)
        {
            $new_parameter .= rawurlencode($parameter_name) . '=' . rawurlencode($parameter_value);
        }
        else
        {
            $new_parameter .= $parameter_name . '=' . $parameter_value;
        }

        $url .= $new_parameter;
    }
}