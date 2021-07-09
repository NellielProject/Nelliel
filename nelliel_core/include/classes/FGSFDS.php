<?php
declare(strict_types = 1);

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class FGSFDS
{
    private static $commands = array();

    function __construct(string $input = null)
    {
        if (!is_null($input))
        {
            $this->addFromString($input);
        }
    }

    public function addFromString(string $input, bool $overwrite = false): bool
    {
        $commands = explode(' ', $input);
        $commands_added = false;

        foreach ($commands as $command)
        {
            $command = utf8_trim($command);

            if ($command === '')
            {
                continue;
            }

            // TODO: work on this
            $value = explode('=', $command);

            if ($value[0] === $command)
            {
                $value = null;
            }

            if($this->addCommand($command, $value, $overwrite))
            {
                $commands_added = true;
            }
        }

        return $commands_added;
    }

    public function addCommand(string $command, $value, bool $overwrite = false): bool
    {
        if ($this->commandIsSet($command) && !$overwrite)
        {
            return false;
        }

        $this->setCommandValue($command, $value);
        return true;
    }

    public function getCommandValue(string $command)
    {
        return self::$commands[$command]['value'] ?? null;
    }

    public function setCommandValue(string $command, $value): void
    {
        self::$commands[$command]['value'] = $value;
    }

    public function commandIsSet(string $command): bool
    {
        return isset(self::$commands[$command]);
    }

    public function getCommandData(string $command, string $key = '')
    {
        if($key === '')
        {
            return self::$commands[$command]['data'] ?? null;
        }

        return self::$commands[$command]['data'][$key] ?? null;
    }

    public function updateCommandData(string $command, string $key, $value): void
    {
        self::$commands[$command]['data'][$key] = $value;
    }

    public function removeCommand(string $command): void
    {
        unset(self::$commands[$command]);
    }
}
