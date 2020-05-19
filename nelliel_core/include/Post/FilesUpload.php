<?php

namespace Nelliel\Post;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Auth\Authorization;
use Nelliel\Domain;
use PDO;

class FilesUpload
{
    private $domain;
    private $uploaded_files = array();
    private $processed_files = array();
    private $authorization;

    function __construct(Domain $domain, array $files = array(), Authorization $authorization)
    {
        $this->domain = $domain;
        $this->uploaded_files = $files;
        $this->authorization = $authorization;
    }

    public function processFiles($post)
    {
        $response_to = $post->data('response_to');
        $data_handler = new PostData($this->domain, $this->authorization);
        $file_handler = new \Nelliel\Utility\FileHandler();
        $error_data = ['delete_files' => true, 'files' => $this->uploaded_files, 'board_id' => $this->domain->id()];
        $file_count = count($this->uploaded_files['upload_files']['name']);

        if ($file_count > 1)
        {
            if ($file_count > $this->domain->setting('max_post_files'))
            {
                nel_derp(25,
                        sprintf(_gettext('You are trying to upload too many files in one post. Limit is %d'),
                                $this->domain->setting('max_post_files')), $error_data);
            }

            if (!$this->domain->setting('allow_multifile'))
            {
                nel_derp(26, _gettext('You cannot upload multiple files.'), $error_data);
            }

            if (!$this->domain->setting('allow_op_multifile'))
            {
                nel_derp(27, _gettext('The OP post cannot have multiple files.'), $error_data);
            }
        }

        $filenames = array();
        $file_duplicate = 1;

        for ($i = 0; $i < $file_count; $i ++)
        {
            $file_data = array();
            $file_data['name'] = $this->uploaded_files['upload_files']['name'][$i];
            $file_data['type'] = $this->uploaded_files['upload_files']['type'][$i];
            $file_data['tmp_name'] = $this->uploaded_files['upload_files']['tmp_name'][$i];
            $file_data['error'] = $this->uploaded_files['upload_files']['error'][$i];
            $file_data['size'] = $this->uploaded_files['upload_files']['size'][$i];

            if (empty($file_data['name']))
            {
                continue;
            }

            $file = new \Nelliel\Content\ContentFile(new \Nelliel\Content\ContentID(), $this->domain);
            $new_file = array();
            $file->changeData('location', $file_data['tmp_name']);
            $file->changeData('name', $file_data['name']);
            $file_data['location'] = $file_data['tmp_name'];
            $this->checkForErrors($file_data);
            $this->doesFileExist($response_to, $file);
            $this->checkFiletype($file);

            if ($file->data('type') === 'graphics' || $file->data('format') === 'swf')
            {
                $dim = getimagesize($file->data('location'));

                if($dim !== false)
                {
                    $file->changeData('display_width', $dim[0]);
                    $file->changeData('display_height', $dim[1]);
                }
            }

            $this->getPathInfo($file);
            $file->changeData('name', $file_handler->filterFilename($file_data['name']));
            $spoiler = $_POST['form_spoiler'];
            $file->changeData('filesize', $file_data['size']);

            if (isset($spoiler) && $this->domain->setting('enable_spoilers'))
            {
                $file->changeData('spoiler', $data_handler->checkEntry($spoiler, 'integer'));
            }

            if (strlen($file->data('fullname') >= 255))
            {
                $file->changeData('filename', substr($file->data('filename'), 0, 254));
            }

            foreach ($filenames as $filename)
            {
                if (strcasecmp($filename, $file->data('fullname')) === 0)
                {
                    if (strlen($file->data('fullname')) >= 255)
                    {
                        $overage = strlen($file->data('fullname')) - 250;
                        $file->changeData('filename', substr($file->data('filename'), 0, $overage));
                    }

                    $file->changeData('filename', $file->data('filename') . '_' . $file_duplicate);
                    $file->changeData('fullname', $file->data('filename') . '.' . $file->data('extension'));
                    ++ $file_duplicate;
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

                case 'sha1':
                    $file->changeData('filename', bin2hex($file->data('sha1')));
                    $file->changeData('fullname', bin2hex($file->data('sha1')) . '.' . $file->data('extension'));
                    break;

                case 'md5':
                    $file->changeData('filename', bin2hex($file->data('md5')));
                    $file->changeData('fullname', bin2hex($file->data('md5')) . '.' . $file->data('extension'));
                    break;

                default:
                    $file->changeData('filename', $post->data('post_time') . $post->data('post_time_milli'));
                    $file->changeData('fullname', $file->data('filename') . '.' . $file->data('extension'));
                    break;
            }

            array_push($filenames, $file->data('fullname'));
            array_push($this->processed_files, $file);

            if ($file_count == $this->domain->setting('max_post_files'))
            {
                break;
            }
        }

        $this->processed_files = nel_plugins()->processHook('nel-post-files-processed',
                [$this->domain, $this->uploaded_files], $this->processed_files);
        return $this->processed_files;
    }

    public function getPathInfo($file)
    {
        $file_info = new \SplFileInfo($file->data('name'));
        $file->changeData('extension', $file_info->getExtension());
        $file->changeData('filename', $file_info->getBasename('.' . $file->data('extension')));
        $file->changeData('fullname', $file_info->getFilename());
    }

    public function checkForErrors($file)
    {
        $error_data = ['delete_files' => true, 'bad-filename' => $file['name'], 'files' => $this->uploaded_files,
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
            nel_derp(17, _gettext("The uploaded file just ain't right. That's all I know.'"), $error_data);
        }

        nel_plugins()->processHook('nel-post-check-file-errors', [$file, $error_data]);
    }

    public function doesFileExist($response_to, $file)
    {
        $database = $this->domain->database();
        $snacks = new \Nelliel\Snacks($database, new \Nelliel\BanHammer($database));
        $error_data = ['delete_files' => true, 'bad-filename' => $file->data('name'), 'files' => $this->uploaded_files,
            'board_id' => $this->domain->id()];
        $is_banned = false;
        $file->changeData('md5', hash_file('md5', $file->data('location'), true));
        $is_banned = $snacks->fileHashIsBanned($file->data('md5'), 'md5');

        if (!$is_banned)
        {
            $file->changeData('sha1', hash_file('sha1', $file->data('location'), true));
            $is_banned = $snacks->fileHashIsBanned($file->data('sha1'), 'sha1');
        }

        if (!$is_banned && $this->domain->setting('file_sha256'))
        {
            $file->changeData('sha256', hash_file('sha256', $file->data('location'), true));
            $is_banned = $snacks->fileHashIsBanned($file->data('sha256'), 'sha256');
        }

        if (!$is_banned && $this->domain->setting('file_sha512'))
        {
            $file->changeData('sha512', hash_file('sha512', $file->data('location'), true));
            $is_banned = $snacks->fileHashIsBanned($file->data('sha512'), 'sha512');
        }

        if ($is_banned)
        {
            nel_derp(22, _gettext('That file is banned.'), $error_data);
        }

        if ($response_to === 0 && $this->domain->setting('only_op_duplicates'))
        {
            $query = 'SELECT 1 FROM "' . $this->domain->reference('content_table') .
                    '" WHERE "parent_thread" = "post_ref" AND ("md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?)';
            $prepared = $database->prepare($query);
            $prepared->bindValue(1, $file->data('md5'), PDO::PARAM_LOB);
            $prepared->bindValue(2, $file->data('sha1'), PDO::PARAM_LOB);
            $prepared->bindValue(3, $file->data('sha256'), PDO::PARAM_LOB);
            $prepared->bindValue(4, $file->data('sha512'), PDO::PARAM_LOB);
        }
        else if ($response_to > 0 && $this->domain->setting('only_thread_duplicates'))
        {
            $query = 'SELECT 1 FROM "' . $this->domain->reference('content_table') .
                    '" WHERE "parent_thread" = ? AND ("md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?)';
            $prepared = $database->prepare($query);
            $prepared->bindValue(1, $response_to, PDO::PARAM_INT);
            $prepared->bindValue(2, $file->data('md5'), PDO::PARAM_LOB);
            $prepared->bindValue(3, $file->data('sha1'), PDO::PARAM_LOB);
            $prepared->bindValue(4, $file->data('sha256'), PDO::PARAM_LOB);
            $prepared->bindValue(5, $file->data('sha512'), PDO::PARAM_LOB);
        }
        else
        {
            $query = 'SELECT 1 FROM "' . $this->domain->reference('content_table') .
                    '" WHERE "md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?';
            $prepared = $database->prepare($query);
            $prepared->bindValue(1, $file->data('md5'), PDO::PARAM_LOB);
            $prepared->bindValue(2, $file->data('sha1'), PDO::PARAM_LOB);
            $prepared->bindValue(3, $file->data('sha256'), PDO::PARAM_LOB);
            $prepared->bindValue(4, $file->data('sha512'), PDO::PARAM_LOB);
        }

        $result = $database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN, true);

        if ($result)
        {
            nel_derp(21, _gettext('Duplicate file detected.'), $error_data);
        }
    }

    public function checkFiletype($file)
    {
        $filetypes = new \Nelliel\FileTypes($this->domain->database());
        $error_data = ['delete_files' => true, 'bad-filename' => $file->data('name'), 'files' => $this->uploaded_files,
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
}