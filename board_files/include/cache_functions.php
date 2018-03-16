<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Cache filetype settings
//
function nel_cache_filetype_settings($board_id)
{
    if(empty($board_id))
    {
        return false;
    }

    $dbh = nel_database();
    $file_handler = new \Nelliel\FileHandler();
    $prepared = $dbh->prepare('SELECT "db_prefix" FROM "nelliel_board_data" WHERE "board_id" = ?');
    $db_prefix = $dbh->executePreparedFetch($prepared, array($board_id), PDO::FETCH_COLUMN);
    $config_table = $db_prefix . '_config';
    $config_list =  $dbh->executeFetchAll('SELECT * FROM "' . $config_table . '" WHERE "config_type" = \'filetype_enable\'', PDO::FETCH_ASSOC);
    $file_config = array();

    foreach ($config_list as $config)
    {
        $file_config[$config['config_category']][utf8_strtolower($config['config_name'])] = (bool)$config['setting'];
    }

    $output = '<?php if(!defined("NELLIEL_VERSION")){die("NOPE.AVI");} $filetype_settings = ' . var_export($file_config, true) . ';';
    $file_handler->writeFile(CACHE_PATH . $board_id . '/filetype_settings.php', $output, FILE_PERM, true);
    return $file_config;
}

function nel_cache_site_settings()
{
    $dbh = nel_database();
    $file_handler = new \Nelliel\FileHandler();
    $config_list =  $dbh->executeFetchAll('SELECT * FROM "nelliel_site_config"', PDO::FETCH_ASSOC);
    $settings_output = '<?php if(!defined("NELLIEL_VERSION")){die("NOPE.AVI");} $site_settings = array();';

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

        $settings_output .= '$site_settings[\'' . $config['config_name'] . '\'] = ' . $config['setting'] . ';';
    }

    $file_handler->writeFile(CACHE_PATH . 'site_settings.php', $settings_output, FILE_PERM, true);
}

function nel_cache_board_settings($board_id)
{
    if(empty($board_id))
    {
        return false;
    }

    $dbh = nel_database();
    $file_handler = new \Nelliel\FileHandler();
    $prepared = $dbh->prepare('SELECT "db_prefix" FROM "nelliel_board_data" WHERE "board_id" = ?');
    $db_prefix = $dbh->executePreparedFetch($prepared, array($board_id), PDO::FETCH_COLUMN);
    $config_table = $db_prefix . '_config';
    $config_list =  $dbh->executeFetchAll('SELECT * FROM "' . $config_table . '" WHERE "config_type" = \'board_setting\'', PDO::FETCH_ASSOC);
    $settings_output = '<?php if(!defined("NELLIEL_VERSION")){die("NOPE.AVI");} $board_settings = array();';

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

    $file_handler->writeFile(CACHE_PATH . $board_id . '/board_settings.php', $settings_output, FILE_PERM, true);
}
