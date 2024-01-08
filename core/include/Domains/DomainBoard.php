<?php
declare(strict_types = 1);

namespace Nelliel\Domains;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielCacheInterface;
use Nelliel\Content\ContentID;
use Nelliel\Content\Thread;
use Nelliel\Database\NellielPDO;
use PDO;

class DomainBoard extends Domain implements NellielCacheInterface
{
    public const DEFAULT_SRC_DIRECTORY = 'source';
    public const DEFAULT_PREVIEW_DIRECTORY = 'preview';
    public const DEFAULT_PAGE_DIRECTORY = 'threads';
    public const DEFAULT_ARCHIVE_DIRECTORY = 'archive';

    public function __construct(string $domain_id, NellielPDO $database)
    {
        parent::__construct($domain_id, $database);

        if ($this->exists()) {
            $this->templatePath($this->front_end_data->getTemplate($this->setting('template_id'))->getPath());
        }
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
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $board_data = $this->database->executePreparedFetch($prepared, [$this->domain_id], PDO::FETCH_ASSOC);
        $new_reference = array();
        $board_path = NEL_PUBLIC_PATH . $this->uri . '/';
        $board_web_path = NEL_BASE_WEB_PATH . rawurlencode($this->uri) . '/';
        $new_reference['board_directory'] = $this->uri;
        $new_reference['board_uri'] = $this->uri;
        $new_reference['formatted_board_uri'] = sprintf(nel_site_domain()->setting('uri_display_format'), $this->uri);
        $title = $new_reference['board_uri'];
        $title .= (!nel_true_empty($this->setting('name')) ? ' - ' . $this->setting('name') : '');
        $new_reference['title'] = $title;
        $new_reference['db_prefix'] = $board_data['db_prefix'];
        $new_reference['locked'] = (bool) $board_data['locked'];
        $new_reference['source_directory'] = $board_data['source_directory'];
        $new_reference['preview_directory'] = $board_data['preview_directory'];
        $new_reference['page_directory'] = $board_data['page_directory'];
        $new_reference['archive_directory'] = $board_data['archive_directory'];
        $new_reference['banners_directory'] = $this->uri;
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
        $new_reference['log_table'] = NEL_SYSTEM_LOGS_TABLE;
        $this->references = $new_reference;
    }

    protected function loadSettingsFromDatabase(): array
    {
        $settings = array();
        $settings_list = $this->database->executeFetchAll(
            'SELECT "setting_name", "default_value", "data_type" FROM "' . NEL_SETTINGS_TABLE .
            '" WHERE "setting_category" = \'board\'', PDO::FETCH_ASSOC);
        $prepared = $this->database->prepare(
            'SELECT "setting_name", "setting_value" FROM "' . NEL_BOARD_CONFIGS_TABLE . '" WHERE "board_id" = :board_id');
        $prepared->bindValue(':board_id', $this->domain_id, PDO::PARAM_STR);
        $config_list = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_KEY_PAIR);

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
            $uri = sprintf(nel_site_domain()->setting('uri_display_format'), $uri);
        }

        return $uri;
    }

    public function updateStatistics(): void
    {
        $limit = time() - nel_site_domain()->setting('min_time_between_board_stat_updates');

        if ($this->statistics->get($this, 'last_update') > $limit) {
            return;
        }

        $thread_count = (int) $this->database->executeFetch(
            'SELECT COUNT(*) FROM "' . $this->reference('threads_table') . '"', PDO::FETCH_COLUMN);
        $this->statistics->update($this, 'thread_count', $thread_count);
        $post_count = (int) $this->database->executeFetch(
            'SELECT COUNT(*) FROM "' . $this->reference('posts_table') . '"', PDO::FETCH_COLUMN);
        $this->statistics->update($this, 'post_count', $post_count);
        $all_time_post_count = (int) $this->database->executeFetch(
            'SELECT MAX("post_number") FROM "' . $this->reference('posts_table') . '"', PDO::FETCH_COLUMN);
        $this->statistics->update($this, 'all_time_post_count', $all_time_post_count);
        $posts_per_month = (int) $this->database->executeFetch(
            'SELECT COUNT(*) FROM "' . $this->reference('posts_table') . '" WHERE "post_time" >= ' .
            (time() - (3600 * 24 * 30)) . '', PDO::FETCH_COLUMN);
        $this->statistics->update($this, 'posts_per_month', $posts_per_month);
        $posts_per_day = (int) $this->database->executeFetch(
            'SELECT COUNT(*) FROM "' . $this->reference('posts_table') . '" WHERE "post_time" >= ' .
            (time() - (3600 * 24)) . '', PDO::FETCH_COLUMN);
        $this->statistics->update($this, 'posts_per_day', $posts_per_day);
        $posts_per_hour = (int) $this->database->executeFetch(
            'SELECT COUNT(*) FROM "' . $this->reference('posts_table') . '" WHERE "post_time" >= ' . (time() - 3600) . '',
            PDO::FETCH_COLUMN);
        $this->statistics->update($this, 'posts_per_hour', $posts_per_hour);
        $file_count = (int) $this->database->executeFetch(
            'SELECT COUNT(*) FROM "' . $this->reference('uploads_table') . '" WHERE "filesize" > 0', PDO::FETCH_COLUMN);
        $this->statistics->update($this, 'file_count', $file_count);
        $total_filesize = (int) $this->database->executeFetch(
            'SELECT SUM("filesize") FROM "' . $this->reference('uploads_table') . '"', PDO::FETCH_COLUMN);
        $this->statistics->update($this, 'total_filesize', $total_filesize);
        $this->statistics->update($this, 'last_update', time());
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

    public function recentPosts(int $limit): array
    {
        $recent_posts = array();

        $prepared = $this->database->prepare(
            'SELECT "post_number", "parent_thread" FROM "' . $this->reference('posts_table') .
            '" ORDER BY "post_time" DESC, "post_time_milli" DESC LIMIT ?');
        $post_list = $this->database->executePreparedFetchAll($prepared, [$limit], PDO::FETCH_ASSOC);

        foreach ($post_list as $post) {
            $content_id = new ContentID(
                ContentID::createIDString(intval($post['parent_thread']), intval($post['post_number'])));
            $recent_posts[] = $content_id->getInstanceFromID($this);
        }

        return $recent_posts;
    }

    public function getThread(int $thread_id): Thread
    {
        $content_id = new ContentID(ContentID::createIDString($thread_id));
        return $content_id->getInstanceFromID($this);
    }
}