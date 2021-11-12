<?php
declare(strict_types = 1);

namespace Nelliel\NewPost;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\BansAccess;
use Nelliel\FileTypes;
use Nelliel\Snacks;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Content\Post;
use Nelliel\Content\Upload;
use Nelliel\Domains\Domain;
use PDO;

class Uploads
{
    private $domain;
    private $database;
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
            $file_provided_type = $this->files['upload_files']['type'][$i];
            $tmp_name = $this->files['upload_files']['tmp_name'][$i];
            $file_upload_error = $this->files['upload_files']['error'][$i];
            $filesize = $this->files['upload_files']['size'][$i];

            if (nel_true_empty($file_original_name) || !is_uploaded_file($tmp_name)) {
                continue;
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
            $upload->changeData('original_filename', $file_original_name);
            $this->setFilenameAndExtension($upload, $post);
            $this->checkFiletype($upload, $upload->data('extension'), $tmp_name);
            // We re-add the extension to help with processing
            $upload->changeData('location', $tmp_name . '.' . $upload->data('extension'));
            nel_utilities()->fileHandler()->moveFile($tmp_name, $upload->data('location'), false);
            // Store this temporarily in case we need it for later processing
            $upload->changeData('temp_exif', @exif_read_data($upload->data('location'), '', true));

            if ($this->domain->setting('store_exif_data') && is_array($upload->data('temp_exif'))) {
                $exif_data = json_encode($upload->data('temp_exif'));

                if (is_string($exif_data)) {
                    $upload->changeData('exif', $exif_data);
                }
            }

            $this->removeEXIF($upload);
            $this->checkHashes($upload);
            $this->checkFileDuplicates($post, $upload);

            if ($this->domain->setting('enable_spoilers')) {
                $spoiler = $_POST['form_spoiler'] ?? 0;
                $upload->changeData('spoiler', $data_handler->checkEntry($spoiler, 'integer'));
            }

            $this->setDimensions($upload);
            $this->processed_uploads[] = $upload;
            $total_files ++;
        }

        $post->changeData('file_count', $total_files);
    }

    private function setFilenameAndExtension(Upload $upload, Post $post): void
    {
        $file_info = new \SplFileInfo($upload->data('original_filename'));
        $extension = $file_info->getExtension();
        $filename = $file_info->getBasename('.' . $extension);

        switch ($this->domain->setting('preferred_filename')) {
            case 'md5':
                $filename = $upload->data('md5');
                break;

            case 'sha1':
                $filename = $upload->data('sha1');
                break;

            case 'sha256':
                $filename = $upload->data('sha256');
                break;

            case 'sha512':
                $filename = $upload->data('sha512');
                break;

            case 'original':
                $filename = $this->filterBasename($filename);
                $maxlength = 255 - utf8_strlen($extension) - 1;
                $filename = substr($filename, 0, $maxlength);
                break;

            case 'timestamp':
            default:
                $filename = $post->data('post_time') . $post->data('post_time_milli');
                break;
        }

        if (nel_true_empty($filename)) {
            $filename = $post->data('post_time') . $post->data('post_time_milli');
        }

        $fullname = $filename . '.' . $extension;
        $temp_fullname = $fullname;
        $duplicate_suffix = 1;

        while (in_array($temp_fullname, $this->fullnames)) {
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
        $snacks = new Snacks($this->domain, new BansAccess($this->database));
        $is_banned = false;
        $file = $upload->data('location');
        $md5 = hash_file('md5', $file);
        $upload->changeData('md5', $md5);
        $is_banned = $snacks->fileHashIsBanned($md5, 'md5');

        if (!$is_banned) {
            $sha1 = hash_file('sha1', $file);
            $upload->changeData('sha1', $sha1);
            $is_banned = $snacks->fileHashIsBanned($sha1, 'sha1');
        }

        if (!$is_banned && $this->domain->setting('generate_file_sha256')) {
            $sha256 = hash_file('sha256', $file);
            $upload->changeData('sha256', $sha256);
            $is_banned = $snacks->fileHashIsBanned($sha256, 'sha256');
        }

        if (!$is_banned && $this->domain->setting('generate_file_sha512')) {
            $sha512 = hash_file('sha512', $file);
            $upload->changeData('sha512', $sha512);
            $is_banned = $snacks->fileHashIsBanned($sha512, 'sha512');
        }

        if ($is_banned) {
            nel_derp(24, _gettext('That file is banned.'));
        }
    }

    private function checkFileDuplicates(Post $post, Upload $upload): void
    {
        $sha256 = ($this->domain->setting('generate_file_sha256')) ? ' OR "sha256" = :sha256' : '';
        $sha512 = ($this->domain->setting('generate_file_sha512')) ? ' OR "sha512" = :sha512' : '';

        if ($this->domain->setting('check_board_file_duplicates')) {
            $query = 'SELECT "parent_thread" FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE ("md5" = :md5 OR "sha1" = :sha1' . $sha256 . $sha512 . ')';
            $prepared = $this->database->prepare($query);
            $prepared->bindValue(':md5', $upload->data('md5'), PDO::PARAM_STR);
            $prepared->bindValue(':sha1', $upload->data('sha1'), PDO::PARAM_STR);
        } else if ($post->data('op') && $this->domain->setting('check_op_file_duplicates')) {
            $query = 'SELECT "parent_thread" FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "parent_thread" = "post_ref" AND ("md5" = :md5 OR "sha1" = :sha1' . $sha256 . $sha512 . ')';
            $prepared = $this->database->prepare($query);
            $prepared->bindValue(':md5', $upload->data('md5'), PDO::PARAM_STR);
            $prepared->bindValue(':sha1', $upload->data('sha1'), PDO::PARAM_STR);
        } else if (!$post->data('op') && $this->domain->setting('check_thread_file_duplicates')) {
            $query = 'SELECT "parent_thread" FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "parent_thread" = :parent_thread AND ("md5" = :md5 OR "sha1" = :sha1' . $sha256 . $sha512 . ')';
            $prepared = $this->database->prepare($query);
            $prepared->bindValue(':parent_thread', $post->contentID()
                ->threadID(), PDO::PARAM_INT);
            $prepared->bindValue(':md5', $upload->data('md5'), PDO::PARAM_STR);
            $prepared->bindValue(':sha1', $upload->data('sha1'), PDO::PARAM_STR);
        } else {
            return;
        }

        if ($sha256 != '') {
            $prepared->bindValue(':sha256', $upload->data('sha256'), PDO::PARAM_STR);
        }

        if ($sha512 != '') {
            $prepared->bindValue(':sha512', $upload->data('sha512'), PDO::PARAM_STR);
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

    private function checkFiletype(Upload $upload, string $extension, string $file): void
    {
        $filetypes = new FileTypes($this->domain->database());

        if (!$filetypes->isValidExtension($extension)) {
            nel_derp(21, _gettext('Unrecognized file type.'));
        }

        if (!$filetypes->extensionIsEnabled($this->domain->id(), $extension)) {
            nel_derp(22, _gettext('File type is not allowed.'));
        }

        if (!$filetypes->verifyFile($extension, $file)) {
            nel_derp(23, _gettext('Incorrect file type detected (does not match extension). Possible Hax.'));
        }

        $type_data = $filetypes->extensionData($extension);
        $upload->changeData('category', $type_data['category']);
        $upload->changeData('format', $type_data['format']);
        $upload->changeData('mime', $type_data['mime']);
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
        $active = ($this->domain->setting('only_active_embed_duplicates')) ? ' AND "old" = 0 ' : '';

        if ($this->domain->setting('check_board_embed_duplicates')) {
            $prepared = $this->database->prepare(
                'SELECT 1 FROM "' . $this->domain->reference('uploads_table') . '" WHERE "embed_url" = ?' . $active);
            $prepared->bindValue(1, $embed_url, PDO::PARAM_STR);
            $duplicates_found = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        } else if ($post->data('op') && $this->domain->setting('check_op_embed_duplicates')) {
            $prepared = $this->database->prepare(
                'SELECT 1 FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "parent_thread" = "post_ref" AND "embed_url" = ?' . $active);
            $prepared->bindValue(1, $embed_url, PDO::PARAM_STR);
        } else if (!$post->data('op') && $this->domain->setting('check_thread_embed_duplicates')) {
            $prepared = $this->database->prepare(
                'SELECT 1 FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "parent_thread" = ? AND "embed_url" = ?' . $active);
            $prepared->bindValue(1, $post->data('parent_thread'), PDO::PARAM_INT);
            $prepared->bindValue(2, $embed_url, PDO::PARAM_STR);
        } else {
            return;
        }

        $duplicates_found = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

        if ($duplicates_found) {
            nel_derp(35, _gettext('Duplicate embed detected.'));
        }
    }

    private function checkCount(Post $post): void
    {
        $embeds_count = 0;
        $files_count = 0;
        $response_to = $post->data('response_to');
        $parent_thread = $post->getParent();
        $parent_thread->loadFromDatabase();

        if (!$response_to) {
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
            $parent_thread->data('total_uploads') >= $this->domain->setting('max_thread_uploads')) {
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
        $graphics_handler = nel_site_domain()->setting('graphics_handler');
        $display_width = 0;
        $display_height = 0;

        $dims = getimagesize($upload->data('location'));

        if ($dims !== false) {
            $display_width = $dims[0];
            $display_height = $dims[1];
        }

        if ($display_width === 0 || $display_height === 0) {
            if ($graphics_handler === 'ImageMagick') {
                if (in_array('imagemagick', $magicks)) {
                    $results = nel_exec('identify -format "%wx%h" ' . escapeshellarg($upload->data('location') . '[0]'));

                    if ($results['result_code'] === 0) {
                        $matches = array();
                        preg_match('/^(\d+)x(\d+)$/', $results['output'][0], $matches);
                        $display_width = intval($matches['1'] ?? 0);
                        $display_height = intval($matches['2'] ?? 0);
                    }
                } else if (in_array('imagick', $magicks)) {
                    $image = new \Imagick($upload->data('location'));
                    $display_width = $image->getimagewidth();
                    $display_height = $image->getimageheight();
                }
            }

            if ($graphics_handler === 'GraphicsMagick') {
                if (in_array('graphicsmagick', $magicks)) {
                    $results = nel_exec(
                        'gm identify -format "%wx%h" ' . escapeshellarg($upload->data('location') . '[0]'));

                    if ($results['result_code'] === 0) {
                        $matches = array();
                        preg_match('/^(\d+)x(\d+)$/', $results['output'][0], $matches);
                        $display_width = intval($matches['1'] ?? 0);
                        $display_height = intval($matches['2'] ?? 0);
                    }
                } else if (in_array('gmagick', $magicks)) {
                    $image = new \Gmagick($upload->data('location'));
                    $display_width = $image->getimagewidth();
                    $display_height = $image->getimageheight();
                }
            }
        }

        if ($display_width > $this->domain->setting('max_image_width') ||
            $display_height > $this->domain->setting('max_image_height')) {
            nel_derp(0,
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

        $results = nel_exec('exiftool ' . $exiftool_args . escapeshellarg($upload->data('location')));

        if ($results['result_code'] === 0) {
            $this->checkHashes($upload);
        }
    }
}