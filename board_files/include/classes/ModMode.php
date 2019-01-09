<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ModMode
{
    private static $return_url;

    function __construct()
    {
    }

    public function returnURL($new_url = null)
    {
        if (!is_null($new_url))
        {
            self::$return_url = $new_url;
        }

        return self::$return_url;
    }
}