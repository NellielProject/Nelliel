<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

nel_parse_links($dataforce['post_links']);

//
// Generate the header
//
function nel_render_header($dataforce, $render, $treeline)
{
    $title = '';
    
    if (isset($dataforce['dotdot']))
    {
        $render->add_data('dotdot', $dataforce['dotdot']);
    }
    else
    {
        $render->add_data('dotdot', '');
    }
    
    if (BS1_SHOW_LOGO)
    {
        $title .= '<img src="' . BS_BOARD_LOGO . '" alt="' . BS_BOARD_NAME . '" class="logo-alt-text">';
    }
    
    if (BS1_SHOW_TITLE)
    {
        $title .= '<h1>' . BS_BOARD_NAME . '</h1>';
    }
    
    $render->add_data('titlepart', $title);
    
    switch ($render->retrieve_data('header_type'))
    {
        case 'ABOUT':
            $render->add_data('page_title', 'About Nelliel Imageboard');
            break;
        
        case 'NORMAL':
            if ($dataforce['page_gen'] == 'main')
            {
                $render->add_data('page_title', BS_BOARD_NAME);
            }
            else
            {
                $render->add_data('page_title', ($treeline[0]['subject'] === '') ? BS_BOARD_NAME . ' > Thread #' . $treeline[0]['post_number'] : BS_BOARD_NAME . ' > ' . $treeline[0]['subject']);
            }
            
            break;
        
        default:
            $render->add_data('page_title', BS_BOARD_NAME);
            break;
    }
    
    $render->add_data('log_out', (!empty($_SESSION) && !$_SESSION['ignore_login']) ? '[<a href="' . $render->retrieve_data('dotdot') . PHP_SELF . '?mode=log_out">Log Out</a>]' : '');
    $render->add_data('page_ref1', (!empty($_SESSION) && !$_SESSION['ignore_login']) ? PHP_SELF . '?mode=display&page=0' : PHP_SELF2 . PHP_EXT);
    $render->parse('header.tpl', '');
}

//
// Generate reply form
//
function nel_render_posting_form($dataforce, $render)
{
    $render->add_data('response_id', (is_null($dataforce['response_id'])) ? '0' : $dataforce['response_id']);
    $render->add_data('rules_list', $dataforce['rules_list']);
    $render->add_data('form_submit_url', $dataforce['dotdot'] . PHP_SELF);
    
    if (BS1_ALLOW_MULTIFILE)
    {
        if ($render->retrieve_data('response_id'))
        {
            $render->add_data('allow_multifile', TRUE);
        }
        else if (!$render->retrieve_data('response_id') && BS1_ALLOW_OP_MULTIFILE)
        {
            $render->add_data('response_id', '0');
            $render->add_data('allow_multifile', TRUE);
        }
        else
        {
            $render->add_data('allow_multifile', FALSE);
        }
    }
    else
    {
        $render->retrieve_data('allow_multifile', FALSE);
    }
    
    $render->add_data('modmode', ($dataforce['get_mode'] === 'display') ? TRUE : FALSE);
    
    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        $render->add_data('logged_in', TRUE);
        $render->add_data('page_ref1', PHP_SELF . '?mode=display&page=0');
        $render->add_data('page_ref2', PHP_SELF . '?page=');
    }
    else
    {
        $render->add_data('logged_in', FALSE);
        $render->add_data('page_ref1', PHP_SELF2 . PHP_EXT);
    }
    
    $render->add_data('max_files', 3);
    $render->parse('posting_form.tpl', '', $render, FALSE);
}

//
// Render posts
//
function nel_render_post($dataforce, $render, $response, $partial, $gen_data, $treeline, $dbh)
{
    global $link_resno;
    
    $render->add_data('insert_hr', $gen_data['insert_hr']);
    $post_data = $treeline[$gen_data['post_counter']];
    $render->add_multiple_data($post_data);
    
    if ($partial)
    {
        $link_resno = 0;
    }
    else
    {
        $link_resno = $dataforce['response_id'];
    }
    
    $render->add_data('expand_post', $gen_data['expand_post']);
    $render->add_data('first100', $gen_data['first100']);
    $render->add_data('response_id', $dataforce['response_id']);
    $render->add_data('tripcode', (!is_null($post_data['tripcode'])) ? BS_TRIPKEY_MARKER . $post_data['tripcode'] : '');
    $render->add_data('secure_tripcode', (!is_null($post_data['secure_tripcode'])) ? BS_TRIPKEY_MARKER . BS_TRIPKEY_MARKER . $post_data['secure_tripcode'] : '');
    $post_data['comment'] = nel_newline_cleanup($post_data['comment']);
    $post_data['comment'] = preg_replace('#(^|>)(&gt;[^<]*|ÅÑ[^<]*)#', '$1<span class="post-quote">$2</span>', $post_data['comment']);
    $post_data['comment'] = preg_replace_callback('#&gt;&gt;([0-9]+)#', 'nel_parse_links', $post_data['comment']);
    if (nel_clear_whitespace($post_data['comment']) === '')
    {
        $post_data['comment'] = nel_stext('THREAD_NOTEXT');
    }
    $render->add_sanitized_data('comment-part', utf8_str_replace('>><a href="../"', '>><a href="', $post_data['comment']));
    $render->add_sanitized_data('comment', $post_data['comment']);
    $render->add_sanitized_data('name', $post_data['name']);
    $render->add_sanitized_data('email', $post_data['email']);
    $render->add_sanitized_data('subject', $post_data['subject']);
    $render->add_data('sticky', (bool) $post_data['sticky']);
    $temp_dot = ($partial) ? '' : $dataforce['dotdot'];
    $post_id = ($response) ? $post_data['response_to'] : $post_data['post_number'];
    
    if (!$dataforce['omitted_done'])
    {
        $render->add_data('omitted_count', $gen_data['post_count'] - BS_ABBREVIATE_THREAD);
        $render->add_data('omitted_posts', TRUE);
    }
    else
    {
        $render->add_data('omitted_posts', FALSE);
    }
    
    if ($gen_data['has_file'])
    {
        $render->add_data('has_file', TRUE);
        $filecount = count($gen_data['files']);
        $render->add_data('multifile', ($filecount > 1) ? TRUE : FALSE);
        $i = 0;
        
        $files = $gen_data['files'];
        
        while ($i < $filecount)
        {
            $files[$i]['img_dim'] = (!is_null($files[$i]['image_width']) && !is_null($files[$i]['image_height'])) ? TRUE : FALSE;
            $files[$i]['file_location'] = $temp_dot . SRC_DIR . $post_id . '/' . $files[$i]['filename'] . "." . $files[$i]['extension'];
            $files[$i]['filesize'] = round(((int) $files[$i]['filesize'] / 1024), 2);
            $files[$i]['md5'] = bin2hex($files[$i]['md5']);
            
            if (BS1_USE_THUMB)
            {
                if (isset($files[$i]['preview_name']))
                {
                    $files[$i]['has_preview'] = TRUE;
                    $files[$i]['preview_location'] = $temp_dot . THUMB_DIR . $post_id . '/' . $files[$i]['preview_name'];
                    
                    if ($files[$i]['preview_width'] != 0)
                    {
                        if ($files[$i]['preview_width'] > BS_MAX_MULTI_WIDTH || $files[$i]['preview_height'] > BS_MAX_MULTI_HEIGHT)
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
            
            $files[$i]['source'] = nel_cleanse_the_aids($files[$i]['source']);
            $files[$i]['license'] = nel_cleanse_the_aids($files[$i]['license']);
            
            $files[$i]['endline'] = (($i + 1) % BS_MAX_FILES_ROW == 0) ? TRUE : FALSE;
            ++ $i;
        }
        
        $render->add_data('files', $files);
    }
    else
    {
        $render->add_data('multifile', FALSE);
    }
    
    $curr_time = floor($render->retrieve_data('post_time') / 1000);
    
    switch (BS_DATE_FORMAT)
    {
        case 'ISO':
            $render->add_data('post_time', date("Y", $curr_time) . BS_DATE_SEPARATOR . date("m", $curr_time) . BS_DATE_SEPARATOR . date("d (D) H:i:s", $curr_time));
            break;
        
        case 'US':
            $render->add_data('post_time', date("m", $curr_time) . BS_DATE_SEPARATOR . date("d", $curr_time) . BS_DATE_SEPARATOR . date("Y (D) H:i:s", $curr_time));
            break;
        
        case 'COM':
            $render->add_data('post_time', date("d", $curr_time) . BS_DATE_SEPARATOR . date("m", $curr_time) . BS_DATE_SEPARATOR . date("Y (D) H:i:s", $curr_time));
            break;
    }
    
    switch ($render->retrieve_data('mod_post'))
    {
        case '1':
            $render->add_data('staff_post', nel_stext('THREAD_JANPOST'));
            $render->add_data('secure_tripcode', '');
            break;
        
        case '2':
            $render->add_data('staff_post', nel_stext('THREAD_MODPOST'));
            $render->add_data('secure_tripcode', '');
            break;
        
        case '3':
            $render->add_data('staff_post', nel_stext('THREAD_ADMINPOST'));
            $render->add_data('secure_tripcode', '');
            break;
        
        default:
            $render->add_data('staff_post', '');
    }
    
    $render->add_data('logged_in', FALSE);
    $render->add_data('page_ref1', PHP_SELF2 . PHP_EXT);
    $render->add_data('page_ref2', '');
    
    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        $render->add_data('logged_in', TRUE);
        $render->add_data('host', (@inet_ntop($render->retrieve_data('host'))) ? inet_ntop($render->retrieve_data('host')) : 'Unknown');
        $render->add_data('perm_ban', $_SESSION['perms']['perm_ban']);
        $render->add_data('page_ref1', PHP_SELF . '?mode=display&page=0');
        $render->add_data('page_ref2', PHP_SELF . '?page=');
        $render->add_data('the_session', session_id());
    }
    
    if ($response)
    {
        $render->parse('response_post.tpl', '');
    }
    else
    {
        $render->parse('op_post.tpl', '');
    }
}

//
// Footer
//
function nel_render_basic_footer($render)
{
    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        $render->add_data('logged_in', TRUE);
        $render->add_data('main_page', FALSE);
        
        if ($_SESSION['perms']['perm_ban'])
        {
            $render->add_data('perm_ban', TRUE);
        }
    }
    else
    {
        $render->add_data('logged_in', FALSE);
    }
    
    $render->parse('footer.tpl', '');
}

function nel_render_footer($render, $link, $styles, $del, $response, $main_page)
{
    $render->add_data('main_page', $main_page);
    
    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        $render->add_data('logged_in', TRUE);
        $render->add_data('main_page', FALSE);
        
        if ($_SESSION['perms']['perm_ban'])
        {
            $render->add_data('perm_ban', TRUE);
        }
    }
    else
    {
        $render->add_data('logged_in', FALSE);
    }
    
    $render->add_data('link', $link);
    $render->add_data('styles_link', $styles);
    $render->add_data('del', $del);
    $render->add_data('response', $response);
    $render->add_data('main_page', $main_page);
    $render->parse('footer.tpl', '');
}

function nel_render_ban_page($dataforce, $bandata)
{
    $render = new nel_render();
    $render->add_multiple_data($bandata);
    $render->add_data('appeal_status', (int) $bandata['appeal_status']);
    $render->add_data('format_length', date("D F jS Y  H:i", $bandata['length_base']));
    $render->add_data('format_time', date("D F jS Y  H:i", $bandata['ban_time']));
    $render->add_data('host', @inet_ntop($bandata['host']) ? inet_ntop($bandata['host']) : 'Unknown');
    nel_render_header($dataforce, $render, array());
    $render->parse('ban_page.tpl', '');
    nel_render_basic_footer($render);
    $render->output(TRUE);
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
    if ($matches[1] !== '')
    {
        return $matches[1];
    }
    else if ($matches[2] !== '')
    {
        return '\'.' . $matches[3] . '.\'';
    }
    else
    {
        return '\\' . $matches[4];
    }
}
?>