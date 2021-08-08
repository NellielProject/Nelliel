<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;

class OutputFile extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $post_data = $parameters['post_data'] ?? array();
        $file = $parameters['file_data'] ?? array();
        $multiple = $post_data['file_count'] > 1;
        $json_post = $parameters['json_instances']['post'];
        $json_upload = $parameters['json_instances']['upload'];
        $json_post->addContentData($json_upload->prepareData($file));
        $file_content_id = new ContentID();
        $file_content_id->changeThreadID($post_data['parent_thread']);
        $file_content_id->changePostID($post_data['post_number']);
        $file_content_id->changeOrderID($file['upload_order']);
        $this->render_data['is_file'] = true;
        $full_filename = $file['filename'] . '.' . $file['extension'];
        $this->render_data['file_container_id'] = 'file-container-' . $file_content_id->getIDString();
        $this->render_data['file_content_id'] = $file_content_id->getIDString();
        $this->render_data['in_modmode'] = $this->session->inModmode($this->domain) && !$this->write_mode;

        if ($this->session->inModmode($this->domain))
        {
            $this->render_data['delete_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                    '&actions=delete&content-id=' . $file_content_id->getIDString() . '&modmode=true&goback=true';
        }

        $this->render_data['display_filesize'] = ' (' . round(((int) $file['filesize'] / 1024), 2) . ' KB)';

        if (!empty($file['display_width']) && !empty($file['display_height']))
        {
            $this->render_data['display_image_dimensions'] = $file['display_width'] . ' x ' . $file['display_height'];
        }

        $this->render_data['file_url'] = $this->domain->reference('src_web_path') . $post_data['parent_thread'] . '/' .
                $post_data['post_number'] . '/' . rawurlencode($full_filename);
        $moar = json_decode($file['moar'], true);
        $display_filename = $file['filename'];
        $display_extension = $file['extension'];

        if ($this->domain->setting('display_original_name') && !empty($moar['original_filename']))
        {
            $display_filename = $moar['original_filename'] ?? $file['extension'];
            $display_extension = $moar['original_extension'] ?? $file['filename'];
        }

        if (utf8_strlen($display_filename) > $this->domain->setting('filename_display_length'))
        {
            $display_filename = substr($display_filename, 0, $this->domain->setting('filename_display_length')) . '...';
        }

        $this->render_data['display_filename'] = $display_filename . '.' . $display_extension;

        if (!empty($file['md5']))
        {
            $md5_data['metadata'] = 'MD5: ' . bin2hex($file['md5']);
            $this->render_data['file_metadata'][] = $md5_data;
        }

        if (!empty($file['sha1']))
        {
            $sha1_data['metadata'] = 'SHA1: ' . bin2hex($file['sha1']);
            $this->render_data['file_metadata'][] = $sha1_data;
        }

        if (!empty($file['sha256']))
        {
            $sha256_data['metadata'] = 'SHA256: ' . bin2hex($file['sha256']);
            $this->render_data['file_metadata'][] = $sha256_data;
        }

        if (!empty($file['sha512']))
        {
            $sha512_data['metadata'] = 'SHA512: ' . bin2hex($file['sha512']);
            $this->render_data['file_metadata'][] = $sha512_data;
        }

        if ($this->domain->setting('generate_preview'))
        {
            $this->render_data['image_preview'] = true;
            $max_width = ($multiple) ? $this->domain->setting('max_multi_display_width') : $this->domain->setting(
                    'max_display_width');
            $max_height = ($multiple) ? $this->domain->setting('max_multi_display_height') : $this->domain->setting(
                    'max_display_height');
            $this->render_data['max_width'] = $max_width;
            $this->render_data['max_height'] = $max_height;

            if ($file['format'] == 'webm' || $file['format'] == 'mpeg4')
            {
                $this->render_data['video_preview'] = true;
                $this->render_data['video_width'] = $max_width;
                $this->render_data['video_height'] = $max_height;
                $this->render_data['mime_type'] = $file['mime'];
                $this->render_data['video_url'] = $this->render_data['file_url'];
            }
            else
            {
                if (!empty($file['preview_name']) && $file['preview_width'] > 0 && $file['preview_height'] > 0)
                {
                    $full_preview_name = $file['preview_name'] . '.' . $file['preview_extension'];
                    $this->render_data['preview_url'] = $this->domain->reference('preview_web_path') .
                            $post_data['parent_thread'] . '/' . $post_data['post_number'] . '/' .
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
                    $icon_set = $this->domain->frontEndData()->getIconSet($this->domain->setting('icon_set_id'));
                    $type = utf8_strtolower($file['type']);
                    $format = utf8_strtolower($file['format']);
                    $web_path = $icon_set->getWebPath('filetype', $format, true);

                    if ($web_path === '')
                    {
                        $web_path = $icon_set->getWebPath('filetype', $type . '-generic', true);

                        if ($web_path === '')
                        {
                            $web_path = $icon_set->getWebPath('filetype', 'generic', true);
                        }
                    }

                    $this->render_data['preview_width'] = ($max_width < 128) ? $max_width : '128';
                    $this->render_data['preview_height'] = ($max_height < 128) ? $max_height : '128';
                    $this->render_data['preview_url'] = $web_path;
                }
                else
                {
                    $this->render_data['image_preview'] = false;
                }

                if ($file['spoiler'])
                {
                    $this->render_data['preview_url'] = NEL_ASSETS_WEB_PATH .
                            $this->domain->setting('image_spoiler_cover');
                    $this->render_data['preview_width'] = ($max_width < 128) ? $max_width : '128';
                    $this->render_data['preview_height'] = ($max_height < 128) ? $max_height : '128';
                }

                if ($file['deleted'])
                {
                    $this->render_data['preview_url'] = NEL_ASSETS_WEB_PATH .
                            $this->domain->setting('image_deleted_file');
                    $this->render_data['preview_width'] = ($max_width < 128) ? $max_width : '128';
                    $this->render_data['preview_height'] = ($max_height < 128) ? $max_height : '128';
                }

                $this->render_data['other_dims'] = 'w' . $file['display_width'] . 'h' . $file['display_height'];
                $this->render_data['other_loc'] = $this->render_data['file_url'];
            }
        }

        $output = $this->output('thread/file_info', $data_only, true, $this->render_data);
        return $output;
    }
}