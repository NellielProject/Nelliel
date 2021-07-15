<?php
declare(strict_types = 1);

namespace Nelliel\Modules\NewPost;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Modules\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentPost;
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

    public function process(ContentPost $post): array
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

    private function files(ContentPost $post)
    {
        $response_to = $post->data('response_to');
        $file_count = count($this->files['upload_files']['name']);
        $data_handler = new PostData($this->domain, $this->authorization, $this->session);
        $file_handler = nel_utilities()->fileHandler();
        $filenames = array();
        $file_duplicate = 1;
        $total_files = 0;

        for ($i = 0; $i < $file_count; $i ++)
        {
            if (nel_true_empty($this->files['upload_files']['name'][$i]))
            {
                continue;
            }

            $file_data = array();
            $file_data['name'] = $this->files['upload_files']['name'][$i];
            $file_data['type'] = $this->files['upload_files']['type'][$i];
            $file_data['tmp_name'] = $this->files['upload_files']['tmp_name'][$i];
            $file_data['error'] = $this->files['upload_files']['error'][$i];
            $file_data['size'] = $this->files['upload_files']['size'][$i];
            $file = new \Nelliel\Content\ContentFile(new \Nelliel\Content\ContentID(), $this->domain);
            $file->changeData('location', $file_data['tmp_name']);
            $file->changeData('name', $file_data['name']);
            $file_data['location'] = $file_data['tmp_name'];
            $this->checkForErrors($file_data);
            $this->doesFileExist($response_to, $file);
            $this->checkFiletype($file);
            $exif_data = @exif_read_data($file->data('location'), '', true);

            if ($this->domain->setting('store_exif_data') && $exif_data !== false)
            {
                $file->changeData('exif', $exif_data);
            }

            if ($file->data('type') === 'graphics' || $file->data('format') === 'swf')
            {
                $dim = getimagesize($file->data('location'));

                if ($dim !== false)
                {
                    $file->changeData('display_width', $dim[0]);
                    $file->changeData('display_height', $dim[1]);
                }
            }

            $this->getPathInfo($file);
            $file->getMoar()->modify('original_filename', $file->data('filename'));
            $file->getMoar()->modify('original_extension', $file->data('extension'));
            $file->changeData('name', $file_handler->filterFilename($file_data['name']));
            $spoiler = $_POST['form_spoiler'];
            $file->changeData('filesize', $file_data['size']);

            if (isset($spoiler) && $this->domain->setting('enable_spoilers'))
            {
                $file->changeData('spoiler', $data_handler->checkEntry($spoiler, 'integer'));
            }

            $filename_maxlength = 255 - strlen($file->data('extension')) - 1;
            $file->changeData('filename', substr($file->data('filename'), 0, $filename_maxlength));

            foreach ($filenames as $filename)
            {
                if (strcasecmp($filename, $file->data('fullname')) === 0)
                {
                    $file->changeData('filename', $file->data('filename') . '_' . $file_duplicate);
                    $file->changeData('fullname', $file->data('filename') . '.' . $file->data('extension'));
                    $file_duplicate ++;
                }
            }

            switch ($this->domain->setting('preferred_filename'))
            {
                case 'original':
                    ;
                    break;

                case 'timestamp':
                    $file->changeData('filename', $post->data('post_time') . $post->data('post_time_milli'));
                    $file->changeData('fullname', $file->data('filename') . '.' . $file->data('extension'));
                    break;

                case 'md5':
                    $file->changeData('filename', $file->data('md5'));
                    $file->changeData('fullname', $file->data('md5') . '.' . $file->data('extension'));
                    break;

                case 'sha1':
                    $file->changeData('filename', $file->data('sha1'));
                    $file->changeData('fullname', $file->data('sha1') . '.' . $file->data('extension'));
                    break;

                case 'sha256':
                    $file->changeData('filename', $file->data('sha256'));
                    $file->changeData('fullname', $file->data('sha256') . '.' . $file->data('extension'));
                    break;

                case 'sha512':
                    $file->changeData('filename', $file->data('sha512'));
                    $file->changeData('fullname', $file->data('sha512') . '.' . $file->data('extension'));
                    break;

                default:
                    $file->changeData('filename', $post->data('post_time') . $post->data('post_time_milli'));
                    $file->changeData('fullname', $file->data('filename') . '.' . $file->data('extension'));
                    break;
            }

            array_push($filenames, $file->data('fullname'));
            $this->processed_uploads[] = $file;
            $total_files ++;
        }

        $post->changeData('file_count', $total_files);
    }

    private function getPathInfo($file)
    {
        $file_info = new \SplFileInfo($file->data('name'));
        $file->changeData('extension', $file_info->getExtension());
        $file->changeData('filename', $file_info->getBasename('.' . $file->data('extension')));
        $file->changeData('fullname', $file_info->getFilename());
    }

    private function checkForErrors($file)
    {
        $error_data = ['delete_files' => true, 'bad-filename' => $file['name'], 'files' => $this->files,
            'board_id' => $this->domain->id()];

        if ($file['size'] > $this->domain->setting('max_filesize') * 1024)
        {
            nel_derp(11, _gettext('Spoon is too big.'), $error_data);
        }

        if ($file['error'] === UPLOAD_ERR_INI_SIZE)
        {
            nel_derp(12, _gettext('File is bigger than the server allows.'), $error_data);
        }

        if ($file['error'] === UPLOAD_ERR_FORM_SIZE)
        {
            nel_derp(13, _gettext('File is bigger than submission form allows.'), $error_data);
        }

        if ($file['error'] === UPLOAD_ERR_PARTIAL)
        {
            nel_derp(14, _gettext('Only part of the file was uploaded.'), $error_data);
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE)
        {
            nel_derp(15, _gettext('File size is 0 or Candlejack stole your uplo'), $error_data);
        }

        if ($file['error'] === UPLOAD_ERR_NO_TMP_DIR || $file['error'] === UPLOAD_ERR_CANT_WRITE)
        {
            nel_derp(16, _gettext('Cannot save uploaded files to server for some reason.'), $error_data);
        }

        if ($file['error'] !== UPLOAD_ERR_OK)
        {
            nel_derp(17, _gettext("The uploaded file just ain't right. Dunno why.'"), $error_data);
        }

        nel_plugins()->processHook('nel-post-check-file-errors', [$file, $error_data]);
    }

    private function doesFileExist($response_to, $file)
    {
        $database = $this->domain->database();
        $snacks = new \Nelliel\Snacks($this->domain, new \Nelliel\BansAccess($database));
        $error_data = ['delete_files' => true, 'bad-filename' => $file->data('name'), 'files' => $this->files,
            'board_id' => $this->domain->id()];
        $is_banned = false;
        $file->changeData('md5', hash_file('md5', $file->data('location')));
        $is_banned = $snacks->fileHashIsBanned($file->data('md5'), 'md5');

        if (!$is_banned)
        {
            $file->changeData('sha1', hash_file('sha1', $file->data('location')));
            $is_banned = $snacks->fileHashIsBanned($file->data('sha1'), 'sha1');
        }

        if (!$is_banned)
        {
            $file->changeData('sha256', hash_file('sha256', $file->data('location')));
            $is_banned = $snacks->fileHashIsBanned($file->data('sha256'), 'sha256');
        }

        if (!$is_banned)
        {
            $file->changeData('sha512', hash_file('sha512', $file->data('location')));
            $is_banned = $snacks->fileHashIsBanned($file->data('sha512'), 'sha512');
        }

        if ($is_banned)
        {
            nel_derp(22, _gettext('That file is banned.'), $error_data);
        }

        $db_md5 = nel_prepare_hash_for_storage($file->data('md5'));
        $db_sha1 = nel_prepare_hash_for_storage($file->data('sha1'));
        $db_sha256 = nel_prepare_hash_for_storage($file->data('sha256'));
        $db_sha512 = nel_prepare_hash_for_storage($file->data('sha512'));
        $query = '';

        if ($response_to === 0 && $this->domain->setting('check_op_duplicates'))
        {
            $query = 'SELECT 1 FROM "' . $this->domain->reference('content_table') .
                    '" WHERE "parent_thread" = "post_ref" AND ("md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?)';
            $prepared = $database->prepare($query);
            $prepared->bindValue(1, $db_md5, PDO::PARAM_LOB);
            $prepared->bindValue(2, $db_sha1, PDO::PARAM_LOB);
            $prepared->bindValue(3, $db_sha256, PDO::PARAM_LOB);
            $prepared->bindValue(4, $db_sha512, PDO::PARAM_LOB);
        }
        else if ($response_to > 0 && $this->domain->setting('check_thread_duplicates'))
        {
            $query = 'SELECT 1 FROM "' . $this->domain->reference('content_table') .
                    '" WHERE "parent_thread" = ? AND ("md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?)';
            $prepared = $database->prepare($query);
            $prepared->bindValue(1, $response_to, PDO::PARAM_INT);
            $prepared->bindValue(2, $db_md5, PDO::PARAM_LOB);
            $prepared->bindValue(3, $db_sha1, PDO::PARAM_LOB);
            $prepared->bindValue(4, $db_sha256, PDO::PARAM_LOB);
            $prepared->bindValue(5, $db_sha512, PDO::PARAM_LOB);
        }

        if (!empty($query))
        {
            $result = $database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN, true);

            if ($result)
            {
                nel_derp(21, _gettext('Duplicate file detected.'), $error_data);
            }
        }
    }

    private function checkFiletype($file)
    {
        $filetypes = new \Nelliel\FileTypes($this->domain->database());
        $error_data = ['delete_files' => true, 'bad-filename' => $file->data('name'), 'files' => $this->files,
            'board_id' => $this->domain->id()];
        $this->getPathInfo($file);
        $test_ext = utf8_strtolower($file->data('extension'));

        if (!$filetypes->isValidExtension($test_ext))
        {
            nel_derp(18, _gettext('Unrecognized file type.'), $error_data);
        }

        if (!$filetypes->extensionIsEnabled($this->domain->id(), $test_ext))
        {
            nel_derp(19, _gettext('Filetype is not allowed.'), $error_data);
        }

        if (!$filetypes->verifyFile($test_ext, $file->data('location'), 65535, 65535))
        {
            nel_derp(20, _gettext('Incorrect file type detected (does not match extension). Possible Hax.'), $error_data);
        }

        $type_data = $filetypes->extensionData($test_ext);
        $file->changeData('type', $type_data['type']);
        $file->changeData('format', $type_data['format']);
        $file->changeData('mime', $type_data['mime']);
    }

    private function embeds(ContentPost $post)
    {
        $response_to = $post->data('response_to');
        $total_embeds = 0;

        foreach ($this->embeds as $embed_url)
        {
            $embed_url = trim($embed_url);

            if (empty($embed_url))
            {
                continue;
            }

            $embed = new \Nelliel\Content\ContentFile(new \Nelliel\Content\ContentID(), $this->domain);

            $checking_duplicates = false;

            if ($response_to === 0 && $this->domain->setting('check_op_duplicates'))
            {
                $prepared = $this->database->prepare(
                        'SELECT 1 FROM "' . $this->domain->reference('content_table') .
                        '" WHERE "parent_thread" = "post_ref" AND "embed_url" = ?');
                $prepared->bindValue(1, $embed_url, PDO::PARAM_STR);
                $checking_duplicates = true;
            }

            if ($response_to > 0 && $this->domain->setting('check_thread_duplicates'))
            {
                $prepared = $this->database->prepare(
                        'SELECT 1 FROM "' . $this->domain->reference('content_table') .
                        '" WHERE "parent_thread" = ? AND "embed_url" = ?');
                $prepared->bindValue(1, $response_to, PDO::PARAM_INT);
                $checking_duplicates = true;
            }

            if ($checking_duplicates)
            {
                $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN, true);

                if ($result)
                {
                    nel_derp(36, _gettext('Duplicate embed detected.'));
                }
            }

            $embed->changeData('type', 'embed');
            $embed->changeData('format', ''); // TODO: Maybe detect specific services
            $embed->changeData('embed_url', $embed_url);
            $this->processed_uploads[] = $embed;
            $total_embeds ++;
        }

        $post->changeData('embed_count', $total_embeds);
    }

    private function checkCount(ContentPost $post)
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
                nel_derp(25, _gettext('File uploads are not allowed.'));
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
            nel_derp(27,
                    sprintf(
                            _gettext(
                                    'You have too many uploads in one post. Received %d embeds and %d files for a total of %d uploads. Limit is %d.'),
                            $embeds_count, $files_count, $total, $this->domain->setting('max_post_uploads')));
        }

        if ($total >= 1 && $this->domain->setting('limit_thread_uploads') &&
                $parent_thread->data('total_content') >= $this->domain->setting('max_thread_uploads'))
        {
            nel_derp(43, _gettext('This thread has reached the maximum number of uploads.'));
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
}