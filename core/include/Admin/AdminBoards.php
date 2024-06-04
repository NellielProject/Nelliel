<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Language\Translator;
use Nelliel\Output\OutputInterstitial;
use Nelliel\Output\OutputPanelManageBoards;
use Nelliel\Setup\Installer\Installer;
use PDO;

class AdminBoards extends Admin
{
    private $site_domain;
    private $remove_confirmed = false;
    private $reserved_uris = [Domain::SITE, Domain::GLOBAL, NEL_ASSETS_DIR, 'overboard', 'sfw_overboard'];

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->site_domain = Domain::getDomainFromID(Domain::SITE);
        $this->data_table = NEL_BOARD_DATA_TABLE;
        $this->id_column = 'board_id';
        $this->panel_name = _gettext('Manage Boards');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_boards_view');
        $output_panel = new OutputPanelManageBoards($this->site_domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {}

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_boards_add');
        $board_uri = trim($_POST['new_board_uri']);
        $board_uri_lower = utf8_strtolower($board_uri);

        if (nel_true_empty($board_uri)) {
            nel_derp(243, _gettext('No board URI provided.'));
        }

        if ($this->site_domain->setting('only_alphanumeric_board_ids')) {
            if (preg_match('/[^a-zA-Z0-9]/', $board_uri) === 1) {
                nel_derp(242, _gettext('Board URI contains invalid characters. Must be alphanumeric only.'));
            }
        }

        if (preg_match('/\.php$|\.html?$|\.xml$|[[:cntrl:]]/i', $board_uri) === 1) {
            nel_derp(249, _gettext('Board URI is problematic.'));
        }

        $uri_max = 255;

        if (utf8_strlen($board_uri) > $uri_max) {
            nel_derp(245, sprintf(_gettext('Board URI is too long. Maximum length is %d characters.'), $uri_max));
        }

        if ($this->isReservedURI($board_uri)) {
            nel_derp(244, _gettext('Board URI is reserved.'));
        }

        $test_domain = Domain::getDomainFromID($board_uri);

        if ($test_domain->exists()) {
            nel_derp(240, sprintf(_gettext('There is already a board with the URI %s.'), $board_uri));
        }

        $final_subdirectories = array();
        $final_subdirectories['source'] = DomainBoard::DEFAULT_SRC_DIRECTORY;
        $final_subdirectories['preview'] = DomainBoard::DEFAULT_PREVIEW_DIRECTORY;
        $final_subdirectories['page'] = DomainBoard::DEFAULT_PAGE_DIRECTORY;
        $final_subdirectories['archive'] = DomainBoard::DEFAULT_ARCHIVE_DIRECTORY;
        $custom_subdirectories = array();
        $custom_subdirectories['source'] = trim($_POST['new_board_src'] ?? '');
        $custom_subdirectories['preview'] = trim($_POST['new_board_preview'] ?? '');
        $custom_subdirectories['page'] = trim($_POST['new_board_page'] ?? '');
        $custom_subdirectories['archive'] = trim($_POST['new_board_archive'] ?? '');

        foreach ($custom_subdirectories as $index => $name) {
            $default_setting = $this->site_domain->setting('default_' . $index . '_subdirectory');

            if ($this->site_domain->setting('allow_custom_subdirectories') && $name !== $default_setting) {
                if (nel_true_empty($name)) {
                    nel_derp(246, _gettext('One or more of the provided subdirectory names is empty.'));
                }

                if (utf8_strlen($name) > $this->site_domain->setting('max_subdirectory_length')) {
                    nel_derp(247,
                        sprintf(
                            _gettext(
                                'One or more of the provided subdirectory names is too long. Maximum %d characters.'),
                            $this->site_domain->setting('max_subdirectory_length')));
                }

                if ($this->site_domain->setting('only_alphanumeric_subdirectories') &&
                    preg_match('/[^a-zA-Z0-9]/', $name) === 1) {
                    nel_derp(248,
                        _gettext(
                            'One or more of the provided subdirectory names contains invalid characters. Must be alphanumeric only.'));
                }

                $final_subdirectories[$index] = $name;
            } else {
                if (!nel_true_empty($default_setting)) {
                    $final_subdirectories[$index] = $default_setting;
                }
            }
        }

        $board_id = $this->generateBoardID($board_uri_lower);
        $db_prefix = $this->generateDBPrefix($board_id);
        $notes = '';

        if ($board_id === '' || $db_prefix === '') {
            nel_derp(241,
                sprintf(_gettext('Had trouble registering the board URI %s. May want to change it.'), $board_uri));
        }

        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_DOMAIN_REGISTRY_TABLE .
            '" ("domain_id", "uri", "display_uri", "notes") VALUES (?, ?, ?, ?)');
        $prepared->bindValue(1, $board_id, PDO::PARAM_STR);
        $prepared->bindValue(2, $board_uri_lower, PDO::PARAM_STR);
        $prepared->bindValue(3, $board_uri, PDO::PARAM_STR);
        $prepared->bindValue(4, $notes, PDO::PARAM_STR);
        $this->database->executePrepared($prepared);

        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table .
            '" ("board_id", "db_prefix", "source_directory", "preview_directory", "page_directory", "archive_directory") VALUES (?, ?, ?, ?, ?, ?)');
        $prepared->bindValue(1, $board_id, PDO::PARAM_STR);
        $prepared->bindValue(2, $db_prefix, PDO::PARAM_STR);
        $prepared->bindValue(3, $final_subdirectories['source'], PDO::PARAM_STR);
        $prepared->bindValue(4, $final_subdirectories['preview'], PDO::PARAM_STR);
        $prepared->bindValue(5, $final_subdirectories['page'], PDO::PARAM_STR);
        $prepared->bindValue(6, $final_subdirectories['archive'], PDO::PARAM_STR);
        $this->database->executePrepared($prepared);

        $installer = new Installer(nel_utilities()->fileHandler(), new Translator(nel_utilities()->fileHandler()));
        $query = 'SELECT "setting_name", "setting_value", "edit_lock" FROM "' . NEL_BOARD_DEFAULTS_TABLE . '"';
        $defaults = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);
        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_BOARD_CONFIGS_TABLE .
            '" ("board_id", "setting_name", "setting_value", "edit_lock") VALUES (?, ?, ?, ?)');

        foreach ($defaults as $default) {
            $prepared->bindValue(1, $board_id, PDO::PARAM_STR);
            $prepared->bindValue(2, $default['setting_name'], PDO::PARAM_STR);
            $prepared->bindValue(3, $default['setting_value'], PDO::PARAM_STR);
            $prepared->bindValue(4, $default['edit_lock'], PDO::PARAM_INT);
            $this->database->executePrepared($prepared);
        }

        $installer->createBoardTables($this->database, nel_utilities()->sqlCompatibility(), $board_id, $db_prefix);
        $installer->createBoardDirectories($board_id);
        $domain = Domain::getDomainFromID($board_id);
        $regen = new Regen();
        $domain->regenCache();
        $regen->boardPages($domain);
        nel_logger('system')->info('Board ' . $domain->uri(true, true) . ' was created.', ['event' => 'board_create', 'username' => $this->session_user->id()]);
        $this->panel();
    }

    public function editor(): void
    {}

    public function update(): void
    {}

    public function delete(string $board_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_boards_delete');
        $domain = Domain::getDomainFromID($board_id);
        $board_uri = $domain->uri(true, true);

        if (!$domain->exists() || !($domain instanceof DomainBoard)) {
            nel_derp(180, _gettext('Not a board ID or board does not exist.'));
        }

        if ($this->database->tableExists($domain->reference('uploads_table'))) {
            $this->database->query('DROP TABLE "' . $domain->reference('uploads_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('uploads_table')]);
        }

        if ($this->database->tableExists($domain->reference('posts_table'))) {
            $this->database->query('DROP TABLE "' . $domain->reference('posts_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('posts_table')]);
        }

        if ($this->database->tableExists($domain->reference('threads_table'))) {
            $this->database->query('DROP TABLE "' . $domain->reference('threads_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('threads_table')]);
        }

        if ($this->database->tableExists($domain->reference('archives_table'))) {
            $this->database->query('DROP TABLE "' . $domain->reference('archives_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('archives_table')]);
        }

        // This should wipe out everything in the board directory
        if (!nel_utilities()->fileHandler()->isCriticalPath($domain->reference('base_path'))) {
            nel_utilities()->fileHandler()->eraserGun($domain->reference('base_path'));
        }

        $domain->deleteCache();
        // Foreign key constraints allow this to handle any removals from site tables
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_DOMAIN_REGISTRY_TABLE . '" WHERE "domain_id" = ?');
        $this->database->executePrepared($prepared, [$domain->id()]);
        nel_logger('system')->info('Board ' . $board_uri . ' was deleted.', ['event' => 'board_delete', 'username' => $this->session_user->id()]);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_boards_view':
                nel_derp(325, sprintf(_gettext('You do not have access to the %s control panel.'), $this->panel_name),
                    403);
                break;

            case 'perm_boards_add':
                nel_derp(311, _gettext('You cannot add new boards.'), 403);
                break;

            case 'perm_boards_modify':
                nel_derp(312, _gettext('You cannot modify existing boards.'), 403);
                break;

            case 'perm_boards_delete':
                nel_derp(313, _gettext('You cannot delete existing boards.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    public function unlock(string $board_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_boards_modify');
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "locked" = 0 WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
        $this->panel();
    }

    public function lock(string $board_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_boards_modify');
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "locked" = 1 WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
        $this->panel();
    }

    public function confirmDelete(string $board_id): void
    {
        $messages[] = sprintf(__('You are about to delete the board: %s'), $board_id);
        $messages[] = __(
            'Doing this will wipe out all posts, files, archives and settings for this board. All the things get shoved into /dev/null. There is no undo or recovery.');
        $messages[] = __('Are you absolutely sure?');
        $no_info['text'] = __('NOPE. Get me out of here!');
        $no_info['url'] = nel_build_router_url([$this->domain->uri(), 'manage-boards']);
        $yes_info['text'] = __('Delete the board');
        $yes_info['url'] = nel_build_router_url([$this->domain->uri(), 'manage-boards', $board_id, 'delete']);
        $output_interstitial = new OutputInterstitial($this->domain, false);
        echo $output_interstitial->confirm([], false, $messages, $yes_info, $no_info);
    }

    private function generateBoardID(string $board_uri): string
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
            $prepared = $this->database->prepare('SELECT 1 FROM "' . $this->data_table . '" WHERE "board_id" = ?');
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
            $prepared = $this->database->prepare('SELECT 1 FROM "' . $this->data_table . '" WHERE "db_prefix" = ?');
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

    public function isReservedURI(string $uri): bool
    {
        $uri_lower = utf8_strtolower($uri);
        $static_found = in_array($uri_lower, array_map('utf8_strtolower', $this->reserved_uris));
        $dynamic_reserved_uris = [$this->site_domain->setting('overboard_uri'),
            $this->site_domain->setting('sfw_overboard_uri')];
        $dynamic_found = in_array($uri_lower, array_map('utf8_strtolower', $dynamic_reserved_uris));
        return $static_found || $dynamic_found;
    }
}
