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
    $language = new \Nelliel\language\Language(nel_authorize());
    $dom_nav = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom_nav, 'index_navigation.html');
    $bottom_nav = $dom_nav->getElementById('index-bottom-nav');
    $bottom_nav = $dom->getElementById('outer-div')->appendChild($dom->importNode($bottom_nav, true));
    $nav_nodes = $bottom_nav->getElementsByAttributeName('data-parse-id', true);

    foreach ($pages as $key => $value)
    {
        $temp_page_nav = $dom->copyNode($nav_nodes['nav-link-container'], $bottom_nav, 'append');
        $page_link = $temp_page_nav->doXPathQuery(".//a")->item(0);
        $content = $key;

        if ($value !== '')
        {
            $page_link->extSetAttribute('href', $value, 'attribute');
            $page_link->setContent($content);
        }
        else
        {
            $temp_page_nav->replaceChild($dom->createTextNode($content), $page_link);
        }
    }

    $nav_nodes['nav-link-container']->remove();
    $language->i18nDom($bottom_nav, nel_parameters_and_data()->boardSettings($board_id, 'board_language'));
}

function nel_render_post($board_id, $gen_data, $dom)
{
    $sessions = new \Nelliel\Sessions();
    $authorize = nel_authorize();
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $board_settings = nel_parameters_and_data()->boardSettings($board_id);
    $output_filter = new \Nelliel\OutputFilter();
    $response = $gen_data['post']['op'] != 1;
    $post_data = $gen_data['post'];
    $thread_data = $gen_data['thread'];

    if($response)
    {
        $base_content_id = \Nelliel\ContentID::createIDString($post_data['parent_thread'], $post_data['post_number']);
    }
    else
    {
        $base_content_id = \Nelliel\ContentID::createIDString($post_data['parent_thread']);
    }

    $thread_id = $post_data['parent_thread'];
    $post_id = $thread_id . '_' . $post_data['post_number'];
    $new_post_dom = $dom->copyNodeIntoDocument($dom->getElementById('post-id-'), true);
    $post_header_node = $new_post_dom->getElementById('post-header');
    $post_header_node->removeAttribute('id');
    $header_nodes = $post_header_node->getElementsByAttributeName('data-parse-id', true);

    $new_post_element = $new_post_dom->getElementById('post-id-');
    $new_post_element->changeId('post-id-' . $post_id);

    $post_container = $new_post_dom->getElementById('post-container-');
    $post_container->changeId('post-container-' . $post_id);

    $indents_element = $new_post_dom->getElementById('indents');
    $base_domain = $_SERVER['SERVER_NAME'] . pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
    $header_nodes['hide-post-thread']->extSetAttribute('data-id', $post_id);

    $board_web_path = '//' . $base_domain . '/' . rawurlencode($references['board_directory']) . '/';
    $pages_web_path = $board_web_path . rawurlencode($references['page_dir']) . '/';
    $thread_page_web_path = $pages_web_path . $thread_id . '/' . $thread_id . '.html';
    $src_web_path = $board_web_path . rawurlencode($references['src_dir']) . '/';
    $thread_src_web_path = $src_web_path . $thread_id . '/';
    $preview_web_path = $board_web_path . rawurlencode($references['thumb_dir']) . '/';
    $thread_preview_web_path = $preview_web_path . $thread_id . '/';

    if ($gen_data['index_rendering'] && !$response)
    {
        $header_nodes['hide-post-thread']->setContent(_gettext('Hide Thread'));
        $header_nodes['hide-post-thread']->extSetAttribute('data-alt-visual', _gettext('Show Thread'));
        $header_nodes['hide-post-thread']->extSetAttribute('data-command', 'hide-thread');
        $header_nodes['hide-post-thread']->changeID('hide-post-thread-' . $post_id);
    }
    else
    {
        $header_nodes['hide-post-thread']->setContent(_gettext('Hide Post'));
        $header_nodes['hide-post-thread']->extSetAttribute('data-alt-visual', _gettext('Show Post'));
        $header_nodes['hide-post-thread']->extSetAttribute('data-command', 'hide-post');
        $header_nodes['hide-post-thread']->changeID('hide-post-thread-' . $post_id);
    }

    if (!$sessions->sessionIsIgnored('render'))
    {
        $ip = @inet_ntop($post_data['ip_address']);
        $header_nodes['modmode-ip-address']->setContent(@inet_ntop($post_data['ip_address']));
        $header_nodes['modmode-ban-link']->extSetAttribute('href',
                '?module=bans&board_id=test&action=new&ban_type=POST&content-id=' . $base_content_id .
                '&ban_ip=' . rawurlencode($ip));

        if ($response)
        {
            $header_nodes['modmode-delete-link']->extSetAttribute('href',
                    '?module=threads&board_id=test&action=delete-post&content-id=' . $base_content_id);
            $header_nodes['modmode-ban-delete-link']->extSetAttribute('href',
                    '?module=multi&board_id=test&action=ban.delete-post&content-id=' . $base_content_id .
                    '&ban_type=POST&ban_ip=' . rawurlencode($ip));
            $header_nodes['modmode-lock-thread-link']->parentNode->remove();
            $header_nodes['modmode-sticky-thread-link']->parentNode->remove();
        }
        else
        {
            $header_nodes['modmode-delete-link']->extSetAttribute('href',
                    '?module=threads&board_id=test&action=delete-thread&content-id=' . $base_content_id);
            $header_nodes['modmode-ban-delete-link']->extSetAttribute('href',
                    '?module=multi&board_id=test&action=ban.delete-thread&content-id=' . $base_content_id .
                    '&ban_type=POST&ban_ip=' . rawurlencode($ip));

            if ($thread_data['locked'] == 1)
            {
                $header_nodes['modmode-lock-thread-link']->extSetAttribute('href',
                        '?module=threads&board_id=test&action=unlock' . '&content-id=' . $base_content_id);
                $header_nodes['modmode-lock-thread-link']->setContent(_gettext('Unlock Thread'));
            }
            else
            {
                $header_nodes['modmode-lock-thread-link']->extSetAttribute('href',
                        '?module=threads&board_id=test&action=lock&content-id=' . $base_content_id);
            }

            if ($thread_data['sticky'] == 1)
            {
                $header_nodes['modmode-sticky-thread-link']->extSetAttribute('href',
                        '?module=threads&board_id=test&action=unsticky&content-id=' . $base_content_id);
                $header_nodes['modmode-sticky-thread-link']->setContent(_gettext('Unsticky Thread'));
            }
            else
            {
                $header_nodes['modmode-sticky-thread-link']->extSetAttribute('href',
                        '?module=threads&board_id=test&action=sticky&content-id=' . $base_content_id);
            }
        }
    }
    else
    {
        $header_nodes['modmode-options']->remove();
    }

    $new_post_dom->getElementById('p-number')->changeId('p' . $post_id);
    $rev_post_id = $post_data['post_number'] . '_' . $post_data['parent_thread'];

    if ($response)
    {
        $post_type = 'reply';
        $post_type_class = 'reply-';
        $post_container->extSetAttribute('class', 'reply-post');

        $indents_element->setContent(nel_parameters_and_data()->boardSettings($board_id, 'indent_marker'));
        $indents_element->removeAttribute('id');

        $post_checkbox = $new_post_dom->getElementById('post_post-id');
        $post_checkbox->changeId('post_' . $post_id);
        $post_checkbox->extSetAttribute('name', $base_content_id);

        $new_post_dom->getElementById('thread_thread-id')->remove();
    }
    else
    {
        $post_type = 'op';
        $post_type_class = 'op-';
        $indents_element->remove();

        $thread_checkbox = $new_post_dom->getElementById('thread_thread-id');
        $thread_checkbox->changeId('thread_' . $thread_id);
        $thread_checkbox->extSetAttribute('name', $base_content_id);

        $post_checkbox = $new_post_dom->getElementById('post_post-id');
        $post_checkbox->changeId('post_' . $post_id);
        $post_checkbox->extSetAttribute('name', $base_content_id);
    }

    $header_nodes['subject']->modifyAttribute('class', $post_type, 'before');
    $header_nodes['subject']->setContent($post_data['subject']);
    $header_nodes['poster-name']->modifyAttribute('class', $post_type, 'before');

    $tripcode = (!empty($post_data['tripcode'])) ? $board_settings['tripkey_marker'] . $post_data['tripcode'] : '';
    $secure_tripcode = (!empty($post_data['secure_tripcode'])) ? $board_settings['tripkey_marker'] .
            $board_settings['tripkey_marker'] . $post_data['secure_tripcode'] : '';
            $capcode_text = ($post_data['mod_post']) ? $authorize->getRole($post_data['mod_post'])->auth_data['capcode_text'] : '';
    $trip_line = $tripcode . $secure_tripcode . '&nbsp;&nbsp;' . $capcode_text;

    if ($post_data['email'])
    {
        $header_nodes['poster-mailto']->modifyAttribute('href', $post_data['email'] . 'after');
        $header_nodes['poster-mailto']->setContent($post_data['poster_name']);
        $header_nodes['trip-line-']->setContent($trip_line);
    }
    else
    {
        $header_nodes['poster-mailto']->remove();
        $header_nodes['trip-line-']->setContent($post_data['poster_name'] . $trip_line);
    }

    $curr_time = $gen_data['post']['post_time'];

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

    $header_nodes['post-time-']->setContent($post_time);
    $header_nodes['post-num-link']->setContent($post_data['post_number']);
    $header_nodes['post-num-link']->extSetAttribute('href', $thread_page_web_path . '#p' . $post_id, 'none');
    $header_nodes['post-link-post']->extSetAttribute('data-id', $post_id);

    if (!$gen_data['index_rendering'] || $response)
    {
        $header_nodes['reply-to-link']->parentNode->remove();
    }
    else
    {
        if (!$sessions->sessionIsIgnored('render'))
        {
            $header_nodes['reply-to-link']->extSetAttribute('href',
                    PHP_SELF . '?module=render&action=view-thread&content-id=' . $base_content_id . '&section=' .
                    $thread_id . '&board_id=' . $board_id);
        }
        else
        {
            $header_nodes['reply-to-link']->extSetAttribute('href', $thread_page_web_path);
        }
    }

    if (!$gen_data['index_rendering'] || $response || !$gen_data['abbreviate'])
    {
        $header_nodes['expand-thread']->parentNode->remove();
    }
    else
    {
        $header_nodes['expand-thread']->extSetAttribute('data-id', $thread_id);
    }

    if ($response || !$thread_data['sticky'])
    {
        $header_nodes['sticky-icon']->remove();
    }

    if ($response || !$thread_data['locked'])
    {
        $header_nodes['locked-icon']->remove();
    }

    $multiple_files = false;
    $post_files_container = $new_post_dom->getElementById('post-files-container-');

    if ($post_data['has_file'] == 1)
    {
        $post_files_container->changeId('post-files-container-' . $post_id);
        $post_files_container->extSetAttribute('class', $post_type . '-files-container');

        $filecount = count($gen_data['files']);
        $multiple_class = '';

        if ($filecount > 1)
        {
            $multiple_class = 'multiple-';
            $multiple_files = true;
        }

        foreach ($gen_data['files'] as $file)
        {
            $file_content_id = 'nci_' . $post_data['parent_thread'] . '_' . $post_data['post_number'] . '_' . $file['file_order'];
            $full_filename = $file['filename'] . '.' . $file['extension'];
            $file_id = $post_data['parent_thread'] . '_' . $post_data['post_number'] . '_' . $file['file_order'];
            $temp_file_dom = $new_post_dom->copyNodeIntoDocument($new_post_dom->getElementById('fileinfo-'), true);

            $temp_file_node = $temp_file_dom->getElementById('fileinfo-');
            $temp_file_node->changeId('fileinfo-' . $file_id);
            $temp_file_node->extSetAttribute('class', $post_type_class . $multiple_class . 'fileinfo');

            $file_nodes = $temp_file_node->getElementsByAttributeName('data-parse-id', true);

            if (!$sessions->sessionIsIgnored('render'))
            {
                $file_nodes['modmode-delete-link']->extSetAttribute('href',
                        '?module=threads&board_id=test&action=delete-file&post-id=' .
                        $post_data['post_number'] . '&file-order=' . $file['file_order']);
            }
            else
            {
                $file_nodes['modmode-options']->remove();
            }

            $file_nodes['select-file']->extSetAttribute('name', $file_content_id);

            $file['file_location'] = $thread_src_web_path . $post_data['post_number'] . '/' .
                    rawurlencode($full_filename);
            $file['display_filename'] = $file['filename'];

            if (strlen($file['filename']) > 32)
            {
                $file['display_filename'] = substr($file['filename'], 0, 25) . '(...)';
            }

            $file_text_link = $temp_file_dom->getElementById('file-link-');
            $file_text_link->changeId('file-link-' . $file_id);
            $file_text_link->extSetAttribute('href', $file['file_location'], 'none');
            $file_text_link->setContent($file['display_filename'] . '.' . $file['extension']);

            $file['img_dim'] = !empty($file['display_width']) && !empty($file['display_height']);
            $file['filesize'] = round(((int) $file['filesize'] / 1024), 2);
            $filesize_display = ' (' . $file['filesize'] . ' KB)';

            if ($file['img_dim'])
            {
                $filesize_display = $file['display_width'] . ' x ' . $file['display_height'] . $filesize_display;
            }

            $file_nodes['filesize-display']->setContent($filesize_display);
            $file_nodes['show-file-meta']->extSetAttribute('data-id', $file_id);
            $file_nodes['show-file-meta']->changeId('show-file-meta-' . $file_id);
            $file_nodes['file-meta']->changeId('file-meta-' . $file_id);

            $output_filter->cleanAndEncode($file['source']);
            $output_filter->cleanAndEncode($file['license']);

            $file_nodes['file-source']->setContent('Source: ' . $file['source']);
            $file_nodes['file-license']->setContent('License: ' . $file['license']);

            // TODO: Find a way to streamline this
            if (!empty($file['md5']))
            {
                $file_nodes['file-md5']->setContent('MD5: ' . bin2hex($file['md5']));
            }
            else
            {
                $file_nodes['file-md5']->remove();
            }

            if (!empty($file['sha1']))
            {
                $file_nodes['file-sha1']->setContent('SHA1: ' . bin2hex($file['sha1']));
            }
            else
            {
                $file_nodes['file-sha1']->remove();
            }

            if (!empty($file['sha256']))
            {
                $file_nodes['file-sha256']->setContent('SHA256: ' . bin2hex($file['sha256']));
            }
            else
            {
                $file_nodes['file-sha256']->remove();
            }

            if (!empty($file['sha512']))
            {
                $file_nodes['file-sha512']->setContent('SHA512: ' . bin2hex($file['sha512']));
            }
            else
            {
                $file_nodes['file-sha512']->remove();
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

                    $location_element->remove();
                }
                else
                {
                    $full_preview_name = $file['preview_name'] . '.' . $file['preview_extension'];
                    $file['has_preview'] = false;
                    $video_preview->remove();

                    $location_element->extSetAttribute('href', $file['file_location'], 'none');
                    $location_element->changeId('file-location-' . $file_id);

                    if (!empty($file['preview_name']))
                    {
                        $file['has_preview'] = true;
                        $file['preview_location'] = $thread_preview_web_path . $post_data['post_number'] . '/' .
                                rawurlencode($full_preview_name);

                        if ($filecount > 1)
                        {
                            if ($file['preview_width'] > $board_settings['max_multi_width'] ||
                                    $file['preview_height'] > $board_settings['max_multi_height'])
                            {
                                $ratio = min(($board_settings['max_multi_height'] / $file['preview_height']),
                                        ($board_settings['max_multi_width'] / $file['preview_width']));
                                $file['preview_width'] = intval($ratio * $file['preview_width']);
                                $file['preview_height'] = intval($ratio * $file['preview_height']);
                            }
                        }
                    }
                    else if ($board_settings['use_file_icon'])
                    {
                        $format_icon = utf8_strtolower($file['format']) . '.png';
                        $type_icon = utf8_strtolower($file['type']) . '.png';

                        if (file_exists(
                                WEB_PATH . 'imagez/nelliel/filetype/' . utf8_strtolower($file['type']) . '/' .
                                $format_icon))
                        {
                            $file['has_preview'] = true;
                            $file['preview_location'] = '//' . $base_domain . '/web/imagez/nelliel/filetype/' .
                                    utf8_strtolower($file['format']) . '/' . $format_icon;
                            $file['preview_width'] = ($board_settings['max_width'] < 128) ? $board_settings['max_width'] : '128';
                            $file['preview_height'] = ($board_settings['max_height'] < 128) ? $board_settings['max_height'] : '128';
                        }
                        else if (file_exists(WEB_PATH . 'imagez/nelliel/filetype/generic/' . $type_icon))
                        {
                            $file['has_preview'] = true;
                            $file['preview_location'] = '//' . $base_domain . '/web/imagez/nelliel/filetype/generic/' .
                                    $type_icon;
                            $file['preview_width'] = ($board_settings['max_width'] < 128) ? $board_settings['max_width'] : '128';
                            $file['preview_height'] = ($board_settings['max_height'] < 128) ? $board_settings['max_height'] : '128';
                        }
                    }

                    if ($file['has_preview'])
                    {
                        $preview_element = $temp_file_dom->getElementById('file-preview-');
                        $preview_element->changeId('file-preview-' . $file_id);
                        $preview_element->extSetAttribute('src', $file['preview_location'], 'none');
                        $preview_element->extSetAttribute('width', $file['preview_width']);
                        $preview_element->extSetAttribute('height', $file['preview_height']);
                        $preview_element->extSetAttribute('alt', $file['alt_text']);
                        $preview_element->extSetAttribute('class', $post_type_class . $multiple_class . 'post-preview');
                        $preview_element->extSetAttribute('data-other-dims',
                                'w' . $file['display_width'] . 'h' . $file['display_height']);
                        $preview_element->extSetAttribute('data-other-loc', $file['file_location'], 'none');
                    }
                    else
                    {
                        $location_element->remove();
                    }
                }
            }
            else
            {
                $location_element->remove();
            }

            $imported = $new_post_dom->importNode($temp_file_node, true);
            $post_files_container->appendChild($imported);
        }

        $new_post_dom->getElementById('fileinfo-')->remove();
    }
    else
    {
        $post_files_container->remove();
    }

    $post_contents_element = $new_post_dom->getElementById('post-contents-');
    $post_contents_element->changeId('post-contents-' . $post_id);

    $contents_nodes = $post_contents_element->getElementsByAttributeName('data-parse-id', true);

    if ($multiple_files)
    {
        $post_contents_element->extSetAttribute('class', $post_type_class . 'post-contents-multifile');
    }
    else
    {
        $post_contents_element->extSetAttribute('class', $post_type_class . 'post-contents');
    }

    $contents_nodes['post-text']->extSetAttribute('class', $post_type_class . 'post-text');

    if (!empty($post_data['mod_comment']))
    {
        $contents_nodes['mod-comment']->setContent('(' . $post_data['mod_comment'] . ')');
    }
    else
    {
        $contents_nodes['mod-comment']->remove();
    }

    $contents_nodes['post-comment']->changeId('post-comment-' . $post_id);
    $output_filter->clearWhitespace($post_data['comment']);

    if ($post_data['comment'] === '')
    {
        $contents_nodes['post-comment']->setContent(_gettext('(no comment)'));
    }
    else
    {
        foreach ($output_filter->newlinesToArray($post_data['comment']) as $line)
        {
            $append_target = $contents_nodes['post-comment'];
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

function nel_render_thread_form_bottom($board_id, $dom)
{
    $sessions = new \Nelliel\Sessions();
    $board_settings = nel_parameters_and_data()->boardSettings($board_id);
    $footer_form_element = $dom->getElementById('footer-form');
    $form_td_list = $footer_form_element->doXPathQuery(".//input");
    $dom->getElementById('board_id_field_footer')->extSetAttribute('value', $board_id);

    if ($sessions->sessionIsIgnored('render'))
    {
        $dom->getElementById('admin-input-set1')->remove();
        $dom->getElementById('bottom-submit-button')->setContent('Submit');
    }
    else
    {
        $dom->getElementById('bottom-pass-input')->remove();
    }

    if (!$board_settings['use_new_imgdel'])
    {
        $form_td_list->item(4)->remove();
    }

    $dom->getElementById('outer-div')->appendChild($footer_form_element);
}
