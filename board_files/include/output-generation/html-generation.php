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
    nel_render_init(TRUE);
    $dat = '';
    lol_html_timer(0);
    
    nel_render_in('titlepart', '');
    nel_render_in('dotdot', $dataforce['dotdot']);
    
    if (BS1_SHOW_LOGO)
    {
        nel_render_in('titlepart', nel_render_out('titlepart') . '<img src="' . BS_BOARD_LOGO . '" alt="' . BS_BOARD_NAME . '" class="logo-alt-text">');
    }
    
    if (BS1_SHOW_TITLE)
    {
        nel_render_in('titlepart', nel_render_out('titlepart') . '<h1>' . BS_BOARD_NAME . '</h1>');
    }
    
    switch ($render_mode)
    {
        case 'ADMIN':
            nel_render_in('page_title', BS_BOARD_NAME);
            break;
        
        case 'DERP':
            nel_render_in('page_title', BS_BOARD_NAME);
            break;
        
        case 'BAN':
            nel_render_in('page_title', BS_BOARD_NAME);
            break;
        
        case 'ABOUT':
            nel_render_in('page_title', 'About Nelliel Imageboard');
            break;
        
        case 'NORMAL':
            if ($dataforce['page_gen'] == 'main')
            {
                nel_render_in('page_title', BS_BOARD_NAME);
            }
            else
            {
                nel_render_in('page_title', ($treeline[0]['subject'] === '') ? BS_BOARD_NAME . ' > Thread #' . $treeline[0]['post_number'] : BS_BOARD_NAME . ' > ' . $treeline[0]['subject']);
            }
            
            break;
    }
    
    nel_render_in('log_out', (!empty($_SESSION) && !$_SESSION['ignore_login']) ? '[<a href="' . nel_render_out('dotdot') . PHP_SELF . '?mode=log_out">Log Out</a>]' : '');
    nel_render_in('page_ref1', (!empty($_SESSION) && !$_SESSION['ignore_login']) ? PHP_SELF . '?mode=display&page=0' : PHP_SELF2 . PHP_EXT);
    $dat .= nel_parse_template('header.tpl', '', '', FALSE);
    return $dat;
}

//
// Generate reply form
//
function nel_render_posting_form($dataforce)
{
    nel_render_in('response_id', (is_null($dataforce['response_id'])) ? '0' : $dataforce['response_id']);
    nel_render_in('rules_list', $dataforce['rules_list']);
    nel_render_in('form_submit_url', $dataforce['dotdot'] . PHP_SELF);
    
    if (BS1_ALLOW_MULTIFILE)
    {
        if (nel_render_out('response_id'))
        {
            nel_render_in('allow_multifile', TRUE);
        }
        else if (!nel_render_out('response_id') && BS1_ALLOW_OP_MULTIFILE)
        {
            nel_render_in('response_id', '0');
            nel_render_in('allow_multifile', TRUE);
        }
        else
        {
            nel_render_in('allow_multifile', FALSE);
        }
    }
    else
    {
        nel_render_out('allow_multifile', FALSE);
    }
    
    nel_render_in('modmode', (nel_render_out('mode2') === 'display') ? TRUE : FALSE);
    
    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        nel_render_in('logged_in', TRUE);
        nel_render_in('page_ref1', PHP_SELF . '?mode=display&page=0');
        nel_render_in('page_ref2', PHP_SELF . '?page=');
    }
    else
    {
        nel_render_in('logged_in', FALSE);
        nel_render_in('page_ref1', PHP_SELF2 . PHP_EXT);
    }
    
    nel_render_in('max_files', 3);
    $dat_temp = nel_parse_template('posting_form.tpl', '', FALSE);
    return $dat_temp;
}

//
// Render posts
//
function nel_render_post($dataforce, $response, $partial, $gen_data, $treeline, $dbh)
{
    global $link_resno;
    nel_render_init(TRUE);
    $dat = '';
    
    if ($gen_data['insert_hr'])
    {
        nel_render_in('insert_hr', $gen_data['insert_hr']);
        $dat .= nel_parse_template('op_post.tpl', '', '', FALSE);
        return $dat;
    }
    
    $post_data = $treeline[$gen_data['post_counter']];
    nel_render_multiple_in($post_data);
    
    if ($partial)
    {
        $link_resno = 0;
    }
    else
    {
        $link_resno = $dataforce['response_id'];
    }

    nel_render_in('expand_post', $gen_data['expand_post']);
    nel_render_in('last50', $gen_data['last50']);
    nel_render_in('first100', $gen_data['first100']);
    nel_render_in('response_id', $dataforce['response_id']);
    nel_render_in('tripcode', (!is_null($post_data['tripcode'])) ? BS_TRIPKEY_MARKER . $post_data['tripcode'] : '');
    nel_render_in('secure_tripcode', (!is_null($post_data['secure_tripcode'])) ? BS_TRIPKEY_MARKER . BS_TRIPKEY_MARKER . $post_data['secure_tripcode'] : '');
    $post_data['comment'] = preg_replace('#(^|>)(&gt;[^<]*|ÅÑ[^<]*)#', '$1<span class="post-quote">$2</span>', $post_data['comment']);
    $post_data['comment'] = preg_replace_callback('#&gt;&gt;([0-9]+)#', 'nel_parse_links', $post_data['comment']);
    nel_render_in('comment-part', utf8_str_replace('>><a href="../"', '>><a href="', $post_data['comment']));
    nel_render_in('comment', $post_data['comment']);
    nel_render_in('sticky', (bool) $post_data['sticky']);
    $temp_dot = ($partial) ? '' : $dataforce['dotdot'];
    $post_id = ($response) ? $post_data['response_to'] : $post_data['post_number'];
    
    if (!$dataforce['omitted_done'])
    {
        nel_render_in('omitted_count', $gen_data['post_count'] - BS_ABBREVIATE_THREAD);
        nel_render_in('omitted_posts', TRUE);
    }
    else
    {
        nel_render_in('omitted_posts',  FALSE);
    }
    
    if ($gen_data['has_file'])
    {
        nel_render_in('has_file', TRUE);
        $filecount = count($gen_data['files']);
        nel_render_in('multifile', ($filecount > 1) ? TRUE : FALSE);
        $i = 0;
        
        $files = $gen_data['files'];
        
        while ($i < $filecount)
        {
            $files[$i]['img_dim'] = (!is_null($files[$i]['image_width'])
                                                && !is_null($files[$i]['image_height'])) ? TRUE : FALSE;
            $files[$i]['file_location'] = $temp_dot . SRC_DIR . $post_id . '/' . $files[$i]['filename'] . "." . $files[$i]['extension'];
            $files[$i]['filesize'] = round(((int) $files[$i]['filesize'] / 1024), 2);
            
            if (BS1_USE_THUMB)
            {
                if (isset($files[$i]['preview_name']))
                {
                    $files[$i]['has_preview'] = TRUE;
                    $files[$i]['preview_location'] = $temp_dot . THUMB_DIR . $post_id . '/' . $files[$i]['preview_name'];
                    
                    if($files[$i]['preview_width'] != 0)
                    {
                        if ($files[$i]['preview_width'] > BS_MAX_MULTI_WIDTH
                            || $files[$i]['preview_height'] > BS_MAX_MULTI_HEIGHT)
                        {
                            $ratio = min((BS_MAX_MULTI_HEIGHT / $files[$i]['preview_height']), (BS_MAX_MULTI_WIDTH / $files[$i]['preview_width']));
                            $files[$i]['preview_width'] = intval($ratio * $files[$i]['preview_width']);
                            $files[$i]['preview_height'] = intval($ratio * $files[$i]['preview_height']);
                        }
                    }
                }
                else if (BS1_USE_FILE_ICON && file_exists(BOARD_FILES . 'imagez/nelliel/filetype/' . utf8_strtolower($files[$i]['supertype']) . '/' . utf8_strtolower($files[$i]['subtype']) . '.png'))
                {
                    $files[$i]['has_preview'] = TRUE;
                    $files[$i]['preview_location'] = $temp_dot . BOARD_FILES . '/imagez/nelliel/filetype/' . utf8_strtolower($files[$i]['supertype']) . '/' . utf8_strtolower($files[$i]['subtype']) . '.png';
                    $files[$i]['preview_width'] = (BS_MAX_WIDTH < 64) ? BS_MAX_WIDTH : '128';
                    $files[$i]['preview_height'] = (BS_MAX_HEIGHT < 64) ? BS_MAX_HEIGHT : '128';
                }
                else
                {
                    $files[$i]['has_preview'] = FALSE;
                }
            }
            else
            {
                $files[$i]['has_preview'] = FALSE;
            }
            
            $files[$i]['endline'] = (($i + 1) % BS_MAX_FILES_ROW == 0) ? TRUE : FALSE;
            ++ $i;
        }
        
        nel_render_in('files', $files);
    }
    else
    {
        nel_render_in('multifile', FALSE);
    }
    
    $curr_time = floor(nel_render_out('post_time') / 1000);
    
    switch (BS_DATE_FORMAT)
    {
        case 'ISO':
            nel_render_in('post_time', date("Y", $curr_time)
                                    . BS_DATE_SEPARATOR . date("m", $curr_time)
                                    . BS_DATE_SEPARATOR . date("d (D) H:i:s", $curr_time));
            break;
        
        case 'US':
            nel_render_in('post_time', date("m", $curr_time) . BS_DATE_SEPARATOR 
                                    . date("d", $curr_time) . BS_DATE_SEPARATOR
                                    . date("Y (D) H:i:s", $curr_time));
            break;
        
        case 'COM':
            nel_render_in('post_time', date("d", $curr_time)
                                    . BS_DATE_SEPARATOR . date("m", $curr_time)
                                    . BS_DATE_SEPARATOR . date("Y (D) H:i:s", $curr_time));
            break;
    }
    
    switch (nel_render_out('mod_post'))
    {
        case '1':
            nel_render_in('staff_post', nel_stext('THREAD_JANPOST'));
            nel_render_in('secure_tripcode', '');
            break;
        
        case '2':
            nel_render_in('staff_post', nel_stext('THREAD_MODPOST'));
            nel_render_in('secure_tripcode', '');
            break;
        
        case '3':
            nel_render_in('staff_post', nel_stext('THREAD_ADMINPOST'));
            nel_render_in('secure_tripcode', '');
            break;
        
        default:
            nel_render_in('staff_post', '');
    }
    
    nel_render_in('logged_in', FALSE);
    nel_render_in('page_ref1', PHP_SELF2 . PHP_EXT);
    nel_render_in('page_ref2', '');

    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        nel_render_in('logged_in', TRUE);
        nel_render_in('host', (@inet_ntop(nel_render_out('host'))) ? inet_ntop(nel_render_out('host')) : 'Unknown');
        nel_render_in('perm_ban', $_SESSION['perms']['perm_ban']);
        nel_render_in('page_ref1', PHP_SELF . '?mode=display&page=0');
        nel_render_in('page_ref2', PHP_SELF . '?page=');
        nel_render_in('the_session', session_id());
    }
    
    $dat .= ($response ? nel_parse_template('response_post.tpl', '', FALSE) : nel_parse_template('op_post.tpl', '', FALSE));
    return $dat;
}

//
// Footer
//
function nel_render_basic_footer()
{
    global $total_html, $total_script;
    nel_render_init(TRUE);
    $dat = '';

    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        nel_render_in('logged_in', TRUE);
        nel_render_in('main_page', FALSE);
        
        if ($_SESSION['perms']['perm_ban'])
        {
            nel_render_in('perm_ban', TRUE);
        }
    }
    else
    {
        nel_render_in('logged_in', FALSE);
    }

    lol_html_timer(1);
    $dat = nel_parse_template('footer.tpl', '', '', FALSE);
    return $dat;
}

function nel_render_footer($link, $styles, $del, $response, $main_page)
{
    global $total_html, $total_script;

    if(!$main_page)
    {
        nel_render_init(TRUE);
        $dat = '';
        nel_render_in('main_page', FALSE);
    }
    else
    {
        nel_render_in('main_page', TRUE);
    }

    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        nel_render_in('logged_in', TRUE);
        nel_render_in('main_page', FALSE);

        if ($_SESSION['perms']['perm_ban'])
        {
            nel_render_in('perm_ban', TRUE);
        }
    }
    else
    {
        nel_render_in('logged_in', FALSE);
    }

    nel_render_in('link', $link);
    nel_render_in('styles_link', $styles);
    nel_render_in('del', $del);
    nel_render_in('response', $response);
    nel_render_in('main_page', $main_page);
    lol_html_timer(1);
    $dat_temp = nel_parse_template('footer.tpl', '', '', FALSE);
    return $dat_temp;
}

function nel_render_ban_page($dataforce, $bandata)
{
    nel_render_init(TRUE);
    $dat = '';
    nel_render_multiple_in($bandata);
    nel_render_in('appeal_status', (int) $bandata['appeal_status']);
    nel_render_in('format_length', date("D F jS Y  H:i", $bandata['length_base']));
    nel_render_in('format_time', date("D F jS Y  H:i", $bandata['ban_time']));
    nel_render_in('host', @inet_ntop($bandata['host']) ? inet_ntop($bandata['host']) : 'Unknown');
    lol_html_timer(0);
    $dat .= nel_render_header($dataforce, 'BAN', array());
    $dat .= nel_parse_template('ban_page.tpl', '', FALSE);
    $dat .= nel_render_basic_footer();
    return $dat;
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

function nel_escape_single_quotes($matches)
{
    if($matches[1] !== '')
    {
        return $matches[1];
    }
    else if($matches[2] !== '')
    {
        return '\'.' . $matches[3] . '.\'';
    }
    else
    {
        return '\\' .$matches[4];
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