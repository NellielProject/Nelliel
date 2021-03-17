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

    public function addFromString(string $input): void
    {
        $commands = explode(' ', $input);

        foreach ($commands as $command)
        {
            $command = utf8_trim($command);

            if ($command === '')
            {
                continue;
            }

            $value = explode('=', $command);

            if ($value[0] === $command)
            {
                $value = null;
            }

            $this->addCommand($command, $value);
        }
    }

    public function addCommand(string $command, $value, bool $overwrite = false): bool
    {
        if (!$overwrite && $this->commandIsSet($command))
        {
            return false;
        }

        return $this->updateCommandData($command, 'value', $value, true);
    }

    public function commandIsSet(string $command): bool
    {
        return isset(self::$commands[$command]);
    }

    public function getCommandData(string $command, string $key)
    {
        return self::$commands[$command][$key] ?? null;
    }

    public function updateCommandData(string $command, string $key, $value, bool $create = false): bool
    {
        if (!$create && !$this->commandIsSet($command))
        {
            return false;
        }

        self::$commands[$command][$key] = $value;
        return true;
    }

    public function removeCommand(string $command): void
    {
        unset(self::$commands[$command]);
    }
}
