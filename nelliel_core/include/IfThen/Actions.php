<?php

namespace Nelliel\IfThen;

use Nelliel\Domains\Domain;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Actions
{
    private $domain;
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
    }

    public function error(array $data)
    {
        nel_derp(0, $data['message']);
    }
}
