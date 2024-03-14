<?php
declare(strict_types = 1);

namespace Nelliel\Render;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Mustache_Loader;

class FileSystemLoader implements Mustache_Loader
{
    private array $templates = array();
    private array $template_texts = array();
    private array $substitute_templates = array();
    private array $substitute_template_texts = array();
    private string $base_path = '';
    private string $extension = '.html';

    function __construct(string $default_base_path, array $options = array())
    {
        $this->setBasePath($default_base_path);

        if (array_key_exists('extension', $options)) {
            $this->setExtension($options['extension']);
        }
    }

    public function load($name): string
    {
        if (!isset($this->templates[$name])) {
            $split = explode('<>', $name);

            if (is_array($split) && isset($split[1])) {
                $this->templates[$name] = new Template($split[0], $split[1], $this->extension);
            } else {
                $this->templates[$name] = new Template($this->base_path, $name, $this->extension);
            }
        }

        if (isset($this->substitute_templates[$name])) {
            $full_path = $this->substitute_templates[$name]->fullPath();

            if (!isset($this->substitute_template_texts[$full_path])) {
                $this->substitue_template_texts[$full_path] = $this->loadFile($full_path);
            }

            $text = $this->substitue_template_texts[$full_path];
        } else {
            $full_path = $this->templates[$name]->fullPath();

            if (!isset($this->template_texts[$full_path])) {
                $this->template_texts[$full_path] = $this->loadFile($full_path);
            }

            $text = $this->template_texts[$full_path];
        }

        return $text;
    }

    private function loadFile(string $file_path): string
    {
        return (string) file_get_contents($file_path);
    }

    public function addSubstitute(string $name, Template $template): void
    {
        $this->substitute_templates[$name] = $template;
    }

    public function removeSubstitute(string $name): void
    {
        unset($this->substitute_templates[$name]);
    }

    public function setBasePath(string $base_path): void
    {
        $this->base_path = rtrim($base_path, '/') . '/';
    }

    public function setExtension(string $extension): void
    {
        $this->extension = !empty($extension) ? '.' . ltrim($extension, '.') : '';
    }
}