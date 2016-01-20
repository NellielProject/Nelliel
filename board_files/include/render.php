<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class nel_render
{
    private $variables;
    private $defaults;
    private $output;
    private $timer_start;
    private $timer_end;

    function __construct()
    {
        $this->defaults = array(
                            'dotdot' => '',
                            'output_timer' => TRUE);
        $this->variables = $this->defaults;
        $this->output = '';
        $this->timer_start = 0;
        $this->timer_end = 0;
        $this->start_timer();
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
    
    public function add_sanitized_data($setting, $input)
    {
        $this->variables[$setting] = $this->cleanse_the_aids($input);
    }

    public function retrieve_data($setting)
    {
        if(isset($this->variables[$setting]))
        {
            return $this->variables[$setting];
        }
        
        return NULL;
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
    
    public function start_timer()
    {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $this->timer_start = $mtime[1] + $mtime[0];
    }
    
    public function end_timer()
    {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $this->timer_end = $mtime[1] + $mtime[0];
    }
    
    public function get_timer($round)
    {
        if($this->timer_end === 0)
        {
            $this->end_timer();
        }

        return round(($this->timer_end - $this->timer_start), $round);
    }
    
    private function cleanse_the_aids($string)
    {
        if (get_magic_quotes_gpc())
        {
            $string = stripslashes($string);
        }
        
        $string = trim($string);
        $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
        return $string;
    }
}

?>