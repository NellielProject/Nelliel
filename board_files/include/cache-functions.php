<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Cache filetype settings
//
function nel_cache_filetype_settings()
{
    $dbh = nel_database();
    $config_list =  $dbh->executeFetchAll('SELECT * FROM "' . CONFIG_TABLE . '" WHERE "config_type" = \'filetype_enable\'', PDO::FETCH_ASSOC);
    $file_config = array();

    foreach ($config_list as $config)
    {
        $file_config[$config['config_category']][utf8_strtolower($config['config_name'])] = (bool)$config['setting'];
    }

    $output = '<?php $filetype_settings = ' . var_export($file_config, true) . ';';
    nel_write_file(CACHE_PATH . 'filetype_settings.nelcache', $output, FILE_PERM);
    return $file_config;
}

//
// Cache the board settings
//
function nel_cache_board_settings()
{
    $dbh = nel_database();
    $config_list =  $dbh->executeFetchAll('SELECT * FROM "' . CONFIG_TABLE . '" WHERE "config_type" = \'board_setting\'', PDO::FETCH_ASSOC);
    $result_count = count($config_list);
    $vars1 = '<?php ';

    foreach ($config_list as $config)
    {
        if($config['data_type'] === 'bool')
        {
            $config['setting'] = var_export((bool)$config['setting'], true);
        }

        if($config['data_type'] === 'int')
        {
            $config['setting'] = intval($config['setting']);
        }

        if($config['data_type'] === 'str')
        {
            $config['setting'] = var_export($config['setting'], true);
        }

        $vars1 .= 'define(\'BS_' . utf8_strtoupper($config['config_name']) . '\', ' . $config['setting'] . ');';
    }

    nel_write_file(CACHE_PATH . 'board_settings.nelcache', $vars1, FILE_PERM);
}

function nel_cache_board_settings_new()
{
    $dbh = nel_database();
    $config_list =  $dbh->executeFetchAll('SELECT * FROM "' . CONFIG_TABLE . '" WHERE "config_type" = \'board_setting\'', PDO::FETCH_ASSOC);
    $result_count = count($config_list);
    $settings_output = '<?php $board_settings = array();';

    foreach ($config_list as $config)
    {
        if($config['data_type'] === 'bool')
        {
            $config['setting'] = var_export((bool)$config['setting'], true);
        }

        if($config['data_type'] === 'int')
        {
            $config['setting'] = intval($config['setting']);
        }

        if($config['data_type'] === 'str')
        {
            $config['setting'] = var_export($config['setting'], true);
        }

        $settings_output .= '$board_settings[\'' . $config['config_name'] . '\'] = ' . $config['setting'] . ';';
    }

    nel_write_file(CACHE_PATH . 'board_settings_new.nelcache', $settings_output, FILE_PERM);
}
