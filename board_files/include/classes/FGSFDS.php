<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class FGSFDS
{
    private static $raw_fgsfds = '';
    private static $commands = array();

    function __construct($raw_fgsfds = null)
    {
        if (!is_null($raw_fgsfds))
        {
            self::$raw_fgsfds = $raw_fgsfds;

            foreach($this->parseRaw($raw_fgsfds) as $command)
            {
                $this->addCommand($command);
            }
        }
    }

    public function parseRaw($raw_fgsfds)
    {
        return preg_split('#[\s,]#u', $raw_fgsfds);
    }

    public function getAllCommands()
    {
        return self::$commands;
    }

    public function getCommand($command)
    {
        if (isset(self::$commands[$command]))
        {
            return self::$commands[$command];
        }

        return false;
    }

    public function getCommandData($command, $data_id)
    {
        if (isset(self::$commands[$command]))
        {
            return self::$commands[$command][$data_id];
        }

        return false;
    }

    public function addCommand($command)
    {
        if (!isset(self::$commands[$command]))
        {
            self::$commands[$command] = array();
            return true;
        }

        return false;
    }

    public function modifyCommandData($command, $data_id, $data)
    {
        if (isset(self::$commands[$command]))
        {
            self::$commands[$command][$data_id] = $data;
            return true;
        }

        return false;
    }
}
