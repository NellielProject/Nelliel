<?php
declare(strict_types = 1);

namespace Nelliel\Setup;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Utility\FileHandler;

class GenerateFiles
{
    protected $file_handler;

    function __construct(FileHandler $file_handler)
    {
        $this->file_handler = $file_handler;
    }

    public function installDone(bool $replace = false): bool
    {
        if (!file_exists(NEL_GENERATED_FILES_PATH . 'install_done.php') || $replace) {
            return $this->file_handler->writeInternalFile(NEL_GENERATED_FILES_PATH . 'install_done.php', '', true);
        }

        return false;
    }

    public function peppers(bool $update = false): bool
    {
        $peppers = array();

        if(file_exists(NEL_GENERATED_FILES_PATH . 'peppers.php')) {
            if($update) {
                include NEL_GENERATED_FILES_PATH . 'peppers.php';
            } else {
                return false;
            }
        }

        $prepend = "\n" . '// DO NOT EDIT THESE VALUES OR REMOVE THIS FILE UNLESS YOU HAVE A DAMN GOOD REASON';
        $prepend .= "\n" . '// DOING SO WILL BREAK SECURE TRIPCODES, POST PASSWORDS AND OTHER THINGS';
        $peppers['tripcode_pepper'] = $peppers['tripcode_pepper'] ?? base64_encode(random_bytes(33));
        $peppers['ip_address_pepper'] = $peppers['ip_address_pepper'] ?? base64_encode(random_bytes(33));
        $peppers['poster_id_pepper'] = $peppers['poster_id_pepper'] ?? base64_encode(random_bytes(33));
        $peppers['post_password_pepper'] = $peppers['post_password_pepper'] ?? base64_encode(random_bytes(33));
        $this->file_handler->writeInternalFile(NEL_GENERATED_FILES_PATH . 'peppers.php',
            $prepend . "\n" . '$peppers = ' . var_export($peppers, true) . ';', true);

        return true;
    }

    public function ownerCreate(string $id, bool $replace = false): bool
    {
        if (!file_exists(NEL_GENERATED_FILES_PATH . 'create_owner.php') || $replace) {
            $text = '$install_id = \'' . $id . '\';';
            return $this->file_handler->writeInternalFile(NEL_GENERATED_FILES_PATH . 'create_owner.php', $text, true);
        }

        return false;
    }

    public function versions(array $versions_data = array(), bool $replace = false): bool
    {
        if (empty($versions_data)) {
            $versions_data['original'] = NELLIEL_VERSION;
            $versions_data['installed'] = NELLIEL_VERSION;
        }

        if (!file_exists(NEL_GENERATED_FILES_PATH . 'versions.php') || $replace) {
            return $this->file_handler->writeInternalFile(NEL_GENERATED_FILES_PATH . 'versions.php',
                '$versions_data = ' . var_export($versions_data, true) . ';', true);
        }

        return false;
    }
}