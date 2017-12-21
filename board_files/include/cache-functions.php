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
    $result = $dbh->query('SELECT * FROM "' . CONFIG_TABLE . '" WHERE "config_type" = \'filetype_enable\'');
    $config_list = $result->fetchAll(PDO::FETCH_ASSOC);
    $file_config = array();

    foreach ($config_list as $config)
    {
        $setting_name = explode('_', $config['config_name']); //TODO: Update with db change

        switch ($setting_name[0])
        {
            case 'g':
                $file_config['graphics'][utf8_strtolower($setting_name[1])] = (bool)$config['setting'];
                break;

            case 'a':
                $file_config['audio'][utf8_strtolower($setting_name[1])] = (bool)$config['setting'];
                break;

            case 'v':
                $file_config['video'][utf8_strtolower($setting_name[1])] = (bool)$config['setting'];
                break;

            case 'd':
                $file_config['document'][utf8_strtolower($setting_name[1])] = (bool)$config['setting'];
                break;

            case 'r':
                $file_config['archive'][utf8_strtolower($setting_name[1])] = (bool)$config['setting'];
                break;

            case 'o':
                $file_config['other'][utf8_strtolower($setting_name[1])] = (bool)$config['setting'];
                break;
        }
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
    $result =  $dbh->query('SELECT * FROM "' . CONFIG_TABLE . '" WHERE "config_type" = \'board_setting\'');
    $config_list = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);

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
