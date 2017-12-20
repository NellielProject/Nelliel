<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_insert_hr($dom)
{
    $hr = $dom->createElement('hr');
    $hr->setAttribute('class', 'clear');
    $dom->getElementById('outer-div')->appendChild($hr);
}

function nel_render_index_navigation($render, $pages)
{
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'index_navigation.html');
    $index_bottom_nav_element = $dom->getElementById('index-bottom-nav');
    $inner_td_elements = $index_bottom_nav_element->doXPathQuery(".//td");
    $page_nav_td = $inner_td_elements->item(0);

    foreach ($pages as $key => $value)
    {
        $temp_page_nav_td = $page_nav_td->cloneNode(true);
        $page_link = $temp_page_nav_td->doXPathQuery(".//a")->item(0);

        $content = $key;

        if ($key === 'prev')
        {
            $content = 'Previous';
        }

        if ($key === 'next')
        {
            $content = 'Next';
        }

        if ($value !== '')
        {
            $page_link->extSetAttribute('href', $value, 'none');
            $page_link->setContent($content);
        }
        else
        {
            $temp_page_nav_td->replaceChild($dom->createTextNode($content), $page_link);
        }

        $page_nav_td->parentNode->insertBefore($temp_page_nav_td, $inner_td_elements->item(2));
    }

    $page_nav_td->removeSelf();
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
}

function nel_render_post($dataforce, $render, $response, $partial, $gen_data, $treeline, $dom)
{
    $authorize = nel_authorize();
    global $link_resno;

    $start = microtime(true);
    $post_data = $gen_data['post'];
    $post_id = $post_data['parent_thread'] . '_' . $post_data['post_number'];
    $new_post_dom = $dom->copyNodeIntoDocument($dom->getElementById('post-id-'), true);
    $new_post_element = $new_post_dom->getElementById('post-id-');
    $new_post_element->changeId('post-id-' . $post_id);
    //$dotdot = isset($dataforce['dotdot']) ? $dataforce['dotdot'] : '';
    $post_container = $new_post_dom->getElementById('post-container-');
    $post_container->changeId('post-container-' . $post_data['post_number']);

    if ($response)
    {
        $post_type = 'reply';
        $post_type_class = 'reply-';
        $post_container->extSetAttribute('class', 'reply-post');
        $new_post_dom->getElementById('indents-')->changeId('indents-' . $post_id);
    }
    else
    {
        $post_type = 'op';
        $post_type_class = 'op-';
        $new_post_dom->getElementById('indents-')->removeSelf();
    }

    $new_post_dom->getElementById('p-number')->changeId('p' . $post_data['post_number']);
    $rev_post_id = $post_data['post_number'] . '_' . $post_data['parent_thread'];
    $thread_id = $post_data['parent_thread'];

    $post_header = $new_post_dom->getElementById('post-header-');
    $post_header->changeId('post-header-' . $post_id);
    $post_checkbox = $new_post_dom->getElementById('post_post-id');
    $post_checkbox->changeId('post_' . $post_id);
    $post_checkbox->extSetAttribute('name', 'post_' . $rev_post_id);
    $post_checkbox->extSetAttribute('value', 'deletepost_' . $post_id);
    $subject_element = $new_post_dom->getElementById('-subject');
    $subject_element->changeId($post_id . '-subject');
    $subject_element->modifyAttribute('class', $post_type, 'before');
    $subject_element->setContent($post_data['subject']);
    $poster_name_element = $new_post_dom->getElementById('-poster-name');
    $poster_name_element->changeId($post_id . '-poster-name');
    $poster_name_element->modifyAttribute('class', $post_type, 'before');

    $tripcode = (!is_null($post_data['tripcode'])) ? BS_TRIPKEY_MARKER . $post_data['tripcode'] : '';
    $secure_tripcode = (!is_null($post_data['secure_tripcode'])) ? BS_TRIPKEY_MARKER . BS_TRIPKEY_MARKER .
         $post_data['secure_tripcode'] : '';
    $capcode_text = '';

    if ($post_data['mod_post'])
    {
        $capcode_text = $authorize->get_role_info($post_data['mod_post'], 'capcode_text');
    }

    $mailto_element = $new_post_dom->getElementById('poster-mailto');
    $trip_line_element = $new_post_dom->getElementById('trip-line-');
    $trip_line = $tripcode . $secure_tripcode . '&nbsp;&nbsp;' . $capcode_text;
    $trip_line_element->changeId('trip-line-' . $post_id);

    if ($post_data['email'])
    {
        $mailto_element->modifyAttribute('href', $post_data['email'] . 'after');
        $mailto_element->setContent($post_data['poster_name']);
        $trip_line_element->setContent($trip_line);
    }
    else
    {
        $mailto_element->removeSelf();
        $trip_line_element->setContent($post_data['poster_name'] . $trip_line);
    }

    $curr_time = floor($gen_data['post']['post_time'] / 1000);
    // Up to 0.00022

    switch (BS_DATE_FORMAT)
    {
        case 'ISO':
            $post_time = date("Y", $curr_time) . BS_DATE_SEPARATOR . date("m", $curr_time) . BS_DATE_SEPARATOR .
                 date("d (D) H:i:s", $curr_time);
            break;

        case 'US':
            $post_time = date("m", $curr_time) . BS_DATE_SEPARATOR . date("d", $curr_time) . BS_DATE_SEPARATOR .
                 date("Y (D) H:i:s", $curr_time);
            break;

        case 'COM':
            $post_time = date("d", $curr_time) . BS_DATE_SEPARATOR . date("m", $curr_time) . BS_DATE_SEPARATOR .
                 date("Y (D) H:i:s", $curr_time);
            break;
    }

    $post_time_element = $new_post_dom->getElementById('post-time-');
    $post_time_element->setContent($post_time);
    $post_time_element->changeId('post-time-' . $post_id);
    $post_quote_element = $new_post_dom->getElementById('post-quote-link-');
    $post_quote_element->setContent($post_data['post_number']);
    $post_quote_element->changeId('post-quote-link-' . $post_id);

    if ($response)
    {
        $post_quote_element->extSetAttribute('href', 'javascript:postQuote(\'' . $post_data['post_number'] . '\')', 'none');
    }
    else
    {
        $post_quote_element->extSetAttribute('href', PAGE_DIR . $post_data['parent_thread'] . '/' .
             $post_data['parent_thread'] . '.html', 'none');
    }

    $sticky_icon_element = $new_post_dom->getElementById('sticky-icon-');

    if ($gen_data['thread']['sticky'])
    {
        $sticky_icon_element->extSetAttribute('src', BOARD_FILES . '/imagez/nelliel/' . nel_stext('THREAD_STICKY_ICON'), 'url');
        //$sticky_icon_element->extSetAttribute('src', $dotdot . BOARD_FILES . '/imagez/nelliel/' .
        //nel_stext('THREAD_STICKY_ICON'), 'url');
        $sticky_icon_element->changeId('sticky-icon-' . $post_id);
    }
    else
    {
        $sticky_icon_element->removeSelf();
    }

    $reply_to_link_element = $new_post_dom->getElementById('reply-to-link-');
    $reply_to_link_element->changeId('reply-to-link-' . $post_id);

    if (!$response)
    {
        if (!nel_session_is_ignored('render'))
        {
            $reply_to_link_element->extSetAttribute('href', PHP_SELF . '?mode=display&post=' . $post_data['post_number']);
        }
        else
        {
            $reply_to_link_element->extSetAttribute('href', PAGE_DIR . $post_data['parent_thread'] . '/' .
                 $post_data['post_number'] . '.html');
        }
    }
    // Up to 0.00028

    $reply_to_link_element->changeId('reply-to-link-' . $post_id);

    if (!$dataforce['index_rendering'] || $response)
    {
        $reply_to_link_element->parentNode->removeSelf();
    }

    $thread_link_html = PAGE_DIR . $thread_id . '/' . $thread_id;

    $expand_js = 'javascript:clientSideInclude(\'thread-expand-' . $thread_id . '\',\'expLink' . $thread_id . '\',\'' .
         $thread_link_html . '-expand.html\',\'' . $thread_link_html . '-collapse.html\',\'Collapse Thread\')';

    $expand_link_element = $new_post_dom->getElementById('expLink');
    $expand_link_element->changeId('expLink' . $thread_id);
    $expand_link_element->extSetAttribute('href', $expand_js);

    if (!$dataforce['index_rendering'] || $response || !$dataforce['abbreviate'])
    {
        $expand_link_element->parentNode->removeSelf();
    }

    $mod_tools_1 = $new_post_dom->getElementById('mod-tools-1');

    if (!nel_session_is_ignored('render'))
    {
        $new_post_dom->getElementById('ip-address-display')->setContent($post_data['ip_address']);
        $set_ban_details = $new_post_dom->getElementById('set-ban-details');

        if (nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_ban_add'))
        {
            $ban_details = 'addBanDetails(\'ban' . $post_data['post_number'] . '\', \'' . $post_data['post_number'] .
                 '\', \'' . $post_data['poster_name'] . '\', \'' . $post_data['ip_address'] . '\')';
            $set_ban_details->extSetAttribute('onclick', $ban_details, 'none');
        }
        else
        {
            $set_ban_details->removeSelf();
        }
    }
    else
    {
        $mod_tools_1->removeSelf();
    }

    $post_files_container = $new_post_dom->getElementById('post-files-container-');
    // Up to 0.00041

    if ($post_data['has_file'] == 1)
    {
        $post_files_container->changeId('post-files-container-' . $post_id);
        $post_files_container->extSetAttribute('class', $post_type . '-files-container');
        $filecount = count($gen_data['files']);
        $file_node = $new_post_dom->getElementById('fileinfo-');
        $multiple_class = '';
        $multiple_files = false;

        if ($filecount > 1)
        {
            $multiple_class = 'multiple-';
            $multiple_files = true;
        }

        foreach ($gen_data['files'] as $file)
        {
            $temp_file_dom = $new_post_dom->copyNodeIntoDocument($new_post_dom->getElementById('fileinfo-'), true);
            $temp_file_node = $temp_file_dom->getElementById('fileinfo-');
            $file_id = $post_data['parent_thread'] . '_' . $post_data['post_number'] . '_' . $file['file_order'];
            $temp_file_node->changeId('fileinfo-' . $file_id);
            $temp_file_node->extSetAttribute('class', $post_type_class . $multiple_class . 'fileinfo');

            $delete_file_element = $temp_file_dom->getElementById('delete-file-');
            $delete_file_element->changeId('delete-file-' . $file_id);
            $delete_file_element->extSetAttribute('name', 'files' . $file_id);

            $file['file_location'] = SRC_DIR . $thread_id . '/' . $file['filename'] . "." . $file['extension'];
            $file['display_filename'] = $file['filename'];

            if (strlen($file['filename']) > 32)
            {
                $file['display_filename'] = substr($file['filename'], 0, 25) . '(...)';
            }

            $file_text_link = $temp_file_dom->getElementById('file-link-');
            $file_text_link->changeId('file-link-' . $file_id);
            $file_text_link->extSetAttribute('href', $file['file_location']);
            $file_text_link->setContent($file['display_filename'] . '.' . $file['extension']);

            $file['img_dim'] = (!is_null($file['image_width']) && !is_null($file['image_height'])) ? true : false;
            $file['filesize'] = round(((int) $file['filesize'] / 1024), 2);
            $filesize_display = ' ( ' . $file['filesize'] . ' KB)';

            if ($file['img_dim'])
            {
                $filesize_display = $file['image_width'] . ' x ' . $file['image_height'] . $filesize_display;
            }

            $filesize_display_element = $temp_file_dom->getElementById('filesize-display-');
            $filesize_display_element->setContent($filesize_display);
            $filesize_display_element->changeId('filesize-display-' . $file_id);
            $show_file_meta_element = $temp_file_dom->getElementById('show-file-meta-');
            $show_script = 'javascript:displayImgMeta(\'file-meta-' . $file_id . '\',\'show-file-meta-' . $file_id .
                 '\',\'none\',\'' . nel_stext('THREAD_LESS_DATA') . '\')';
            $show_file_meta_element->extSetAttribute('href', $show_script, 'none');
            $show_file_meta_element->changeId('show-file-meta-' . $file_id);
            $temp_file_dom->getElementById('file-meta-')->changeId('file-meta-' . $file_id);

            $file['source'] = nel_cleanse_the_aids($file['source']);
            $file['license'] = nel_cleanse_the_aids($file['license']);

            $source_element = $temp_file_dom->getElementById('file-source-');
            $source_element->changeId('file-source-' . $file_id);
            $source_element->setContent('Source: ' . $file['source']);

            $license_element = $temp_file_dom->getElementById('file-license-');
            $license_element->changeId('file-license-' . $file_id);
            $license_element->setContent('License: ' . $file['license']);

            $md5_element = $temp_file_dom->getElementById('file-md5-');
            $md5_element->changeId('file-md5-' . $file_id);
            $md5_element->setContent('MD5: ' . $file['md5']);

            $sha1_element = $temp_file_dom->getElementById('file-sha1-');
            $sha1_element->changeId('file-sha1-' . $file_id);
            $sha1_element->setContent('SHA1: ' . $file['sha1']);

            $location_element = $temp_file_dom->getElementById('file-location-');

            if (BS_USE_THUMB)
            {
                $location_element->extSetAttribute('href', $file['file_location'], 'none');
                $location_element->changeId('file-location-' . $file_id);
                $preview_element = $temp_file_dom->getElementById('file-preview-');
                $preview_element->changeId('file-preview-' . $file_id);

                if (isset($file['preview_name']))
                {
                    $file['has_preview'] = true;
                    $file['preview_location'] = THUMB_DIR . $thread_id . '/' . $file['preview_name'];

                    if ($filecount > 1)
                    {
                        if ($file['preview_width'] > BS_MAX_MULTI_WIDTH || $file['preview_height'] > BS_MAX_MULTI_HEIGHT)
                        {
                            $ratio = min((BS_MAX_MULTI_HEIGHT / $file['preview_height']), (BS_MAX_MULTI_WIDTH /
                                 $file['preview_width']));
                            $file['preview_width'] = intval($ratio * $file['preview_width']);
                            $file['preview_height'] = intval($ratio * $file['preview_height']);
                        }
                    }
                }
                else if (BS_USE_FILE_ICON && file_exists(BOARD_FILES . 'imagez/nelliel/filetype/' .
                     utf8_strtolower($file['supertype']) . '/' . utf8_strtolower($file['subtype']) . '.png'))
                {
                    $file['has_preview'] = true;
                    $file['preview_location'] = BOARD_FILES . '/imagez/nelliel/filetype/' .
                         utf8_strtolower($files[$i]['supertype']) . '/' . utf8_strtolower($file['subtype']) . '.png';
                    $file['preview_width'] = (BS_MAX_WIDTH < 64) ? BS_MAX_WIDTH : '128';
                    $file['preview_height'] = (BS_MAX_HEIGHT < 64) ? BS_MAX_HEIGHT : '128';
                }
                else
                {
                    $file['has_preview'] = false;
                }

                $preview_element->extSetAttribute('src', $file['preview_location'], 'none');
                $preview_element->extSetAttribute('width', $file['preview_width']);
                $preview_element->extSetAttribute('height', $file['preview_height']);
                $preview_element->extSetAttribute('alt', $file['filesize'] . ' KB)');
                $preview_element->extSetAttribute('class', $post_type_class . $multiple_class . 'post-preview');
            }
            else
            {
                $location_element->removeSelf();
            }

            if (strlen($file['filename']) > 32)
            {
                $file['display_filename'] = substr($file['filename'], 0, 25) . '(...)';
            }

            $imported = $new_post_dom->importNode($temp_file_node, true);
            $post_files_container->appendChild($imported);
        }

        $file_node->removeSelf();
    }
    else
    {
        $post_files_container->removeSelf();
    }
    // Up to 0.0041 (no file)
    // Approx 0.00030 per file

    $post_data['comment'] = nel_newline_cleanup($post_data['comment']);
    $post_data['comment'] = preg_replace('#(^|>)(&gt;[^<]*|ÅÑ[^<]*)#', '$1<span class="post-quote">$2</span>', $post_data['comment']);
    $post_data['comment'] = preg_replace_callback('#&gt;&gt;([0-9]+)#', 'nel_parse_links', $post_data['comment']);

    if (nel_clear_whitespace($post_data['comment']) === '')
    {
        $post_data['comment'] = nel_stext('THREAD_NOTEXT');
    }

    $post_contents_element = $new_post_dom->getElementById('post-contents-');
    $post_contents_element->changeId('post-contents-' . $post_id);
    $post_contents_element->extSetAttribute('class', $post_type_class . 'post-text');
    $post_text_element = $new_post_dom->getElementById('-post-text');
    $post_text_element->changeId($post_id . '-post-text');
    $post_text_element->extSetAttribute('class', $post_type_class . 'post-contents');
    $post_comment_element = $new_post_dom->getElementById('post-comment-');
    $post_comment_element->setContent($post_data['comment']);
    $post_comment_element->changeId('post-comment-' . $post_id);
    $mod_comment_element = $new_post_dom->getElementById('mod-comment-');
    $mod_comment_element->setContent($post_data['mod_comment']);
    $mod_comment_element->changeId('mod-comment-' . $post_id);
    $new_post_dom->getElementById('ban-')->changeId('ban' . $post_data['post_number']);
    // Up to 0.00044 (no file)
    nel_process_i18n($new_post_dom);
    // Up to 0.00056 (no file)
    return $new_post_element;
}

function nel_render_post_adjust_relative($node, $gen_data)
{
    $post_id = $gen_data['post']['parent_thread'] . '_' . $gen_data['post']['post_number'];
    $new_post_dom = $node->ownerDocument->copyNodeIntoDocument($node, true);
    $sticky_element = $new_post_dom->getElementById('sticky-icon-');

    if (!is_null($sticky_element))
    {
        $sticky_element->modifyAttribute('src', '../../', 'before');
    }

    if ($gen_data['post']['has_file'] == 1)
    {
        foreach ($gen_data['files'] as $file)
        {
            $file_id = $post_id . '_' . $file['file_order'];
            $new_post_dom->getElementById('file-link-' . $file_id)->modifyAttribute('href', '../../', 'before');
            $new_post_dom->getElementById('file-location-' . $file_id)->modifyAttribute('href', '../../', 'before');

            $preview_element = $new_post_dom->getElementById('file-preview-' . $file_id);

            if (!is_null($preview_element))
            {
                $preview_element->modifyAttribute('src', '../../', 'before');
            }
        }
    }

    return $new_post_dom->getElementById('post-id-' . $post_id);
}