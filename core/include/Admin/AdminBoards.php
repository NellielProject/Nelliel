<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Domains\DomainSite;
use Nelliel\Output\OutputPanelManageBoards;
use Nelliel\Setup\Setup;
use Nelliel\Utility\FileHandler;
use PDO;

class AdminBoards extends Admin
{
    private $site_domain;
    private $remove_confirmed = false;
    private $static_reserved_uris = [Domain::SITE, Domain::GLOBAL, NEL_ASSETS_DIR, 'overboard', 'sfw_overboard'];

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->site_domain = new DomainSite($this->database);
        $this->data_table = NEL_BOARD_DATA_TABLE;
        $this->id_field = 'board-id';
        $this->id_column = 'board_id';
        $this->panel_name = _gettext('Manage Boards');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);

        foreach ($inputs['actions'] as $action) {
            switch ($action) {
                case 'lock':
                    $this->lock();
                    break;

                case 'unlock':
                    $this->unlock();
                    break;

                case 'remove-confirmed':
                    $this->remove_confirmed = true;
                    $this->remove();
                    break;
            }
        }
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
        $site_domain = new DomainSite($this->database);
        $board_uri = trim($_POST['new_board_uri']);
        $board_uri_lower = utf8_strtolower($board_uri);

        if (nel_true_empty($board_uri)) {
            nel_derp(243, _gettext('No board URI provided.'));
        }

        if ($site_domain->setting('only_alphanumeric_board_ids')) {
            if (preg_match('/[^a-zA-Z0-9]/', $board_uri) === 1) {
                nel_derp(242, _gettext('Board URI contains invalid characters. Must be alphanumeric only.'));
            }
        }

        $uri_max = 255;

        if (utf8_strlen($board_uri) > $uri_max) {
            nel_derp(245, sprintf(_gettext('Board URI is too long. Maximum length is %d characters.'), $uri_max));
        }

        if ($this->isReservedURI($board_uri)) {
            nel_derp(244, _gettext('Board URI is reserved.'));
        }

        $board_uris = $this->database->executeFetchAll('SELECT "board_uri" FROM "' . $this->data_table . '"',
            PDO::FETCH_COLUMN);
        $uri_exists = in_array($board_uri_lower, array_map('strtolower', $board_uris));

        if ($uri_exists) {
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
        $db_prefix = $this->generateDBPrefix($board_uri_lower);

        if ($board_id === '' || $db_prefix === '') {
            nel_derp(241,
                sprintf(_gettext('Had trouble registering the board URI %s. May want to change it.'), $board_uri));
        }

        $prepared = $this->database->prepare('INSERT INTO "' . NEL_DOMAIN_REGISTRY_TABLE . '" ("domain_id") VALUES (?)');
        $prepared->bindValue(1, $board_id, PDO::PARAM_STR);
        $this->database->executePrepared($prepared);

        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table .
            '" ("board_id", "db_prefix", "board_uri", "source_directory", "preview_directory", "page_directory", "archive_directory") VALUES (?, ?, ?, ?, ?, ?, ?)');
        $prepared->bindValue(1, $board_id, PDO::PARAM_STR);
        $prepared->bindValue(2, $db_prefix, PDO::PARAM_STR);
        $prepared->bindValue(3, $board_uri, PDO::PARAM_STR);
        $prepared->bindValue(4, $final_subdirectories['source'], PDO::PARAM_STR);
        $prepared->bindValue(5, $final_subdirectories['preview'], PDO::PARAM_STR);
        $prepared->bindValue(6, $final_subdirectories['page'], PDO::PARAM_STR);
        $prepared->bindValue(7, $final_subdirectories['archive'], PDO::PARAM_STR);
        $this->database->executePrepared($prepared);

        $setup = new Setup($this->database, nel_utilities()->sqlCompatibility(), new FileHandler());
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

        $setup->createBoardTables($board_id, $db_prefix);
        $setup->createBoardDirectories($board_id);
        $domain = new DomainBoard($board_id, $this->database);
        $regen = new Regen();
        $domain->regenCache();
        $regen->allBoardPages($domain);
        $this->outputMain(true);
    }

    public function editor(): void
    {}

    public function update(): void
    {}

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_boards_delete');
        $board_id = $_GET['board-id'];
        $domain = new DomainBoard($board_id, $this->database);

        if (!$domain->exists()) {
            nel_derp(180, _gettext('Board does not appear to exist.'));
        }

        if (!$this->remove_confirmed) {
            $this->createInterstitial('remove_warning');
            return;
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
        nel_utilities()->fileHandler()->eraserGun($domain->reference('base_path'));
        $domain->deleteCache();
        // Foreign key constraints allow this to handle any removals from site tables
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_DOMAIN_REGISTRY_TABLE . '" WHERE "domain_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_boards_view':
                nel_derp(325, sprintf(_gettext('You do not have access to the %s control panel.'), $this->panel_name));
                break;

            case 'perm_boards_add':
                nel_derp(311, _gettext('You cannot add new boards.'));
                break;

            case 'perm_boards_modify':
                nel_derp(312, _gettext('You cannot modify existing boards.'));
                break;

            case 'perm_boards_delete':
                nel_derp(313, _gettext('You cannot delete existing boards.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    public function unlock()
    {
        $this->verifyPermissions($this->domain, 'perm_boards_modify');
        $board_id = $_GET['board-id'] ?? '';
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "locked" = 0 WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
        $this->outputMain(true);
    }

    public function lock()
    {
        $this->verifyPermissions($this->domain, 'perm_boards_modify');
        $board_id = $_GET['board-id'] ?? '';
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "locked" = 1 WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
        $this->outputMain(true);
    }

    private function createInterstitial(string $which)
    {
        $output_panel = new OutputPanelManageBoards($this->domain, false);

        switch ($which) {
            case 'remove_warning':
                $this->verifyPermissions($this->domain, 'perm_boards_delete');
                $output_panel->removeWarning([], false);
                break;
        }

        $this->outputMain(false);
    }

    private function generateBoardID(string $board_uri): string
    {
        $test_id = $board_uri;
        $base_id = utf8_substr($test_id, 0, 45);
        $final_id = '';

        for ($i = 0; $i <= 20; $i ++) {
            $prepared = $this->database->prepare('SELECT 1 FROM "' . $this->data_table . '" WHERE "board_id" = ?');
            $result = $this->database->executePreparedFetch($prepared, [utf8_strtolower($test_id)], PDO::FETCH_COLUMN);

            if (!$result) {
                $final_id = $test_id;
                break;
            }

            $test_id = $base_id . '_' . nel_random_alphanumeric(4);
        }

        return $final_id;
    }

    // While most engines can handle unicode, there is potential for issues
    // We also have to account for table name max lengths (especially PostgreSQL's tiny 63 byte limit)
    private function generateDBPrefix(string $board_uri): string
    {
        $ascii_prefix = preg_replace('/[^a-zA-Z0-9_]/', '', $board_uri);
        $final_prefix = '';

        for ($i = 0; $i <= 10; $i ++) {
            if (utf8_strlen($ascii_prefix) <= 0) {
                $test_prefix = '_' . nel_random_alphanumeric(8);
            } else {
                $truncated_prefix = utf8_substr($ascii_prefix, 0, 18);
                $test_prefix = '_' . utf8_strtolower($truncated_prefix);
            }

            $prepared = $this->database->prepare('SELECT 1 FROM "' . $this->data_table . '" WHERE "db_prefix" = ?');
            $result = $this->database->executePreparedFetch($prepared, [$test_prefix], PDO::FETCH_COLUMN);

            if (!$result) {
                $final_prefix = $test_prefix;
                break;
            }
        }

        return $final_prefix;
    }

    public function isReservedURI(string $uri): bool
    {
        $uri_lower = utf8_strtolower($uri);
        $static_found = in_array($uri_lower, array_map('utf8_strtolower', $this->static_reserved_uris));
        $dynamic_reserved_uris = [$this->site_domain->setting('overboard_uri'),
            $this->site_domain->setting('sfw_overboard_uri')];
        $dynamic_found = in_array($uri_lower, array_map('utf8_strtolower', $dynamic_reserved_uris));
        return $static_found || $dynamic_found;
    }
}
