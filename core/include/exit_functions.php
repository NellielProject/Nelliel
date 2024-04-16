<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Auth\Authorization;
use Nelliel\Redirect;

function nel_exception_handler(Throwable $exception)
{
    if ($exception instanceof PDOException) {
        $message = sprintf(__('A database error has occurred: %s in file %s.'), $exception->getCode(),
            basename($exception->getFile()));
        nel_derp(81, $message);
    }

    throw $exception;
}

function nel_clean_exit()
{
    $authorization = new Authorization(nel_database('core'));
    $authorization->saveUsers();
    $authorization->saveRoles();
    $redirect = new Redirect();
    $redirect->go();
}