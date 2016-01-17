<?php
class nel_render
{
    private $variables;
    private $defaults;
    private $output;

    function __construct()
    {
        $this->defaults = array();
        $this->variables = array();
        $this->output = '';
    }

    public function add_data($setting, $input)
    {
        $this->variables[$setting] = $input;
    }

    public function add_multiple_data($input)
    {
        foreach ($input as $key => $value)
        {
            $this->add_data($key, $value);
        }
    }

    public function retrieve_data($setting)
    {
        return $this->variables[$setting];
    }

    public function remove_data($setting)
    {
        unset($this->variables[$setting]);
    }

    public function add_defaults($setting, $input)
    {
        if (is_null($setting))
        {
            foreach ($input as $key => $value)
            {
                $this->defaults[$key] = $value;
            }
        }
        else
        {
            $this->defaults[$setting] = $input;
        }
    }

    public function remove_defaults($setting, $input)
    {
        if (is_null($setting))
        {
            foreach ($input as $key)
            {
                unset($this->defaults[$key]);
            }
        }
        else
        {
            unset($this->defaults[$setting]);
        }
    }

    public function reset($use_defaults)
    {
        if ($use_defaults)
        {
            $this->variables = $this->defaults;
        }
        else
        {
            $this->variables = array();
        }
        
        $this->output = '';
    }

    public function input($input)
    {
        $this->output .= $input;
    }

    public function output()
    {
        return $this->output;
    }

    public function parse($template, $subdirectory)
    {
        $output = nel_parse_template($template, $subdirectory, $this, FALSE);
        $this->output .= $output;
    }
}

?>