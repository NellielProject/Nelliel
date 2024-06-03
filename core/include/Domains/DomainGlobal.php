<?php
declare(strict_types = 1);

namespace Nelliel\Domains;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielCacheInterface;
use Nelliel\Database\NellielPDO;
use PDO;

class DomainGlobal extends Domain implements NellielCacheInterface
{

    public function __construct(NellielPDO $database)
    {
        parent::__construct(Domain::GLOBAL, $database);
        $this->templatePath($this->front_end_data->getTemplate(nel_site_domain()->setting('template_id'))->getPath());
    }

    protected function loadSettings(): void
    {
        $settings = array();

        if (NEL_USE_FILE_CACHE) {
            $settings = $this->cache_handler->loadArrayFromFile('domain_settings', 'domain_settings.php',
                'domains/' . $this->domain_id);
        }

        if (empty($settings)) {
            $settings = $this->loadSettingsFromDatabase();
            $this->regenCache();
        }

        $this->settings = $settings;
        $this->updateLocale($this->setting('locale'));
    }

    protected function loadReferences(): void
    {}

    protected function loadSettingsFromDatabase(): array
    {
        $settings = array();
        $settings_list = $this->database->executeFetchAll(
            'SELECT "setting_name", "default_value", "data_type" FROM "' . NEL_SETTINGS_TABLE .
            '" WHERE "setting_category" = \'board\'', PDO::FETCH_ASSOC);
        $config_list = $this->database->executeFetchAll(
            'SELECT "setting_name", "setting_value" FROM "' . NEL_BOARD_DEFAULTS_TABLE . '"', PDO::FETCH_KEY_PAIR);

        foreach ($settings_list as $setting) {
            $settings[$setting['setting_name']] = nel_typecast(
                $config_list[$setting['setting_name']] ?? $setting['default_value'], $setting['data_type'], false);
        }

        return $settings;
    }

    public function uri(bool $display = false, bool $formatted = false): string
    {
        $uri = ($display) ? $this->display_uri : $this->uri;

        if ($formatted) {
            $uri = __('Global');
        }

        return $uri;
    }

    public function url(): string
    {
        return nel_site_domain()->setting('absolute_url_protocol') . '://' .
            rtrim(nel_site_domain()->setting('site_domain'), '/') . '/' . rtrim($this->uri, '/') . '/';
    }

    public function updateStatistics(): void
    {}

    public function regenCache()
    {
        $query = 'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);

        foreach ($board_ids as $board_id) {
            $board = $this->getDomainFromID($board_id);
            $board->regenCache();
        }
    }

    public function deleteCache()
    {
        $query = 'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);

        foreach ($board_ids as $board_id) {
            $board = $this->getDomainFromID($board_id);
            $board->deleteCache();
        }
    }
}