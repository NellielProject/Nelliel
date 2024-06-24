<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Domains\DomainSite;
use Nelliel\Language\Translator;
use Nelliel\Output\OutputInterstitial;
use Nelliel\Setup\Installer\Installer;
use PDO;
use Nelliel\Account\Session;

class BoardEditor
{
    private NellielPDO $database;
    private DomainSite $site_domain;
    private $reserved_uris = [Domain::SITE, Domain::GLOBAL, NEL_ASSETS_DIR, 'overboard', 'sfw_overboard'];

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
        $this->site_domain = nel_get_cached_domain(Domain::SITE);
    }

    public function create(string $board_uri, array $custom = array()): bool
    {
        $test_domain = Domain::getDomainFromID($board_uri);

        if ($test_domain->exists()) {
            return false;
        }

        $this->validateURI($board_uri);

        if (isset($custom['subdirectories'])) {
            $this->validateSubdirectories($custom['subdirectories']);
        }

        $board_id = $this->generateID($board_uri);
        $display_uri = $board_uri;
        $uri = utf8_strtolower($board_uri);

        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_DOMAIN_REGISTRY_TABLE .
            '" ("domain_id", "uri", "display_uri", "notes") VALUES (:domain_id, :uri, :display_uri, :notes)');

        $prepared->bindValue(':domain_id', $board_id, PDO::PARAM_STR);
        $prepared->bindValue(':uri', $uri, PDO::PARAM_STR);
        $prepared->bindValue(':display_uri', $display_uri, PDO::PARAM_STR);
        $prepared->bindValue(':notes', '', PDO::PARAM_STR);
        $this->database->executePrepared($prepared);

        $db_prefix = $this->generateDBPrefix($board_id);

        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_BOARD_DATA_TABLE .
            '" ("board_id", "db_prefix", "source_directory", "preview_directory", "page_directory", "archive_directory") VALUES (:board_id, :db_prefix, :source_directory, :preview_directory, :page_directory, :archive_directory)');
        $prepared->bindValue(':board_id', $board_id, PDO::PARAM_STR);
        $prepared->bindValue(':db_prefix', $db_prefix, PDO::PARAM_STR);
        $prepared->bindValue(':source_directory', $this->site_domain->setting('default_source_subdirectory'),
            PDO::PARAM_STR);
        $prepared->bindValue(':preview_directory', $this->site_domain->setting('default_preview_subdirectory'),
            PDO::PARAM_STR);
        $prepared->bindValue(':page_directory', $this->site_domain->setting('default_page_subdirectory'), PDO::PARAM_STR);
        $prepared->bindValue(':archive_directory', $this->site_domain->setting('default_archive_subdirectory'),
            PDO::PARAM_STR);
        $this->database->executePrepared($prepared);

        $query = 'SELECT "setting_name", "setting_value", "edit_lock" FROM "' . NEL_BOARD_DEFAULTS_TABLE . '"';
        $defaults = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);
        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_BOARD_CONFIGS_TABLE .
            '" ("board_id", "setting_name", "setting_value", "edit_lock") VALUES (:board_id, :setting_name, :setting_value, :edit_lock)');

        foreach ($defaults as $default) {
            $prepared->bindValue(':board_id', $board_id, PDO::PARAM_STR);
            $prepared->bindValue(':setting_name', $default['setting_name'], PDO::PARAM_STR);
            $prepared->bindValue(':setting_value', $default['setting_value'], PDO::PARAM_STR);
            $prepared->bindValue(':edit_lock', $default['edit_lock'], PDO::PARAM_INT);
            $this->database->executePrepared($prepared);
        }

        $installer = new Installer(nel_utilities()->fileHandler(), new Translator(nel_utilities()->fileHandler()));
        $installer->createBoardTables($this->database, nel_utilities()->sqlCompatibility(), $board_id, $db_prefix);
        $board = Domain::getDomainFromID($board_id);

        if (isset($custom['subdirectories'])) {
            $this->updateSubdirectories($board, $custom['subdirectories']);
        }

        $installer->createBoardDirectories($board->id());
        $regen = new Regen();
        $board->regenCache();
        $regen->boardPages($board);
        nel_get_cached_domain($board->id(), true);
        $session = new Session();
        nel_logger('system')->info('Board ' . $board->uri(true, true) . ' was created.',
            ['event' => 'board_create', 'username' => $session->user()->id()]);
        return true;
    }

    public function updateSubdirectories(DomainBoard $board, array $subdirectories): void
    {
        $this->validateSubdirectories($subdirectories);

        $final = array();
        $final['source'] = $board->reference('source_directory');
        $final['preview'] = $board->reference('preview_directory');
        $final['page'] = $board->reference('page_directory');
        $final['archive'] = $board->reference('archive_directory');

        foreach ($final as $index => $current_name) {
            $new_name = $subdirectories[$index] ?? '';

            if (nel_true_empty($new_name) || $new_name === $current_name) {
                continue;
            }

            $default_setting = $this->site_domain->setting('default_' . $index . '_subdirectory');

            if (nel_true_empty($current_name)) {
                $final[$index] = $default_setting;
            }

            $final[$index] = $new_name;
        }

        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_BOARD_DATA_TABLE .
            '" SET "source_directory" = :source_directory, "preview_directory" = :preview_directory, "page_directory" = :page_directory, "archive_directory" = :archive_directory WHERE "board_id" = :board_id');
        $prepared->bindValue(':board_id', $board->id(), PDO::PARAM_STR);
        $prepared->bindValue(':source_directory', $final['source'], PDO::PARAM_STR);
        $prepared->bindValue(':preview_directory', $final['preview'], PDO::PARAM_STR);
        $prepared->bindValue(':page_directory', $final['page'], PDO::PARAM_STR);
        $prepared->bindValue(':archive_directory', $final['archive'], PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
        $board->reload();
        nel_get_cached_domain($board->id(), true);
    }

    public function validateSubdirectories(array $subdirectories): void
    {
        $max_length = $this->site_domain->setting('max_subdirectory_length');

        foreach ($subdirectories as $index => $name) {
            if (nel_true_empty($name)) {
                continue;
            }

            $default_setting = $this->site_domain->setting('default_' . $index . '_subdirectory');

            if (!$this->site_domain->setting('allow_custom_subdirectories') && $name !== $default_setting) {
                nel_derp(246, __('Custom subdirectory names are not allowed.'));
            }

            if (utf8_strlen($name) > $max_length) {
                nel_derp(247,
                    sprintf(__('One or more of the provided subdirectory names is too long. Maximum %d characters.'),
                        $max_length));
            }

            if ($this->site_domain->setting('only_alphanumeric_subdirectories') &&
                preg_match('/[^a-zA-Z0-9]/', $name) === 1) {
                    nel_derp(248,
                        _gettext(
                            'One or more of the provided subdirectory names contain invalid characters. Must be alphanumeric only.'));
                }
        }
    }

    public function updateURI(DomainBoard $board, string $new_uri): void
    {
        $this->validateURI($new_uri);

        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_DOMAIN_REGISTRY_TABLE .
            '" SET "display_uri" = :display_uri, "uri" = :uri WHERE "domain_id" = :domain_id');
        $prepared->bindValue(':domain_id', $board->id(), PDO::PARAM_STR);
        $prepared->bindValue(':uri', utf8_strtolower($new_uri), PDO::PARAM_STR);
        $prepared->bindValue(':display_uri', $new_uri, PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
        $board->reload();
        nel_get_cached_domain($board->id(), true);
    }

    public function validateURI(string $uri): void
    {
        if (nel_true_empty($uri)) {
            nel_derp(243, __('No board URI provided.'));
        }

        if ($this->site_domain->setting('only_alphanumeric_board_ids')) {
            if (preg_match('/[^a-zA-Z0-9]/', $uri) === 1) {
                nel_derp(242, __('Board URI contains invalid characters. Must be alphanumeric only.'));
            }
        }

        if (preg_match('/\.php$|\.html?$|\.xml$|[[:cntrl:]]/i', $uri) === 1) {
            nel_derp(249, __('Board URI is problematic.'));
        }

        $uri_max = 255;

        if (utf8_strlen($uri) > $uri_max) {
            nel_derp(245, sprintf(__('Board URI is too long. Maximum length is %d characters.'), $uri_max));
        }

        if ($this->reservedURI($uri)) {
            nel_derp(244, __('Board URI is reserved.'));
        }

        $test_domain = Domain::getDomainFromID($uri);

        if ($test_domain->exists()) {
            nel_derp(240, sprintf(__('There is already a board with the URI %s.'), $uri));
        }
    }

    public function reservedURI(string $uri): bool
    {
        $uri_lower = utf8_strtolower($uri);
        $static_found = in_array($uri_lower, array_map('utf8_strtolower', $this->reserved_uris));
        $dynamic_reserved_uris = [$this->site_domain->setting('overboard_uri'),
            $this->site_domain->setting('sfw_overboard_uri')];
        $dynamic_found = in_array($uri_lower, array_map('utf8_strtolower', $dynamic_reserved_uris));
        return $static_found || $dynamic_found;
    }

    private function generateID(string $board_uri): string
    {
        $lower_uri = utf8_strtolower($board_uri);
        $first = preg_replace('/[^[:alpha:]_]/', '', utf8_substr($lower_uri, 0, 1));
        $last = preg_replace('/[^[:alnum:]_]/', '', utf8_substr($lower_uri, 1));

        if ($last === '') {
            $last = nel_random_alphanumeric(8);
        }

        $base_id = utf8_substr($first . $last, 0, 40);
        $suffix = nel_random_alphanumeric(4);
        $test_id = $base_id . '_' . $suffix;
        $final_id = '';

        for ($i = 0; $i <= 20; $i ++) {
            $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
            $result = $this->database->executePreparedFetch($prepared, [$test_id], PDO::FETCH_COLUMN);

            if (!$result) {
                $final_id = $test_id;
                break;
            }

            $suffix = nel_random_alphanumeric(4);
            $test_id = $base_id . '_' . $suffix;
        }

        return $final_id;
    }

    // While most engines can handle unicode, there is potential for issues
    // We also have to account for table name max lengths (especially PostgreSQL's tiny 63 byte limit)
    private function generateDBPrefix(string $board_id): string
    {
        $lower_id = utf8_strtolower($board_id);
        $ascii_prefix = preg_replace('/[^[:alnum:]_]/', '', $lower_id);
        $final_prefix = '';

        if ($ascii_prefix === '') {
            $test_prefix = nel_random_alphanumeric(8);
        } else {
            $test_prefix = utf8_substr($ascii_prefix, 0, 18);
        }

        for ($i = 0; $i <= 10; $i ++) {
            $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "db_prefix" = ?');
            $result = $this->database->executePreparedFetch($prepared, [$test_prefix], PDO::FETCH_COLUMN);

            if ($result) {
                $test_prefix = utf8_substr($test_prefix, -2) . nel_random_alphanumeric(2);
            } else {
                $final_prefix = '_' . $test_prefix;
                break;
            }
        }

        return $final_prefix;
    }

    public function delete(DomainBoard $board): void
    {
        if (!$board->exists()) {
            nel_derp(180, __('Cannot delete a board that doesn\'t exist.'));
        }

        $board_uri = $board->uri(true, true);

        if ($this->database->tableExists($board->reference('uploads_table'))) {
            $this->database->query('DROP TABLE "' . $board->reference('uploads_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$board->reference('uploads_table')]);
        }

        if ($this->database->tableExists($board->reference('posts_table'))) {
            $this->database->query('DROP TABLE "' . $board->reference('posts_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$board->reference('posts_table')]);
        }

        if ($this->database->tableExists($board->reference('threads_table'))) {
            $this->database->query('DROP TABLE "' . $board->reference('threads_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$board->reference('threads_table')]);
        }

        if ($this->database->tableExists($board->reference('archives_table'))) {
            $this->database->query('DROP TABLE "' . $board->reference('archives_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$board->reference('archives_table')]);
        }

        // This should wipe out everything in the board directory
        if (!nel_utilities()->fileHandler()->isCriticalPath($board->reference('base_path'))) {
            nel_utilities()->fileHandler()->eraserGun($board->reference('base_path'));
        }

        $board->deleteCache();

        // Foreign key constraints allow this to handle any removals from site tables
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_DOMAIN_REGISTRY_TABLE . '" WHERE "domain_id" = ?');
        $this->database->executePrepared($prepared, [$board->id()]);
        nel_get_cached_domain($board->id(), true);
        $session = new Session();
        nel_logger('system')->info('Board ' . $board_uri . ' was deleted.',
            ['event' => 'board_delete', 'username' => $session->user()->id()]);
    }

    public function confirmDelete(DomainBoard $board): void
    {
        $messages[] = sprintf(__('You are about to delete the board: %s'), $board->uri(true, true));
        $messages[] = __(
            'Doing this will wipe out all posts, files, archives and settings for this board. All the things get shoved into /dev/null. There is no undo or recovery.');
        $messages[] = __('Are you absolutely sure?');
        $no_info['text'] = __('NOPE. Get me out of here!');
        $no_info['url'] = nel_build_router_url([$this->site_domain->uri(), 'manage-boards']);
        $yes_info['text'] = __('Delete the board');
        $yes_info['url'] = nel_build_router_url([$this->site_domain->uri(), 'manage-boards', $board->uri(), 'delete']);
        $output_interstitial = new OutputInterstitial($this->site_domain, false);
        echo $output_interstitial->confirm([], false, $messages, $yes_info, $no_info);
    }

    public function lock(DomainBoard $board): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_BOARD_DATA_TABLE . '" SET "locked" = 1 WHERE "board_id" = :board_id');
        $prepared->bindValue(':board_id', $board->id(), PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }

    public function unlock(DomainBoard $board): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_BOARD_DATA_TABLE . '" SET "locked" = 0 WHERE "board_id" = :board_id');
        $prepared->bindValue(':board_id', $board->id(), PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }
}