<?php
$plugin_example_class = new plugin_example_class($plugins);

class plugin_example_class
{
    private $plugin_id;

    public function __construct($plugins)
    {
        $this->plugin_id = $plugins->register_plugin($this, 'Example plugin using a class', 'Nelliel', 'v1.0');
        $plugins->register_hook_function('plugin-example', array($this, 'plugin_example'), 10, $this->plugin_id);
    }

    public function plugin_example($input)
    {
        $output = $input + 5;
        return $output;
    }
}
?>