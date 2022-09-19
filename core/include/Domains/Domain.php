<?php
declare(strict_types = 1);

namespace Nelliel\Domains;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielCacheInterface;
use Nelliel\Database\NellielPDO;
use Nelliel\FrontEnd\FrontEndData;
use Nelliel\Language\Language;
use Nelliel\Language\Translator;
use PDO;

abstract class Domain implements NellielCacheInterface
{
    const SITE = 'site';
    const GLOBAL = 'global';
    protected $domain_id;
    protected $settings;
    protected $references;
    protected $cache_handler;
    protected $database;
    protected $front_end_data;
    protected $file_handler;
    protected $render_core;
    protected $render_active;
    protected $template_path;
    protected $translator;
    protected $locale;
    protected $language;

    protected abstract function loadSettings(): void;

    protected abstract function loadReferences(): void;

    protected abstract function loadSettingsFromDatabase(): array;

    protected function utilitySetup()
    {
        $this->front_end_data = new FrontEndData($this->database);
        $this->file_handler = nel_utilities()->fileHandler();
        $this->cache_handler = nel_utilities()->cacheHandler();
        $this->translator = new Translator($this);
        $this->language = new Language();
    }

    public function database(NellielPDO $new_database = null)
    {
        if (!is_null($new_database)) {
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
        if (empty($this->settings)) {
            $this->loadSettings();
        }

        if (is_null($setting)) {
            return $this->settings;
        }

        if (!isset($this->settings[$setting])) {
            return null;
        }

        return $this->settings[$setting];
    }

    public function reference(string $reference = null)
    {
        if (empty($this->references)) {
            $this->loadReferences();
        }

        if (is_null($reference)) {
            return $this->references;
        }

        return $this->references[$reference] ?? '';
    }

    public function templatePath($new_path = null)
    {
        if (!is_null($new_path)) {
            $this->template_path = $new_path;
        }

        return $this->template_path;
    }

    public function translator()
    {
        return $this->translator;
    }

    public function locale(bool $html_format = false)
    {
        if (!isset($this->locale)) {
            return NEL_DEFAULT_LOCALE;
        }

        // Convert underscore notation to hyphen for HTML
        if ($html_format) {
            return utf8_str_replace('_', '-', $this->locale());
        }

        return $this->locale;
    }

    public function updateLocale(string $locale)
    {
        $this->locale = $locale;
    }

    public function frontEndData()
    {
        return $this->front_end_data;
    }

    protected function cacheSettings()
    {
        $settings = $this->loadSettingsFromDatabase();
        $this->cache_handler->writeArrayToFile('domain_settings', $settings, 'domain_settings.php',
            'domains/' . $this->domain_id);
    }

    public function reload(): void
    {
        $this->loadSettings();
        $this->loadReferences();
    }

    public static function getDomainFromID(string $id, NellielPDO $database): Domain
    {
        if ($id === Domain::SITE) {
            return new DomainSite($database);
        } else if ($id === Domain::GLOBAL) {
            return new DomainGlobal($database);
        } else {
            return new DomainBoard($id, $database);
        }
    }

    public static function validID(string $domain_id): bool
    {
        $database = nel_database('core');
        $prepared = $database->prepare('SELECT 1 FROM "' . NEL_DOMAIN_REGISTRY_TABLE . '" WHERE "domain_id" = ?');
        $prepared->bindValue(1, $domain_id, PDO::PARAM_STR);
        $result = $database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        return $result !== false;
    }
}