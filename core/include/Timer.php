<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class Timer
{
    protected $start = 0;
    protected $elapsed = 0;
    protected $stopped = true;

    function __construct()
    {}

    /**
     * Start the timer.
     */
    public function start(): void
    {
        if ($this->stopped) {
            $this->start = microtime(true);
            $this->stopped = false;
        }
    }

    /**
     * Stop the timer and update the elapsed time.
     */
    public function stop(): void
    {
        if (!$this->stopped) {
            $this->elapsed += microtime(true) - $this->start;
            $this->start = 0;
            $this->stopped = true;
        }
    }

    /**
     * Stop the timer and reset elapsed time.
     */
    public function reset(): void
    {
        $this->start = 0;
        $this->elapsed = 0;
        $this->stopped = true;
    }

    /**
     * Get the elapsed time as a float.
     */
    public function elapsed(): float
    {
        return $this->stopped ? $this->elapsed : (microtime(true) - $this->start) + $this->elapsed;
    }

    /**
     * Get the elapsed time as a string formatted to the given precision.
     * Default precision is 4.
     */
    public function elapsedFormatted(int $precision = 4): string
    {
        return number_format($this->elapsed(), $precision);
    }
}