<?php

declare(strict_types=1);

namespace Nelliel\Modules\NewPost;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\IfThens\Actions;

class ActionsPost implements Actions
{

    function __construct()
    {
    }

    public function do(array $actions)
    {
        foreach ($actions as $action => $data)
        {
            switch ($action)
            {
                case 'reject':
                    nel_derp(34, $data['message']);
                    break;
            }
        }
    }
}
