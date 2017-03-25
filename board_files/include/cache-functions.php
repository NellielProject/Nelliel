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
    nel_write_multi_cache($dataforce, $template_info);
}

require_once CACHE_PATH . 'multi-cache.nelcache';

//
// Cache the posting rules
//
function nel_cache_rules()
{
    $dbh = nel_get_db_handle();
    $gmode = '';
    $amode = '';
    $vmode = '';
    $dmode = '';
    $rmode = '';
    $omode = '';
    $file_config = nel_build_filetype_config($dbh);
    $rule_list = '';

    foreach($file_config as $key => $value)
    {
        if(!$file_config[$key]['ENABLE'])
        {
            continue;
        }

        foreach ($file_config[$key] as $key2 => $value2)
        {
            if($key2 === 'ENABLE')
            {
                continue;
            }

            if($key === 'GRAPHICS' && $value2)
            {
                $gmode .= utf8_strtoupper($key2) . ', ';
            }

            if($key === 'AUDIO' && $value2)
            {
                $amode .= utf8_strtoupper($key2) . ', ';
            }

            if($key === 'VIDEO' && $value2)
            {
                $vmode .= utf8_strtoupper($key2) . ', ';
            }

            if($key === 'DOCUMENT' && $value2)
            {
                $dmode .= utf8_strtoupper($key2) . ', ';
            }

            if($key === 'ARCHIVE' && $value2)
            {
                $rmode .= utf8_strtoupper($key2) . ', ';
            }

            if($key === 'OTHER' && $value2)
            {
                $omode .= utf8_strtoupper($key2) . ', ';
            }
        }
    }

    if ($gmode !== '')
    {
        $gmode = utf8_substr($gmode, 0, -2);
        $rule_list .= '<li>' . nel_stext('FILES_GRAPHICS') . $gmode . '</li>';
    }
    if ($amode !== '')
    {
        $amode = utf8_substr($amode, 0, -2);
        $rule_list .= '
							<li>' . nel_stext('FILES_AUDIO') . utf8_strtoupper($amode) . '</li>';
    }
    if ($vmode !== '')
    {
        $vmode = utf8_substr($vmode, 0, -2);
        $rule_list .= '
							<li>' . nel_stext('FILES_VIDEO') . utf8_strtoupper($vmode) . '</li>';
    }
    if ($dmode !== '')
    {
        $dmode = utf8_substr($dmode, 0, -2);
        $rule_list .= '
							<li>' . nel_stext('FILES_DOCUMENT') . utf8_strtoupper($dmode) . '</li>';
    }
    if ($rmode !== '')
    {
        $rmode = utf8_substr($rmode, 0, -2);
        $rule_list .= '
							<li>' . nel_stext('FILES_ARCHIVE') . utf8_strtoupper($rmode) . '</li>';
    }
    if ($omode !== '')
    {
        $omode = utf8_substr($omode, 0, -2);
        $rule_list .= '
							<li>' . nel_stext('FILES_OTHER') . $omode . '</li>';
    }

    return $rule_list;
}

function nel_build_filetype_config($dbh)
{
    $result = $dbh->query('SELECT * FROM ' . CONFIG_TABLE . ' WHERE config_type="filetype"');
    $config_list = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);
    $result_count = count($config_list);
    $file_config = array();

    foreach ($config_list as $config)
    {
        $setting_name = explode('_', $config['config_name']);
        $file_config[utf8_strtoupper($setting_name[0])][utf8_strtoupper($setting_name[1])] = (bool)$config['setting'];
    }

    return $file_config;
}

//
// Cache the board settings
//
function nel_cache_settings()
{
    $dbh = nel_get_db_handle();

    // Get true/false (1-bit) board settings
    $result2 = $dbh->query('SELECT * FROM ' . CONFIG_TABLE . ' WHERE config_type="board_setting"');
    $config_list = $result2->fetchAll(PDO::FETCH_ASSOC);
    unset($result);

    $result_count = count($config_list);
    $vars1 = '';

    foreach ($config_list as $config)
    {
        if($config['data_type'] === '1')
        {
            $config['setting'] = var_export((bool)$config['setting'], TRUE);
        }

        if($config['data_type'] === '2')
        {
            $config['setting'] = intval($config['setting']);
        }

        if($config['data_type'] === '3')
        {
            $config['setting'] = var_export((string)$config['setting'], TRUE);
        }

        $vars1 .= 'define(\'BS_' . utf8_strtoupper($config['config_name']) . '\', ' . $config['setting'] . ');';
    }

    $file_config = nel_build_filetype_config($dbh);
    $fvars = '$enabled_types = ' . var_export($file_config, true) . ';';
    $final_vars = '<?php ' . $vars1 . $fvars . ' ?>';
    nel_write_file(CACHE_PATH . 'parameters.nelcache', $final_vars, 0644);
}

//
// Regenerate the template cache
//
function nel_regen_template_cache()
{
    foreach (glob(TEMPLATE_PATH . '*.tpl') as $template)
    {
        $template = basename($template);
        nel_parse_template($template, '', NULL, TRUE);
    }
}

function nel_reset_template_status($template_info)
{
    foreach ($template_info as $key => $value)
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
$dataforce[\'post_links\'] = \'' . $dataforce['post_links'] . '\';
$dataforce[\'rules_list\'] = \'' . $dataforce['rules_list'] . '\';
$template_info = ' . var_export($template_info, TRUE) . ';
nel_template_info(NULL, NULL, $template_info, FALSE);
?>';

    nel_write_file(CACHE_PATH . 'multi-cache.nelcache', $cache, 0644);
}
?>