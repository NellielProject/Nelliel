<?php

declare(strict_types=1);

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Timer
{
    protected $start = 0;
    protected $elapsed = 0;
    protected $stopped = true;

    function __construct()
    {
    }

    public function start()
    {
        if ($this->stopped)
        {
            $this->start = microtime(true);
            $this->stopped = false;
        }
    }

    public function stop()
    {
        if (!$this->stopped)
        {
            $this->elapsed += microtime(true) - $this->start;
            $this->start = 0;
            $this->stopped = true;
        }
    }

    public function reset()
    {
        $this->start = 0;
        $this->elapsed = 0;
        $this->stopped = true;
    }

    public function elapsed(bool $rounded = true, int $precision = 4)
    {
        $elapsed = $this->stopped ? $this->elapsed : (microtime(true) - $this->start) + $this->elapsed;

        if ($rounded)
        {
            return number_format($elapsed, $precision);
        }
        else
        {
            return $elapsed;
        }
    }
}