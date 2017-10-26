<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

// Cached settings
if (!file_exists(CACHE_PATH . 'parameters.nelcache'))
{
    nel_cache_settings($dbh);
}

require_once CACHE_PATH . 'parameters.nelcache';

// Cached rules, post links and template info cache
if (!file_exists(CACHE_PATH . 'multi-cache.nelcache'))
{
    $dataforce['rules_list'] = nel_cache_rules();
    nel_write_multi_cache($dataforce, $template_info);
}

require_once CACHE_PATH . 'multi-cache.nelcache';

//
// Cache the posting rules
//
function nel_cache_rules()
{
    $dbh = nel_get_database_handle();
    $gtypes = '';
    $atypes = '';
    $vtypes = '';
    $dtypes = '';
    $rtypes = '';
    $otypes = '';
    $file_config = nel_build_filetype_config($dbh);
    $rule_list = '';

    $graphics = $file_config['graphics']['graphics'];

    if($file_config['graphics']['graphics'])
    {
        foreach ($file_config['graphics'] as $key => $value)
        {
            $gtypes .= ($value && $key !== 'graphics') ? utf8_strtoupper($key) . ', ' : '';
        }
    }

    if($file_config['audio']['audio'])
    {
        foreach ($file_config['audio'] as $key => $value)
        {
            $atypes .= ($value && $key !== 'audio') ? utf8_strtoupper($key) . ', ' : '';
        }
    }

    if($file_config['video']['video'])
    {
        foreach ($file_config['video'] as $key => $value)
        {
            $vtypes .= ($value && $key !== 'video') ? utf8_strtoupper($key) . ', ' : '';
        }
    }

    if($file_config['document']['document'])
    {
        foreach ($file_config['document'] as $key => $value)
        {
            $dtypes .= ($value && $key !== 'document') ? utf8_strtoupper($key) . ', ' : '';
        }
    }

    if($file_config['archive']['archive'])
    {
        foreach ($file_config['archive'] as $key => $value)
        {
            $rtypes .= ($value && $key !== 'archive') ? utf8_strtoupper($key) . ', ' : '';
        }
    }

    if($file_config['other']['other'])
    {
        foreach ($file_config['other'] as $key => $value)
        {
            $otypes .= ($value && $key !== 'other') ? utf8_strtoupper($key) . ', ' : '';
        }
    }

    if ($gtypes !== '')
    {
        $gtypes = utf8_substr($gtypes, 0, -2);
        $rule_list .= '<li>' . nel_stext('FILES_GRAPHICS') . $gtypes . '</li>';
    }
    if ($atypes !== '')
    {
        $atypes = utf8_substr($atypes, 0, -2);
        $rule_list .= '
							<li>' . nel_stext('FILES_AUDIO') . $atypes . '</li>';
    }
    if ($vtypes !== '')
    {
        $vtypes = utf8_substr($vtypes, 0, -2);
        $rule_list .= '
							<li>' . nel_stext('FILES_VIDEO') . $vtypes . '</li>';
    }
    if ($dtypes !== '')
    {
        $dtypes = utf8_substr($dtypes, 0, -2);
        $rule_list .= '
							<li>' . nel_stext('FILES_DOCUMENT') . $dtypes . '</li>';
    }
    if ($rtypes !== '')
    {
        $rtypes = utf8_substr($rtypes, 0, -2);
        $rule_list .= '
							<li>' . nel_stext('FILES_ARCHIVE') . $rtypes . '</li>';
    }
    if ($otypes !== '')
    {
        $otypes = utf8_substr($otypes, 0, -2);
        $rule_list .= '
							<li>' . nel_stext('FILES_OTHER') . $otypes . '</li>';
    }

    return $rule_list;
}

function nel_build_filetype_config($dbh)
{
    $dbh = nel_get_database_handle();
    $result =  $dbh->query('SELECT * FROM "' . CONFIG_TABLE . '" WHERE "config_type" = \'filetype_enable\'');
    $config_list = $result->fetchAll(PDO::FETCH_ASSOC);
    $file_config = array();

    foreach ($config_list as $config)
    {
        $setting_name = explode('_', $config['config_name']);

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

            case 'o':
                $file_config['other'][utf8_strtolower($setting_name[1])] = (bool)$config['setting'];
                break;

            case 'r':
                $file_config['archive'][utf8_strtolower($setting_name[1])] = (bool)$config['setting'];
                break;
        }
    }

    return $file_config;
}

//
// Cache the board settings
//
function nel_cache_settings()
{
    $dbh = nel_get_database_handle();
    $result =  $dbh->query('SELECT * FROM "' . CONFIG_TABLE . '" WHERE "config_type" = \'board_setting\'');
    $config_list = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);

    $result_count = count($config_list);
    $vars1 = '';

    foreach ($config_list as $config)
    {
        if($config['data_type'] === 'bool')
        {
            $config['setting'] = var_export((bool)$config['setting'], TRUE);
        }

        if($config['data_type'] === 'int')
        {
            $config['setting'] = intval($config['setting']);
        }

        if($config['data_type'] === 'str')
        {
            $config['setting'] = var_export($config['setting'], TRUE);
        }

        $vars1 .= 'define(\'BS_' . utf8_strtoupper($config['config_name']) . '\', ' . $config['setting'] . ');';
    }

    $file_config = nel_build_filetype_config($dbh);
    $fvars = '$enabled_types = ' . var_export($file_config, true) . ';';
    $final_vars = '<?php ' . $vars1 . $fvars . ' ?>';
    nel_write_file(CACHE_PATH . 'parameters.nelcache', $final_vars, FILE_PERM);
}

//
// Regenerate the template cache
//
function nel_regen_template_cache()
{
    $Directory = new RecursiveDirectoryIterator(TEMPLATE_PATH);
    $Iterator = new RecursiveIteratorIterator($Directory);
    $Regex = new RegexIterator($Iterator, '/^.+\.tpl$/i', RecursiveRegexIterator::GET_MATCH);

    foreach($Regex as $key => $value)
    {
        $file = str_replace(TEMPLATE_PATH, '', $key);
        $template = basename($file);
        $subdirectory = str_replace($template, '', $file);
        nel_parse_template($template, $subdirectory, null, true);
    }
}

function nel_reset_template_status($template_info)
{
    foreach ($template_info as $key => $value) // TODO: Invalid argument?
    {
        $template_info[$key]['loaded'] = FALSE;
    }

    return $template_info;
}

//
// Write out rules, post links and template info cache
//
function nel_write_multi_cache($dataforce)
{
    $template_info = nel_template_info(NULL, NULL, NULL, TRUE);
    $template_info = nel_reset_template_status($template_info);
    $cache = '<?php
$dataforce[\'rules_list\'] = \'' . $dataforce['rules_list'] . '\';
$template_info = ' . var_export($template_info, TRUE) . ';
nel_template_info(NULL, NULL, $template_info, FALSE);
?>';

    nel_write_file(CACHE_PATH . 'multi-cache.nelcache', $cache, FILE_PERM);
}
