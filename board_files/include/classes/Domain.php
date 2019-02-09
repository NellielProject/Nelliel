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
    protected $render_instance;
    protected $render_active;
    protected $template_path;

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

    public function renderActive($status = null)
    {
        if (!is_null($status))
        {
            $this->render_active = $status;
        }

        return $this->render_active;
    }

    public function templatePath($new_path = null)
    {
        if(!is_null($new_path))
        {
            $this->template_path = $new_path;
        }

        return $this->template_path;
    }

    public function renderInstance($new_instance = null)
    {
        if (is_null($new_instance) && empty($this->render_instance))
        {
            $this->render_instance = new RenderCore();
            $front_end_data = new FrontEndData($this->database);
            $this->templatePath(TEMPLATES_FILE_PATH . $front_end_data->template($this->setting('template_id'))['directory']);
            $this->render_instance->getTemplateInstance()->setTemplatePath($this->template_path);
        }

        if (!is_null($new_instance))
        {
            $this->render_instance = $new_instance;
            $front_end_data = new FrontEndData($this->database);
            $this->templatePath(TEMPLATES_FILE_PATH . $front_end_data->template($this->setting('template_id'))['directory']);
            $this->render_instance->getTemplateInstance()->setTemplatePath($this->template_path);
        }

        return $this->render_instance;
    }

    protected abstract function loadSettings();

    protected abstract function loadReferences();

    protected abstract function loadSettingsFromDatabase();

    public abstract function regenCache();
}