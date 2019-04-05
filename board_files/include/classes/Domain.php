<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class Domain
{
    protected $domain_id;
    protected $domain_settings;
    protected $domain_references;
    protected $cache_handler;
    protected $database;
    protected $front_end_data;
    protected $file_handler;
    protected $render_core;
    protected $render_active;
    protected $template_path;
    protected $translator;

    protected abstract function loadSettings();

    protected abstract function loadReferences();

    protected abstract function loadSettingsFromDatabase();

    public abstract function regenCache();

    protected function utilitySetup()
    {
        $this->front_end_data = new FrontEndData($this->database);
        $this->file_handler = new \Nelliel\FileHandler();
        $this->cache_handler = new \Nelliel\CacheHandler();
        $this->translator = new \Nelliel\Language\Translator();
    }

    public function database($new_database = null)
    {
        if (!is_null($new_database))
        {
            $this->database = $new_database;
        }

        return $this->database;
    }

    public function id()
    {
        return $this->domain_id;
    }

    public function setting(string $setting = null)
    {
        if (empty($this->domain_settings))
        {
            $this->loadSettings();
        }

        if (is_null($setting))
        {
            return $this->domain_settings;
        }

        if (!isset($this->domain_settings[$setting]))
        {
            return null;
        }

        return $this->domain_settings[$setting];
    }

    public function reference(string $reference = null)
    {
        if (empty($this->domain_references))
        {
            $this->loadReferences();
        }

        if (is_null($reference))
        {
            return $this->domain_references;
        }

        return $this->domain_references[$reference];
    }

    public function templatePath($new_path = null)
    {
        if(!is_null($new_path))
        {
            $this->template_path = $new_path;
        }

        return $this->template_path;
    }

    public function translator()
    {
        return $this->translator;
    }
}