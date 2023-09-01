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
        $settings = $this->cache_handler->loadArrayFromFile('domain_settings', 'domain_settings.php',
            'domains/' . $this->domain_id);

        if (empty($settings)) {
            $settings = $this->loadSettingsFromDatabase();
            $this->cache_handler->writeArrayToFile('domain_settings', $settings, 'domain_settings.php',
                'domains/' . $this->domain_id);
        }

        $this->settings = $settings;
        $this->updateLocale($this->setting('locale'));
    }

    protected function loadReferences(): void
    {
        $this->references['board_uri'] = Domain::GLOBAL;
    }

    protected function loadSettingsFromDatabase(): array
    {
        $settings = array();
        $query = 'SELECT * FROM "' . NEL_SETTINGS_TABLE . '" INNER JOIN "' . NEL_BOARD_DEFAULTS_TABLE . '" ON "' .
            NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_BOARD_DEFAULTS_TABLE .
            '"."setting_name" WHERE "setting_category" = \'board\'';
        $config_list = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);

        foreach ($config_list as $config) {
            $config['setting_value'] = nel_typecast($config['setting_value'], $config['data_type']);
            $settings[$config['setting_name']] = $config['setting_value'];
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

    public function updateStatistics(): void
    {}

    public function regenCache()
    {
        $query = 'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);

        foreach ($board_ids as $board_id) {
            $board = new DomainBoard($board_id, $this->database);
            $board->regenCache();
        }
    }

    public function deleteCache()
    {
        $query = 'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);

        foreach ($board_ids as $board_id) {
            $board = new DomainBoard($board_id, $this->database);
            $board->deleteCache();
        }
    }
}