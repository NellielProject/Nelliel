<?php
declare(strict_types = 1);

namespace Nelliel\Modules;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

trait DispatchErrors
{

    protected function invalidModule()
    {
        nel_derp(250, _gettext('The selected module is invalid.'));
    }

    protected function invalidSection()
    {
        nel_derp(251, _gettext('Section is invalid for this module.'));
    }

    protected function invalidAction()
    {
        nel_derp(252, _gettext('Action is invalid for this module.'));
    }
}