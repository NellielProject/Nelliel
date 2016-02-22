<?php

function nel_process_file_info()
{
    global $enabled_types;
    
    $files = array();
    $i = 0;
    $filetypes_loaded = FALSE;
    
    foreach ($_FILES as $file)
    {
        if ($file['error'] === UPLOAD_ERR_OK)
        {
            if (!empty($file['name']))
            {
                if (!$filetypes_loaded)
                {
                    include INCLUDE_PATH . 'filetype.php';
                    $filetypes_loaded = TRUE;
                }
                
                // Grab/strip the file extension
                $files[$i]['ext'] = ltrim(strrchr($file['name'], '.'), '.');
                $files[$i]['basic_filename'] = utf8_str_replace('.' . $files[$i]['ext'], "", $file['name']);
                
                $max_upload = ini_get('upload_max_filesize');
                $size_unit = utf8_strtolower(utf8_substr($max_upload, -1, 1));
                $max_upload = utf8_strtolower(utf8_substr($max_upload, 0, -1));
                
                if ($size_unit === 'g')
                {
                    $max_upload = $max_upload * 1024 * 1024 * 1024;
                }
                else if ($size_unit === 'm')
                {
                    $max_upload = $max_upload * 1024 * 1024;
                }
                else if ($size_unit === 'k')
                {
                    $max_upload = $max_upload * 1024;
                }
                else
                {
                    ; // Already in bytes
                }
                
                if ($file['size'] > BS_MAX_FILESIZE * 1024)
                {
                    nel_derp(19, array('origin' => 'POST', 'bad-filename' => $files[i]['basic_filename'] . $files[i]['ext'], 'files' => array($files[$i])));
                }
                
                $files[$i]['dest'] = SRC_PATH . $file['name'] . '.tmp';
                move_uploaded_file($file['tmp_name'], $files[$i]['dest']);
                chmod($files[$i]['dest'], 0644);
                $files[$i]['fsize'] = filesize($files[$i]['dest']);
                $test_ext = utf8_strtolower($files[$i]['ext']);
                $file_test = file_get_contents($files[$i]['dest'], NULL, NULL, 0, 65535);
                $file_good = FALSE;
                $file_allowed = FALSE;
                
                // Graphics
                if (array_key_exists($test_ext, $filetypes))
                {
                    if ($enabled_types['enable_' . utf8_strtolower($filetypes[$test_ext]['subtype'])] && $enabled_types['enable_' . utf8_strtolower($filetypes[$test_ext]['supertype'])])
                    {
                        $file_allowed = TRUE;
                        
                        if (preg_match('#' . $filetypes[$test_ext]['id_regex'] . '#', $file_test))
                        {
                            $files[$i]['supertype'] = $filetypes[$test_ext]['supertype'];
                            $files[$i]['subtype'] = $filetypes[$test_ext]['subtype'];
                            $files[$i]['mime'] = $filetypes[$test_ext]['mime'];
                            $file_good = TRUE;
                        }
                    }
                }
                
                if (!$file_allowed)
                {
                    nel_derp(6, array('origin' => 'POST', 'bad-filename' => $files[i]['basic_filename'] . $files[i]['ext'], 'files' => array($files[$i])));
                }
                
                if (!$file_good)
                {
                    nel_derp(18, array('origin' => 'POST', 'bad-filename' => $files[i]['basic_filename'] . $files[i]['ext'], 'files' => array($files[$i])));
                }
                
                ++ $i;
            }
            
            if ($files_count == BS_MAX_POST_FILES)
            {
                break;
            }
        }
        else if ($file['error'] === UPLOAD_ERR_INI_SIZE)
        {
            nel_derp(19, array('origin' => 'POST', 'bad-filename' => $files[i]['basic_filename'] . $files[i]['ext'], 'files' => array($files[$i])));
        }
    }
    
    return $files;
}
