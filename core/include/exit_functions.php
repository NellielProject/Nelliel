<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Authorization;
use Nelliel\Redirect;

function nel_clean_exit(): void
{
    $authorization = new Authorization(nel_database('core'));
    $authorization->saveUsers();
    $authorization->saveRoles();
    $redirect = new Redirect();
    $redirect->go();
}