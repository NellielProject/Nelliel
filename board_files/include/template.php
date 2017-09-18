<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_parse_template($template, $subdirectory, $render, $regen)
{
    $authorize = nel_get_authorization();
    $template_cache_dir = 'templates/';

    if (!empty($subdirectory) && substr($subdirectory, -1) !== '/')
    {
        $subdirectory .= '/';
    }

    $template_short = utf8_str_replace('.tpl', '', $template);
    $info = nel_template_info($template, null, null, true);

    if($regen)
    {
        $info['loaded'] = false;
    }

    if (is_null($info) || $info['loaded'] === false || $info['loaded'] === null)
    {
        clearstatcache();
        $modify_time = filemtime(TEMPLATE_PATH . $subdirectory . $template);

        if ($regen || !isset($info['modify_time']) || $modify_time !== $info['modify_time'] || !file_exists(CACHE_PATH . $template_short . '.nelcache'))
        {
            $info['modify-time'] = $modify_time;
            $lol = file_get_contents(TEMPLATE_PATH . $subdirectory . $template);
            $lol = trim($lol);
            $begin = '<?php function nel_template_render_' . $template_short . '($render) { $temp = \''; // Start of the cached template
            $lol = preg_replace_callback('#({{.*?}})|({(.*?)})|(\')#', 'nel_escape_single_quotes', $lol); // Do escaping and variable parse
            $lol = preg_replace('#(})\s*?({)#', '$1$2', $lol); // Clear white space between control statements
            $lol = preg_replace('#{{\s*?(if|elseif|foreach|for|while)\s*?(.*?)}}#', '\'; $1($2): $temp .= \'', $lol); // Parse opening control statements
            $lol = preg_replace('#{{\s*?else\s*?}}#', '\'; else: $temp .= \'', $lol); // Parse else statements
            $lol = preg_replace('#{{\s*?(endif|endforeach|endfor|endwhile|endswitch)\s*?}}#', '\'; $1; $temp .= \'', $lol); // Parse closing control statements
            $lol = preg_replace('#{{{\s*?(.*?)\s*?}}}#', '\'; $1; $temp .= \'', $lol); // Parse other PHP code
            $end = '\'; return $temp; } ?>'; // End of the caches template
            $lol_out = $begin . $lol . $end;
            nel_write_file_create_dirs(CACHE_PATH . $template_cache_dir . $subdirectory . $template_short . '.nelcache', $lol_out, 0644, 0755);
        }

        if(!$regen)
        {
            include (CACHE_PATH . $template_cache_dir . $subdirectory . $template_short . '.nelcache');
            $info['loaded'] = true;
            nel_template_info($template, null, $info, false);
        }
    }

    if (!$regen)
    {
        $dat_temp = call_user_func('nel_template_render_' . $template_short, $render);
        return $dat_temp;
    }
}

function nel_template_info($template, $parameter, $update, $return)
{
    static $info;

    if (!$return)
    {
        if (is_null($template))
        {
            $info = $update;
        }
        else if (is_null($parameter))
        {
            $info[$template] = $update;
        }
        else
        {
            $info[$template][$parameter] = $update;
        }
    }
    else
    {
        if (is_null($template))
        {
            return $info;
        }
        else if (is_null($parameter))
        {
            if (isset($info[$template]))
            {
                return $info[$template];
            }
        }
        else
        {
            if (isset($info[$template]['parameter']))
            {
                return $info[$template][$parameter];
            }
        }

        return NULL;
    }
}
