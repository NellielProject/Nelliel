<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Access point for database connections.
// Databases connection can be added, retrieved or removed using the hash table ID.
//

function nel_database($input = null, $wat_do = null)
{
    static $databases = array();
    static $default_database;

    // No arguments provided: send back the default database
    if (is_null($wat_do) && is_null($input))
    {
        if (!isset($default_database))
        {
            $default_database = nel_default_database_connection();
        }

        return $default_database;
    }

    // ID provided but no instructions: send back the requested database if available
    if (is_null($wat_do) && !is_null($input))
    {
        if (array_key_exists($input, $databases))
        {
            return $databases[$input];
        }
    }

    // Both ID and instructions provided
    if (!is_null($wat_do) && !is_null($input))
    {
        switch ($wat_do)
        {
            case 'store':
                $id = spl_object_hash($input);
                $databases[$id] = $input;
                return $id;
                break;

            case 'retrieve':
                if (array_key_exists($input, $databases))
                {
                    return $databases[$input];
                }

                break;

            case 'identify':
                if (in_array($input, $databases))
                {
                    return array_search($input, $databases);
                }
                break;

            case 'remove':
                if (array_key_exists($input, $databases))
                {
                    unset($input);
                    return true;
                }

                break;
        }
    }

    return false;
}

function nel_authorize()
{
    static $authorize;

    if (!isset($authorize))
    {
        $authorize = new \Nelliel\Authorization();
    }

    return $authorize;
}

function nel_ban_hammer()
{
    static $ban_hammer;

    if (!isset($ban_hammer))
    {
        $ban_hammer= new \Nelliel\BanHammer();
    }

    return $ban_hammer;
}

function nel_board_settings($setting)
{
    static $board_settings;

    if (!isset($board_settings))
    {
        if (!file_exists(CACHE_PATH . 'board_settings_new.nelcache'))
        {
            nel_cache_board_settings_new();
        }

        require_once CACHE_PATH . 'board_settings_new.nelcache';
    }

    return $board_settings[$setting];
}

function nel_archive()
{
    static $archive;

    if (!isset($archive))
    {
        $archive = new \Nelliel\ArchiveAndPrune();
    }

    return $archive;
}