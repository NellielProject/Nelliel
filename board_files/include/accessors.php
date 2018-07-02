<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
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

function nel_sessions()
{
    static $sessions;

    if (!isset($sessions))
    {
        $sessions = new \Nelliel\Sessions();
    }

    return $sessions;
}

function nel_plugins()
{
    static $api;

    if (!isset($api))
    {
        $api = new PluginAPI();
    }

    return $api;
}

function nel_parameters()
{
    static $parameters;

    if (!isset($parameters))
    {
        $parameters = new \Nelliel\Parameters();
    }

    return $parameters;
}

function nel_fgsfds($entry, $new_value = null)
{
    static $fgsfds;

    if (!isset($fgsfds))
    {
        $fgsfds = array();
    }

    if (!is_null($new_value))
    {
        $fgsfds[$entry] = $new_value;
    }

    if (isset($fgsfds[$entry]))
    {
        return $fgsfds[$entry];
    }

    return null;
}

function nel_get_filetype_data($extension = null)
{
    static $filetypes;

    if (!isset($filetypes))
    {
        $filetypes = array();

        $dbh = nel_database();
        $db_results = $dbh->executeFetchAll('SELECT * FROM "nelliel_filetypes"', PDO::FETCH_ASSOC);
        $sub_extensions = array();

        foreach ($db_results as $result)
        {
            if ($result['extension'] == $result['parent_extension'])
            {
                $filetypes[$result['extension']] = $result;
            }
            else
            {
                $sub_extensions[] = $result;
            }
        }

        foreach ($sub_extensions as $sub_extension)
        {
            if (array_key_exists($sub_extension['parent_extension'], $filetypes))
            {
                $filetypes[$sub_extension['extension']] = $filetypes[$sub_extension['parent_extension']];
                $filetypes[$sub_extension['extension']]['extension'] = $sub_extension['extension'];
            }
        }
    }

    if (is_null($extension))
    {
        return $filetypes;
    }
    else
    {
        return $filetypes[$extension];
    }
}

function nel_get_file_filters($cache_regen = false)
{
    static $file_filters;

    if (!isset($file_filters))
    {
        $file_filters = array();
        $loaded = false;

        if (!$loaded)
        {
            $dbh = nel_database();
            $filters = $dbh->executeFetchAll('SELECT "hash_type", "file_hash" FROM "nelliel_file_filters"', PDO::FETCH_ASSOC);

            foreach ($filters as $filter)
            {
                $file_filters[$filter['hash_type']][] = $filter['file_hash'];
            }
        }
    }

    return $file_filters;
}