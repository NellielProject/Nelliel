<?php

namespace Nelliel\Post;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class FilesUpload
{
    private $board;
    private $uploaded_files = array();
    private $processed_files = array();
    private $authorization;

    function __construct($board, $files = array(), $authorization)
    {
        $this->board = $board;
        $this->uploaded_files = $files;
        $this->authorization = $authorization;
    }

    public function processFiles($response_to)
    {
        $data_handler = new PostData($this->board, $this->authorization);
        $file_handler = new \Nelliel\FileHandler();
        $post_data = $file_count = 1;
        $filenames = array();
        $file_duplicate = 1;

        foreach ($this->uploaded_files as $entry => $file_data)
        {
            if (empty($file_data['name']))
            {
                continue;
            }

            $file = new \Nelliel\Content\ContentFile(nel_database(), new \Nelliel\ContentID(), $this->board->id());
            $new_file = array();
            $this->uploaded_files[$entry]['location'] = $file_data['tmp_name'];
            $file->content_data['location'] = $file_data['tmp_name'];
            $file->content_data['name'] = $file_data['name'];
            $file_data['location'] = $file_data['tmp_name'];
            $this->checkForErrors($file_data);
            $this->doesFileExist($response_to, $file);
            $this->checkFiletype($file);
            $this->getPathInfo($file);
            $file->content_data['name'] = $file_handler->filterFilename($file_data['name']);
            $form_info = $_POST['new_post']['file_info'][$entry];
            $file->content_data['filesize'] = $file_data['size'];
            $file->content_data['source'] = $data_handler->checkEntry($form_info['sauce'], 'string');
            $file->content_data['license'] = $data_handler->checkEntry($form_info['lol_drama'], 'string');
            $file->content_data['alt_text'] = $data_handler->checkEntry($form_info['alt_text'], 'string');

            foreach ($filenames as $filename)
            {
                if (strcasecmp($filename, $file->content_data['fullname']) === 0)
                {
                    if (strlen($file->content_data['fullname'] >= 255))
                    {
                        $file->content_data['filename'] = substr($file->content_data['filename'], 0, -5);
                    }

                    $file->content_data['filename'] = $file->content_data['filename'] . '_' . $file_duplicate;
                    $file->content_data['fullname'] = $file->content_data['filename'] . '.' .
                            $file->content_data['extension'];
                    ++ $file_duplicate;
                }
            }

            array_push($filenames, $file->content_data['fullname']);
            array_push($this->processed_files, $file);

            if ($file_count == $this->board->setting('max_post_files'))
            {
                break;
            }

            ++ $file_count;
        }

        return $this->processed_files;
    }

    public function getPathInfo($file)
    {
        $file_info = new \SplFileInfo($file->content_data['name']);
        $file->content_data['extension'] = $file_info->getExtension();
        $file->content_data['filename'] = $file_info->getBasename('.' . $file->content_data['extension']);
        $file->content_data['fullname'] = $file_info->getFilename();
    }

    public function checkForErrors($file)
    {
        $error_data = array('delete_files' => true, 'bad-filename' => $file['name'], 'files' => $this->uploaded_files,
            'board_id' => $this->board->id());

        if ($file['size'] > $this->board->setting('max_filesize') * 1024)
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
            nel_derp(17, _gettext('The uploaded file just ain\'t right. That\'s all I know.'), $error_data);
        }
    }

    public function doesFileExist($response_to, $file)
    {
        $database = nel_database();
        $snacks = new \Nelliel\Snacks($database, new \Nelliel\BanHammer($database));
        $error_data = array('delete_files' => true, 'bad-filename' => $file->content_data['name'],
            'files' => $this->uploaded_files, 'board_id' => $this->board->id());
        $is_banned = false;
        $file->content_data['md5'] = hash_file('md5', $file->content_data['location'], true);
        $is_banned = $snacks->fileHashIsBanned($file->content_data['md5'], 'md5');

        if (!$is_banned)
        {
            $file->content_data['sha1'] = hash_file('sha1', $file->content_data['location'], true);
            $is_banned = $snacks->fileHashIsBanned($file->content_data['sha1'], 'sha1');
        }

        $file->content_data['sha256'] = null;

        if (!$is_banned && $this->board->setting('file_sha256'))
        {
            $file->content_data['sha256'] = hash_file('sha256', $file->content_data['location'], true);
            $is_banned = $snacks->fileHashIsBanned($file->content_data['sha256'], 'sha256');
        }

        $file->content_data['sha512'] = null;

        if (!$is_banned && $this->board->setting('file_sha512'))
        {
            $file->content_data['sha512'] = hash_file('sha512', $file->content_data['location'], true);
            $is_banned = $snacks->fileHashIsBanned($file->content_data['sha512'], 'sha512');
        }

        if ($is_banned)
        {
            nel_derp(22, _gettext('That file is banned.'), $error_data);
        }

        if ($response_to === 0 && $this->board->setting('only_op_duplicates'))
        {
            $query = 'SELECT 1 FROM "' . $this->board->reference('content_table') .
                    '" WHERE "parent_thread" = "post_ref" AND ("md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?) LIMIT 1';
            $prepared = $database->prepare($query);
            $prepared->bindValue(1, $file->content_data['md5'], PDO::PARAM_LOB);
            $prepared->bindValue(2, $file->content_data['sha1'], PDO::PARAM_LOB);
            $prepared->bindValue(3, $file->content_data['sha256'], PDO::PARAM_LOB);
            $prepared->bindValue(4, $file->content_data['sha512'], PDO::PARAM_LOB);
        }
        else if ($response_to > 0 && $this->board->setting('only_thread_duplicates'))
        {
            $query = 'SELECT 1 FROM "' . $this->board->reference('content_table') .
                    '" WHERE "parent_thread" = ? AND ("md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?) LIMIT 1';
            $prepared = $database->prepare($query);
            $prepared->bindValue(1, $response_to, PDO::PARAM_INT);
            $prepared->bindValue(2, $file->content_data['md5'], PDO::PARAM_LOB);
            $prepared->bindValue(3, $file->content_data['sha1'], PDO::PARAM_LOB);
            $prepared->bindValue(4, $file->content_data['sha256'], PDO::PARAM_LOB);
            $prepared->bindValue(5, $file->content_data['sha512'], PDO::PARAM_LOB);
        }
        else
        {
            $query = 'SELECT 1 FROM "' . $this->board->reference('content_table') .
                    '" WHERE "md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ? LIMIT 1';
            $prepared = $database->prepare($query);
            $prepared->bindValue(1, $file->content_data['md5'], PDO::PARAM_LOB);
            $prepared->bindValue(2, $file->content_data['sha1'], PDO::PARAM_LOB);
            $prepared->bindValue(3, $file->content_data['sha256'], PDO::PARAM_LOB);
            $prepared->bindValue(4, $file->content_data['sha512'], PDO::PARAM_LOB);
        }

        $result = $database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN, true);

        if ($result)
        {
            nel_derp(21, _gettext('Duplicate file detected.'), $error_data);
        }
    }

    public function checkFiletype($file)
    {
        $filetypes = new \Nelliel\FileTypes(nel_database());
        $error_data = array('delete_files' => true, 'bad-filename' => $file->content_data['name'],
            'files' => $this->uploaded_files, 'board_id' => $this->board->id());
        $this->getPathInfo($file);
        $test_ext = utf8_strtolower($file->content_data['extension']);

        if (!$filetypes->isValidExtension($test_ext))
        {
            nel_derp(18, _gettext('Unrecognized file type.'), $error_data);
        }

        $type_data = $filetypes->extensionData($test_ext);

        if (!$filetypes->extensionIsEnabled($this->board->id(), $test_ext))
        {
            nel_derp(19, _gettext('Filetype is not allowed.'), $error_data);
        }

        if (!$filetypes->verifyFile($test_ext, $file->content_data['location'], 65535, 65535))
        {
            nel_derp(20, _gettext('Incorrect file type detected (does not match extension). Possible Hax.'), $error_data);
        }

        $file->content_data['type'] = $type_data['type'];
        $file->content_data['format'] = $type_data['format'];
        $file->content_data['mime'] = $type_data['mime'];
    }
}