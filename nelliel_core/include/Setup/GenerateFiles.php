<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\FileHandler;

class GenerateFiles
{
    protected $file_handler;

    function __construct(FileHandler $file_handler)
    {
        $this->file_handler = $file_handler;
    }

    public function installDone(bool $replace = false)
    {
        if (!file_exists(GENERATED_FILE_PATH . 'install_done.php') || $replace)
        {
            $this->file_handler->writeInternalFile(GENERATED_FILE_PATH . 'install_done.php', '', true, false);
            return true;
        }

        return false;
    }

    public function peppers(bool $replace = false)
    {
        if (!file_exists(GENERATED_FILE_PATH . 'peppers.php') || $replace)
        {
            $peppers = array();
            $peppers['tripcode_pepper'] = base64_encode(random_bytes(32));
            $peppers['ip_pepper'] = base64_encode(random_bytes(32));
            $peppers['poster_id_pepper'] = base64_encode(random_bytes(32));
            $peppers['post_password_pepper'] = base64_encode(random_bytes(32));
            $prepend = "\n" . '// DO NOT EDIT THESE VALUES OR REMOVE THIS FILE UNLESS YOU HAVE A DAMN GOOD REASON';
            $this->file_handler->writeInternalFile(GENERATED_FILE_PATH . 'peppers.php',
                    $prepend . "\n" . '$peppers = ' . var_export($peppers, true) . ';', true, false);
            return true;
        }

        return false;
    }
}