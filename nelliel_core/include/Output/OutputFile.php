<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
use Nelliel\Domain;
use PDO;

class OutputFile extends OutputCore
{

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $post_data = $parameters['post_data'] ?? array();
        $file = $parameters['file_data'] ?? $this->getFileFromDatabase($post_data['post_number'], $content_order);
        $web_paths = $parameters['web_paths'] ?? array();
        $post_type_class = $post_data['op'] == 1 ? 'op-' : 'reply-';
        $multiple = $post_data['content_count'] > 1;
        $multiple_class = $multiple ? 'multiple-' : '';
        $dotdot = $parameters['dotdot'] ?? '';
        $json_post = $parameters['json_instances']['post'];
        $json_content = $parameters['json_instances']['content'];
        $json_post->addContentData($json_content->prepareData($file));

        $authorization = new \Nelliel\Auth\Authorization($this->domain->database());
        $session = new \Nelliel\Account\Session();
        $file_content_id = new ContentID();
        $file_content_id->changeThreadID($post_data['parent_thread']);
        $file_content_id->changePostID($post_data['post_number']);
        $file_content_id->changeOrderID($file['content_order']);
        $full_filename = $file['filename'] . '.' . $file['extension'];
        $file_id = $post_data['parent_thread'] . '_' . $post_data['post_number'] . '_' . $file['content_order'];
        $this->render_data['file_info_id'] = 'fileinfo-' . $file_content_id->getIDString();
        $this->render_data['file_content_id'] = $file_content_id->getIDString();
        $this->render_data['file_info_class'] = $post_type_class . $multiple_class . 'fileinfo';
        $this->render_data['file_select_class'] = $multiple_class . 'content-select';
        $this->render_data['file_preview_class'] = $post_type_class . $multiple_class . 'post-preview';

        if ($session->inModmode($this->domain))
        {
            $this->render_data['in_modmode'] = true;
            $this->render_data['delete_url'] = '?module=admin&section=threads&board_id=' . $this->domain->id() .
                    '&action=delete&content-id=' . $file_content_id->getIDString() . '&modmode=true&goback=true';
        }

        $this->render_data['display_filesize'] = ' (' . round(((int) $file['filesize'] / 1024), 2) . ' KB)';

        if (!empty($file['display_width']) && !empty($file['display_height']))
        {
            $this->render_data['display_image_dimensions'] = $file['display_width'] . ' x ' . $file['display_height'];
        }

        $this->render_data['file_url'] = $web_paths['thread_src'] . $post_data['post_number'] . '/' .
                rawurlencode($full_filename);
        $meta = json_decode($file['meta'], true);
        $display_filename = $file['filename'];
        $display_extension = $file['extension'];

        if ($this->domain->setting('display_original_name') && !empty($meta['original_filename']))
        {
            $display_filename = $meta['original_filename'] ?? $file['extension'];
            $display_extension = $meta['original_extension'] ?? $file['filename'];
        }

        if (strlen($display_filename) > 32)
        {
            $display_filename = substr($display_filename, 0, 25) . '(...)';
        }

        $this->render_data['display_filename'] = $display_filename . '.' . $display_extension;
        $this->render_data['show_file_meta_id'] = 'show-file-meta-' . $file_content_id->getIDString();
        $this->render_data['file_meta_id'] = 'file-meta-' . $file_content_id->getIDString();

        if (!empty($file['md5']))
        {
            $md5_data['metadata_class'] = 'file-hash';
            $md5_data['metadata'] = 'MD5: ' . bin2hex($file['md5']);
            $this->render_data['file_metadata'][] = $md5_data;
        }

        if (!empty($file['sha1']))
        {
            $sha1_data['metadata_class'] = 'file-hash';
            $sha1_data['metadata'] = 'SHA1: ' . bin2hex($file['sha1']);
            $this->render_data['file_metadata'][] = $sha1_data;
        }

        if (!empty($file['sha256']))
        {
            $sha256_data['metadata_class'] = 'file-hash';
            $sha256_data['metadata'] = 'SHA256: ' . bin2hex($file['sha256']);
            $this->render_data['file_metadata'][] = $sha256_data;
        }

        if (!empty($file['sha512']))
        {
            $sha512_data['metadata_class'] = 'file-hash';
            $sha512_data['metadata'] = 'SHA512: ' . bin2hex($file['sha512']);
            $this->render_data['file_metadata'][] = $sha512_data;
        }

        if ($this->domain->setting('use_preview'))
        {
            $this->render_data['image_preview'] = true;
            $max_width = ($multiple) ? $this->domain->setting('max_multi_width') : $this->domain->setting('max_width');
            $max_height = ($multiple) ? $this->domain->setting('max_multi_height') : $this->domain->setting(
                    'max_height');

            if ($file['format'] == 'webm' || $file['format'] == 'mpeg4')
            {
                $this->render_data['video_preview'] = true;
                $this->render_data['preview_width'] = $max_width;
                $this->render_data['mime_type'] = $file['mime'];
                $this->render_data['video_url'] = $file['file_location'];
            }
            else
            {
                if (!empty($file['preview_name']) && $file['preview_width'] > 0 && $file['preview_height'] > 0)
                {
                    $full_preview_name = $file['preview_name'] . '.' . $file['preview_extension'];
                    $this->render_data['preview_url'] = $web_paths['thread_preview'] . $post_data['post_number'] . '/' .
                            rawurlencode($full_preview_name);

                    if ($file['preview_width'] > $max_width || $file['preview_height'] > $max_height)
                    {
                        $ratio = min(($max_height / $file['preview_height']), ($max_width / $file['preview_width']));
                        $this->render_data['preview_width'] = intval($ratio * $file['preview_width']);
                        $this->render_data['preview_height'] = intval($ratio * $file['preview_height']);
                    }
                    else
                    {
                        $this->render_data['preview_width'] = $file['preview_width'];
                        $this->render_data['preview_height'] = $file['preview_height'];
                    }
                }
                else if ($this->domain->setting('use_file_icon'))
                {
                    $front_end_data = new \Nelliel\FrontEndData($this->domain->database());
                    $icon_set = $front_end_data->iconSet($this->domain->setting('icon_set_id'));
                    $web_path = $front_end_data->iconSetIsCore($this->domain->setting('icon_set_id')) ? NEL_CORE_ICON_SETS_WEB_PATH : NEL_CUSTOM_ICON_SETS_WEB_PATH;
                    $icons_web_path = '//' . $web_paths['base_domain'] . $web_path . $icon_set['directory'] .
                            '/';
                    $file_path = $front_end_data->iconSetIsCore($this->domain->setting('icon_set_id')) ? NEL_CORE_ICON_SETS_FILES_PATH : NEL_CUSTOM_ICON_SETS_FILES_PATH;
                    $icons_file_path = $file_path . $icon_set['directory'] . '/';
                    $format_icon = utf8_strtolower($file['format']) . '.png';
                    $type_icon = utf8_strtolower($file['type']) . '.png';

                    $this->render_data['preview_width'] = ($max_width < 128) ? $max_width : '128';
                    $this->render_data['preview_height'] = ($max_height < 128) ? $max_height : '128';

                    if (file_exists($icons_file_path . 'filetype/' . utf8_strtolower($file['type']) . '/' . $format_icon))
                    {
                        $this->render_data['preview_url'] = $icons_web_path . 'filetype/' . utf8_strtolower($file['type']) . '/' .
                                $format_icon;
                    }
                    else if (file_exists($icons_file_path . 'filetype/generic/' . $type_icon))
                    {
                        $this->render_data['preview_url'] = $icons_web_path . 'filetype/generic/' . $type_icon;
                    }
                    else
                    {
                        $this->render_data['image_preview'] = false;
                    }
                }
                else
                {
                    $this->render_data['image_preview'] = false;
                }

                if ($file['spoiler'])
                {
                    $this->render_data['preview_url'] = '//' . $web_paths['base_domain'] . NEL_CORE_IMAGES_WEB_PATH .
                            'covers/spoiler_alert.png';
                    $this->render_data['preview_width'] = ($max_width < 128) ? $max_width : '128';
                    $this->render_data['preview_height'] = ($max_height < 128) ? $max_height : '128';
                }

                if ($file['deleted'])
                {
                    $this->render_data['preview_url'] = '//' . $web_paths['base_domain'] . NEL_CORE_IMAGES_WEB_PATH .
                    'covers/deleted_file.png';
                    $this->render_data['preview_width'] = ($max_width < 128) ? $max_width : '128';
                    $this->render_data['preview_height'] = ($max_height < 128) ? $max_height : '128';
                }

                $this->render_data['other_dims'] = 'w' . $file['display_width'] . 'h' . $file['display_height'];
                $this->render_data['other_loc'] = $this->render_data['file_url'];
            }
        }

        $output = $this->output('thread/file_info', $data_only, true);
        return $output;
    }

    public function getFileFromDatabase($post_id)
    {
        $query = 'SELECT * FROM "' . $domain->reference('content_table') .
                '" WHERE "post_ref" = ? ORDER BY "content_order" ASC';
        $prepared = $this->database->prepare($query);
        $file_data = $this->database->executePreparedFetchAll($prepared, [$post_id], PDO::FETCH_ASSOC);

        if (empty($file_data))
        {
            $file_data = array();
        }

        return $file_data;
    }
}