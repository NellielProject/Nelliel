<?php
declare(strict_types = 1);

namespace Nelliel\Domains;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielCacheInterface;
use Nelliel\Statistics;
use Nelliel\Database\NellielPDO;
use Nelliel\FrontEnd\FrontEndData;
use Nelliel\Language\Language;
use Nelliel\Language\Translator;
use Nelliel\Utility\CacheHandler;
use Nelliel\Utility\FileHandler;
use DateTime;
use DateTimeZone;
use PDO;

abstract class Domain implements NellielCacheInterface
{
    const SITE = 'site';
    const GLOBAL = 'global';
    protected string $domain_id = '';
    protected string $uri = '';
    protected string $display_uri = '';
    protected string $notes = '';
    protected array $settings = array();
    protected array $references = array();
    protected CacheHandler $cache_handler;
    protected NellielPDO $database;
    protected FrontEndData $front_end_data;
    protected FileHandler $file_handler;
    protected string $template_path = '';
    protected Translator $translator;
    protected string $locale = NEL_DEFAULT_LOCALE;
    protected Language $language;
    protected Statistics $statistics;
    protected bool $exists = false;

    protected abstract function loadSettings(): void;

    protected abstract function loadReferences(): void;

    protected abstract function loadSettingsFromDatabase(): array;

    public abstract function updateStatistics(): void;

    public abstract function uri(bool $display = false, bool $formatted = false): string;

    function __construct(string $domain_id, NellielPDO $database)
    {
        $this->database = $database;
        $this->loadDomainInfo($domain_id);
        $this->utilitySetup();
        $this->locale();
    }

    protected function loadDomainInfo(string $id): void
    {
        $id_lower = utf8_strtolower($id);
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_DOMAIN_REGISTRY_TABLE . '" WHERE "domain_id" = ? OR "uri" = ?');
        $info = $this->database->executePreparedFetch($prepared, [$id_lower, $id_lower], PDO::FETCH_ASSOC);

        if (is_array($info)) {
            $this->domain_id = $info['domain_id'] ?? '';
            $this->uri = $info['uri'] ?? '';
            $this->display_uri = $info['display_uri'] ?? '';
            $this->notes = $info['notes'] ?? '';
        }

        $this->exists = isset($this->domain_id) && $this->domain_id !== '';
    }

    protected function utilitySetup(): void
    {
        $this->front_end_data = new FrontEndData($this->database);
        $this->file_handler = nel_utilities()->fileHandler();
        $this->cache_handler = nel_utilities()->cacheHandler();
        $this->translator = new Translator($this->file_handler);
        $this->language = new Language();
        $this->statistics = new Statistics();
    }

    public function database(NellielPDO $new_database = null): NellielPDO
    {
        if (!is_null($new_database)) {
            $this->database = $new_database;
        }

        return $this->database;
    }

    public function id(): string
    {
        return $this->domain_id;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function setting(string $setting = null)
    {
        if (empty($this->settings)) {
            $this->loadSettings();
        }

        if (is_null($setting)) {
            return $this->settings;
        }

        return $this->settings[$setting] ?? null;
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

    public function templatePath($new_path = null): string
    {
        if (!is_null($new_path)) {
            $this->template_path = $new_path;
        }

        return $this->template_path;
    }

    public function translator(): Translator
    {
        return $this->translator;
    }

    public function locale(bool $html_format = false)
    {
        // Convert underscore notation to hyphen for HTML
        if ($html_format) {
            return utf8_str_replace('_', '-', $this->locale());
        }

        return $this->locale;
    }

    public function updateLocale(string $locale): void
    {
        $this->locale = utf8_str_replace('-', '_', $locale);
    }

    public function frontEndData(): FrontEndData
    {
        return $this->front_end_data;
    }

    protected function cacheSettings(): void
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
            $board_domain = new DomainBoard($id, $database);

            // Check if we were passed a URI
            if (!$board_domain->exists()) {
                $prepared = $database->prepare(
                    'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_uri" = ?');
                $prepared->bindValue(1, $id, PDO::PARAM_STR);
                $result = $database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

                if ($result !== false) {
                    return new DomainBoard($result, $database);
                }
            }

            return $board_domain;
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

    public function domainDateTime(int $timestamp): DateTime
    {
        $date_time = new DateTime();
        $date_time->setTimestamp($timestamp);
        $date_time->setTimezone(new DateTimeZone((string) $this->setting('time_zone')));
        return $date_time;
    }
}