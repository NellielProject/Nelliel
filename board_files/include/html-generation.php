<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

nel_parse_links($dataforce['post_links']);

//
// Generate the header
//
function nel_render_header($dataforce, $render_mode, $treeline)
{
    global $rendervar;
    lol_html_timer(0);
    
    $rendervar['titlepart'] = '';
    
    if (BS1_SHOW_LOGO)
    {
        $rendervar['titlepart'] .= '<img src="' . BS_BOARD_LOGO . '" alt="' . BS_BOARD_NAME . '" class="logo-alt-text">';
    }
    
    if (BS1_SHOW_TITLE)
    {
        $rendervar['titlepart'] .= '<h1>' . BS_BOARD_NAME . '</h1>';
    }
    
    switch ($render_mode)
    {
        case 'ADMIN':
            $rendervar['page_title'] = BS_BOARD_NAME;
            $rendervar['dotdot'] = '';
            break;
        
        case 'DERP':
            $rendervar['page_title'] = BS_BOARD_NAME;
            $rendervar['dotdot'] = '';
            break;
        
        case 'BAN':
            $rendervar['page_title'] = BS_BOARD_NAME;
            $rendervar['dotdot'] = '';
            break;
        
        case 'ABOUT':
            $rendervar['page_title'] = 'About Nelliel Imageboard';
            $rendervar['dotdot'] = '';
            break;
        
        case 'NORMAL':
            if ($dataforce['page_gen'] == 'main')
            {
                $rendervar['page_title'] = BS_BOARD_NAME;
            }
            else
            {
                $rendervar['page_title'] = ($treeline[0]['subject'] === '') ? BS_BOARD_NAME . ' > Thread #' . $treeline[0]['post_number'] : BS_BOARD_NAME . ' > ' . $treeline[0]['subject'];
            }
            
            break;
    }
    
    $rendervar['log_out'] = (!empty($_SESSION) && !$_SESSION['ignore_login']) ? '[<a href="' . $rendervar['dotdot'] . PHP_SELF . '?mode=log_out">Log Out</a>]' : '';
    $rendervar['page_ref1'] = (!empty($_SESSION) && !$_SESSION['ignore_login']) ? PHP_SELF . '?mode=display&page=0' : PHP_SELF2 . PHP_EXT;
    $dat_temp = nel_parse_template('header.tpl', FALSE);
    return $dat_temp;
}

//
// Generate reply form
//
function nel_render_posting_form($dataforce)
{
    global $rendervar;
    
    $rendervar['response_id'] = (is_null($dataforce['response_id'])) ? '0' : $dataforce['response_id'];
    $rendervar['rules_list'] = $dataforce['rules_list'];
    $rendervar['form_submit_url'] = $rendervar['dotdot'] . PHP_SELF;
    
    if (BS1_ALLOW_MULTIFILE)
    {
        if ($rendervar['response_id'])
        {
            $rendervar['allow_multifile'] = TRUE;
        }
        else if (!$rendervar['response_id'] && BS1_ALLOW_OP_MULTIFILE)
        {
            $rendervar['response_id'] = '0';
            $rendervar['allow_multifile'] = TRUE;
        }
        else
        {
            $rendervar['allow_multifile'] = FALSE;
        }
    }
    else
    {
        $rendervar['allow_multifile'] = FALSE;
    }
    
    $rendervar['modmode'] = ($rendervar['mode2'] === 'display') ? TRUE : FALSE;
    
    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        $rendervar['logged_in'] = TRUE;
        $rendervar['page_ref1'] = PHP_SELF . '?mode=display&page=0';
        $rendervar['page_ref2'] = PHP_SELF . '?page=';
    }
    else
    {
        $rendervar['logged_in'] = FALSE;
        $rendervar['page_ref1'] = PHP_SELF2 . PHP_EXT;
    }
    
    $rendervar['max_files'] = 3;
    $dat_temp = nel_parse_template('posting_form.tpl', FALSE);
    return $dat_temp;
}

//
// Render posts
//
function nel_render_post($dataforce, $response, $partial, $gen_data, $treeline, $dbh)
{
    global $rendervar, $link_resno;
    
    if ($rendervar['insert_hr'])
    {
        $dat_temp = nel_parse_template('op_post.tpl', FALSE);
        return $dat_temp;
    }
    
    $rendervar_first = $rendervar;
    $rendervar = array_merge($rendervar, (array) $treeline[$gen_data['post_counter']]);
    
    if ($partial)
    {
        $link_resno = 0;
    }
    else
    {
        $link_resno = $dataforce['response_id'];
    }
    
    $rendervar['response_id'] = $dataforce['response_id'];
    $rendervar['tripcode'] = (isset($rendervar['tripcode']) && $rendervar['tripcode'] !== '') ? BS_TRIPKEY_MARKER . $rendervar['tripcode'] : '';
    $rendervar['secure_tripcode'] = (isset($rendervar['secure_tripcode']) && $rendervar['secure_tripcode'] !== '') ? BS_TRIPKEY_MARKER . BS_TRIPKEY_MARKER . $rendervar['secure_tripcode'] : '';
    $rendervar['comment'] = preg_replace('#(^|>)(&gt;[^<]*|ÅÑ[^<]*)#', '$1<span class="post-quote">$2</span>', $rendervar['comment']);
    $rendervar['comment'] = preg_replace_callback('#&gt;&gt;([0-9]+)#', 'nel_parse_links', $rendervar['comment']);
    $rendervar['comment-part'] = utf8_str_replace('>><a href="../"', '>><a href="', $rendervar['comment']);
    $rendervar['sticky'] = (bool) $rendervar['sticky'];
    $temp_dot = ($partial) ? '' : $rendervar['dotdot'];
    $post_id = ($response) ? $rendervar['response_to'] : $rendervar['post_number'];
    
    if (!$dataforce['omitted_done'])
    {
        $rendervar['omitted_count'] = $gen_data['post_count'] - BS_ABBREVIATE_THREAD;
        $rendervar['omitted_posts'] = TRUE;
    }
    else
    {
        $rendervar['omitted_posts'] = FALSE;
    }
    
    if ($rendervar['has_file'])
    {
        $filecount = count($rendervar['files']);
        $rendervar['multifile'] = ($filecount > 1) ? TRUE : FALSE;
        $i = 0;
        
        while ($i < $filecount)
        {
            $rendervar['files'][$i]['img_dim'] = (!is_null($rendervar['files'][$i]['image_width'])
                                                && !is_null($rendervar['files'][$i]['image_height'])) ? TRUE : FALSE;
            $rendervar['files'][$i]['file_location'] = $temp_dot . SRC_DIR . $post_id . '/' . $rendervar['files'][$i]['filename'] . "." . $rendervar['files'][$i]['extension'];
            $rendervar['files'][$i]['filesize'] = round(((int) $rendervar['files'][$i]['filesize'] / 1024), 2);
            
            if (BS1_USE_THUMB)
            {
                if (isset($rendervar['files'][$i]['preview_name']))
                {
                    $rendervar['files'][$i]['has_preview'] = TRUE;
                    $rendervar['files'][$i]['preview_location'] = $temp_dot . THUMB_DIR . $post_id . '/' . $rendervar['files'][$i]['preview_name'];
                    
                    if($rendervar['files'][$i]['preview_width'] != 0)
                    {
                        if ($rendervar['files'][$i]['preview_width'] > BS_MAX_MULTI_WIDTH
                            || $rendervar['files'][$i]['preview_height'] > BS_MAX_MULTI_HEIGHT)
                        {
                            $ratio = min((BS_MAX_MULTI_HEIGHT / $rendervar['files'][$i]['preview_height']), (BS_MAX_MULTI_WIDTH / $rendervar['files'][$i]['preview_width']));
                            $rendervar['files'][$i]['preview_width'] = intval($ratio * $rendervar['files'][$i]['preview_width']);
                            $rendervar['files'][$i]['preview_height'] = intval($ratio * $rendervar['files'][$i]['preview_height']);
                        }
                    }
                }
                else if (BS1_USE_FILE_ICON && file_exists(BOARD_FILES . 'imagez/nelliel/filetype/' . utf8_strtolower($rendervar['files'][$i]['supertype']) . '/' . utf8_strtolower($rendervar['files'][$i]['subtype']) . '.png'))
                {
                    $rendervar['files'][$i]['has_preview'] = TRUE;
                    $rendervar['files'][$i]['preview_location'] = $temp_dot . BOARD_FILES . '/imagez/nelliel/filetype/' . utf8_strtolower($rendervar['files'][$i]['supertype']) . '/' . utf8_strtolower($rendervar['files'][$i]['subtype']) . '.png';
                    $rendervar['files'][$i]['preview_width'] = (BS_MAX_WIDTH < 64) ? BS_MAX_WIDTH : '128';
                    $rendervar['files'][$i]['preview_height'] = (BS_MAX_HEIGHT < 64) ? BS_MAX_HEIGHT : '128';
                }
                else
                {
                    $rendervar['files'][$i]['has_preview'] = FALSE;
                }
            }
            else
            {
                $rendervar['files'][$i]['has_preview'] = FALSE;
            }
            
            $rendervar['files'][$i]['endline'] = (($i + 1) % BS_MAX_FILES_ROW == 0) ? TRUE : FALSE;
            ++ $i;
        }
    }
    else
    {
        $rendervar['multifile'] = FALSE;
    }
    
    $curr_time = floor($rendervar['post_time'] / 1000);
    
    switch (BS_DATE_FORMAT)
    {
        case 'ISO':
            $rendervar['post_time'] = date("Y", $curr_time)
                                    . BS_DATE_SEPARATOR . date("m", $curr_time)
                                    . BS_DATE_SEPARATOR . date("d (D) H:i:s", $curr_time);
            break;
        
        case 'US':
            $rendervar['post_time'] = date("m", $curr_time) . BS_DATE_SEPARATOR 
                                    . date("d", $curr_time) . BS_DATE_SEPARATOR
                                    . date("Y (D) H:i:s", $curr_time);
            break;
        
        case 'COM':
            $rendervar['post_time'] = date("d", $curr_time)
                                    . BS_DATE_SEPARATOR . date("m", $curr_time)
                                    . BS_DATE_SEPARATOR . date("Y (D) H:i:s", $curr_time);
            break;
    }
    
    switch ($rendervar['mod_post'])
    {
        case '1':
            $rendervar['staff_post'] = nel_stext('THREAD_JANPOST');
            $rendervar['secure_tripcode'] = '';
            break;
        
        case '2':
            $rendervar['staff_post'] = nel_stext('THREAD_MODPOST');
            $rendervar['secure_tripcode'] = '';
            break;
        
        case '3':
            $rendervar['staff_post'] = nel_stext('THREAD_ADMINPOST');
            $rendervar['secure_tripcode'] = '';
            break;
        
        default:
            $rendervar['staff_post'] = '';
    }
    
    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        $rendervar['logged_in'] = TRUE;
        $rendervar['host'] = (@inet_ntop($rendervar['host'])) ? inet_ntop($rendervar['host']) : 'Unknown';
        $rendervar['perm_ban'] = $_SESSION['perms']['perm_ban'];
        $rendervar['page_ref1'] = PHP_SELF . '?mode=display&page=0';
        $rendervar['page_ref2'] = PHP_SELF . '?page=';
        $rendervar['the_session'] = session_id();
    }
    else
    {
        $rendervar['logged_in'] = FALSE;
        $rendervar['page_ref1'] = PHP_SELF2 . PHP_EXT;
        $rendervar['page_ref2'] = '';
    }
    
    $dat_temp = ($response ? nel_parse_template('response_post.tpl', FALSE) : nel_parse_template('op_post.tpl', FALSE));
    $rendervar = $rendervar_first;
    return $dat_temp;
}

//
// Footer
//
function nel_render_footer($link, $styles, $del, $response)
{
    global $total_html, $total_script, $rendervar;
    
    if (!isset($rendervar['main_page']))
    {
        $rendervar['main_page'] = FALSE;
    }
    
    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        $rendervar['logged_in'] = TRUE;
        $rendervar['main_page'] = FALSE;
        
        if ($_SESSION['perms']['perm_ban'])
        {
            $rendervar['perm_ban'] = TRUE;
        }
    }
    else
    {
        $rendervar['logged_in'] = FALSE;
    }
    
    $rendervar['link'] = $link;
    $rendervar['styles_link'] = $styles;
    $rendervar['del'] = $del;
    $rendervar['response'] = $response;
    lol_html_timer(1);
    $dat_temp = nel_parse_template('footer.tpl', FALSE);
    return $dat_temp;
}

//
// Generate HTML for Mod control panel
//
function nel_render_thread_panel($dataforce, $thread_data, $mode)
{
    global $rendervar;
    
    $rendervar = array_merge($rendervar, (array) $thread_data);
    $rendervar['thread_panel_form'] = FALSE;
    $rendervar['thread_panel_loop'] = FALSE;
    $rendervar['thread_panel_end'] = FALSE;
    
    if ($mode === 'FORM')
    {
        $rendervar['thread_panel_form'] = TRUE;
    }
    else if ($mode === 'THREAD')
    {
        $rendervar['thread_panel_loop'] = TRUE;
        
        switch (BS_DATE_FORMAT)
        {
            case 'ISO':
                $rendervar['post_time'] = date("Y/m/d H:i:s", floor($rendervar['post_time'] / 1000));
                break;
            
            case 'US':
                $rendervar['post_time'] = date("m/d/Y H:i:s", floor($rendervar['post_time'] / 1000));
                break;
            
            case 'COM':
                $rendervar['post_time'] = date("d/m/Y H:i:s", floor($rendervar['post_time'] / 1000));
                break;
        }
        
        if (utf8_strlen($thread_data['name']) > 12)
        {
            $rendervar['post_name'] = utf8_substr($rendervar['name'], 0, 11) . "...";
        }
        
        if (utf8_strlen($thread_data['subject']) > 12)
        {
            $rendervar['subject'] = utf8_substr($rendervar['subject'], 0, 11) . "...";
        }
        
        if ($thread_data['email'])
        {
            $rendervar['post_name'] = '"<a href="mailto:' . $rendervar['email'] . '">' . $rendervar['name'] . '</a>';
        }
        
        $rendervar['comment'] = utf8_str_replace("<br>", " ", $rendervar['comment']);
        $rendervar['comment'] = htmlspecialchars($rendervar['comment']);
        
        if (utf8_strlen($thread_data['comment']) > 20)
        {
            $rendervar['comment'] = utf8_substr($rendervar['comment'], 0, 19) . "...";
        }
        
        $rendervar['host'] = (@inet_ntop($rendervar['host'])) ? inet_ntop($rendervar['host']) : 'Unknown';
        
        if ($rendervar['response_to'] === '0')
        {
            $rendervar['is_op'] = TRUE;
            $num = $rendervar['post_number'];
        }
        else
        {
            $rendervar['is_op'] = FALSE;
            $num = $rendervar['response_to'];
        }
        
        if (!empty($rendervar['files']))
        {
            $filecount = count($rendervar['files']);
            $i = 0;
            
            while ($i < $filecount)
            {
                $rendervar['files'][$i]['filesize'] = (int) ceil($rendervar['files'][$i]['filesize'] / 1024);
                ++ $i;
            }
        }
        
        $rendervar['bg_class'] = ($dataforce['j_increment'] % 2) ? $rendervar['bg_class'] = 'row1' : $rendervar['bg_class'] = 'row2';
    }
    else if ($mode === 'END')
    {
        $rendervar['all_filesize'] = $dataforce['all_filesize'];
        $rendervar['thread_panel_end'] = TRUE;
    }
    
    $dat_temp = nel_parse_template('manage_thread_panel.tpl', FALSE);
    return $dat_temp;
}

//
// Ban modification form
//
function nel_render_ban_panel($dataforce, $baninfo, $mode)
{
    global $rendervar;
    
    $rendervar = array_merge($rendervar, (array) $baninfo);
    $rendervar['ban_panel_head'] = FALSE;
    $rendervar['ban_panel_loop'] = FALSE;
    $rendervar['ban_panel_end'] = FALSE;
    $rendervar['ban_panel_add'] = FALSE;
    $rendervar['ban_panel_modify'] = FALSE;
    
    if ($mode === 'HEAD')
    {
        $rendervar['ban_panel_head'] = TRUE;
    }
    else if ($mode === 'END')
    {
        $rendervar['ban_panel_end'] = TRUE;
    }
    else if ($mode === 'LIST')
    {
        $rendervar['ban_panel_loop'] = TRUE;
        $rendervar['host'] = (@inet_ntop($rendervar['host'])) ? inet_ntop($rendervar['host']) : 'Unknown';
        $rendervar['ban_appeal_response'] = $baninfo['appeal_response'];
        $rendervar['ban_expire'] = date("D F jS Y  H:i:s", $rendervar['length'] + $rendervar['ban_time']);
        if ($dataforce['j_increment'] % 2)
        {
            $rendervar['bg_class'] = "row1";
        }
        else
        {
            $rendervar['bg_class'] = "row2";
        }
    }
    else if ($mode === 'MODIFY')
    {
        $rendervar['ban_panel_modify'] = TRUE;
        $rendervar['appeal_check'] = '';
        $rendervar['ban_expire'] = date("D F jS Y  H:i:s", $rendervar['length'] + $rendervar['ban_time']);
        $rendervar['ban_time'] = date("D F jS Y  H:i:s", $rendervar['ban_time']);
        $length2 = $rendervar['length'] / 3600;
        $rendervar['ban_length_hours'] = 0;
        $rendervar['ban_length_days'] = 0;
        
        if ($length2 >= 24)
        {
            $length2 = $length2 / 24;
            $rendervar['ban_length_days'] = floor($length2);
            $length2 = $length2 - $rendervar['ban_length_days'];
            $rendervar['ban_length_hours'] = $length2 * 24;
        }
        
        if ($rendervar['appeal_status'] > 1)
        {
            $rendervar['appeal_check'] = 'checked';
        }
    }
    else if ($mode === 'ADD')
    {
        $rendervar['ban_panel_add'] = TRUE;
    }
    
    $dat_temp = nel_parse_template('manage_bans_panel.tpl', FALSE);
    return $dat_temp;
}

//
// Parse links in posts and update a cache to avoid a potential assload of database hits during rendering
//
function nel_parse_links($matches)
{
    global $link_resno, $dbh;
    static $links;
    
    if (!is_array($matches))
    {
        if ($matches === TRUE)
        {
            return $links;
        }
        
        $links = $matches;
        return;
    }
    
    $back = ($link_resno === 0) ? PAGE_DIR : '../';
    $pattern = '#p' . $matches[1] . 't([0-9]+)#';
    $cached = preg_match($pattern, $links, $matches2);
    
    if ($cached === 0)
    {
        $isquoted2 = preg_match($pattern, $link_updates, $matches2);
        $prepared = $dbh->prepare('SELECT response_to FROM ' . POSTTABLE . ' WHERE post_number=:pnum');
        $prepared->bindParam(':pnum', $matches[1], PDO::PARAM_STR);
        $prepared->execute();
        $link = $prepared->fetch(PDO::FETCH_NUM);
        unset($prepared);
        $links .= 'p' . $matches[1] . 't' . $link[0];
        return '>>' . $matches[1];
    }
    else
    {
        $link = $matches2[1];
        
        if ($link[0] == '0')
        {
            return '<a href="' . $back . $matches[1] . '/' . $matches[1] . '.html" class="link_quote">>>' . $matches[1] . '</a>';
        }
        else
        {
            return '<a href="' . $back . $link . '/' . $link . '.html#' . $matches[1] . '" class="link_quote">>>' . $matches[1] . '</a>';
        }
    }
}

//
// Parse the templates into code form
//
// TODO: Split into caching function and parse which calls caching if needed
function nel_parse_template($template, $regen)
{
    global $rendervar, $template_info, $total_html;
    
    $template_short = utf8_str_replace('.tpl', '', $template);
    
    if (!$template_info[$template]['loaded'])
    {
        clearstatcache();
        $modify_time = filemtime(TEMPLATE_PATH . $template);
        
        if (!isset($template_info[$template]) || $modify_time !== $template_info[$template]['modify_time'] || !file_exists(CACHE_PATH . $template_short . '.nelcache'))
        {
            $functions = get_defined_functions();
            $function_list = '';
            
            foreach ($functions['user'] as $function)
            {
                $function_list .= '|' . $function . '\(';
            }
            
            $template_info[$template]['modify-time'] = $modify_time;
            $lol = file_get_contents(TEMPLATE_PATH . $template);
            $lol = preg_replace('#(?<!\[|\'' . $function_list . ')\'(?!\]|\'|\)})#', '\\\'', $lol); // Do some escaping
            $lol = trim($lol);
            $begin = '<?php function render_' . $template_short . '() { global $rendervar, $total_html; $temp = \''; // Start of the cached template
            $lol = preg_replace('#[ \r\n\t]*{{[ \r\n\t]*(if|elseif|foreach|for|while)[ \r\n\t]*([^{]*)}}#', '\'; $1( $2 ): $temp .= \'', $lol); // Opening control statements
            $lol = preg_replace('#[ \r\n\t]*{{[ \r\n\t]*else[ \r\n\t]*}}#', '\'; else: $temp .= \'', $lol); // Else
            $lol = preg_replace('#[ \r\n\t]*{{[ \r\n\t]*(endif|endforeach|endfor|endwhile)[ \r\n\t]*}}#', '\'; $1; $temp .= \'', $lol); // Closing control statements
            $lol = preg_replace('#{([\w]*?\(.*?\))}#', "'.$1.'", $lol); // Inline function calls
            $lol = preg_replace('#{([^({)|(}]*)}#', "'.$1.'", $lol); // Variables and constants
            $end = '\'; return $temp; } ?>'; // End of the caches template
            $lol_out = $begin . $lol . $end;
            nel_write_file(CACHE_PATH . $template_short . '.nelcache', $lol_out, 0644);
        }
        
        include (CACHE_PATH . $template_short . '.nelcache');
        $template_info[$template]['loaded'] = TRUE;
    }
    
    if (!$regen)
    {
        $dat_temp = call_user_func('render_' . $template_short);
        return $dat_temp;
    }
}

//
// Start/end timer
//
function lol_html_timer($derp)
{
    global $start_html, $end_html, $total_html;
    
    if ($derp === 0)
    {
        $start_html = 0;
        $end_html = 0;
        $total_html = 0;
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $start_html = $mtime[1] + $mtime[0];
        return;
    }
    else
    {
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $end_html = $mtime[1] + $mtime[0];
        $total_html = round(($end_html - $start_html), 4);
        return;
    }
}
?>