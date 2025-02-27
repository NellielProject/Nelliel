<?php
declare(strict_types = 1);

namespace Nelliel\NewPost;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\FileTypes;
use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Content\Post;
use Nelliel\Content\Upload;
use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use Nelliel\Filters\Filters;
use PDO;
use SplFileInfo;

class Uploads
{
    private Domain $domain;
    private NellielPDO $database;
    private $embeds = array();
    private $files = array();
    private $processed_uploads = array();
    private $authorization;
    private $session;
    private $total = 0;
    private $fullnames = array();

    function __construct(Domain $domain, Authorization $authorization, Session $session)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->embeds = $_POST['embed_urls'] ?? array();
        $this->files = $_FILES;
        $this->authorization = $authorization;
        $this->session = $session;
    }

    public function process(Post $post): array
    {
        $this->checkCount($post);
        $this->embeds($post);

        if (!empty($this->processed_uploads) && $this->domain->setting('embed_replaces_file')) {
            return $this->processed_uploads;
        }

        $this->files($post);
        return $this->processed_uploads;
    }

    private function files(Post $post)
    {
        if (!isset($this->files['upload_files']['name'])) {
            return;
        }

        $file_count = count($this->files['upload_files']['name']);
        $data_handler = new PostData($this->domain, $this->authorization, $this->session);
        $total_files = 0;
        $total_filesize = 0;

        for ($i = 0; $i < $file_count; $i ++) {
            $file_original_name = $this->files['upload_files']['name'][$i];
            $tmp_name = $this->files['upload_files']['tmp_name'][$i];
            $file_upload_error = $this->files['upload_files']['error'][$i];
            $filesize = $this->files['upload_files']['size'][$i];

            if (nel_true_empty($file_original_name) || !is_uploaded_file($tmp_name)) {
                continue;
            }

            if (!$this->domain->setting('enable_uploads')) {
                nel_derp(56, __('Uploads are not enabled.'));
            }

            $this->checkForErrors($file_upload_error);

            if ($filesize > $this->domain->setting('max_filesize')) {
                nel_derp(12, _gettext('File is too big.'));
            }

            $total_filesize += $filesize;

            if ($total_filesize > $this->domain->setting('max_filesize_all_files')) {
                nel_derp(36, _gettext('Total size of files is too big.'));
            }

            $upload = new Upload(new ContentID(), $this->domain);
            $upload->changeData('filesize', $filesize);
            $upload->changeData('tmp_name', $tmp_name);
            $upload->changeData('location', $tmp_name);
            $upload->changeData('original_filename', $file_original_name);
            $this->checkHashes($upload);
            $this->checkFileDuplicates($post, $upload);
            $this->deduplicate($upload);
            $this->setFilenameAndExtension($upload, $post);
            $this->checkFiletype($upload);

            // We re-add the extension to help with processing
            $old_location = $upload->getData('location');
            $upload->changeData('location', $upload->getData('location') . '.' . $upload->getData('extension'));

            // Not sure what would cause this to fail but best to stop if it does
            if (!move_uploaded_file($old_location, $upload->getData('location'))) {
                nel_utilities()->fileHandler()->eraserGun($old_location);
                nel_derp(52, sprintf(__('The file %s is not a valid upload.'), $upload->getData('original_filename')));
            }

            // Store this temporarily in case we need it for later processing
            $temp_exif = @exif_read_data($upload->getData('location'));
            $upload->changeData('temp_exif', @exif_read_data($upload->getData('location'), '', true));

            if ($this->domain->setting('store_exif_data') && is_array($temp_exif)) {
                // EXIF read picks up the temporary filename so we correct it here
                if (isset($temp_exif['FILE'])) {
                    $temp_exif['FILE']['FileName'] = $upload->getData('original_filename');
                }

                $exif_data = json_encode($temp_exif);

                if (is_string($exif_data)) {
                    $upload->changeData('exif', $exif_data);
                }
            }

            $this->removeEXIF($upload);

            if ($this->domain->setting('enable_spoilers')) {
                $upload->changeData('spoiler', intval($_POST['form_spoiler'] ?? 0));
            }

            $this->setDimensions($upload);
            $this->processed_uploads[] = $upload;
            $total_files ++;
        }

        $post->changeData('file_count', $total_files);
    }

    private function setFilenameAndExtension(Upload $upload, Post $post): void
    {
        if ($upload->getData('use_existing')) {
            return;
        }

        $file_info = new SplFileInfo($upload->getData('original_filename'));
        $extension = $file_info->getExtension();
        $filename = $file_info->getBasename('.' . $extension);

        switch ($this->domain->setting('preferred_filename')) {
            case 'md5':
                $filename = $upload->getData('md5');
                break;

            case 'sha1':
                $filename = $upload->getData('sha1');
                break;

            case 'sha256':
                $filename = $upload->getData('sha256');
                break;

            case 'sha512':
                $filename = $upload->getData('sha512');
                break;

            case 'original':
                $filename = $this->filterBasename($filename);
                $maxlength = 255 - utf8_strlen($extension) - 1;
                $filename = utf8_substr($filename, 0, $maxlength);
                break;

            case 'timestamp':
            default:
                $filename = $post->getData('post_time') . $post->getData('post_time_milli');
                break;
        }

        if (nel_true_empty($filename)) {
            $filename = $post->getData('post_time') . $post->getData('post_time_milli');
        }

        $fullname = $filename . '.' . $extension;
        $temp_fullname = $fullname;
        $duplicate_suffix = 1;

        while (in_array($temp_fullname, $this->fullnames) || file_exists($post->srcFilePath() . $temp_fullname)) {
            $duplicate_suffix ++;
            $temp_fullname = $filename . '_' . $duplicate_suffix . '.' . $extension;
        }

        if ($duplicate_suffix > 1) {
            $fullname = $temp_fullname;
            $filename = $filename . '_' . $duplicate_suffix;
        }

        array_push($this->fullnames, $fullname);
        $upload->changeData('extension', $extension);
        $upload->changeData('filename', $filename);
        $upload->changeData('fullname', $fullname);
    }

    private function checkForErrors(int $upload_error): void
    {
        if ($upload_error === UPLOAD_ERR_INI_SIZE) {
            nel_derp(13, _gettext('File is bigger than the server allows.'));
        }

        if ($upload_error === UPLOAD_ERR_FORM_SIZE) {
            nel_derp(14, _gettext('File is bigger than submission form allows.'));
        }

        if ($upload_error === UPLOAD_ERR_PARTIAL) {
            nel_derp(15, _gettext('Only part of the file was uploaded.'));
        }

        if ($upload_error === UPLOAD_ERR_NO_FILE) {
            nel_derp(16, _gettext('File size is 0 or Candlejack stole your upl'));
        }

        if ($upload_error === UPLOAD_ERR_NO_TMP_DIR) {
            nel_derp(17, _gettext('Temp directory for uploads is unavailable.'));
        }

        if ($upload_error === UPLOAD_ERR_CANT_WRITE) {
            nel_derp(18, _gettext('Cannot write uploaded files to server for some reason.'));
        }

        if ($upload_error === UPLOAD_ERR_EXTENSION) {
            nel_derp(19, _gettext("A PHP extension interfered with upload.'"));
        }

        if ($upload_error !== UPLOAD_ERR_OK) {
            nel_derp(20, _gettext("The uploaded file just ain't right. Dunno why.'"));
        }
    }

    private function checkHashes(Upload $upload): void
    {
        $file = $upload->getData('location');
        $md5 = md5_file($file);
        $sha1 = sha1_file($file);

        if ($md5 === false || $sha1 === false) {
            return;
        }

        $upload->changeData('md5', $md5);
        $upload->changeData('sha1', $sha1);

        $filters = new Filters($this->database);
        $filters->applyFileFilters($md5, [$this->domain->id(), Domain::GLOBAL]);
        $filters->applyFileFilters($sha1, [$this->domain->id(), Domain::GLOBAL]);

        if ($this->domain->setting('generate_file_sha256')) {
            $sha256 = hash_file('sha256', $file);

            if ($sha256 !== false) {
                $upload->changeData('sha256', $sha256);
                $filters->applyFileFilters($sha256, [$this->domain->id(), Domain::GLOBAL]);
            }
        }

        if ($this->domain->setting('generate_file_sha512')) {
            $sha512 = hash_file('sha512', $file);

            if ($sha512 !== false) {
                $upload->changeData('sha512', $sha512);
                $filters->applyFileFilters($sha512, [$this->domain->id(), Domain::GLOBAL]);
            }
        }
    }

    private function checkFileDuplicates(Post $post, Upload $upload): void
    {
        $sha256 = ($this->domain->setting('generate_file_sha256')) ? ' OR "sha256" = :sha256' : '';
        $sha512 = ($this->domain->setting('generate_file_sha512')) ? ' OR "sha512" = :sha512' : '';

        if ($this->domain->setting('check_board_file_duplicates')) {
            $query = 'SELECT "parent_thread" FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE ("md5" = :md5 OR "sha1" = :sha1' . $sha256 . $sha512 . ') AND "shadow" = 0';
            $prepared = $this->database->prepare($query);
            $prepared->bindValue(':md5', $upload->getData('md5'), PDO::PARAM_STR);
            $prepared->bindValue(':sha1', $upload->getData('sha1'), PDO::PARAM_STR);
        } else if ($post->getData('op') && $this->domain->setting('check_op_file_duplicates')) {
            $query = 'SELECT "parent_thread" FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "parent_thread" = "post_ref" AND ("md5" = :md5 OR "sha1" = :sha1' . $sha256 . $sha512 .
                ') AND "shadow" = 0';
            $prepared = $this->database->prepare($query);
            $prepared->bindValue(':md5', $upload->getData('md5'), PDO::PARAM_STR);
            $prepared->bindValue(':sha1', $upload->getData('sha1'), PDO::PARAM_STR);
        } else if (!$post->getData('op') && $this->domain->setting('check_thread_file_duplicates')) {
            $query = 'SELECT "parent_thread" FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "parent_thread" = :parent_thread AND ("md5" = :md5 OR "sha1" = :sha1' . $sha256 . $sha512 . ')';
            $prepared = $this->database->prepare($query);
            $prepared->bindValue(':parent_thread', $post->contentID()->threadID(), PDO::PARAM_INT);
            $prepared->bindValue(':md5', $upload->getData('md5'), PDO::PARAM_STR);
            $prepared->bindValue(':sha1', $upload->getData('sha1'), PDO::PARAM_STR);
        } else {
            return;
        }

        if ($sha256 != '') {
            $prepared->bindValue(':sha256', $upload->getData('sha256'), PDO::PARAM_STR);
        }

        if ($sha512 != '') {
            $prepared->bindValue(':sha512', $upload->getData('sha512'), PDO::PARAM_STR);
        }

        $parent_threads = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);

        if (empty($parent_threads)) {
            return;
        }

        $duplicate_found = false;

        if ($this->domain->setting('only_active_file_duplicates')) {
            foreach ($parent_threads as $thread_id) {
                $prepared = $this->database->prepare(
                    'SELECT 1 FROM "' . $this->domain->reference('threads_table') .
                    '" WHERE "thread_id" = :thread_id AND "old" = 0');
                $prepared->bindValue(':thread_id', $thread_id, PDO::PARAM_INT);

                if ($this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN)) {
                    $duplicate_found = true;
                    break;
                }
            }
        } else {
            $duplicate_found = true;
        }

        if ($duplicate_found) {
            nel_derp(25, _gettext('Duplicate file detected.'));
        }
    }

    private function checkFiletype(Upload $upload): void
    {
        $extension = $upload->getData('extension');
        $filetypes = new FileTypes($this->domain->database());
        $file_format = $filetypes->getFileFormat($extension, $upload->getData('location'));

        if (empty($file_format)) {
            nel_derp(21, _gettext('Unrecognized file type.'));
        }

        $format_data = $filetypes->formatData($file_format);

        if (!$filetypes->formatIsEnabled($this->domain, $file_format)) {
            nel_derp(22, _gettext('File type is not allowed.'));
        }

        if (!$filetypes->formatHasExtension($extension, $file_format)) {
            nel_derp(23, _gettext('Detected file format does not match extension. Possible Hax.'));
        }

        $category_max_size = intval(
            $filetypes->categorySetting($upload->domain(), $format_data['category'], 'max_size'));

        if ($category_max_size > 0 && $upload->getData('filesize') > $category_max_size) {
            nel_derp(58, _gettext('File is larger than allowed for that type.'));
        }

        $upload->changeData('category', $format_data['category']);
        $upload->changeData('format', $format_data['format']);
        $upload->changeData('mime', $filetypes->getFormatMime($file_format));
    }

    private function embeds(Post $post)
    {
        $total_embeds = 0;

        foreach ($this->embeds as $embed_url) {
            $embed_url = trim($embed_url);

            if (empty($embed_url)) {
                continue;
            }

            $this->checkEmbedDuplicates($post, $embed_url);
            $embed = new Upload(new ContentID(), $this->domain);
            $embed->parseEmbedURL($embed_url, true);
            $embed->changeData('category', 'embed');
            $embed->changeData('format', 'embed');
            $embed->changeData('embed_url', $embed_url);
            $this->processed_uploads[] = $embed;
            $total_embeds ++;
        }

        $post->changeData('embed_count', $total_embeds);
    }

    private function checkEmbedDuplicates(Post $post, string $embed_url): void
    {
        if ($this->domain->setting('check_board_embed_duplicates')) {
            $prepared = $this->database->prepare(
                'SELECT "parent_thread" FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "embed_url" = :embed_url');
            $prepared->bindValue(':embed_url', $embed_url, PDO::PARAM_STR);
        } else if ($post->getData('op') && $this->domain->setting('check_op_embed_duplicates')) {
            $prepared = $this->database->prepare(
                'SELECT "parent_thread" FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "parent_thread" = "post_ref" AND "embed_url" = :embed_url');
            $prepared->bindValue(':embed_url', $embed_url, PDO::PARAM_STR);
        } else if (!$post->getData('op') && $this->domain->setting('check_thread_embed_duplicates')) {
            $prepared = $this->database->prepare(
                'SELECT "parent_thread" FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "parent_thread" = :parent_thread AND "embed_url" = :embed_url');
            $prepared->bindValue(':parent_thread', $post->getData('parent_thread'), PDO::PARAM_INT);
            $prepared->bindValue(':embed_url', $embed_url, PDO::PARAM_STR);
        } else {
            return;
        }

        $parent_threads = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);

        if (empty($parent_threads)) {
            return;
        }

        $duplicate_found = false;

        if ($this->domain->setting('only_active_file_duplicates')) {
            foreach ($parent_threads as $thread_id) {
                $prepared = $this->database->prepare(
                    'SELECT 1 FROM "' . $this->domain->reference('threads_table') .
                    '" WHERE "thread_id" = :thread_id AND "old" = 0');
                $prepared->bindValue(':thread_id', $thread_id, PDO::PARAM_INT);

                if ($this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN)) {
                    $duplicate_found = true;
                    break;
                }
            }
        } else {
            $duplicate_found = true;
        }

        if ($duplicate_found) {
            nel_derp(35, _gettext('Duplicate embed detected.'));
        }
    }

    private function checkCount(Post $post): void
    {
        $embeds_count = 0;
        $files_count = 0;
        $parent_thread = $post->getParent();
        $parent_thread->loadFromDatabase();

        if ($post->getData('op')) {
            $allow_files = $this->domain->setting('allow_op_files');
            $require_file = $this->domain->setting('require_op_file');
            $allow_embeds = $this->domain->setting('allow_op_embeds');
            $require_embed = $this->domain->setting('require_op_embed');
            $max_files = $this->domain->setting('max_op_files');
            $max_embeds = $this->domain->setting('max_op_embeds');
            $max_total_uploads = $this->domain->setting('max_op_total_uploads');
        } else {
            $allow_files = $this->domain->setting('allow_reply_files');
            $require_file = $this->domain->setting('require_reply_file');
            $allow_embeds = $this->domain->setting('allow_reply_embeds');
            $require_embed = $this->domain->setting('require_reply_embed');
            $max_files = $this->domain->setting('max_reply_files');
            $max_embeds = $this->domain->setting('max_reply_embeds');
            $max_total_uploads = $this->domain->setting('max_reply_total_uploads');
        }

        foreach ($this->embeds as $embed) {
            $embeds_count += (nel_true_empty($embed)) ? 0 : 1;
        }

        if (isset($this->files['upload_files']['name'])) {
            $entry_count = count($this->files['upload_files']['name']);

            for ($i = 0; $i < $entry_count; $i ++) {
                $files_count += (nel_true_empty($this->files['upload_files']['name'][$i])) ? 0 : 1;
            }
        }

        $total_uploads = ($this->domain->setting('embed_replaces_file') && $embeds_count > 0) ? $embeds_count : $embeds_count +
            $files_count;

        if ($total_uploads > 0 && !$allow_files && !$allow_embeds) {
            nel_derp(26, _gettext('This post cannot have files or embedded content.'));
        }

        if ($total_uploads > $max_total_uploads) {
            nel_derp(27,
                sprintf(
                    _gettext(
                        'You have too many uploads in your post. Received %d embeds and %d files for a total of %d uploads. Maximum is %d.'),
                    $embeds_count, $files_count, $total_uploads, $max_total_uploads));
        }

        if ($files_count === 0 && $total_uploads === 0 && $require_file) {
            nel_derp(28, _gettext('At least one file is required.'));
        }

        if ($files_count > 0 && !$allow_files && ($embeds_count === 0 || !$this->domain->setting('embed_replaces_file'))) {
            nel_derp(29, _gettext('Files are not allowed in this post.'));
        }

        if ($files_count > $max_files) {
            nel_derp(30,
                sprintf(_gettext('You have too many files in your post. Received %d files but the maximum is %d.'),
                    $files_count, $max_files));
        }

        if ($embeds_count === 0 && $require_embed) {
            nel_derp(31, _gettext('Embedded content is required.'));
        }

        if ($embeds_count > 0 && !$allow_embeds) {
            nel_derp(32, _gettext('Embedded content is not allowed in this post.'));
        }

        if ($embeds_count > $max_embeds) {
            nel_derp(33,
                sprintf(_gettext('You have too many embeds in your post. Received %d embeds but the maximum is %d.'),
                    $embeds_count, $max_embeds));
        }

        if ($this->domain->setting('limit_thread_uploads') &&
            $parent_thread->getData('total_uploads') >= $this->domain->setting('max_thread_uploads')) {
            nel_derp(34, _gettext('This thread has reached the maximum number of uploads.'));
        }
    }

    private function filterBasename(string $basename): string
    {
        $filtered = $basename;
        $bad_found = true;

        // This is done in a loop to deal with filenames taking advantage of the filters
        while ($bad_found) {
            $before = $filtered;

            // Decode in case bad things are hidden
            // Has the side benefit of fixing some mangled filenames
            $filtered = rawurldecode($filtered);

            $filtered = preg_replace('#[[:cntrl:]]#', '', $filtered); // Filter out the ASCII control characters
            $filtered = preg_replace('#[\p{C}]#u', '', $filtered); // Filter out invisible Unicode characters

            // https://msdn.microsoft.com/en_US/library/aa365247(VS.85).aspx
            $filtered = preg_replace('#[<>:"\/\\|\?\*]#', '_', $filtered); // Reserved characters for Windows
            $filtered = preg_replace('#com[1-9]|lpt[1-9]|con|prn|aux|nul#i', '', $filtered); // Reserved filenames for Windows

            $filtered = preg_replace('#\.#', '_', $filtered); // Simply eliminating periods blocks many traversal and extension attacks

            if ($filtered === '') {
                nel_derp(140, _gettext('Filename was empty or was purged by filter.'));
            }

            if ($before === $filtered) {
                $bad_found = false;
            }
        }

        return $filtered;
    }

    private function setDimensions(Upload $upload)
    {
        $magicks = nel_magick_available();
        $graphics_handler = nel_get_cached_domain(Domain::SITE)->setting('graphics_handler');
        $display_width = 0;
        $display_height = 0;

        $dims = getimagesize($upload->getData('location'));

        if ($dims !== false) {
            $display_width = $dims[0];
            $display_height = $dims[1];
        }

        if ($display_width === 0 || $display_height === 0) {
            if ($graphics_handler === 'ImageMagick') {
                if (in_array('imagemagick', $magicks)) {
                    $results = nel_exec(
                        'identify -format "%wx%h" ' . escapeshellarg($upload->getData('location') . '[0]'));

                    if ($results['result_code'] === 0) {
                        $matches = array();
                        preg_match('/^(\d+)x(\d+)$/', $results['output'][0], $matches);
                        $display_width = intval($matches['1'] ?? 0);
                        $display_height = intval($matches['2'] ?? 0);
                    }
                } else if (in_array('imagick', $magicks)) {
                    $image = new \Imagick($upload->getData('location'));
                    $display_width = $image->getimagewidth();
                    $display_height = $image->getimageheight();
                }
            }

            if ($graphics_handler === 'GraphicsMagick') {
                if (in_array('graphicsmagick', $magicks)) {
                    $results = nel_exec(
                        'gm identify -format "%wx%h" ' . escapeshellarg($upload->getData('location') . '[0]'));

                    if ($results['result_code'] === 0) {
                        $matches = array();
                        preg_match('/^(\d+)x(\d+)$/', $results['output'][0], $matches);
                        $display_width = intval($matches['1'] ?? 0);
                        $display_height = intval($matches['2'] ?? 0);
                    }
                } else if (in_array('gmagick', $magicks)) {
                    $image = new \Gmagick($upload->getData('location'));
                    $display_width = $image->getimagewidth();
                    $display_height = $image->getimageheight();
                }
            }
        }

        if ($display_width > $this->domain->setting('max_image_width') ||
            $display_height > $this->domain->setting('max_image_height')) {
            nel_derp(38,
                sprintf(_gettext('Image dimensions are too large. Maximum is %d x %d pixels.'),
                    $this->domain->setting('max_image_width'), $this->domain->setting('max_image_height')));
        }

        if ($display_width > 0) {
            $upload->changeData('display_width', $display_width);
        }

        if ($display_height > 0) {
            $upload->changeData('display_height', $display_height);
        }
    }

    private function removeEXIF(Upload $upload): void
    {
        if (!$this->domain->setting('strip_exif')) {
            return;
        }

        if ($this->domain->setting('keep_icc')) {
            $exiftool_args = '-all= --icc_profile:all ';
        } else {
            $exiftool_args = '-all= ';
        }

        $results = nel_exec('exiftool ' . $exiftool_args . escapeshellarg($upload->getData('location')));

        if ($results['result_code'] === 0) {
            $this->checkHashes($upload);
        }

        clearstatcache();
        $new_size = filesize($upload->getData('location'));

        if ($new_size !== false) {
            $upload->changeData('filesize', $new_size);
        }
    }

    private function deduplicate(Upload $upload): void
    {
        if (!$this->domain->setting('file_deduplication')) {
            return;
        }

        $query = 'SELECT "filename", "extension" FROM "' . $this->domain->reference('uploads_table') .
            '" WHERE "md5" = :md5 AND "sha1" = :sha1';
        $prepared = $this->database->prepare($query);
        $prepared->bindValue(':md5', $upload->getData('md5'), PDO::PARAM_STR);
        $prepared->bindValue(':sha1', $upload->getData('sha1'), PDO::PARAM_STR);
        $existing = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if (!is_array($existing)) {
            return;
        }

        $fullname = $existing['filename'] . '.' . $existing['extension'];

        if (!file_exists($upload->srcFilePath() . $fullname)) {
            return;
        }

        $upload->changeData('use_existing', true);
        $upload->changeData('extension', $existing['extension']);
        $upload->changeData('filename', $existing['filename']);
        $upload->changeData('fullname', $fullname);
        array_push($this->fullnames, $fullname);
    }
}