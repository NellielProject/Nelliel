<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

function nel_exception_handler(Throwable $exception): void
{
    if ($exception instanceof PDOException) {
        $message = sprintf(__('A database error has occurred: %s in file %s.'), $exception->getCode(),
            basename($exception->getFile()));
        nel_derp(81, $message);
    }

    throw $exception;
}
