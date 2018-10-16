<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class PanelBase
{
    protected $database;
    protected $authorize;

    public abstract function actionDispatch($inputs);

    public abstract function renderPanel();

    public abstract function add();

    public abstract function edit();

    public abstract function update();

    public abstract function remove();
}

