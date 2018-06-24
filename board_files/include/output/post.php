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

function nel_render_index_navigation($board_id, $dom, $render, $pages)
{
    $dom_nav = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom_nav, 'index_navigation.html');
    $index_bottom_nav_element = $dom_nav->getElementById('index-bottom-nav');
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
            $temp_page_nav_td->replaceChild($dom_nav->createTextNode($content), $page_link);
        }

        $page_nav_td->parentNode->insertBefore($temp_page_nav_td, $inner_td_elements->item(2));
    }

    $page_nav_td->removeSelf();
    nel_process_i18n($dom_nav, nel_board_settings($board_id, 'board_language'));
    $imported = $dom->importNode($index_bottom_nav_element, true);
    $dom->getElementById('outer-div')->appendChild($imported);
}

function nel_render_post($board_id, $gen_params, $response, $gen_data, $dom)
{
    $authorize = nel_authorize();
    $references = nel_board_references($board_id);
    $board_settings = nel_board_settings($board_id);
    $output_filter = new \Nelliel\OutputFilter();
    $start = microtime(true);
    $post_data = $gen_data['post'];
    $thread_id = $post_data['parent_thread'];
    $post_id = $thread_id . '_' . $post_data['post_number'];
    $new_post_dom = $dom->copyNodeIntoDocument($dom->getElementById('post-id-'), true);
    $post_header_node = $new_post_dom->getElementById('post-header');
    $post_header_node->removeAttribute('id');
    $post_header_node_array = $new_post_dom->getAssociativeNodeArray('data-parse-id', $post_header_node);
    $new_post_element = $new_post_dom->getElementById('post-id-');
    $new_post_element->changeId('post-id-' . $post_id);
    $post_container = $new_post_dom->getElementById('post-container-');
    $post_container->changeId('post-container-' . $post_id);
    $hide_post_thread = $new_post_dom->getElementById('hide-post-thread-');
    $hide_post_thread->extSetAttribute('data-id', $post_id);
    $hide_post_thread->changeId('hide-post-thread-' . $post_id);
    $indents_element = $new_post_dom->getElementById('indents');
    $base_domain = $_SERVER['SERVER_NAME'] . pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);

    if ($gen_params['index_rendering'] && !$response)
    {
        $hide_post_thread->setContent('Hide Thread');
        $hide_post_thread->extSetAttribute('data-alt-visual', 'Show Thread');
        $hide_post_thread->extSetAttribute('data-command', 'hide-thread');
    }
    else
    {
        $hide_post_thread->setContent('Hide Post');
        $hide_post_thread->extSetAttribute('data-alt-visual', 'Show Post');
        $hide_post_thread->extSetAttribute('data-command', 'hide-post');
    }

    $new_post_dom->getElementById('p-number')->changeId('p' . $post_id);
    $rev_post_id = $post_data['post_number'] . '_' . $post_data['parent_thread'];

    if ($response)
    {
        $post_type = 'reply';
        $post_type_class = 'reply-';
        $post_container->extSetAttribute('class', 'reply-post');
        $indents_element->setContent(nel_board_settings($board_id, 'indent_marker'));
        $indents_element->removeAttribute('id');
        $post_checkbox = $new_post_dom->getElementById('post_post-id');
        $post_checkbox->changeId('post_' . $post_id);
        $post_checkbox->extSetAttribute('name', 'post_' . $rev_post_id);
        $post_checkbox->extSetAttribute('value', 'deletepost_' . $post_id);
        $new_post_dom->getElementById('thread_thread-id')->removeSelf();
    }
    else
    {
        $post_type = 'op';
        $post_type_class = 'op-';
        $indents_element->removeSelf();
        $thread_checkbox = $new_post_dom->getElementById('thread_thread-id');
        $thread_checkbox->changeId('thread_' . $thread_id);
        $thread_checkbox->extSetAttribute('name', 'thread_' . $thread_id);
        $thread_checkbox->extSetAttribute('value', 'deletethread_' . $thread_id);
        $new_post_dom->getElementById('post_post-id')->removeSelf();
    }

    $post_header_node_array['subject']->modifyAttribute('class', $post_type, 'before');
    $post_header_node_array['subject']->setContent($post_data['subject']);
    $post_header_node_array['poster-name']->modifyAttribute('class', $post_type, 'before');
    $tripcode = (!is_null($post_data['tripcode'])) ? $board_settings['tripkey_marker'] . $post_data['tripcode'] : '';
    $secure_tripcode = (!is_null($post_data['secure_tripcode'])) ? $board_settings['tripkey_marker'] .
        $board_settings['tripkey_marker'] . $post_data['secure_tripcode'] : '';
    $capcode_text = ($post_data['mod_post']) ? $authorize->get_role_info($post_data['mod_post'], 'capcode_text') : '';
    $trip_line = $tripcode . $secure_tripcode . '&nbsp;&nbsp;' . $capcode_text;

    if ($post_data['email'])
    {
        $post_header_node_array['poster-mailto']->modifyAttribute('href', $post_data['email'] . 'after');
        $post_header_node_array['poster-mailto']->setContent($post_data['poster_name']);
        $post_header_node_array['trip-line-']->setContent($trip_line);
    }
    else
    {
        $post_header_node_array['poster-mailto']->removeSelf();
        $post_header_node_array['trip-line-']->setContent($post_data['poster_name'] . $trip_line);
    }

    $curr_time = floor($gen_data['post']['post_time'] / 1000);

    switch ($board_settings['date_format'])
    {
        case 'ISO':
            $post_time = date("Y", $curr_time) . $board_settings['date_separator'] . date("m", $curr_time) .
                $board_settings['date_separator'] . date("d (D) H:i:s", $curr_time);
            break;

        case 'US':
            $post_time = date("m", $curr_time) . $board_settings['date_separator'] . date("d", $curr_time) .
                $board_settings['date_separator'] . date("Y (D) H:i:s", $curr_time);
            break;

        case 'COM':
            $post_time = date("d", $curr_time) . $board_settings['date_separator'] . date("m", $curr_time) .
                $board_settings['date_separator'] . date("Y (D) H:i:s", $curr_time);
            break;
    }

    $post_header_node_array['post-time-']->setContent($post_time);
    $post_header_node_array['post-num-link-']->setContent($post_data['post_number']);
    $post_header_node_array['post-num-link-']->extSetAttribute('href', $references['page_dir'] .
        $post_data['parent_thread'] . '/' . $post_data['parent_thread'] . '.html#p' . $post_id, 'none');
    $post_header_node_array['post-num-link-']->changeId('post-num-link-' . $post_id);
    $post_header_node_array['post-link-post']->extSetAttribute('data-id', $post_id);

    if (!$response && $gen_data['thread']['sticky'])
    {
        $post_header_node_array['sticky-icon']->extSetAttribute('src', IMAGES_DIR . '/nelliel/' . _gettext('sticky.png'), 'url');
        $post_header_node_array['sticky-icon']->changeId('sticky-icon-' . $post_id);
    }
    else
    {
        $post_header_node_array['sticky-icon']->removeSelf();
    }

    if (!$response)
    {
        if (!nel_sessions()->sessionIsIgnored('render'))
        {
            $post_header_node_array['reply-to-link']->extSetAttribute('href', PHP_SELF . '?mode=display&post=' .
                $post_data['post_number']);
        }
        else
        {
            $post_header_node_array['reply-to-link']->extSetAttribute('href', $references['page_dir'] .
                $post_data['parent_thread'] . '/' . $post_data['post_number'] . '.html');
        }
    }

    if (!$gen_params['index_rendering'] || $response)
    {
        $post_header_node_array['reply-to-link']->parentNode->removeSelf();
    }

    $thread_link_html = $references['page_dir'] . $thread_id . '/' . $thread_id;
    $post_header_node_array['expand-thread']->extSetAttribute('data-id', $thread_id);

    if (!$gen_params['index_rendering'] || $response || !$gen_params['abbreviate'])
    {
        $post_header_node_array['expand-thread']->parentNode->removeSelf();
    }

    // TODO: New inline ban options
    $mod_tools_1 = $new_post_dom->getElementById('mod-tools-1');
    $mod_tools_1->removeSelf();

    $multiple_files = false;
    $post_files_container = $new_post_dom->getElementById('post-files-container-');

    if ($post_data['has_file'] == 1)
    {
        $post_files_container->changeId('post-files-container-' . $post_id);
        $post_files_container->extSetAttribute('class', $post_type . '-files-container');
        $filecount = count($gen_data['files']);
        $file_node = $new_post_dom->getElementById('fileinfo-');
        $multiple_class = '';

        if ($filecount > 1)
        {
            $multiple_class = 'multiple-';
            $multiple_files = true;
        }

        foreach ($gen_data['files'] as $file)
        {
            $file_id = $post_data['parent_thread'] . '_' . $post_data['post_number'] . '_' . $file['file_order'];
            $temp_file_dom = $new_post_dom->copyNodeIntoDocument($new_post_dom->getElementById('fileinfo-'), true);
            $temp_file_node = $temp_file_dom->getElementById('fileinfo-');
            $temp_file_node_array = $temp_file_dom->getAssociativeNodeArray('data-parse-id', $temp_file_node);
            $temp_file_node->changeId('fileinfo-' . $file_id);
            $temp_file_node->extSetAttribute('class', $post_type_class . $multiple_class . 'fileinfo');
            $temp_file_node_array['delete-file']->extSetAttribute('name', 'file_' . $file_id);
            $temp_file_node_array['delete-file']->extSetAttribute('value', 'deletefile_' . $file_id);

            $file['file_location'] = 'http://' . $base_domain . '/' . $board_id . '/' . $references['src_dir'] .
                $thread_id . '/' . $post_data['post_number'] . '/' . rawurlencode($file['filename']) . '.' .
                $file['extension'];
            $file['display_filename'] = $file['filename'];

            if (strlen($file['filename']) > 32)
            {
                $file['display_filename'] = substr($file['filename'], 0, 25) . '(...)';
            }

            $file_text_link = $temp_file_dom->getElementById('file-link-');
            $file_text_link->changeId('file-link-' . $file_id);
            $file_text_link->extSetAttribute('href', $file['file_location'], 'none');
            $file_text_link->setContent($file['display_filename'] . '.' . $file['extension']);

            $file['img_dim'] = (!is_null($file['image_width']) && !is_null($file['image_height'])) ? true : false;
            $file['filesize'] = round(((int) $file['filesize'] / 1024), 2);
            $filesize_display = ' ( ' . $file['filesize'] . ' KB)';

            if ($file['img_dim'])
            {
                $filesize_display = $file['image_width'] . ' x ' . $file['image_height'] . $filesize_display;
            }

            $temp_file_node_array['filesize-display']->setContent($filesize_display);
            $temp_file_node_array['show-file-meta']->extSetAttribute('data-id', $file_id);
            $temp_file_node_array['show-file-meta']->changeId('show-file-meta-' . $file_id);
            $temp_file_node_array['file-meta']->changeId('file-meta-' . $file_id);

            $output_filter->cleanAndEncode($file['source']);
            $output_filter->cleanAndEncode($file['license']);

            $temp_file_node_array['file-source']->setContent('Source: ' . $file['source']);
            $temp_file_node_array['file-license']->setContent('License: ' . $file['license']);

            // TODO: Find a way to streamline this
            if (!empty($file['md5']))
            {
                $temp_file_node_array['file-md5']->setContent('MD5: ' . bin2hex($file['md5']));
            }
            else
            {
                $temp_file_node_array['file-md5']->removeSelf();
            }

            if (!empty($file['sha1']))
            {
                $temp_file_node_array['file-sha1']->setContent('SHA1: ' . bin2hex($file['sha1']));
            }
            else
            {
                $temp_file_node_array['file-sha1']->removeSelf();
            }

            if (!empty($file['sha256']))
            {
                $temp_file_node_array['file-sha256']->setContent('SHA256: ' . bin2hex($file['sha256']));
            }
            else
            {
                $temp_file_node_array['file-sha256']->removeSelf();
            }

            if (!empty($file['sha512']))
            {
                $temp_file_node_array['file-sha512']->setContent('SHA512: ' . bin2hex($file['sha512']));
            }
            else
            {
                $temp_file_node_array['file-sha512']->removeSelf();
            }

            $location_element = $temp_file_dom->getElementById('file-location-');
            $video_preview = $temp_file_dom->getElementById('video-preview-');

            if ($board_settings['use_thumb'])
            {
                if ($file['format'] == 'webm' || $file['format'] == 'mpeg4')
                {
                    $video_preview->changeId('video-preview-' . $file_id);
                    $video_preview->extSetAttribute('width', $board_settings['max_width']);
                    $video_preview_source = $video_preview->getElementsByTagName('source')->item(0);
                    $video_preview_source->extSetAttribute('src', $file['file_location']);
                    $video_preview_source->extSetAttribute('type', $file['mime']);
                    $location_element->removeSelf();
                }
                else
                {
                    $file['has_preview'] = false;
                    $video_preview->removeSelf();
                    $location_element->extSetAttribute('href', $file['file_location'], 'none');
                    $location_element->changeId('file-location-' . $file_id);
                    $preview_element = $temp_file_dom->getElementById('file-preview-');
                    $preview_element->changeId('file-preview-' . $file_id);

                    if (!empty($file['preview_name']))
                    {
                        $file['has_preview'] = true;
                        $file['preview_location'] = 'http://' . $base_domain . '/' . $board_id . '/' .
                            $references['thumb_dir'] . $thread_id . '/' . $post_data['post_number'] . '/' .
                            rawurlencode($file['preview_name'] . '.' . $file['preview_extension']);

                        if ($filecount > 1)
                        {
                            if ($file['preview_width'] > $board_settings['max_multi_width'] ||
                                $file['preview_height'] > $board_settings['max_multi_height'])
                            {
                                $ratio = min(($board_settings['max_multi_height'] / $file['preview_height']), ($board_settings['max_multi_width'] /
                                    $file['preview_width']));
                                $file['preview_width'] = intval($ratio * $file['preview_width']);
                                $file['preview_height'] = intval($ratio * $file['preview_height']);
                            }
                        }
                    }
                    else if ($board_settings['use_file_icon'])
                    {
                        $format_icon = utf8_strtolower($file['format']) . '.png';
                        $type_icon = utf8_strtolower($file['type']) . '.png';

                        if (file_exists(WEB_PATH . 'imagez/nelliel/filetype/' . utf8_strtolower($file['type']) . '/' .
                            $format_icon))
                        {
                            $file['has_preview'] = true;
                            $file['preview_location'] = '../' . IMAGES_DIR . 'nelliel/filetype/' .
                                utf8_strtolower($file['format']) . '/' . $format_icon;
                            $file['preview_width'] = ($board_settings['max_width'] < 128) ? $board_settings['max_width'] : '128';
                            $file['preview_height'] = ($board_settings['max_height'] < 128) ? $board_settings['max_height'] : '128';
                        }
                        else if (file_exists(WEB_PATH . 'imagez/nelliel/filetype/generic/' . $type_icon))
                        {
                            $file['has_preview'] = true;
                            $file['preview_location'] = '../' . IMAGES_DIR . 'nelliel/filetype/generic/' . $type_icon;
                            $file['preview_width'] = ($board_settings['max_width'] < 128) ? $board_settings['max_width'] : '128';
                            $file['preview_height'] = ($board_settings['max_height'] < 128) ? $board_settings['max_height'] : '128';
                        }
                    }

                    if ($file['has_preview'])
                    {
                        $preview_element->extSetAttribute('src', $file['preview_location'], 'none');
                        $preview_element->extSetAttribute('width', $file['preview_width']);
                        $preview_element->extSetAttribute('height', $file['preview_height']);
                        $preview_element->extSetAttribute('alt', $file['alt_text']);
                        $preview_element->extSetAttribute('class', $post_type_class . $multiple_class . 'post-preview');
                        $preview_element->extSetAttribute('data-other-dims', 'w' . $file['image_width'] . 'h' .
                            $file['image_height']);
                        $preview_element->extSetAttribute('data-other-loc', $file['file_location'], 'none');
                    }
                    else
                    {
                        $location_element->removeSelf();
                    }
                }
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

    $post_contents_element = $new_post_dom->getElementById('post-contents-');
    $post_contents_element->changeId('post-contents-' . $post_id);
    $post_contents_node_array = $new_post_dom->getAssociativeNodeArray('data-parse-id', $post_contents_element);

    if ($multiple_files)
    {
        $post_contents_element->extSetAttribute('class', $post_type_class . 'post-contents-multifile');
    }
    else
    {
        $post_contents_element->extSetAttribute('class', $post_type_class . 'post-contents');
    }

    $post_contents_node_array['post-text']->extSetAttribute('class', $post_type_class . 'post-text');
    $post_contents_node_array['mod-comment']->setContent($post_data['mod_comment']);
    $post_contents_node_array['post-comment']->changeId('post-comment-' . $post_id);
    $output_filter->clearWhitespace($post_data['comment']);

    if ($post_data['comment'] === '')
    {
        $post_contents_node_array['post-comment']->setContent(_gettext('(no comment)'));
    }
    else
    {
        foreach ($output_filter->newlinesToArray($post_data['comment']) as $line)
        {
            $append_target = $post_contents_node_array['post-comment'];
            $quote_result = $output_filter->postQuote($append_target, $line);

            if ($quote_result !== false)
            {
                $append_target = $quote_result;
            }

            $output_filter->postQuoteLink($board_id, $append_target, $line);
            $append_target->appendChild($new_post_dom->createElement('br'));
        }
    }

    $new_post_dom->getElementById('ban-')->changeId('ban' . $post_data['post_number']);
    return $new_post_element;
}

function nel_render_post_adjust_relative($node, $gen_data)
{
    $post_id = $gen_data['post']['parent_thread'] . '_' . $gen_data['post']['post_number'];
    $new_post_dom = $node->ownerDocument->copyNodeIntoDocument($node, true);
    $new_post_dom->getElementById('post-num-link-' . $post_id)->modifyAttribute('href', '../../', 'before');
    $sticky_element = $new_post_dom->getElementById('sticky-icon-');

    foreach ($new_post_dom->getElementById('post-comment-' . $post_id)->getElementsByClassName('link-quote') as $comment_element)
    {
        $comment_element->modifyAttribute('href', '../../', 'before');
    }

    if (!is_null($sticky_element))
    {
        $sticky_element->modifyAttribute('src', '../../', 'before');
    }

    return $new_post_dom->getElementById('post-id-' . $post_id);
}

function nel_render_thread_form_bottom($board_id, $dom)
{
    $board_settings = nel_board_settings($board_id);
    $footer_form_element = $dom->getElementById('footer-form');
    $form_td_list = $footer_form_element->doXPathQuery(".//input");
    $dom->getElementById('board_id_field_footer')->extSetAttribute('value', $board_id);

    if (nel_sessions()->sessionIsIgnored('render'))
    {
        $dom->getElementById('admin-input-set1')->removeSelf();
        $dom->getElementById('bottom-submit-button')->setContent('Submit');
    }
    else
    {
        $dom->getElementById('bottom-pass-input')->removeSelf();
    }

    if (!$board_settings['use_new_imgdel'])
    {
        $form_td_list->item(4)->removeSelf();
    }

    $dom->getElementById('outer-div')->appendChild($footer_form_element);
}
