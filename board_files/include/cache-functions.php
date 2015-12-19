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
function nel_cache_rules($dbh)
{
    $gmode = '';
    $amode = '';
    $vmode = '';
    $dmode = '';
    $rmode = '';
    $omode = '';

    $result = $dbh->query('SELECT * FROM ' . CONFIGTABLE . ' WHERE config_type IN ("filetype_allow_g","filetype_allow_a","filetype_allow_o","filetype_allow_p","filetype_allow_d","filetype_allow_r")');
    $config_list = $result->fetchALL(PDO::FETCH_ASSOC);
    $result_count = count($config_list);
    $config_list2 = array();
    
    foreach ($config_list as $array)
    {
        if (array_search('enable_graphics', $array) !== FALSE)
        {
            $config_list2['graphics'] = $array['setting'];
        }
        else if (array_search('enable_audio', $array) !== FALSE)
        {
            $config_list2['audio'] = $array['setting'];
        }
        else if (array_search('enable_video', $array) !== FALSE)
        {
            $config_list2['video'] = $array['setting'];
        }
        else if (array_search('enable_other', $array) !== FALSE)
        {
            $config_list2['other'] = $array['setting'];
        }
        else if (array_search('enable_package', $array) !== FALSE)
        {
            $config_list2['package'] = $array['setting'];
        }
        else if (array_search('enable_document', $array) !== FALSE)
        {
            $config_list2['document'] = $array['setting'];
        }
        else if (array_search('enable_archive', $array) !== FALSE)
        {
            $config_list2['archive'] = $array['setting'];
        }
    }
    
    $i = 0;
    
    while ($i < $result_count)
    {
        $t_element = utf8_str_replace('enable_', '', $config_list[$i]['config_name']);
        
        if ($config_list[$i]['setting'] !== '1')
        {
            ++ $i;
            continue;
        }
        
        if ($config_list[$i]['config_type'] === 'filetype_allow_g' && $t_element !== 'graphics' && $config_list2['graphics'] === '1')
        {
            $gmode = $gmode . $t_element . ', ';
        }
        else if ($config_list[$i]['config_type'] === 'filetype_allow_a' && $t_element !== 'audio' && $config_list2['audio'] === '1')
        {
            $amode = $amode . $t_element . ', ';
        }
        else if ($config_list[$i]['config_type'] === 'filetype_allow_v' && $t_element !== 'video' && $config_list2['video'] === '1')
        {
            $vmode = $vmode . $t_element . ', ';
        }
        else if ($config_list[$i]['config_type'] === 'filetype_allow_o' && $t_element !== 'other' && $config_list2['other'] === '1')
        {
            $omode = $omode . $t_element . ', ';
        }
        else if ($config_list[$i]['config_type'] === 'filetype_allow_d' && $t_element !== 'document' && $config_list2['document'] === '1')
        {
            $dmode = $dmode . $t_element . ', ';
        }
        else if ($config_list[$i]['config_type'] === 'filetype_allow_r' && $t_element !== 'archive' && $config_list2['archive'] === '1')
        {
            $rmode = $rmode . $t_element . ', ';
        }
        
        ++ $i;
    }
    
    $rule_list = '';
    
    if ($gmode !== '')
    {
        $gmode = utf8_substr($gmode, 0, -2);
        $rule_list .= '<li>' . nel_stext('FILES_GRAPHICS') . utf8_strtoupper($gmode) . '</li>';
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
							<li>' . nel_stext('FILES_OTHER') . utf8_strtoupper($omode) . '</li>';
    }
    
    return $rule_list;
}

//
// Cache the board settings
//
function nel_cache_settings($dbh)
{
    // Get true/false (1-bit) board settings
    $result = $dbh->query('SELECT config_name,setting FROM ' . CONFIGTABLE . ' WHERE config_type="board_setting_1bit"');
    $config_list = $result->fetchALL(PDO::FETCH_ASSOC);
    unset($result);
    
    $result_count = count($config_list);
    $i = 0;
    $vars1 = '';
    
    while ($i < $result_count)
    {
        if ($config_list[$i]['setting'] === '1')
        {
            $vars1 .= 'define(\'BS1_' . utf8_strtoupper($config_list[$i]['config_name']) . '\',TRUE);';
        }
        else
        {
            $vars1 .= 'define(\'BS1_' . utf8_strtoupper($config_list[$i]['config_name']) . '\',FALSE);';
        }
        ++ $i;
    }
    
    $rows = array();
    
    // Get other board settings
    $result = $dbh->query('SELECT config_name,setting FROM ' . CONFIGTABLE . ' WHERE config_type="board_setting"');
    $config_list = $result->fetchALL(PDO::FETCH_ASSOC);
    unset($result);
    
    $result_count = count($config_list);
    $i = 0;
    $vars2 = '';
    
    while ($i < $result_count)
    {
        $rows[$config_list[$i]['config_name']] = $config_list[$i]['setting'];
        if (is_numeric($config_list[$i]['setting']))
        {
            $vars2 .= 'define(\'BS_' . utf8_strtoupper($config_list[$i]['config_name']) . '\',' . $config_list[$i]['setting'] . ');';
        }
        else
        {
            $vars2 .= 'define(\'BS_' . utf8_strtoupper($config_list[$i]['config_name']) . '\',\'' . $config_list[$i]['setting'] . '\');';
        }
        ++ $i;
    }
    
    $result = $dbh->query('SELECT * FROM ' . CONFIGTABLE . ' WHERE config_type="filetype_allow_g" 
					UNION SELECT * FROM ' . CONFIGTABLE . ' WHERE config_type="filetype_allow_a"
					UNION SELECT * FROM ' . CONFIGTABLE . ' WHERE config_type="filetype_allow_v"
					UNION SELECT * FROM ' . CONFIGTABLE . ' WHERE config_type="filetype_allow_o"
					UNION SELECT * FROM ' . CONFIGTABLE . ' WHERE config_type="filetype_allow_d"
					UNION SELECT * FROM ' . CONFIGTABLE . ' WHERE config_type="filetype_allow_r"');
    
    $config_list = $result->fetchALL(PDO::FETCH_ASSOC);
    unset($result);
    
    $fvars = '$enabled_types = array(';
    
    $result_count = count($config_list);
    $i = 0;
    $rows = array();
    
    while ($i < $result_count)
    {
        if ($config_list[$i]['setting'] === '1')
        {
            $fvars .= '\'' . $config_list[$i]['config_name'] . '\'=>TRUE,';
        }
        else
        {
            $fvars .= '\'' . $config_list[$i]['config_name'] . '\'=>FALSE,';
        }
        
        ++ $i;
    }
    
    $fvars = utf8_substr($fvars, 0, utf8_strlen($fvars) - 4) . ');';
    $final_vars = '<?php ' . $vars1 . $vars2 . $fvars . ' ?>';
    
    nel_write_file(CACHE_PATH . 'parameters.nelcache', $final_vars, 0644);
    
    unset($rows);
}

//
// Cache post links
//
function nel_cache_links()
{
    return nel_parse_links(TRUE);
}

//
// Regenerate the template cache
//
function nel_regen_template_cache()
{
    foreach (glob(TEMPLATE_PATH . '*.tpl') as $template)
    {
        $template = basename($template);
        nel_parse_template($template, TRUE);
    }
}

function nel_reset_template_status()
{
    global $template_info;
    
    foreach ($template_info as $key => $value)
    {
        $template_info[$key]['loaded'] = FALSE;
    }
}

//
// Write out rules, post links and template info cache
//
function nel_write_multi_cache($dataforce)
{
    global $template_info;
    
    nel_reset_template_status();
    $cache = '<?php
$dataforce[\'post_links\'] = \'' . $dataforce['post_links'] . '\';
$dataforce[\'rules_list\'] = \'' . $dataforce['rules_list'] . '\';
$template_info = ' . var_export($template_info, TRUE) . ';
?>';
    
    nel_write_file(CACHE_PATH . 'multi-cache.nelcache', $cache, 0644);
}
?>