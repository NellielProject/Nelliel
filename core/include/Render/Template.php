<?php
declare(strict_types = 1);

namespace Nelliel\Render;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class Template
{
    private string $base_path = '';
    private string $name = '';
    private string $extension = 'html';
    private string $text = '';

    function __construct(string $base_path, string $name, string $extension)
    {
        $this->base_path = $base_path;
        $this->name = $name;
        $this->extension = $extension;
    }

    public function basePath(): string
    {
        return $this->base_path;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function fullPath(): string
    {
        return rtrim($this->base_path, '/') . '/' . $this->name . '.' . ltrim($this->extension, '.');
    }

    public function getText(): string {
        return $this->text;
    }

    public function updateText(string $new_text): void {
        $this->text = $new_text;
    }
}
