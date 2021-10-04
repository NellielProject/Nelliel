<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Upload;
use Nelliel\Domains\Domain;

class OutputFile extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(Upload $file, array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $post = $file->getParent();
        $catalog = $parameters['catalog'] ?? false;
        $multiple = $post->data('file_count') > 1;
        $this->render_data['is_file'] = true;
        $full_filename = $file->data('filename') . '.' . $file->data('extension');
        $this->render_data['file_container_id'] = 'file-container-' . $file->contentID()->getIDString();
        $this->render_data['file_content_id'] = $file->contentID()->getIDString();
        $this->render_data['in_modmode'] = $this->session->inModmode($this->domain) && !$this->write_mode;

        if ($this->session->inModmode($this->domain))
        {
            $this->render_data['delete_url'] = '?module=admin&section=threads&board-id=' . $this->domain->id() .
                    '&actions=delete&content-id=' . $file->contentID()->getIDString() . '&modmode=true&goback=true';
        }

        $this->render_data['display_filesize'] = ' (' . round(((int) $file->data('filesize') / 1024), 2) . ' KB)';

        if (!empty($file->data('display_width')) && !empty($file->data('display_height')))
        {
            $this->render_data['display_image_dimensions'] = $file->data('display_width') . ' x ' .
                    $file->data('display_height');
        }

        $this->render_data['file_url'] = $this->domain->reference('src_web_path') . $post->data('parent_thread') . '/' .
                $post->data('post_number') . '/' . rawurlencode($full_filename);

        if ($this->domain->setting('display_original_name') && !nel_true_empty($file->data('original_filename')))
        {
            $display_filename = $file->data('original_filename');
        }
        else
        {
            $display_filename = $file->data('filename') . '.' . $file->data('extension');
        }

        if (utf8_strlen($display_filename) > $this->domain->setting('filename_display_length'))
        {
            $display_filename = substr($display_filename, 0, $this->domain->setting('filename_display_length')) . '...';
        }

        $this->render_data['display_filename'] = $display_filename;

        if (!empty($file->data('md5')))
        {
            $md5_data['metadata'] = 'MD5: ' . $file->data('md5');
            $this->render_data['file_metadata'][] = $md5_data;
        }

        if (!empty($file->data('sha1')))
        {
            $sha1_data['metadata'] = 'SHA1: ' . $file->data('sha1');
            $this->render_data['file_metadata'][] = $sha1_data;
        }

        if (!empty($file->data('sha256')))
        {
            $sha256_data['metadata'] = 'SHA256: ' . $file->data('sha256');
            $this->render_data['file_metadata'][] = $sha256_data;
        }

        if (!empty($file->data('sha512')))
        {
            $sha512_data['metadata'] = 'SHA512: ' . $file->data('sha512');
            $this->render_data['file_metadata'][] = $sha512_data;
        }

        if ($catalog)
        {
            $max_width = $this->domain->setting('max_catalog_display_width');
            $max_height = $this->domain->setting('max_catalog_display_height');
        }
        else
        {
            $max_width = ($multiple) ? $this->domain->setting('max_multi_display_width') : $this->domain->setting(
                    'max_preview_display_width');
            $max_height = ($multiple) ? $this->domain->setting('max_multi_display_height') : $this->domain->setting(
                    'max_preview_display_height');
        }

        $this->render_data['max_width'] = $max_width;
        $this->render_data['max_height'] = $max_height;
        $preview_size_not_zero = $file->data('preview_width') > 0 && $file->data('preview_height') > 0;
        $has_static_preview = !nel_true_empty($file->data('static_preview_name')) && $preview_size_not_zero;
        $has_animated_preview = !nel_true_empty($file->data('animated_preview_name')) && $preview_size_not_zero;
        $preview_type = null;

        if ($file->data('deleted'))
        {
            $this->render_data['deleted_url'] = NEL_ASSETS_WEB_PATH . $this->domain->setting('image_deleted_file');
            $preview_type = 'image';
        }

        if (is_null($preview_type) && $file->data('spoiler'))
        {
            $this->render_data['preview_url'] = NEL_ASSETS_WEB_PATH . $this->domain->setting('image_spoiler_cover');
            $this->render_data['preview_width'] = ($max_width < 128) ? $max_width : '128';
            $this->render_data['preview_height'] = ($max_height < 128) ? $max_height : '128';
            $preview_type = 'image';
        }

        if ($file->data('category') === 'video')
        {
            $this->render_data['alt_tag'] = "video";

            if (is_null($preview_type) &&
                    (!$this->domain->setting('display_video_preview') || (!$has_static_preview && !$has_animated_preview)))
            {
                if ($this->domain->setting('embed_video_files') &&
                        ($file->data('format') == 'webm' || $file->data('format') == 'mpeg4'))
                {
                    $this->render_data['video_width'] = $max_width;
                    $this->render_data['video_height'] = $max_height;
                    $this->render_data['mime_type'] = $file->data('mime');
                    $this->render_data['video_url'] = $this->render_data['file_url'];
                    $this->render_data['video_preview'] = true;
                    $preview_type = 'video';
                }
            }
        }

        if (is_null($preview_type))
        {
            if ($file->data('category') === 'graphics')
            {
                $this->render_data['alt_tag'] = "img";
            }

            if ($this->domain->setting('display_static_preview') && $has_static_preview)
            {
                $preview_name = $file->data('static_preview_name');
            }

            if ($this->domain->setting('display_animated_preview') && $has_animated_preview)
            {
                $preview_name = $file->data('animated_preview_name');
            }

            if (!empty($preview_name))
            {
                $this->render_data['preview_url'] = $this->domain->reference('preview_web_path') .
                        $post->data('parent_thread') . '/' . $post->data('post_number') . '/' .
                        rawurlencode($preview_name);

                if ($file->data('preview_width') > $max_width || $file->data('preview_height') > $max_height)
                {
                    $ratio = min(($max_height / $file->data('preview_height')),
                            ($max_width / $file->data('preview_width')));
                    $this->render_data['preview_width'] = intval($ratio * $file->data('preview_width'));
                    $this->render_data['preview_height'] = intval($ratio * $file->data('preview_height'));
                }
                else
                {
                    $this->render_data['preview_width'] = $file->data('preview_width');
                    $this->render_data['preview_height'] = $file->data('preview_height');
                }

                $preview_type = 'image';
            }
            else if ($this->domain->setting('use_file_icon'))
            {
                $icon_set = $this->domain->frontEndData()->getIconSet($this->domain->setting('filetype_icon_set'));
                $type = utf8_strtolower($file->data('category'));
                $format = utf8_strtolower($file->data('format'));
                $web_path = $icon_set->getWebPath('filetype', $format, true);

                if ($web_path === '')
                {
                    $web_path = $icon_set->getWebPath('filetype', 'generic-' . $type, true);

                    if ($web_path === '')
                    {
                        $web_path = $icon_set->getWebPath('filetype', 'generic', true);
                    }
                }

                $this->render_data['preview_width'] = ($max_width < 128) ? $max_width : '128';
                $this->render_data['preview_height'] = ($max_height < 128) ? $max_height : '128';
                $this->render_data['preview_url'] = $web_path;
                $preview_type = 'image';
            }
        }

        if (!is_null($preview_type))
        {
            $this->render_data['other_dims'] = 'w' . $file->data('display_width') . 'h' . $file->data('display_height');
            $this->render_data['other_loc'] = $this->render_data['file_url'];
            $this->render_data['image_preview'] = $preview_type === 'image';
            $this->render_data['video_preview'] = $preview_type === 'video';
        }

        $output = $this->output('thread/file_info', $data_only, true, $this->render_data);
        return $output;
    }
}