<?php
declare(strict_types = 1);

namespace Nelliel\Render;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Mustache_Loader;

class MultipathFileSystemLoader implements Mustache_Loader
{
    protected $template_paths = array();
    protected $templates = array();
    protected $substitutes = array();
    protected $extension = '.html';

    function __construct(string $baseDir, array $options = array())
    {
        if (array_key_exists('extension', $options)) {
            $this->setExtension($options['extension']);
        }
    }

    public function load($name): string
    {
        $substitute_name = $this->substitutes[$name] ?? $name;
        $split = explode('<>', $substitute_name);

        if (!isset($split[1])) {
            $path_id = 'default';
            $final_name = $substitute_name;
        } else {
            $path_id = $split[0];
            $final_name = $split[1];
        }

        if (!isset($this->templates[$path_id][$final_name])) {
            $this->templates[$path_id][$final_name] = $this->loadFile($path_id, $final_name);
        }

        return $this->templates[$path_id][$final_name];
    }

    protected function loadFile(string $path_id, string $file): string
    {
        $file_path = $this->template_paths[$path_id] . $file . $this->extension;
        return (string) file_get_contents($file_path);
    }

    public function updateSubstituteTemplates(array $substitutes, bool $clear = false)
    {
        if ($clear) {
            $this->substitutes = array();
        }

        $this->substitutes = array_merge($this->substitutes, $substitutes);
    }

    public function addTemplatePath(string $id, string $path): bool
    {
        $this->template_paths[$id] = rtrim($path, '/') . '/';
        return file_exists($path);
    }

    public function removeTemplatePath(string $id, string $path): void
    {
        unset($this->template_paths[$id]);
    }

    public function setDefaultTemplatePath(string $path): bool
    {
        return $this->addTemplatePath('default', $path);
    }

    public function setExtension(string $extension): void
    {
        $this->extension = !empty($extension) ? '.' . ltrim($extension, '.') : '';
    }
}