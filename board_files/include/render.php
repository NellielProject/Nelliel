<?php

function nel_render($setting, $data, $type)
{
    static $render_variables;
    static $render_variable_keyed;
    static $current_key;
    static $defaults;
    
    if(empty($current_key))
    {
        $current_key = rand(0, 1000);
    }
    
    if($type === 'out')
    {
        return $render_variables[$setting];
    }
    else if($type === 'in')
    {
        $render_variables[$setting] = $data;
    }
    else if($type === 'reset')
    {
        if(!is_null($data))
        {
            $render_variables_keyed[$data] = $defaults;
        }
        else
        {
            $render_variables = $defaults;
        }

        return $current_key;
    }
    else if($type === 'reset_no_defaults')
    {
        if(!is_null($data))
        {
            $render_variables_keyed[$data] = array();
        }
        else
        {
            $render_variables = array();
        }

        return $current_key;
    }
    else if($type === 'add_default')
    {
        $defaults[$setting] = $data;
    }
    else if($type === 'change_key')
    {
        $current_key = $data;
    }
}

function nel_render_init($defaults)
{
    if($defaults)
    {
        return nel_render(NULL, NULL, 'reset');
    }
    else
    {
        return nel_render(NULL, NULL, 'reset_no_defaults');
    }
}

function nel_render_init_keyed($defaults, $key)
{
    if($defaults)
    {
        return nel_render(NULL, $key, 'reset');
    }
    else
    {
        return nel_render(NULL, $key, 'reset_no_defaults');
    }
}

function nel_render_in($setting, $data)
{
    nel_render($setting, $data, 'in');
}

function nel_render_multiple_in($data)
{
    foreach($data as $key => $value)
    {
        nel_render($key, $value, 'in');
    }
}

function nel_render_out($setting)
{
    return nel_render($setting, NULL, 'out');
}

function nel_render_add_default($setting, $data)
{
    nel_render($setting, $data, 'add_default');
}

function nel_render_change_key($key)
{
    nel_render(NULL, $key, 'change_key');
}

class render
{
    private $variables;
    private $template_info;
    
    function __construct()
    {
        $this->variables = array();
        //$this->template_info = nel_template_info();
    }
    
    public function add($setting, $input)
    {
        $this->variables[$setting] = $input;
    }
    
    public function add_multiple($input)
    {
        foreach($input as $key => $value)
        {
            $this->add($key, $value);
        }
    }
    
    public function update($setting, $input)
    {
        $this->variables[$setting] = $input;
    }
    
    public function output($setting)
    {
        return $this->variables[$setting];
    }
    
    public function remove($setting)
    {
        unset($this->variables[$setting]);
    }
    
    public function template_set($templates, $return)
    {
        $output = '';

        foreach($templates as $template)
        {
            $output .= nel_parse_template($template, '', FALSE);
        }

        if($return)
        {
            return $output;
        }
        else
        {
            
        }
    }
}

?>