<?php

//
// This file is used to register the plugin with Nelliel
// The filename should be specified in plugin.ini under the 'initializer' setting
//

include_once 'plugin_example_class.php';
include_once 'plugin_example_function.php';

$plugin_example_class = new \ExamplePlugin\plugin_example_class();
\ExamplePlugin\plugin_example_function(false);