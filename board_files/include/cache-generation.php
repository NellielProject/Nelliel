<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Cache the posting rules
//
function cache_rules()
{
    global $dbh;
    
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
        $t_element = str_replace('enable_', '', $config_list[$i]['config_name']);
        
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
        $gmode = substr($gmode, 0, -2);
        $rule_list .= '<li>' . LANG_FILES_GRAPHICS . strtoupper($gmode) . '</li>';
    }
    if ($amode !== '')
    {
        $amode = substr($amode, 0, -2);
        $rule_list .= '
							<li>' . LANG_FILES_AUDIO . strtoupper($amode) . '</li>';
    }
    if ($vmode !== '')
    {
        $vmode = substr($vmode, 0, -2);
        $rule_list .= '
							<li>' . LANG_FILES_VIDEO . strtoupper($vmode) . '</li>';
    }
    if ($dmode !== '')
    {
        $dmode = substr($dmode, 0, -2);
        $rule_list .= '
							<li>' . LANG_FILES_DOCUMENT . strtoupper($dmode) . '</li>';
    }
    if ($rmode !== '')
    {
        $rmode = substr($rmode, 0, -2);
        $rule_list .= '
							<li>' . LANG_FILES_ARCHIVE . strtoupper($rmode) . '</li>';
    }
    if ($omode !== '')
    {
        $omode = substr($omode, 0, -2);
        $rule_list .= '
							<li>' . LANG_FILES_OTHER . strtoupper($omode) . '</li>';
    }
    
    $output = '<?php
	$rule_list = \'' . $rule_list . '\';
?>';
    
    write_file(CACHE_PATH . 'rules.nelcache', $output, 0644);
    return $rule_list;
}

//
// Cache the board settings
//
function cache_settings()
{
    global $dbh;
    
    // Get true/false (1-bit) board settings
    $result = $dbh->query('SELECT config_name,setting FROM ' . CONFIGTABLE . ' WHERE config_type="board_setting_1bit"');
    $config_list = $result->fetchALL(PDO::FETCH_ASSOC);
    unset($result);
    
    $result_count = count($config_list);
    $i = 0;
    $vars1 = '
';
    
    while ($i < $result_count)
    {
        if ($config_list[$i]['setting'] === '1')
        {
            $vars1 .= 'define(\'BS1_' . strtoupper($config_list[$i]['config_name']) . '\', TRUE);
';
        }
        else
        {
            $vars1 .= 'define(\'BS1_' . strtoupper($config_list[$i]['config_name']) . '\', FALSE);
';
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
    $vars2 = '
';
    
    while ($i < $result_count)
    {
        $rows[$config_list[$i]['config_name']] = $config_list[$i]['setting'];
        if (is_numeric($config_list[$i]['setting']))
        {
            $vars2 .= 'define(\'BS_' . strtoupper($config_list[$i]['config_name']) . '\', ' . $config_list[$i]['setting'] . ');
';
        }
        else
        {
            $vars2 .= 'define(\'BS_' . strtoupper($config_list[$i]['config_name']) . '\', \'' . $config_list[$i]['setting'] . '\');
';
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
    
    $fvars = '
	$enabled_types = array(
	';
    
    $result_count = count($config_list);
    $i = 0;
    $rows = array();
    
    while ($i < $result_count)
    {
        if ($config_list[$i]['setting'] === '1')
        {
            $fvars .= '\'' . $config_list[$i]['config_name'] . '\' => TRUE,
	';
        }
        else
        {
            $fvars .= '\'' . $config_list[$i]['config_name'] . '\' => FALSE,
	';
        }
        
        ++ $i;
    }
    
    $fvars = substr($fvars, 0, strlen($fvars) - 4) . ');';
    $final_vars = '<?php' . $vars1 . $vars2 . $fvars . '
?>';
    
    write_file(CACHE_PATH . 'parameters.nelcache', $final_vars, 0644);
    
    unset($rows);
}

//
// Cache the post links
// This helps immensely in terms of speed
//
function cache_post_links()
{
    global $post_link_reference;
    $post_link_reference_php = '<?php
	$post_link_reference = \'' . $post_link_reference . '\';
?>';
    
    write_file(CACHE_PATH . 'post_link_references.nelcache', $post_link_reference_php, 0644);
}

//
// Cache the template files
//
function cache_template_info()
{
    global $template_info;
    $info = '<?php
	$template_info = ' . var_export($template_info, TRUE) . ';
?>';
    
    write_file(CACHE_PATH . 'template_info.nelcache', $info, 0644);
}

//
// Parse the templates into code form
//
function parse_template($template, $regen)
{
    global $rendervar, $template_loaded, $template_info, $total_html;
    
    $template_short = str_replace('.tpl', '', $template);
    
    if (!isset($template_loaded[$template]) || !$template_loaded[$template])
    {
        $md5 = md5_file(TEMPLATE_PATH . $template);
        
        if (!isset($template_info[$template]) || $md5 !== $template_info[$template] || !file_exists(CACHE_PATH . $template_short . '.nelcache'))
        {
            $template_info[$template] = $md5;
            $durr = file_get_contents(TEMPLATE_PATH . $template);
            $lol = $durr;
            $has_php = strstr($durr, '{{');
            $lol = preg_replace('#(?<!\[|\')\'(?!\]|\')#', '\\\'', $lol);
            
            if ($has_php !== FALSE)
            {
                $begins_php = preg_match('#^\s*{{#', $lol);
                
                if ($begins_php === 1)
                {
                    $lol = trim($lol);
                    $begin = '<?php function render_' . $template_short . '() { global $rendervar,$total_html; $temp = \'';
                }
                else
                {
                    $begin = '<?php function render_' . $template_short . '() { global $rendervar,$total_html; $temp = \'\'; $temp .= \'';
                }
                
                $end = '\'; return $temp; } ?>';
                $lol = preg_replace('#[\r\n\t]*{{[ \t]*(if|elseif|foreach|for|while)[ \t]*([^{]*)}}#', '\'; $1( $2 ): $temp .= \'', $lol);
                $lol = preg_replace('#[\r\n\t]*{{[ \t]*else[ \t]*}}[ \t]*#', '\'; else: $temp .= \'', $lol);
                $lol = preg_replace('#[\r\n\t]*{{[ \t]*(endif|endforeach|endfor|endwhile)[ \t]*}}#', '\'; $1; $temp .= \'', $lol);
            }
            else
            {
                $begin = '<?php function render_' . $template_short . '() { global $rendervar,$total_html; $temp = \'\'; $temp .= \'';
                $end = '\'; return $temp; } ?>';
            }
            
            $lol = preg_replace('#{([^({)|(}]*)}#', "'.$1.'", $lol);
            $durr_out = $begin . $lol . $end;
            write_file(CACHE_PATH . $template_short . '.nelcache', $durr_out, 0644);
            cache_template_info();
        }
        
        if (!$regen)
        {
            include (CACHE_PATH . $template_short . '.nelcache');
            $template_loaded[$template] = TRUE;
        }
    }
    
    if (!$regen)
    {
        $dat_temp = call_user_func('render_' . $template_short);
        return $dat_temp;
    }
}

//
// Regenerate the template cache
//
function regen_template_cache()
{
    global $template_info;
    
    $template_info = array();
    
    foreach (glob(TEMPLATE_PATH . '*.tpl') as $template)
    {
        $template = basename($template);
        parse_template($template, TRUE);
    }
    
    cache_template_info();
}
?>