<?php

namespace ExamplePlugin;

class PluginExampleClass
{
    private $plugin_id;

    function __construct($plugin_id)
    {
        $this->plugin_id = $plugin_id;
        $plugin_api = new \Nelliel\API\Plugin\PluginAPI();
        $plugin_api->addMethod('nel-plugin-example', $this, 'exampleMethod', $this->plugin_id, 5);
        $plugin_api->addMethod('nel-plugin-example-return', $this, 'exampleMethodReturnable', $this->plugin_id, 10);
    }

    public function exampleMethod($arg)
    {
        if($arg)
        {
            $var = $arg;
        }
    }

    public function exampleMethodReturnable($data, $string)
    {
        return $data + 3;
    }
}
