<?php
declare(strict_types = 1);

namespace Nelliel\Render;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Mustache_Loader;

class MultipathFileSystemLoader implements Mustache_Loader
{
    protected $templates = array();
    protected $substitutes = array();
    protected $templates_path = '';
    protected $extension = '.html';

    function __construct(string $baseDir, array $options = array())
    {
        if (array_key_exists('extension', $options)) {
            $this->extension = !empty($options['extension']) ? '.' . ltrim($options['extension'], '.') : '';
        }
    }

    public function load($name): string
    {
        $final_name = $this->substitutes[$name] ?? $name;

        if (!isset($this->templates[$this->templates_path][$final_name])) {
            $this->templates[$this->templates_path][$final_name] = $this->loadFile($final_name);
        }

        return $this->templates[$this->templates_path][$final_name];
    }

    protected function loadFile(string $file): string
    {
        $file_path = $this->templates_path . $file . $this->extension;
        return (string) file_get_contents($file_path);
    }

    public function updateSubstituteTemplates(array $substitutes, bool $clear = false)
    {
        if ($clear) {
            $this->substitutes = array();
        }

        $this->substitutes = array_merge($this->substitutes, $substitutes);
    }

    public function setTemplatePath(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        $this->templates_path = rtrim($path, '/') . '/';
        return true;
    }
}