<?php
declare(strict_types = 1);

namespace Nelliel\Setup\Installer;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Language\Translator;
use Nelliel\Render\RenderCoreSimple;
use PDO;

class EnvironmentCheck
{
    private $translator;
    private $render_core;

    function __construct(Translator $translator)
    {
        $this->translator = $translator;
        $this->render_core = new RenderCoreSimple(NEL_INCLUDE_PATH . 'Setup/Installer/templates/');
    }

    public function check()
    {
        $render_data = array();
        $render_data['page_title'] = __('Environment check');
        $render_data['php_check'] = $this->checkPHP();
        $render_data['required_extensions_check'] = $this->checkRequiredExtensions();
        $render_data['optional_extensions_check'] = $this->checkOptionalExtensions();
        $render_data['directory_permissions_check'] = $this->checkDirectories();
        $render_data['pdo_drivers_check'] = $this->checkPDODrivers();
        $render_data['minimum_requirements_met'] = !$render_data['php_check']['php_check_failed'] &&
            !$render_data['required_extensions_check']['extensions_check_failed'] &&
            !$render_data['directory_permissions_check']['directory_check_failed'] &&
            !$render_data['pdo_drivers_check']['pdo_driver_check_failed'];
        $this->output('environment_check', $render_data);
    }

    private function checkPHP(): array
    {
        $render_data = array();
        $render_data['php_minimum'] = NELLIEL_PHP_MINIMUM;
        $render_data['php_detected'] = PHP_VERSION;
        $render_data['php_check_failed'] = version_compare(PHP_VERSION, NELLIEL_PHP_MINIMUM, '<');
        return $render_data;
    }

    private function checkRequiredExtensions(): array
    {
        $render_data = array();
        $render_data['extensions_check_failed'] = false;
        $required_extensions = array();
        $required_extensions['PDO'] = extension_loaded('pdo');
        $required_extensions['GD'] = extension_loaded('gd');
        $required_extensions['DOM'] = extension_loaded('dom');
        $required_extensions['iconv'] = extension_loaded('iconv');
        $required_extensions['libxml'] = extension_loaded('libxml');
        $required_extensions['session'] = extension_loaded('session');

        foreach ($required_extensions as $extension => $installed) {
            if (!$installed) {
                $render_data['extensions_check_failed'] = true;
            }

            $render_data['extensions'][] = ['name' => $extension, 'installed' => $installed];
        }

        return $render_data;
    }

    private function checkDirectories(): array
    {
        $render_data = array();
        $render_data['directory_check_failed'] = false;
        $directories = array();
        $directories['core'] = is_writable(NEL_CORE_PATH);
        $directories['configuration'] = is_writable(NEL_CONFIG_FILES_PATH);
        $directories['public'] = is_writable(NEL_PUBLIC_PATH);
        $directories['assets'] = is_writable(NEL_ASSETS_FILES_PATH);

        foreach ($directories as $directory => $writable) {
            if (!$writable) {
                $render_data['directory_check_failed'] = true;
            }

            $render_data['directories'][] = ['directory' => $directory, 'writable' => $writable];
        }

        return $render_data;
    }

    private function checkOptionalExtensions(): array
    {
        $render_data = array();
        $optional_extensions = array();
        $optional_extensions['Imagick'] = extension_loaded('imagick');
        $optional_extensions['Gmagick'] = extension_loaded('gmagick');
        $optional_extensions['mbstring'] = extension_loaded('mbstring');

        foreach ($optional_extensions as $extension => $installed) {
            if (!$installed) {
                continue;
            }

            $render_data['extensions'][] = ['name' => $extension, 'installed' => $installed];
        }

        return $render_data;
    }

    private function checkPDODrivers(): array
    {
        $render_data = array();
        $drivers = array();
        $pdo_drivers = PDO::getAvailableDrivers();
        $drivers['MySQL'] = in_array('mysql', $pdo_drivers);
        $drivers['MariaDB'] = in_array('mysql', $pdo_drivers);
        $drivers['PostgreSQL'] = in_array('pgsql', $pdo_drivers);
        $drivers['SQLite'] = in_array('sqlite', $pdo_drivers);

        foreach ($drivers as $driver => $installed) {
            if (!$installed) {
                continue;
            }

            $render_data['drivers'][] = ['driver' => $driver, 'installed' => $installed];
        }

        $render_data['pdo_driver_check_failed'] = empty($render_data['drivers']);
        return $render_data;
    }

    private function output(string $template_file, array $render_data = array()): void
    {
        $render_data['base_stylesheet'] = NEL_STYLES_WEB_PATH . 'core/base_style.css';
        $html = $this->render_core->renderFromTemplateFile($template_file, $render_data);
        echo $this->translator->translateHTML($html);
        die();
    }
}