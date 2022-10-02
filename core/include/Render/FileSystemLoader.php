<?php
declare(strict_types = 1);

namespace Nelliel\Render;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Mustache_Loader_FilesystemLoader;

class FileSystemLoader extends Mustache_Loader_FilesystemLoader
{
    protected $templates = array();
    protected $substitutes = array();

    function __construct($baseDir, array $options = array())
    {
        parent::__construct($baseDir, $options);
    }

    public function load($name)
    {
        $final_name = $this->substitutes[$name] ?? $name;

        if (!isset($this->templates[$final_name])) {
            $this->templates[$final_name] = $this->loadFile($final_name);
        }

        return $this->templates[$final_name];
    }

    public function updateSubstituteTemplates(array $substitutes, bool $clear = false)
    {
        if ($clear) {
            $this->substitutes = array();
        }

        $this->substitutes = array_merge($this->substitutes, $substitutes);
    }
}