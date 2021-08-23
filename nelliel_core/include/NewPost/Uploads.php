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

    function __construct(Domain $domain, array $files = array(), array $embeds = array(), Authorization $authorization,
            Session $session)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->embeds = $embeds;
        $this->files = $files;
        $this->authorization = $authorization;
        $this->session = $session;
    }

    public function process(Post $post): array
    {
        $this->checkCount($post);
        $this->embeds($post);

        if (!empty($this->processed_uploads) && $this->domain->setting('embed_replaces_file'))
        {
            return $this->processed_uploads;
        }

        $this->files($post);
        return $this->processed_uploads;
    }

    private function files(Post $post)
    {
        $file_count = count($this->files['upload_files']['name']);
        $data_handler = new PostData($this->domain, $this->authorization, $this->session);
        $filenames = array();
        $file_duplicate = 1;
        $total_files = 0;

        for ($i = 0; $i < $file_count; $i ++)
        {
            $file_original_name = $this->files['upload_files']['name'][$i];
            $file_provided_type = $this->files['upload_files']['type'][$i];
            $temp_file = $this->files['upload_files']['tmp_name'][$i];
            $file_upload_error = $this->files['upload_files']['error'][$i];
            $file_size = $this->files['upload_files']['size'][$i];

            if (nel_true_empty($file_original_name) || !is_uploaded_file($temp_file))
            {
                continue;
            }

            $this->checkForErrors($file_upload_error);

            if ($file_size > $this->domain->setting('max_filesize') * 1024)
            {
                nel_derp(12, _gettext('File is too big.'));
            }

            $upload = new Upload(new ContentID(), $this->domain);
            $upload->changeData('filesize', $file_size);

            $file_info = new \SplFileInfo($file_original_name);
            $file_extension = $file_info->getExtension();
            $upload->changeData('original_filename', $file_original_name);
            $this->checkFiletype($upload, $file_extension, $temp_file);
            $upload->changeData('extension', $file_extension);
            $this->checkHashes($upload, $temp_file);
            $this->checkDuplicates($post, $upload);
            $upload->changeData('location', $this->files['upload_files']['tmp_name'][$i]);
            $upload->changeData('name', $this->files['upload_files']['name'][$i]);
            $upload->changeData('tmp_name', $this->files['upload_files']['tmp_name'][$i]);

            if ($this->domain->setting('store_exif_data'))
            {
                $exif_data = json_encode(@exif_read_data($temp_file, '', true));

                if (is_string($exif_data))
                {
                    $upload->changeData('exif', $exif_data);
                }
            }

            if ($upload->data('type') === 'graphics' || $upload->data('format') === 'swf')
            {
                $dim = getimagesize($upload->data('location'));

                if ($dim !== false)
                {
                    $upload->changeData('display_width', $dim[0]);
                    $upload->changeData('display_height', $dim[1]);
                }
            }

            if ($this->domain->setting('enable_spoilers'))
            {
                $spoiler = $_POST['form_spoiler'] ?? 0;
                $upload->changeData('spoiler', $data_handler->checkEntry($spoiler, 'integer'));
            }

            $upload->changeData('filename', $this->filterBasename($file_original_name));
            $this->setFilename($upload, $post);

            foreach ($filenames as $filename)
            {
                if (strcasecmp($filename, $upload->data('fullname')) === 0)
                {
                    $upload->changeData('filename', $upload->data('filename') . '_' . $file_duplicate);
                    $upload->changeData('fullname', $upload->data('filename') . '.' . $upload->data('extension'));
                    $file_duplicate ++;
                }
            }

            array_push($filenames, $upload->data('fullname'));
            $this->processed_uploads[] = $upload;
            $total_files ++;
        }

        $post->changeData('file_count', $total_files);
    }

    private function setFilename(Upload $upload, Post $post): void
    {
        switch ($this->domain->setting('preferred_filename'))
        {
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
                $filename = $this->filterBasename($upload->data('filename'));
                $maxlength = 255 - strlen($upload->data('extension')) - 1;
                $filename = substr($filename, 0, $maxlength);

            case 'timestamp':
            default:
                $filename = $post->data('post_time') . $post->data('post_time_milli');
                break;
        }

        $upload->changeData('filename', $filename);
        $upload->changeData('fullname', $upload->data('filename') . '.' . $upload->data('extension'));
    }

    private function checkForErrors(int $upload_error): void
    {
        if ($upload_error === UPLOAD_ERR_INI_SIZE)
        {
            nel_derp(13, _gettext('File is bigger than the server allows.'));
        }

        if ($upload_error === UPLOAD_ERR_FORM_SIZE)
        {
            nel_derp(14, _gettext('File is bigger than submission form allows.'));
        }

        if ($upload_error === UPLOAD_ERR_PARTIAL)
        {
            nel_derp(15, _gettext('Only part of the file was uploaded.'));
        }

        if ($upload_error === UPLOAD_ERR_NO_FILE)
        {
            nel_derp(16, _gettext('File size is 0 or Candlejack stole your upl'));
        }

        if ($upload_error === UPLOAD_ERR_NO_TMP_DIR)
        {
            nel_derp(17, _gettext('Temp directory for uploads is unavailable.'));
        }

        if ($upload_error === UPLOAD_ERR_CANT_WRITE)
        {
            nel_derp(18, _gettext('Cannot write uploaded files to server for some reason.'));
        }

        if ($upload_error === UPLOAD_ERR_EXTENSION)
        {
            nel_derp(19, _gettext("A PHP extension interfered with upload.'"));
        }

        if ($upload_error !== UPLOAD_ERR_OK)
        {
            nel_derp(20, _gettext("The uploaded file just ain't right. Dunno why.'"));
        }
    }

    private function checkHashes(Upload $upload, string $file): void
    {
        $snacks = new Snacks($this->domain, new BansAccess($this->database));
        $is_banned = false;
        $md5 = hash_file('md5', $file);
        $is_banned = $snacks->fileHashIsBanned($md5, 'md5');

        if (!$is_banned)
        {
            $sha1 = hash_file('sha1', $file);
            $is_banned = $snacks->fileHashIsBanned($sha1, 'sha1');
        }

        if (!$is_banned)
        {
            $sha256 = hash_file('sha1', $file);
            $is_banned = $snacks->fileHashIsBanned($sha256, 'sha256');
        }

        if (!$is_banned)
        {
            $sha512 = hash_file('sha1', $file);
            $is_banned = $snacks->fileHashIsBanned($sha512, 'sha512');
        }

        if ($is_banned)
        {
            nel_derp(24, _gettext('That file is banned.'));
        }

        $upload->changeData('md5', $md5);
        $upload->changeData('sha1', $sha1);
        $upload->changeData('sha256', $sha256);
        $upload->changeData('sha512', $sha512);
    }

    private function checkDuplicates(Post $post, Upload $upload): void
    {
        if ($post->data('op') && $this->domain->setting('check_op_duplicates'))
        {
            $query = 'SELECT 1 FROM "' . $this->domain->reference('uploads_table') .
                    '" WHERE "parent_thread" = "post_ref" AND ("md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?)';
            $prepared = $this->database->prepare($query);
            $prepared->bindValue(1, $upload->data('md5'), PDO::PARAM_STR);
            $prepared->bindValue(2, $upload->data('sha1'), PDO::PARAM_STR);
            $prepared->bindValue(3, $upload->data('sha256'), PDO::PARAM_STR);
            $prepared->bindValue(4, $upload->data('sha512'), PDO::PARAM_STR);
        }
        else if (!$post->data('op') && $this->domain->setting('check_thread_duplicates'))
        {
            $query = 'SELECT 1 FROM "' . $this->domain->reference('uploads_table') .
                    '" WHERE "parent_thread" = ? AND ("md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?)';
            $prepared = $this->database->prepare($query);
            $prepared->bindValue(1, $post->contentID()->threadID(), PDO::PARAM_INT);
            $prepared->bindValue(2, $upload->data('md5'), PDO::PARAM_STR);
            $prepared->bindValue(3, $upload->data('sha1'), PDO::PARAM_STR);
            $prepared->bindValue(4, $upload->data('sha256'), PDO::PARAM_STR);
            $prepared->bindValue(5, $upload->data('sha512'), PDO::PARAM_STR);
        }
        else
        {
            return;
        }

        $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN, true);

        if ($result)
        {
            nel_derp(25, _gettext('Duplicate file detected.'));
        }
    }

    private function checkFiletype(Upload $upload, string $extension, string $file): void
    {
        $filetypes = new FileTypes($this->domain->database());

        if (!$filetypes->isValidExtension($extension))
        {
            nel_derp(21, _gettext('Unrecognized file type.'));
        }

        if (!$filetypes->extensionIsEnabled($this->domain->id(), $extension))
        {
            nel_derp(22, _gettext('Filetype is not allowed.'));
        }

        if (!$filetypes->verifyFile($extension, $file, 65535, 65535))
        {
            nel_derp(23, _gettext('Incorrect file type detected (does not match extension). Possible Hax.'));
        }

        $type_data = $filetypes->extensionData($extension);
        $upload->changeData('type', $type_data['type']);
        $upload->changeData('format', $type_data['format']);
        $upload->changeData('mime', $type_data['mime']);
    }

    private function embeds(Post $post)
    {
        $total_embeds = 0;

        foreach ($this->embeds as $embed_url)
        {
            $embed_url = trim($embed_url);

            if (empty($embed_url))
            {
                continue;
            }

            $duplicates_found = false;

            if ($post->data('op') && $this->domain->setting('check_op_duplicates'))
            {
                $prepared = $this->database->prepare(
                        'SELECT 1 FROM "' . $this->domain->reference('uploads_table') .
                        '" WHERE "parent_thread" = "post_ref" AND "embed_url" = ?');
                $prepared->bindValue(1, $embed_url, PDO::PARAM_STR);
                $duplicates_found = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
            }

            if (!$duplicates_found && $this->domain->setting('check_thread_duplicates'))
            {
                $prepared = $this->database->prepare(
                        'SELECT 1 FROM "' . $this->domain->reference('uploads_table') .
                        '" WHERE "parent_thread" = ? AND "embed_url" = ?');
                $prepared->bindValue(1, $post->data('parent_thread'), PDO::PARAM_INT);
                $prepared->bindValue(2, $embed_url, PDO::PARAM_STR);
                $duplicates_found = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
            }

            if ($duplicates_found)
            {
                nel_derp(36, _gettext('Duplicate embed detected.'));
            }

            $embed = new Upload(new ContentID(), $this->domain);
            $embed->changeData('type', 'embed');
            $embed->changeData('format', 'embed');
            $embed->changeData('embed_url', $embed_url);
            $this->processed_uploads[] = $embed;
            $total_embeds ++;
        }

        $post->changeData('embed_count', $total_embeds);
    }

    private function checkCount(Post $post)
    {
        $embeds_count = 0;
        $files_count = 0;
        $response_to = $post->data('response_to');
        $parent_thread = $post->getParent();
        $parent_thread->loadFromDatabase();

        foreach ($this->embeds as $embed)
        {
            $embeds_count += (nel_true_empty($embed)) ? 0 : 1;
        }

        if ($embeds_count > 0 && !$this->domain->setting('allow_embeds'))
        {
            nel_derp(26, _gettext('You are not allowed to post embedded content.'));
        }

        if (isset($this->files['upload_files']['name']))
        {
            foreach ($this->files['upload_files']['name'] as $file_name)
            {
                $files_count += (nel_true_empty($file_name)) ? 0 : 1;
            }
        }

        if ($embeds_count === 0 || !$this->domain->setting('embed_replaces_file'))
        {
            if ($files_count > 0 && !$this->domain->setting('allow_files'))
            {
                nel_derp(27, _gettext('File uploads are not allowed.'));
            }
        }

        if ($embeds_count > 0 && $this->domain->setting('embed_replaces_file'))
        {
            $total = $embeds_count;
        }
        else
        {
            $total = $embeds_count + $files_count;
        }

        if ($total === 0)
        {
            if ($response_to && $this->domain->setting('require_reply_upload'))
            {
                nel_derp(8, _gettext('An image, file or embed is required when replying.'));
            }

            if (!$response_to && $this->domain->setting('require_op_upload'))
            {
                nel_derp(9, _gettext('An image, file or embed is required to make new thread.'));
            }

            return;
        }

        if ($total > $this->domain->setting('max_post_uploads'))
        {
            nel_derp(28,
                    sprintf(
                            _gettext(
                                    'You have too many uploads in one post. Received %d embeds and %d files for a total of %d uploads. Limit is %d.'),
                            $embeds_count, $files_count, $total, $this->domain->setting('max_post_uploads')));
        }

        if ($total >= 1 && $this->domain->setting('limit_thread_uploads') &&
                $parent_thread->data('total_uploads') >= $this->domain->setting('max_thread_uploads'))
        {
            nel_derp(48, _gettext('This thread has reached the maximum number of uploads.'));
        }

        if ($total === 1)
        {
            if (!$response_to && !$this->domain->setting('allow_op_uploads'))
            {
                nel_derp(37, _gettext('The first post cannot have uploads.'));
            }

            if ($response_to && !$this->domain->setting('allow_reply_uploads'))
            {
                nel_derp(38, _gettext('Replies cannot have uploads.'));
            }
        }

        if ($total > 1)
        {
            if (!$response_to && !$this->domain->setting('allow_op_multiple'))
            {
                nel_derp(39, _gettext('The first post cannot have multiple uploads.'));
            }

            if ($response_to && !$this->domain->setting('allow_reply_multiple'))
            {
                nel_derp(40, _gettext('You cannot have multiple uploads in a reply.'));
            }
        }
    }

    private function filterBasename(string $basename): string
    {
        $filtered = $basename;
        $bad_found = true;

        // This is done in a loop to deal with filenames taking advantage of the filters
        while ($bad_found)
        {
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

            if ($filtered === '')
            {
                nel_derp(140, _gettext('Filename was empty or was purged by filter.'));
            }

            if ($before === $filtered)
            {
                $bad_found = false;
            }
        }

        return $filtered;
    }
}