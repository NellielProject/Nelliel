<?php

namespace Nelliel\post;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class FilesUpload
{
    private $board_id;
    private $uploaded_files = array();
    private $processed_files = array();
    private $data_handler;

    function __construct($board_id, $files = array())
    {
        $this->board_id = $board_id;
        $this->uploaded_files = $files;
        $this->data_handler = new \Nelliel\post\PostData($board_id);
    }

    public function processFiles($response_to)
    {
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);
        $file_handler = new \Nelliel\FileHandler();
        $post_data =
        $file_count = 1;
        $filenames = array();
        $file_duplicate = 1;

        foreach ($this->uploaded_files as $entry => $file_data)
        {
            if (empty($file_data['name']))
            {
                continue;
            }

            $file = new \Nelliel\ContentFile(nel_database(), new \Nelliel\ContentID('nci_0_0_0'), $this->board_id);
            $new_file = array();
            $this->uploaded_files[$entry]['location'] = $file_data['tmp_name'];
            $file->file_data['location'] = $file_data['tmp_name'];
            $file->file_data['name'] = $file_data['name'];
            $file_data['location'] = $file_data['tmp_name'];
            $this->checkForErrors($file_data);
            $this->doesFileExist($response_to, $file);
            $this->checkFiletype($file);
            $this->getPathInfo($file);
            $file->file_data['name'] = $file_handler->filterFilename($file_data['name']);
            $form_info = $_POST['new_post']['file_info'][$entry];
            $file->file_data['filesize'] = $file_data['size'];
            $file->file_data['source'] = $this->data_handler->checkEntry($form_info['sauce'], 'string');
            $file->file_data['license'] = $this->data_handler->checkEntry($form_info['lol_drama'], 'string');
            $file->file_data['alt_text'] = $this->data_handler->checkEntry($form_info['alt_text'], 'string');

            foreach ($filenames as $filename)
            {
                if (strcasecmp($filename, $file->file_data['fullname']) === 0)
                {
                    if (strlen($file->file_data['fullname'] >= 255))
                    {
                        $file->file_data['filename'] = substr($file->file_data['filename'], 0, -5);
                    }

                    $file->file_data['filename'] = $file->file_data['filename'] . '_' . $file_duplicate;
                    $file->file_data['fullname'] = $file->file_data['filename'] . '.' . $file->file_data['extension'];
                    ++ $file_duplicate;
                }
            }

            array_push($filenames, $file->file_data['fullname']);
            array_push($this->processed_files, $file);

            if ($file_count == $board_settings['max_post_files'])
            {
                break;
            }

            ++ $file_count;
        }

        return $this->processed_files;
    }

    public function getPathInfo($file)
    {
        $file_info = new \SplFileInfo($file->file_data['name']);
        $file->file_data['extension'] = $file_info->getExtension();
        $file->file_data['filename'] = $file_info->getBasename('.' . $file->file_data['extension']);
        $file->file_data['fullname'] = $file_info->getFilename();
    }

    public function checkForErrors($file)
    {
        $error_data = array('delete_files' => true, 'bad-filename' => $file['name'], 'files' => $this->uploaded_files,
            'board_id' => $this->board_id);
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);

        if ($file['size'] > $board_settings['max_filesize'] * 1024)
        {
            nel_derp(100, _gettext('Spoon is too big.'), $error_data);
        }

        if ($file['error'] === UPLOAD_ERR_INI_SIZE)
        {
            nel_derp(101, _gettext('File is bigger than the server allows.'), $error_data);
        }

        if ($file['error'] === UPLOAD_ERR_FORM_SIZE)
        {
            nel_derp(102, _gettext('File is bigger than submission form allows.'), $error_data);
        }

        if ($file['error'] === UPLOAD_ERR_PARTIAL)
        {
            nel_derp(103, _gettext('Only part of the file was uploaded.'), $error_data);
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE)
        {
            nel_derp(104, _gettext('File size is 0 or Candlejack stole your uplo'), $error_data);
        }

        if ($file['error'] === UPLOAD_ERR_NO_TMP_DIR || $file['error'] === UPLOAD_ERR_CANT_WRITE)
        {
            nel_derp(105, _gettext('Cannot save uploaded files to server for some reason.'), $error_data);
        }

        if ($file['error'] !== UPLOAD_ERR_OK)
        {
            nel_derp(106, _gettext('The uploaded file just ain\'t right. That\'s all I know.'), $error_data);
        }
    }

    public function doesFileExist($response_to, $file)
    {
        $dbh = nel_database();
        $references = nel_parameters_and_data()->boardReferences($this->board_id);
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);
        $error_data = array('delete_files' => true, 'bad-filename' => $file->file_data['name'], 'files' => $this->uploaded_files,
            'board_id' => $this->board_id);
        $is_banned = false;
        $file->file_data['md5'] = hash_file('md5', $file->file_data['location'], true);
        $is_banned = nel_file_hash_is_banned($file->file_data['md5'], 'md5');

        if (!$is_banned)
        {
            $file->file_data['sha1'] = hash_file('sha1', $file->file_data['location'], true);
            $is_banned = nel_file_hash_is_banned($file->file_data['sha1'], 'sha1');
        }

        $file->file_data['sha256'] = null;

        if (!$is_banned && $board_settings['file_sha256'])
        {
            $file->file_data['sha256'] = hash_file('sha256', $file->file_data['location'], true);
            $is_banned = nel_file_hash_is_banned($file->file_data['sha256'], 'sha256');
        }

        $file->file_data['sha512'] = null;

        if (!$is_banned && $board_settings['file_sha512'])
        {
            $file->file_data['sha512'] = hash_file('sha512', $file->file_data['location'], true);
            $is_banned = nel_file_hash_is_banned($file->file_data['sha512'], 'sha512');
        }

        if ($is_banned)
        {
            nel_derp(150, _gettext('That file is banned.'), $error_data);
        }

        if ($response_to === 0 && $board_settings['only_op_duplicates'])
        {
            $query = 'SELECT 1 FROM "' . $references['file_table'] .
                    '" WHERE "parent_thread" = "post_ref" AND ("md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?) LIMIT 1';
            $prepared = $dbh->prepare($query);
            $prepared->bindValue(1, $file->file_data['md5'], PDO::PARAM_LOB);
            $prepared->bindValue(2, $file->file_data['sha1'], PDO::PARAM_LOB);
            $prepared->bindValue(3, $file->file_data['sha256'], PDO::PARAM_LOB);
            $prepared->bindValue(4, $file->file_data['sha512'], PDO::PARAM_LOB);
        }
        else if ($response_to > 0 && $board_settings['only_thread_duplicates'])
        {
            $query = 'SELECT 1 FROM "' . $references['file_table'] .
                    '" WHERE "parent_thread" = ? AND ("md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?) LIMIT 1';
            $prepared = $dbh->prepare($query);
            $prepared->bindValue(1, $response_to, PDO::PARAM_INT);
            $prepared->bindValue(2, $file->file_data['md5'], PDO::PARAM_LOB);
            $prepared->bindValue(3, $file->file_data['sha1'], PDO::PARAM_LOB);
            $prepared->bindValue(4, $file->file_data['sha256'], PDO::PARAM_LOB);
            $prepared->bindValue(5, $file->file_data['sha512'], PDO::PARAM_LOB);
        }
        else
        {
            $query = 'SELECT 1 FROM "' . $references['file_table'] .
                    '" WHERE "md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ? LIMIT 1';
            $prepared = $dbh->prepare($query);
            $prepared->bindValue(1, $file->file_data['md5'], PDO::PARAM_LOB);
            $prepared->bindValue(2, $file->file_data['sha1'], PDO::PARAM_LOB);
            $prepared->bindValue(3, $file->file_data['sha256'], PDO::PARAM_LOB);
            $prepared->bindValue(4, $file->file_data['sha512'], PDO::PARAM_LOB);
        }

        $result = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN, true);

        if ($result)
        {
            nel_derp(110, _gettext('Duplicate file detected.'), $error_data);
        }
    }

    public function checkFiletype($file)
    {
        $filetypes = nel_parameters_and_data()->filetypeData();
        $filetype_settings = nel_parameters_and_data()->filetypeSettings($this->board_id);
        $error_data = array('delete_files' => true, 'bad-filename' => $file->file_data['name'], 'files' => $this->uploaded_files,
            'board_id' => $this->board_id);
        $this->getPathInfo($file);
        $test_ext = utf8_strtolower($file->file_data['extension']);
        $file_length = filesize($file->file_data['location']);
        $end_offset = ($file_length < 65535) ? $file_length : $file_length - 65535;
        $file_test_begin = file_get_contents($file->file_data['location'], NULL, NULL, 0, 65535);
        $file_test_end = file_get_contents($file->file_data['location'], NULL, NULL, $end_offset);

        if (!array_key_exists($test_ext, $filetypes))
        {
            nel_derp(107, _gettext('Unrecognized file type.'), $error_data);
        }

        if (!$filetype_settings[$filetypes[$test_ext]['type']][$filetypes[$test_ext]['type']] ||
                !$filetype_settings[$filetypes[$test_ext]['type']][$filetypes[$test_ext]['format']])
        {
            nel_derp(108, _gettext('Filetype is not allowed.'), $error_data);
        }

        if (preg_match('#' . $filetypes[$test_ext]['id_regex'] . '#', $file_test_begin) ||
                preg_match('#' . $filetypes[$test_ext]['id_regex'] . '#', $file_test_end))
        {
            $file->file_data['type'] = $filetypes[$test_ext]['type'];
            $file->file_data['format'] = $filetypes[$test_ext]['format'];
            $file->file_data['mime'] = $filetypes[$test_ext]['mime'];
        }
        else
        {
            nel_derp(109, _gettext('Incorrect file type detected (does not match extension). Possible Hax.'),
                    $error_data);
        }
    }
}