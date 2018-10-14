<?php

namespace ExamplePlugin;

// This file is used to register the plugin with Nelliel
// The filename should be specified in plugin.ini under the 'initializer' setting
//

include_once 'plugin_example_class.php';
include_once 'plugin_example_function.php';

$plugin_id = nel_plugins()->registerPlugin(__DIR__, __FILE__);
new \ExamplePlugin\plugin_example_class($plugin_id);
\ExamplePlugin\plugin_example_function($plugin_id);

