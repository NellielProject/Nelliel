<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_insert_hr($render)
{
    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $hr = $dom->createElement('hr');
    $hr->setAttribute('class', 'clear');
    $dom->appendChild($hr);
    $render->appendOutput($dom->saveHTML());
    return;
}

function nel_render_index_navigation($render, $pages)
{
    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $render1->loadTemplateFromFile($dom, 'index_navigation.html');
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
    $render->appendOutput($render1->outputHTML($dom));
}

function nel_render_post($dataforce, $render, $response, $partial, $gen_data, $treeline)
{
    $authorize = nel_get_authorization();
    global $link_resno;

    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $render1->loadTemplateFromFile($dom, 'post.html');
    $dotdot = isset($dataforce['dotdot']) ? $dataforce['dotdot'] : '';

    if ($dataforce['posts_beginning'])
    {
        $dom->getElementById('form-post-index')->extSetAttribute('action', $dotdot . PHP_SELF);
    }
    else
    {
        $dom->removeElementKeepChildren($dom->getElementById('outer-div'));
        $dom->removeElementKeepChildren($dom->getElementById('form-post-index'));
    }

    $post_data = $gen_data['post'];

    $post_container = $dom->getElementById('post-container-');
    $post_container->changeId('post-container-' . $post_data['post_number']);
    $post_type = ($response) ? 'reply' : 'op';

    if ($response)
    {
        $post_container->extSetAttribute('class', 'reply-post');
    }
    else
    {
        $dom->getElementsByClassName('indents')->item(0)->removeSelf();
    }

    $render->add_multiple_data($gen_data['post']);
    $render->add_multiple_data($gen_data['thread']);

    $dom->getElementById('p-number')->changeId('p' . $render->get('post_number'));
    $post_id = $post_data['parent_thread'] . '_' . $render->get('post_number');
    $rev_post_id = $render->get('post_number') . '_' . $post_data['parent_thread'];
    $thread_id = $post_data['parent_thread'];

    $post_header = $dom->getElementsByClassName('post-header')->item(0);

    $post_checkbox = $post_header->doXPathQuery(".//input[@name='post_post-id']")->item(0);
    $post_checkbox->extSetAttribute('name', 'post_' . $rev_post_id);
    $post_checkbox->extSetAttribute('value', 'deletepost_' . $post_id);
    $subject_element = $post_header->doXPathQuery(".//span[@class='-subject']")->item(0);
    $subject_element->modifyAttribute('class', $post_type, 'before');
    $subject_element->setContent($post_data['subject']);
    $poster_name_element = $post_header->doXPathQuery(".//span[@class='-poster-name']")->item(0);
    $poster_name_element->modifyAttribute('class', $post_type, 'before');

    $tripcode = (!is_null($post_data['tripcode'])) ? BS_TRIPKEY_MARKER . $post_data['tripcode'] : '';
    $secure_tripcode = (!is_null($post_data['secure_tripcode'])) ? BS_TRIPKEY_MARKER . BS_TRIPKEY_MARKER .
         $post_data['secure_tripcode'] : '';

    $mailto_element = $dom->getElementById('poster-mailto');
    $trip_line_element = $dom->getElementById('trip-line-');
    $trip_line = $tripcode . $secure_tripcode . '&nbsp;&nbsp;' . $render->get('staff_post');
    $trip_line_element->changeId('trip-line-' . $post_id);

    if ($render->get('email'))
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

    $post_time_element = $dom->getElementById('post-time-');
    $post_time_element->setContent($post_time);
    $post_time_element->changeId('post-time-' . $post_id);
    $post_quote_element = $dom->getElementById('post-quote-link-');
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

    $sticky_icon_element = $dom->getElementById('sticky-icon-');
    $sticky_icon_element->changeId('sticky-icon-' . $post_id);

    if ($render->get('sticky'))
    {
        $sticky_icon_element->extSetAttribute('src', $dotdot . BOARD_FILES . '/imagez/nelliel/' .
             nel_stext('THREAD_STICKY_ICON'), 'url');
    }
    else
    {
        $sticky_icon_element->removeSelf();
    }

    $reply_to_link_element = $dom->getElementById('reply-to-link-');
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

    if(!$dataforce['index_rendering'] || $response)
    {
        $reply_to_link_element->parentNode->removeSelf();
    }

    $mod_tools_1 = $dom->getElementById('mod-tools-1');

    if (!nel_session_is_ignored('render'))
    {
        $dom->getElementById('ip-address-display')->setContent($render->get('ip_address'));
        $set_ban_details = $dom->getElementById('set-ban-details');

        if (nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_ban_add'))
        {
            $ban_details = 'addBanDetails(\'ban' . $post_data['post_number'] . '\', \'' . $post_data['post_number'] .
                 '\', \'' . $post_data['poster_name'] . '\', \'' . $render->get('ip_address') . '\')';
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

    $temp_dot = ($partial) ? '' : $dataforce['dotdot'];
    $post_files_container = $dom->getElementById('post-files-container-');

    if ($post_data['has_file'] == 1)
    {
        $post_files_container->changeId('post-files-container-' . $post_id);
        $post_files_container->extSetAttribute('class', $post_type . '-files-container');

        $filecount = count($gen_data['files']);
        $render->add_data('has_file', true);
        $render->add_data('multifile', ($filecount > 1) ? true : false);

        $file_node = $dom->getElementById('fileinfo-');

        foreach ($gen_data['files'] as $file)
        {
            $file_id = $post_data['parent_thread'] . '_' . $post_data['post_number'] . '_' . $file['file_order'];

            $temp_file_node = $file_node->cloneNode(true);
            $temp_file_node->changeId('fileinfo-' . $file_id);
            $post_files_container->appendChild($temp_file_node);

            if ($filecount > 1)
            {
                $temp_file_node->extSetAttribute('class', $post_type . '-multiple-fileinfo');
            }
            else
            {
                $temp_file_node->extSetAttribute('class', $post_type . '-fileinfo');
            }

            $delete_file_element = $temp_file_node->doXPathQuery(".//input[@id='delete-file-']")->item(0);
            $delete_file_element->changeId('delete-file-' . $file_id);
            $delete_file_element->extSetAttribute('name', 'files' . $file_id);

            $file['file_location'] = $temp_dot . SRC_DIR . $thread_id . '/' . $file['filename'] . "." .
                 $file['extension'];
            $file['display_filename'] = $file['filename'];

            if (strlen($file['filename']) > 32)
            {
                $file['display_filename'] = substr($file['filename'], 0, 25) . '(...)';
            }

            $file_text_link = $temp_file_node->doXPathQuery(".//a[@id='file-link-']")->item(0);
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

            $filesize_display_element = $temp_file_node->doXPathQuery(".//*[@id='filesize-display-']")->item(0);
            $filesize_display_element->setContent($filesize_display);
            $filesize_display_element->changeId('filesize-display-' . $file_id);
            $show_file_meta_element = $temp_file_node->doXPathQuery(".//*[@id='show-file-meta-']")->item(0);
            $show_script = 'javascript:displayImgMeta(\'file-meta-' . $file_id . '\',\'show-file-meta-' . $file_id .
                 '\',\'none\',\'' . nel_stext('THREAD_LESS_DATA') . '\')';
            $show_file_meta_element->extSetAttribute('href', $show_script, 'none');
            $show_file_meta_element->changeId('show-file-meta-' . $file_id);
            $file_meta_element = $temp_file_node->doXPathQuery(".//*[@id='file-meta-']")->item(0);
            $file_meta_element->changeId('file-meta-' . $file_id);

            $file['source'] = nel_cleanse_the_aids($file['source']);
            $file['license'] = nel_cleanse_the_aids($file['license']);

            $source_element = $temp_file_node->doXPathQuery(".//*[@id='file-source-']")->item(0);
            $source_element->changeId('file-source-' . $file_id);
            $source_element->setContent('Source: ' . $file['source']);

            $license_element = $temp_file_node->doXPathQuery(".//*[@id='file-license-']")->item(0);
            $license_element->changeId('file-license-' . $file_id);
            $license_element->setContent('License: ' . $file['license']);

            $md5_element = $temp_file_node->doXPathQuery(".//*[@id='file-md5-']")->item(0);
            $md5_element->changeId('file-md5-' . $file_id);
            $md5_element->setContent('MD5: ' . $file['md5']);

            $sha1_element = $temp_file_node->doXPathQuery(".//*[@id='file-sha1-']")->item(0);
            $sha1_element->changeId('file-sha1-' . $file_id);
            $sha1_element->setContent('SHA1: ' . $file['sha1']);

            $location_element = $temp_file_node->doXPathQuery(".//*[@id='file-location-']")->item(0);

            if (BS_USE_THUMB)
            {
                $location_element->extSetAttribute('href', $file['file_location'], 'none');
                $location_element->changeId('file-location-' . $file_id);
                $preview_element = $temp_file_node->doXPathQuery(".//*[@id='file-preview-']")->item(0);
                $preview_element->changeId('file-preview-' . $file_id);

                if (isset($file['preview_name']))
                {
                    $file['has_preview'] = true;
                    $file['preview_location'] = $temp_dot . THUMB_DIR . $thread_id . '/' . $file['preview_name'];

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
                    $file['preview_location'] = $temp_dot . BOARD_FILES . '/imagez/nelliel/filetype/' .
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
                $preview_element->extSetAttribute('class', 'preview_class'); // TODO: wat
            }
            else
            {
                $location_element->removeSelf();
            }

            if (strlen($file['filename']) > 32)
            {
                $file['display_filename'] = substr($file['filename'], 0, 25) . '(...)';
            }
        }

        $file_node->removeSelf();
    }
    else
    {
        $post_files_container->removeSelf();
    }

    // TODO: Fix/remove this
    if ($partial)
    {
        $link_resno = 0;
    }
    else
    {
        $link_resno = $dataforce['response_id'];
    }

    $post_data['comment'] = nel_newline_cleanup($post_data['comment']);
    $post_data['comment'] = preg_replace('#(^|>)(&gt;[^<]*|ÅÑ[^<]*)#', '$1<span class="post-quote">$2</span>', $post_data['comment']);
    $post_data['comment'] = preg_replace_callback('#&gt;&gt;([0-9]+)#', 'nel_parse_links', $post_data['comment']);

    if (nel_clear_whitespace($post_data['comment']) === '')
    {
        $post_data['comment'] = nel_stext('THREAD_NOTEXT');
    }

    $omitted_element = $dom->getElementsByClassName('omitted-posts')->item(0);

    if (!$dataforce['omitted_done'])
    {
        $omitted_count = $gen_data['thread']['post_count'] - BS_ABBREVIATE_THREAD;
        $omitted_element->firstChild->setContent($omitted_count);
    }
    else
    {
        $omitted_element->removeSelf();
    }

    $post_contents_element = $dom->getElementById('post-contents-');
    $post_contents_element->changeID('post-contents-' . $post_id);
    $post_contents_element->extSetAttribute('class', $post_type . '-post_text');
    $post_text_element = $dom->getElementsByClassName('-post-text')->item(0);
    $post_text_element->extSetAttribute('class', $post_type . '-post-contents');
    $post_comment_element = $dom->getElementById('post-comment-');
    $post_comment_element->setContent($post_data['comment']);
    $post_comment_element->changeID('post-comment-' . $post_id);
    $mod_comment_element = $dom->getElementById('mod-comment-');
    //$post_comment_element->setContent($post_data['mod_comment']); TODO: Fix this
    $mod_comment_element->changeID('mod-comment-' . $post_id);
    $dom->getElementById('ban-')->changeID('ban' . $post_data['post_number']);

    $mod_post_role = $render->get('mod_post');

    if ($mod_post_role)
    {
        $capcode_text = $authorize->get_role_info($mod_post_role, 'capcode_text');
        $render->add_data('staff_post', $capcode_text);
        $render->add_data('secure_tripcode', '');
    }
    else
    {
        $render->add_data('staff_post', '');
    }

    $render->add_data('logged_in', FALSE);
    $render->add_data('page_ref1', PHP_SELF2 . PHP_EXT);
    $render->add_data('page_ref2', '');

    nel_process_i18n($dom);
    $render->appendOutput($render1->outputHTML($dom));
}