<?php
declare(strict_types = 1);

namespace Nelliel\Interfaces;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

interface SelfPersisting
{

    public function load(): void;

    public function save(): void;

    public function delete(): void;
}