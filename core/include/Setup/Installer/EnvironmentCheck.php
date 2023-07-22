<?php
declare(strict_types = 1);

namespace Nelliel\Setup\Installer;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Utility\FileHandler;

class EnvironmentCheck
{
    protected $database;
    protected $file_handler;

    function __construct(FileHandler $file_handler)
    {
        $this->file_handler = $file_handler;
    }

    public function check()
    {
        echo '
<!DOCTYPE html>
<html>
	<head>
		<title data-i18n="">Environment check</title>
	</head>
	<body>
';

        $php_check = $this->checkPHP();
        $required_extension_check = $this->checkRequiredExtensions();
        $directory_check = $this->checkDirectories();

        $success = $php_check && $required_extension_check && $directory_check;

        if (!$success) {
            echo '<p><span  style="font-weight: bold;">' . __('Problems were detected:') . '</span><br>';

            if (!$php_check) {
                echo __('PHP version is too old.') . '<br>';
            }

            if (!$required_extension_check) {
                echo __('Not all required extensions are installed.') . '<br>';
            }

            if (!$directory_check) {
                echo __('Not all directories are writable.') . '</br>';
            }

            echo '</p>';
            echo '<p>' .
                __(
                    'Some requirements for installation have not been met. Please correct these before retrying installation.') .
                '</p>';
            die();
        } else {
            echo __('All minimum requirements have been met!') . '</p>';
        }

        echo '<p><span  style="font-weight: bold; text-decoration: underline;">' . __('Additional environment information') . '</span><br>';
        $this->checkOptionalExtensions();
        echo '</p>';

        echo '
        <form accept-charset="utf-8" action="imgboard.php?install&step=environment-check" method="post">
            <div>
                <input type="submit" value="' . __('Continue') . '">
            </div>
        </form>
    </body>
</html>';
        die();
    }

    private function checkPHP(): bool
    {
        echo '<p><span  style="font-weight: bold;">' . __('PHP version check:') . '</span><br>';
        echo __('Minimum PHP version required: ' . NELLIEL_PHP_MINIMUM), '<br>';
        echo __('PHP version detected: ' . PHP_VERSION), '<br>';
        return version_compare(PHP_VERSION, NELLIEL_PHP_MINIMUM, '>=');
    }

    private function checkRequiredExtensions(): bool
    {
        $success = true;
        $required_extensions = array();
        $required_extensions['PDO'] = extension_loaded('pdo');
        $required_extensions['GD'] = extension_loaded('gd');
        $required_extensions['DOM'] = extension_loaded('dom');
        $required_extensions['iconv'] = extension_loaded('iconv');
        $required_extensions['libxml'] = extension_loaded('libxml');
        $required_extensions['session'] = extension_loaded('session');

        echo '<p><span  style="font-weight: bold;">' . __('Required PHP extensions:') . '</span><br>';

        foreach ($required_extensions as $extension => $present) {
            if (!$present) {
                echo $extension . ': <span style="color: red;">' . __('not installed') . '</span><br>';
                $success = false;
            } else {
                echo $extension . ': <span style="color: green;">' . __('installed') . '</span><br>';
            }
        }

        return $success;
    }

    private function checkOptionalExtensions(): void
    {
        $optional_extensions = array();
        $optional_extensions['Imagick'] = extension_loaded('imagick');
        $optional_extensions['Gmagick'] = extension_loaded('gmagick');
        $optional_extensions['mbstring'] = extension_loaded('mbstring');

        echo '<p><span  style="font-weight: bold;">' . __('Optional PHP extensions:') . '</span><br>';

        foreach ($optional_extensions as $extension => $present) {
            if (!$present) {
                echo $extension . ': <span style="color: red;">' . __('not installed') . '</span><br>';
            } else {
                echo $extension . ': <span style="color: green;">' . __('installed') . '</span><br>';
            }
        }

        echo '</p>';
    }

    private function checkDirectories(): bool
    {
        $success = true;
        $directories = array();
        $directories['main directory'] = is_writable(NEL_BASE_PATH);
        $directories['core'] = is_writable(NEL_CORE_PATH);
        $directories['configuration'] = is_writable(NEL_CONFIG_FILES_PATH);
        $directories['public'] = is_writable(NEL_PUBLIC_PATH);
        $directories['assets'] = is_writable(NEL_ASSETS_FILES_PATH);

        echo '<p><span  style="font-weight: bold;">' . __('Directory permissions check:') . '</span><br>';

        foreach ($directories as $directory => $writable) {
            if (!$writable) {
                echo $directory . ': <span style="color: red;">' . __('not writable') . '</span><br>';
                $success = false;
            } else {
                echo $directory . ': <span style="color: green;">' . __('writable') . '</span><br>';
            }
        }

        echo '</p>';
        return $success;
    }
}