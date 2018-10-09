<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class ContentBase
{
    public $content_id;
    public $database;
    public $board_id;

    public abstract function loadFromDatabase($database = null);

    public abstract function writeToDatabase($database = null);

    public abstract function removeFromDatabase($database = null);
}