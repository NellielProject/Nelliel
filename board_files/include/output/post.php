<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_insert_hr($dom)
{
    $hr = $dom->createElement('hr');
    $hr->setAttribute('class', 'clear');
    $dom->getElementById('form-content-action')->appendChild($hr);
}

function nel_render_index_navigation($domain, $dom, $nav_pieces)
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $translator = new \Nelliel\Language\Translator();
    $dom_nav = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom_nav, 'index_navigation.html');
    $bottom_nav = $dom_nav->getElementById('index-bottom-nav');
    $bottom_nav = $dom->getElementById('form-content-action')->appendChild($dom->importNode($bottom_nav, true));
    $nav_nodes = $bottom_nav->getElementsByAttributeName('data-parse-id', true);

    foreach ($nav_pieces as $piece)
    {
        $temp_page_nav = $dom->copyNode($nav_nodes['nav-link-container'], $bottom_nav, 'append');
        $page_link = $temp_page_nav->doXPathQuery(".//a")->item(0);
        $content = $piece['text'];

        if ($piece['link'] !== '')
        {
            $page_link->extSetAttribute('href', $piece['link'], 'attribute');
            $page_link->setContent($content);
        }
        else
        {
            $temp_page_nav->replaceChild($dom->createTextNode($content), $page_link);
        }
    }

    $nav_nodes['nav-link-container']->remove();
    $translator->translateDom($bottom_nav, $domain->setting('language'));
}

function nel_render_post($domain, $gen_data, $dom)
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $session = new \Nelliel\Session($authorization);
    $output_filter = new \Nelliel\OutputFilter();
    $response = $gen_data['post']['op'] != 1;
    $post_data = $gen_data['post'];
    $thread_data = $gen_data['thread'];
    $new_post_dom = $dom->copyNodeIntoDocument($dom->getElementById('post-id-nci_0_0_0'), true);

    if ($response)
    {
        $post_type = 'reply';
        $post_type_class = 'reply-';
    }
    else
    {
        $post_type = 'op';
        $post_type_class = 'op-';
    }

    $thread_content_id = new \Nelliel\ContentID(\Nelliel\ContentID::createIDString($post_data['parent_thread']));
    $post_content_id = new \Nelliel\ContentID(
            \Nelliel\ContentID::createIDString($post_data['parent_thread'], $post_data['post_number']));

    $post_header_node = $new_post_dom->getElementById('header-nci_0_0_0');
    $post_header_node->changeId('header-' . $post_content_id->getIDString());
    $post_header_node->extSetAttribute('class', $post_type_class . 'post-header');
    $header_nodes = $post_header_node->getElementsByAttributeName('data-parse-id', true);

    $new_post_element = $new_post_dom->getElementById('post-id-nci_0_0_0');
    $new_post_element->changeId('post-id-' . $post_content_id->getIDString());

    $post_container = $new_post_dom->getElementById('post-container-nci_0_0_0');
    $post_container->changeId('post-container-' . $post_content_id->getIDString());

    $indents_element = $new_post_dom->getElementById('indents');
    $base_domain = $_SERVER['SERVER_NAME'] . pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);

    $board_web_path = '//' . $base_domain . '/' . rawurlencode($domain->reference('board_directory')) . '/';
    $pages_web_path = $board_web_path . rawurlencode($domain->reference('page_dir')) . '/';
    $thread_page_web_path = $pages_web_path . $thread_content_id->thread_id . '/thread-' . $thread_content_id->thread_id .
            '.html';
    $src_web_path = $board_web_path . rawurlencode($domain->reference('src_dir')) . '/';
    $thread_src_web_path = $src_web_path . $thread_content_id->thread_id . '/';
    $preview_web_path = $board_web_path . rawurlencode($domain->reference('thumb_dir')) . '/';
    $thread_preview_web_path = $preview_web_path . $thread_content_id->thread_id . '/';

    if ($response)
    {
        $header_nodes['thread-header-options']->remove();
        $header_nodes['post-header-options']->extSetAttribute('class', 'reply-post-header-options');
        $header_nodes['post-header-info']->extSetAttribute('class', 'reply-post-header-options');
    }

    if ($gen_data['index_rendering'] && !$response)
    {
        $header_nodes['hide-thread']->extSetAttribute('data-content-id', $post_content_id->getIDString());
        $header_nodes['hide-thread']->changeID('hide-thread-' . $thread_content_id->getIDString());
    }

    $header_nodes['hide-post']->extSetAttribute('data-content-id', $post_content_id->getIDString());
    $header_nodes['hide-post']->changeID('hide-post-' . $post_content_id->getIDString());

    $raw_poster_id = hash('sha256', @inet_ntop($post_data['ip_address']) . $thread_data['thread_id'] . TRIPCODE_PEPPER);
    $poster_id = substr($raw_poster_id, 0, $domain->setting('poster_id_length'));
    $header_nodes['poster-id']->setContent('ID: ' . $poster_id);

    if ($session->inModmode($domain->id()) && !$domain->renderActive())
    {
        $ip = @inet_ntop($post_data['ip_address']);
        $header_nodes['modmode-ip-address']->setContent($ip);

        if ($response)
        {
            $temp_content_id = $post_content_id;
            $header_nodes['modmode-lock-thread-link']->parentNode->remove();
            $header_nodes['modmode-sticky-thread-link']->parentNode->remove();
        }
        else
        {
            $temp_content_id = $thread_content_id;

            if ($thread_data['locked'] == 1)
            {
                $header_nodes['modmode-lock-thread-link']->extSetAttribute('href',
                        '?module=threads-admin&board_id=' . $domain->id() . '&action=unlock' . '&content-id=' .
                        $thread_content_id->getIDString() . '&modmode=true');
                $header_nodes['modmode-lock-thread-link']->setContent(_gettext('Unlock Thread'));
            }
            else
            {
                $header_nodes['modmode-lock-thread-link']->extSetAttribute('href',
                        '?module=threads-admin&board_id=' . $domain->id() . '&action=lock&content-id=' .
                        $thread_content_id->getIDString() . '&modmode=true');
            }

            if ($thread_data['sticky'] == 1)
            {
                $header_nodes['modmode-sticky-thread-link']->extSetAttribute('href',
                        '?module=threads-admin&board_id=' . $domain->id() . '&action=unsticky&content-id=' .
                        $thread_content_id->getIDString() . '&modmode=true');
                $header_nodes['modmode-sticky-thread-link']->setContent(_gettext('Unsticky Thread'));
            }
            else
            {
                $header_nodes['modmode-sticky-thread-link']->extSetAttribute('href',
                        '?module=threads-admin&board_id=' . $domain->id() . '&action=sticky&content-id=' .
                        $thread_content_id->getIDString() . '&modmode=true');
            }
        }

        $header_nodes['modmode-ban-link']->extSetAttribute('href',
                '?module=bans&board_id=' . $domain->id() . '&action=new&ban_type=POST&content-id=' .
                $temp_content_id->getIDString() . '&ban_ip=' . rawurlencode($ip) . '&modmode=true');

        $header_nodes['modmode-delete-link']->extSetAttribute('href',
                '?module=threads-admin&board_id=' . $domain->id() . '&action=delete&content-id=' .
                $temp_content_id->getIDString() . '&modmode=true');

        $header_nodes['modmode-ban-delete-link']->extSetAttribute('href',
                '?module=threads-admin&board_id=' . $domain->id() . '&action=ban-delete&content-id=' .
                $temp_content_id->getIDString() . '&ban_type=POST&ban_ip=' . rawurlencode($ip) . '&modmode=true');
    }
    else
    {
        $header_nodes['modmode-header']->remove();
    }

    $new_post_dom->getElementById('thread-post-anchor')->changeId(
            't' . $post_content_id->thread_id . 'p' . $post_content_id->post_id);
    $rev_post_id = $post_data['post_number'] . '_' . $post_data['parent_thread'];

    $header_nodes['post-select']->extSetAttribute('name', $post_content_id->getIDString());

    if ($response)
    {
        $post_container->extSetAttribute('class', 'reply-post');

        $indents_element->setContent($domain->setting('indent_marker'));
        $indents_element->removeAttribute('id');
        $header_nodes['post-select']->extSetAttribute('class', 'reply-post-select');
        $header_nodes['thread-select']->remove();
    }
    else
    {
        $indents_element->remove();
        $header_nodes['thread-select']->extSetAttribute('name', $thread_content_id->getIDString());
    }

    $header_nodes['subject']->extSetAttribute('class', $post_type_class . 'subject');
    $header_nodes['subject']->setContent($post_data['subject']);

    $tripcode = (!empty($post_data['tripcode'])) ? $domain->setting('tripkey_marker') . $post_data['tripcode'] : '';
    $secure_tripcode = (!empty($post_data['secure_tripcode'])) ? $domain->setting('tripkey_marker') .
            $domain->setting('tripkey_marker') . $post_data['secure_tripcode'] : '';
    $capcode_text = ($post_data['mod_post_id']) ? $authorization->getRole($post_data['mod_post_id'])->auth_data['capcode_text'] : '';
    $trip_line = $tripcode . $secure_tripcode;

    if (!nel_true_empty($post_data['email']))
    {
        $header_nodes['poster-mailto']->extSetAttribute('class', $post_type_class . 'mailto');
        $header_nodes['poster-mailto']->modifyAttribute('href', $post_data['email'], 'after');
        $header_nodes['poster-mailto']->setContent($post_data['poster_name']);
    }
    else
    {
        $header_nodes['poster-mailto']->remove();
        $header_nodes['poster-name']->extSetAttribute('class', $post_type_class . 'poster-name');
        $header_nodes['poster-name']->setContent($post_data['poster_name']);
    }

    $header_nodes['trip-line']->extSetAttribute('class', $post_type_class . 'trip-line');
    $header_nodes['trip-line']->setContent($trip_line);
    $header_nodes['capcode']->innerHTML = $capcode_text;

    $post_time = date($domain->setting('date_format'), $gen_data['post']['post_time']);
    $header_nodes['post-time']->setContent($post_time);
    $header_nodes['post-time']->extSetAttribute('class', $post_type_class . 'post-time');
    $header_nodes['post-link']->extSetAttribute('class', $post_type_class . 'post-link');
    $header_nodes['post-number-link']->setContent($post_data['post_number']);
    $header_nodes['post-number-link']->extSetAttribute('class', $post_type_class . 'post-number-link');
    $header_nodes['post-number-link']->extSetAttribute('href',
            $thread_page_web_path . '#t' . $post_content_id->thread_id . 'p' . $post_content_id->post_id, 'none');
    $header_nodes['post-link-post']->extSetAttribute('data-content-id', $post_content_id->getIDString());

    if (!$gen_data['index_rendering'] || $response)
    {
        $header_nodes['reply-to-link']->parentNode->remove();
    }
    else
    {
        if ($session->inModmode($domain->id()) && !$domain->renderActive())
        {
            $header_nodes['reply-to-link']->extSetAttribute('href',
                    PHP_SELF . '?module=render&action=view-thread&content-id=' . $thread_content_id->getIDString() .
                    '&section=' . $thread_content_id->thread_id . '&board_id=' . $domain->id() . '&modmode=true');
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
        $header_nodes['expand-thread']->extSetAttribute('data-content-id', $thread_content_id->getIDString());
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
    $post_files_container = $new_post_dom->getElementById('files-nci_0_0_0');

    if ($post_data['has_file'] == 1)
    {
        $filecount = count($gen_data['files']);
        $multiple_class = '';

        if ($filecount > 1)
        {
            $multiple_class = 'multiple-';
            $multiple_files = true;
        }

        $post_files_container->changeId('files-' . $post_content_id->getIDString());
        $post_files_container->extSetAttribute('class', $post_type_class . $multiple_class . 'files-container');

        foreach ($gen_data['files'] as $file)
        {
            $file_content_id = new \Nelliel\ContentID();
            $file_content_id->thread_id = $post_data['parent_thread'];
            $file_content_id->post_id = $post_data['post_number'];
            $file_content_id->order_id = $file['content_order'];
            $full_filename = $file['filename'] . '.' . $file['extension'];
            $file_id = $post_data['parent_thread'] . '_' . $post_data['post_number'] . '_' . $file['content_order'];
            $temp_file_dom = $new_post_dom->copyNodeIntoDocument($new_post_dom->getElementById('fileinfo-nci_0_0_0'),
                    true);

            $temp_file_node = $temp_file_dom->getElementById('fileinfo-nci_0_0_0');
            $temp_file_node->changeId('fileinfo-' . $file_content_id->getIDString());
            $temp_file_node->extSetAttribute('class', $post_type_class . $multiple_class . 'fileinfo');

            $file_nodes = $temp_file_node->getElementsByAttributeName('data-parse-id', true);

            if ($session->inModmode($domain->id()) && !$domain->renderActive())
            {
                $file_nodes['modmode-delete-link']->extSetAttribute('href',
                        '?module=threads-admin&board_id=' . $domain->id() . '&action=delete&content-id=' .
                        $file_content_id->getIDString() . '&modmode=true');
            }
            else
            {
                $file_nodes['modmode-options']->remove();
            }

            $file_nodes['select-file']->extSetAttribute('name', $file_content_id->getIDString());
            $file_nodes['select-file']->extSetAttribute('class', $multiple_class . 'file-select');

            $file['file_location'] = $thread_src_web_path . $post_data['post_number'] . '/' .
                    rawurlencode($full_filename);
            $file['display_filename'] = $file['filename'];

            if (strlen($file['filename']) > 32)
            {
                $file['display_filename'] = substr($file['filename'], 0, 25) . '(...)';
            }

            $file_nodes['file-link']->extSetAttribute('href', $file['file_location'], 'none');
            $file_nodes['file-link']->setContent($file['display_filename'] . '.' . $file['extension']);

            $file['img_dim'] = !empty($file['display_width']) && !empty($file['display_height']);
            $file['filesize'] = round(((int) $file['filesize'] / 1024), 2);
            $filesize_display = ' (' . $file['filesize'] . ' KB)';

            if ($file['img_dim'])
            {
                $filesize_display = $file['display_width'] . ' x ' . $file['display_height'] . $filesize_display;
            }

            $file_nodes['filesize-display']->setContent($filesize_display);
            $file_nodes['show-file-meta']->extSetAttribute('data-content-id', $file_content_id->getIDString());
            $file_nodes['show-file-meta']->changeId('show-file-meta-' . $file_content_id->getIDString());
            $file_nodes['file-meta']->changeId('file-meta-' . $file_content_id->getIDString());

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

            if ($domain->setting('use_thumb'))
            {
                if ($file['format'] == 'webm' || $file['format'] == 'mpeg4')
                {
                    $file_nodes['video-preview']->extSetAttribute('width', $domain->setting('max_width'));
                    $file_nodes['video-preview-source']->extSetAttribute('src', $file['file_location']);
                    $file_nodes['video-preview-source']->extSetAttribute('type', $file['mime']);
                    $file_nodes['file-location']->remove();
                }
                else
                {
                    $full_preview_name = $file['preview_name'] . '.' . $file['preview_extension'];
                    $file['has_preview'] = false;
                    $file_nodes['video-preview']->remove();

                    $file_nodes['file-location']->extSetAttribute('href', $file['file_location'], 'none');

                    if (!empty($file['preview_name']))
                    {
                        $file['has_preview'] = true;
                        $file['preview_location'] = $thread_preview_web_path . $post_data['post_number'] . '/' .
                                rawurlencode($full_preview_name);

                        if ($filecount > 1)
                        {
                            if ($file['preview_width'] > $domain->setting('max_multi_width') ||
                                    $file['preview_height'] > $domain->setting('max_multi_height'))
                            {
                                $ratio = min(($domain->setting('max_multi_height') / $file['preview_height']),
                                        ($domain->setting('max_multi_width') / $file['preview_width']));
                                $file['preview_width'] = intval($ratio * $file['preview_width']);
                                $file['preview_height'] = intval($ratio * $file['preview_height']);
                            }
                        }
                    }
                    else if ($domain->setting('use_file_icon'))
                    {
                        $front_end_data = new \Nelliel\FrontEndData(nel_database());
                        $icon_set = $front_end_data->filetypeIconSet($domain->setting('filetype_icon_set_id'));
                        $icons_web_path = '//' . $base_domain . '/' . WEB_FILES . 'icon_sets/filetype/' .
                                $icon_set['directory'] . '/';
                        $icons_file_path = FILETYPE_ICON_PATH . $icon_set['directory'] . '/';
                        $format_icon = utf8_strtolower($file['format']) . '.png';
                        $type_icon = utf8_strtolower($file['type']) . '.png';

                        if (file_exists($icons_file_path . utf8_strtolower($file['type']) . '/' . $format_icon))
                        {
                            $file['has_preview'] = true;
                            $file['preview_location'] = $icons_web_path . utf8_strtolower($file['type']) . '/' .
                                    $format_icon;
                            $file['preview_width'] = ($domain->setting('max_width') < 128) ? $domain->setting(
                                    'max_width') : '128';
                            $file['preview_height'] = ($domain->setting('max_height') < 128) ? $domain->setting(
                                    'max_height') : '128';
                        }
                        else if (file_exists($icons_file_path . 'generic/' . $type_icon))
                        {
                            $file['has_preview'] = true;
                            $file['preview_location'] = $icons_web_path . '/generic/' . $type_icon;
                            $file['preview_width'] = ($domain->setting('max_width') < 128) ? $domain->setting(
                                    'max_width') : '128';
                            $file['preview_height'] = ($domain->setting('max_height') < 128) ? $domain->setting(
                                    'max_height') : '128';
                        }
                    }

                    if ($file['has_preview'])
                    {
                        $file_nodes['file-preview']->extSetAttribute('src', $file['preview_location'], 'none');
                        $file_nodes['file-preview']->extSetAttribute('width', $file['preview_width']);
                        $file_nodes['file-preview']->extSetAttribute('height', $file['preview_height']);
                        $file_nodes['file-preview']->extSetAttribute('alt', $file['alt_text']);
                        $file_nodes['file-preview']->extSetAttribute('class',
                                $post_type_class . $multiple_class . 'post-preview');
                        $file_nodes['file-preview']->extSetAttribute('data-other-dims',
                                'w' . $file['display_width'] . 'h' . $file['display_height']);
                        $file_nodes['file-preview']->extSetAttribute('data-other-loc', $file['file_location'], 'none');
                    }
                    else
                    {
                        $file_nodes['file-location']->remove();
                    }
                }
            }
            else
            {
                $file_nodes['file-location']->remove();
            }

            $imported = $new_post_dom->importNode($temp_file_node, true);
            $post_files_container->appendChild($imported);
        }

        $new_post_dom->getElementById('fileinfo-nci_0_0_0')->remove();
    }
    else
    {
        $post_files_container->remove();
    }

    $post_contents_element = $new_post_dom->getElementById('post-contents-nci_0_0_0');
    $post_contents_element->changeId('post-contents-' . $post_content_id->getIDString());
    $post_contents_element->extSetAttribute('class', $post_type_class . 'post-contents');

    $contents_nodes = $post_contents_element->getElementsByAttributeName('data-parse-id', true);
    $contents_nodes['post-text']->extSetAttribute('class', $post_type_class . 'post-text');

    if (!nel_true_empty($post_data['mod_comment']))
    {
        $contents_nodes['mod-comment']->setContent('(' . $post_data['mod_comment'] . ')');
    }

    $output_filter->clearWhitespace($post_data['comment']);
    $contents_nodes['post-comment']->extSetAttribute('class', $post_type_class . 'post-comment');

    if (nel_true_empty($post_data['comment']))
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

            $output_filter->postQuoteLink($domain, $append_target, $line);
            $append_target->appendChild($new_post_dom->createElement('br'));
        }
    }

    return $new_post_element;
}

function nel_render_thread_form_bottom($domain, $dom)
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $session = new \Nelliel\Session($authorization);
    $footer_form_element = $dom->getElementById('footer-form');
    $form_td_list = $footer_form_element->doXPathQuery(".//input");
    $dom->getElementById('board_id_field_footer')->extSetAttribute('value', $domain->id());
    $dom->getElementById('form-content-action')->appendChild($footer_form_element);
}
