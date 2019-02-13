<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class AdminHandler
{
    protected $database;
    protected $authorization;
    protected $domain;

    public abstract function actionDispatch($inputs);

    public abstract function renderPanel($user);

    public abstract function creator($user);

    public abstract function add($user);

    public abstract function editor($user);

    public abstract function update($user);

    public abstract function remove($user);
}

