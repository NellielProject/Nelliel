<?php
declare(strict_types = 1);

namespace Nelliel\Domains;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\FileTypes;
use Nelliel\NellielCacheInterface;
use Nelliel\NellielPDO;
use Nelliel\Content\ContentID;
use PDO;

class DomainBoard extends Domain implements NellielCacheInterface
{
    public const DEFAULT_SRC_DIRECTORY = 'source';
    public const DEFAULT_PREVIEW_DIRECTORY = 'preview';
    public const DEFAULT_PAGE_DIRECTORY = 'threads';
    public const DEFAULT_ARCHIVE_DIRECTORY = 'archive';

    public function __construct(string $domain_id, NellielPDO $database)
    {
        $this->domain_id = utf8_strtolower($domain_id);
        $this->database = $database;
        $this->utilitySetup();
        $this->locale();

        if ($this->exists()) {
            $this->templatePath($this->front_end_data->getTemplate($this->setting('template_id'))->getPath());
        }
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
    }

    protected function loadReferences(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $board_data = $this->database->executePreparedFetch($prepared, [$this->domain_id], PDO::FETCH_ASSOC);
        $new_reference = array();
        $board_path = NEL_PUBLIC_PATH . $board_data['board_uri'] . '/';
        $board_web_path = NEL_BASE_WEB_PATH . rawurlencode($board_data['board_uri']) . '/';
        $new_reference['board_directory'] = $board_data['board_uri'];
        $new_reference['board_uri'] = $board_data['board_uri'];
        $new_reference['formatted_board_uri'] = sprintf(nel_site_domain()->setting('uri_display_format'),
            $board_data['board_uri']);
        $title = $new_reference['board_uri'];
        $title .= (!nel_true_empty($this->setting('name')) ? ' - ' . $this->setting('name') : '');
        $new_reference['title'] = $title;
        $new_reference['db_prefix'] = $board_data['db_prefix'];
        $new_reference['locked'] = (bool) $board_data['locked'];
        $new_reference['source_directory'] = $board_data['source_directory'];
        $new_reference['preview_directory'] = $board_data['preview_directory'];
        $new_reference['page_directory'] = $board_data['page_directory'];
        $new_reference['archive_directory'] = $board_data['archive_directory'];
        $new_reference['banners_directory'] = $this->domain_id;
        $new_reference['banners_path'] = NEL_BANNERS_FILES_PATH . $new_reference['banners_directory'] . '/';
        $new_reference['banners_web_path'] = NEL_BANNERS_WEB_PATH . rawurlencode($new_reference['banners_directory']) .
            '/';
        $new_reference['base_path'] = $board_path;
        $new_reference['board_web_path'] = $board_web_path;
        $new_reference['archive_path'] = $board_path . $new_reference['archive_directory'] . '/';
        $new_reference['archive_web_path'] = $board_web_path . rawurlencode($new_reference['archive_directory']) . '/';
        $new_reference['src_path'] = $board_path . $new_reference['source_directory'] . '/';
        $new_reference['src_web_path'] = $board_web_path . rawurlencode($new_reference['source_directory']) . '/';
        $new_reference['archive_src_path'] = $new_reference['archive_path'] . $new_reference['source_directory'] . '/';
        $new_reference['archive_src_web_path'] = $new_reference['archive_web_path'] .
            rawurlencode($new_reference['source_directory']) . '/';
        $new_reference['preview_path'] = $board_path . $new_reference['preview_directory'] . '/';
        $new_reference['preview_web_path'] = $board_web_path . rawurlencode($new_reference['preview_directory']) . '/';
        $new_reference['archive_preview_path'] = $new_reference['archive_path'] . $new_reference['preview_directory'] .
            '/';
        $new_reference['archive_preview_web_path'] = $new_reference['archive_web_path'] .
            rawurlencode($new_reference['preview_directory']) . '/';
        $new_reference['page_path'] = $board_path . $new_reference['page_directory'] . '/';
        $new_reference['page_web_path'] = $board_web_path . rawurlencode($new_reference['page_directory']) . '/';
        $new_reference['archive_page_path'] = $new_reference['archive_path'] . $new_reference['page_directory'] . '/';
        $new_reference['archive_page_web_path'] = $new_reference['archive_web_path'] .
            rawurlencode($new_reference['page_directory']) . '/';
        $new_reference['threads_table'] = $new_reference['db_prefix'] . '_threads';
        $new_reference['posts_table'] = $new_reference['db_prefix'] . '_posts';
        $new_reference['uploads_table'] = $new_reference['db_prefix'] . '_uploads';
        $new_reference['archives_table'] = $new_reference['db_prefix'] . '_archives';
        $new_reference['config_table'] = NEL_BOARD_CONFIGS_TABLE;
        $new_reference['log_table'] = NEL_LOGS_TABLE;
        $this->references = $new_reference;
    }

    protected function loadSettingsFromDatabase(): array
    {
        $settings = array();

        $query = 'SELECT * FROM "' . NEL_SETTINGS_TABLE . '" INNER JOIN "' . NEL_BOARD_CONFIGS_TABLE . '" ON "' .
            NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_BOARD_CONFIGS_TABLE . '"."setting_name" WHERE "' .
            NEL_BOARD_CONFIGS_TABLE . '"."board_id" = ? AND "setting_category" = ?';
        $prepared = $this->database->prepare($query);
        $config_list = $this->database->executePreparedFetchAll($prepared, [$this->domain_id, 'board'],
            PDO::FETCH_ASSOC);

        foreach ($config_list as $config) {
            $config['setting_value'] = nel_typecast($config['setting_value'], $config['data_type']);
            $settings[$config['setting_name']] = $config['setting_value'];
        }

        return $settings;
    }

    public function exists()
    {
        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $board_data = $this->database->executePreparedFetch($prepared, [$this->domain_id], PDO::FETCH_COLUMN);
        return !empty($board_data);
    }

    public function regenCache()
    {
        if (NEL_USE_FILE_CACHE) {
            $this->cacheSettings();
        }
    }

    public function deleteCache()
    {
        if (NEL_USE_FILE_CACHE) {
            $this->file_handler->eraserGun(NEL_CACHE_FILES_PATH . 'domains/' . $this->domain_id);
        }
    }

    public function activeThreads(bool $index_sort): array
    {
        $active_threads = array();

        if ($index_sort) {
            $query = 'SELECT "thread_id" FROM "' . $this->reference('threads_table') .
                '" WHERE "old" = 0 ORDER BY "sticky" DESC, "bump_time" DESC, "bump_time_milli" DESC';
        } else {
            $query = 'SELECT "thread_id" FROM "' . $this->reference('threads_table') . '" WHERE "old" = 0';
        }

        $thread_list = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);

        foreach ($thread_list as $thread) {
            $content_id = new ContentID(ContentID::createIDString(intval($thread)));
            $active_threads[] = $content_id->getInstanceFromID($this);
        }

        return $active_threads;
    }
}