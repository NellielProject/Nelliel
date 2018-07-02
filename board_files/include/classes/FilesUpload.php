<?php

namespace Nelliel;

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

    function __construct($board_id = '', $files = array())
    {
        $this->board_id = $board_id;
        $this->uploaded_files = $files;
    }

    public function processFiles($response_to)
    {
        $board_settings = nel_parameters()->boardSettings($this->board_id);
        $file_handler = new \Nelliel\FileHandler();
        $file_count = 1;
        $filenames = array();
        $file_duplicate = 1;

        foreach ($this->uploaded_files as $entry => $file)
        {
            if (empty($file['name']))
            {
                continue;
            }

            $new_file = array();
            $this->uploaded_files[$entry]['location'] = $file['tmp_name'];
            $file['location'] = $file['tmp_name'];
            $new_file['location'] = $file['tmp_name'];
            $this->checkForErrors($file);
            $file_hashes = $this->doesFileExist($file, $response_to);
            $new_file = $new_file + $file_hashes;
            $type_data = $this->checkFiletype($file);
            $new_file = $new_file + $type_data;
            $path_info = $this->getPathInfo($file['name']);
            $new_file = $new_file + $path_info;
            $new_file['name'] = $file_handler->filterFilename($file['name']);
            $form_info = $_POST['new_post']['file_info'][$entry];
            $new_file['filesize'] = $file['size'];
            $new_file['source'] = nel_check_post_entry($form_info['sauce'], 'string');
            $new_file['license'] = nel_check_post_entry($form_info['lol_drama'], 'string');
            $new_file['alt_text'] = nel_check_post_entry($form_info['alt_text'], 'string');

            foreach ($filenames as $filename)
            {
                if (strcasecmp($filename, $new_file['fullname']) === 0)
                {
                    if (strlen($new_file['fullname'] >= 255))
                    {
                        $new_file['filename'] = substr($new_file['filename'], 0, -5);
                    }

                    $new_file['filename'] = $new_file['filename'] . '_' . $file_duplicate;
                    $new_file['fullname'] = $new_file['filename'] . '.' . $new_file['extension'];
                    ++ $file_duplicate;
                }
            }

            array_push($filenames, $new_file['fullname']);
            array_push($this->processed_files, $new_file);

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
        $file_info = new \SplFileInfo($file);
        $path_info['filename'] = $file_info->getFilename();
        $path_info['fullname'] = $file_info->getBasename();
        $path_info['extension'] = $file_info->getExtension();
        return $path_info;
    }

    public function checkForErrors($file)
    {
        $error_data = array('delete_files' => true, 'bad-filename' => $file['name'], 'files' => $this->uploaded_files, 'board_id' => $this->board_id);
        $board_settings = nel_parameters()->boardSettings($this->board_id);

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

    public function doesFileExist($file, $response_to)
    {
        $dbh = nel_database();
        $references = nel_parameters()->boardReferences($this->board_id);
        $board_settings = nel_parameters()->boardSettings($this->board_id);
        $error_data = array('delete_files' => true, 'bad-filename' => $file['name'], 'files' => $this->uploaded_files, 'board_id' => $this->board_id);
        $is_banned = false;
        $hashes = array();
        $hashes['md5'] = hash_file('md5', $file['location'], true);
        $is_banned = nel_file_hash_is_banned($hashes['md5'], 'md5');

        if (!$is_banned)
        {
            $hashes['sha1'] = hash_file('sha1', $file['location'], true);
            $is_banned = nel_file_hash_is_banned($hashes['sha1'], 'sha1');
        }

        $file['sha256'] = null;

        if (!$is_banned && $board_settings['file_sha256'])
        {
            $hashes['sha256'] = hash_file('sha256', $file['location'], true);
            $is_banned = nel_file_hash_is_banned($hashes['sha256'], 'sha256');
        }

        $file['sha512'] = null;

        if (!$is_banned && $board_settings['file_sha512'])
        {
            $hashes['sha512'] = hash_file('sha512', $file['location'], true);
            $is_banned = nel_file_hash_is_banned($hashes['sha512'], 'sha512');
        }

        if ($is_banned)
        {
            nel_derp(150, _gettext('That file is banned.'), $error_data);
        }

        if ($response_to === 0 && $board_settings['only_op_duplicates'])
        {
            $query = 'SELECT 1 FROM "' . $references['file_table'] .
                '" WHERE ("parent_thread" = ? AND "post_ref" = ?) AND ("md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?) LIMIT 1';
            $prepared = $dbh->prepare($query);
            $prepared->bindValue(1, $response_to, PDO::PARAM_INT);
            $prepared->bindValue(2, $response_to, PDO::PARAM_INT);
            $prepared->bindValue(3, $hashes['md5'], PDO::PARAM_LOB);
            $prepared->bindValue(4, $hashes['sha1'], PDO::PARAM_LOB);
            $prepared->bindValue(5, $hashes['sha256'], PDO::PARAM_LOB);
            $prepared->bindValue(6, $hashes['sha512'], PDO::PARAM_LOB);
        }
        else if ($response_to > 0 && $board_settings['only_thread_duplicates'])
        {
            $query = 'SELECT 1 FROM "' . $references['file_table'] .
                '" WHERE "parent_thread" = ? AND ("md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ?) LIMIT 1';
            $prepared = $dbh->prepare($query);
            $prepared->bindValue(1, $response_to, PDO::PARAM_INT);
            $prepared->bindValue(2, $hashes['md5'], PDO::PARAM_LOB);
            $prepared->bindValue(3, $hashes['sha1'], PDO::PARAM_LOB);
            $prepared->bindValue(4, $hashes['sha256'], PDO::PARAM_LOB);
            $prepared->bindValue(5, $hashes['sha512'], PDO::PARAM_LOB);
        }
        else
        {
            $query = 'SELECT 1 FROM "' . $references['file_table'] .
                '" WHERE "md5" = ? OR "sha1" = ? OR "sha256" = ? OR "sha512" = ? LIMIT 1';
            $prepared = $dbh->prepare($query);
            $prepared->bindValue(1, $hashes['md5'], PDO::PARAM_LOB);
            $prepared->bindValue(2, $hashes['sha1'], PDO::PARAM_LOB);
            $prepared->bindValue(3, $hashes['sha256'], PDO::PARAM_LOB);
            $prepared->bindValue(4, $hashes['sha512'], PDO::PARAM_LOB);
        }

        $result = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN, true);

        if ($result)
        {
            nel_derp(110, _gettext('Duplicate file detected.'), $error_data);
        }

        return $hashes;
    }

    public function checkFiletype($file)
    {
        $filetypes = nel_get_filetype_data();
        $filetype_settings = nel_parameters()->filetypeSettings($this->board_id);
        $error_data = array('delete_files' => true, 'bad-filename' => $file['name'], 'files' => $this->uploaded_files, 'board_id' => $this->board_id);
        $path_info = $this->getPathInfo($file['name']);
        $test_ext = utf8_strtolower($path_info['extension']);
        $file_length = filesize($file['location']);
        $end_offset = ($file_length < 65535) ? $file_length : $file_length - 65535;
        $file_test_begin = file_get_contents($file['location'], NULL, NULL, 0, 65535);
        $file_test_end = file_get_contents($file['location'], NULL, NULL, $end_offset);
        $type_data = array();

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
            $type_data['type'] = $filetypes[$test_ext]['type'];
            $type_data['format'] = $filetypes[$test_ext]['format'];
            $type_data['mime'] = $filetypes[$test_ext]['mime'];
        }
        else
        {
            nel_derp(109, _gettext('Incorrect file type detected (does not match extension). Possible Hax.'), $error_data);
        }

        return $type_data;
    }
}