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

        foreach ($inputs['actions'] as $action)
        {
            switch ($action)
            {
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
    {
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_boards_add');
        $site_domain = new DomainSite($this->database);
        $board_uri = trim($_POST['new_board_uri']);

        if (nel_true_empty($board_uri))
        {
            nel_derp(243, _gettext('No board URI provided.'));
        }

        if ($site_domain->setting('only_alphanumeric_board_ids'))
        {
            if (preg_match('/[^a-zA-Z0-9]/', $board_uri) === 1)
            {
                nel_derp(242, _gettext('Board URI contains invalid characters. Must be alphanumeric only.'));
            }
        }

        $uri_max = 255;

        if (utf8_strlen($board_uri) > $uri_max)
        {
            nel_derp(245, sprintf(_gettext('Board URI is too long. Maximum length is %d characters.'), $uri_max));
        }

        if ($board_uri === Domain::SITE || $board_uri === Domain::GLOBAL || $board_uri === NEL_ASSETS_DIR ||
                $board_uri === NEL_CORE_DIRECTORY || $board_uri === $site_domain->setting('overboard_uri') ||
                $board_uri === $site_domain->setting('sfw_overboard_uri') || $board_uri === NEL_DOCUMENTATION_DIR ||
                $board_uri === NEL_ASSETS_DIR)
        {
            nel_derp(244, _gettext('Board URI is reserved.'));
        }

        $prepared = $this->database->prepare('SELECT 1 FROM "' . $this->data_table . '" WHERE "board_uri" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$board_uri], PDO::FETCH_COLUMN);

        if ($result)
        {
            nel_derp(240, sprintf(_gettext('There is already a board with the URI %s.'), $board_uri));
        }

        $directory_max = 255;
        $src_directory = DomainBoard::DEFAULT_SRC_DIRECTORY;
        $preview_directory = DomainBoard::DEFAULT_PREVIEW_DIRECTORY;
        $page_directory = DomainBoard::DEFAULT_PAGE_DIRECTORY;
        $archive_directory = DomainBoard::DEFAULT_ARCHIVE_DIRECTORY;

        if ($this->site_domain->setting('allow_custom_directories'))
        {
            $src_directory = trim($_POST['new_board_src'] ?? $src_directory);
            $preview_directory = trim($_POST['new_board_preview'] ?? $preview_directory);
            $page_directory = trim($_POST['new_board_page'] ?? $page_directory);
            $archive_directory = trim($_POST['new_board_archive'] ?? $archive_directory);
        }

        if ($site_domain->setting('only_alphanumeric_directories'))
        {
            if (preg_match('/[^a-zA-Z0-9]/', $src_directory) === 1 ||
                    preg_match('/[^a-zA-Z0-9]/', $preview_directory) === 1 ||
                    preg_match('/[^a-zA-Z0-9]/', $page_directory) === 1 ||
                    preg_match('/[^a-zA-Z0-9]/', $archive_directory) === 1)
            {
                nel_derp(248,
                        _gettext(
                                'One or more of the provided subdirectory names contains invalid characters. Must be alphanumeric only.'));
            }
        }

        if (nel_true_empty($src_directory) || nel_true_empty($preview_directory) || nel_true_empty($page_directory) ||
                nel_true_empty($archive_directory))
        {
            nel_derp(246, _gettext('One or more of the provided subdirectory names is empty.'));
        }

        if (utf8_strlen($src_directory > 255) || utf8_strlen($preview_directory > 255) ||
                utf8_strlen($page_directory > 255) || utf8_strlen($archive_directory > 255))
        {
            nel_derp(247,
                    sprintf(
                            _gettext(
                                    'One or more of the provided subdirectory names is too long. Maximum %d characters.'),
                            $directory_max));
        }

        $board_id = $this->generateBoardID($board_uri);
        $db_prefix = $this->generateDBPrefix($board_uri);

        if ($board_id === '' || $db_prefix === '')
        {
            nel_derp(241,
                    sprintf(_gettext('Had trouble registering the board URI %s. May want to change it.'), $board_uri));
        }

        $hashed_board_id = hash('sha256', $board_id);
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_DOMAIN_REGISTRY_TABLE . '" ("domain_id", "hashed_domain_id") VALUES (?, ?)');
        $prepared->bindValue(1, $board_id, PDO::PARAM_STR);
        $prepared->bindValue(2, $hashed_board_id, PDO::PARAM_STR);
        $this->database->executePrepared($prepared);

        $prepared = $this->database->prepare(
                'INSERT INTO "' . $this->data_table .
                '" ("board_id", "db_prefix", "board_uri", "src_directory", "preview_directory", "page_directory", "archive_directory") VALUES (?, ?, ?, ?, ?, ?, ?)');
        $prepared->bindValue(1, $board_id, PDO::PARAM_STR);
        $prepared->bindValue(2, $db_prefix, PDO::PARAM_STR);
        $prepared->bindValue(3, $board_uri, PDO::PARAM_STR);
        $prepared->bindValue(4, $src_directory, PDO::PARAM_STR);
        $prepared->bindValue(5, $preview_directory, PDO::PARAM_STR);
        $prepared->bindValue(6, $page_directory, PDO::PARAM_STR);
        $prepared->bindValue(7, $archive_directory, PDO::PARAM_STR);
        $this->database->executePrepared($prepared);

        $setup = new Setup($this->database, nel_utilities()->sqlCompatibility(), new FileHandler());
        $query = 'SELECT "setting_name", "setting_value", "edit_lock" FROM "' . NEL_BOARD_DEFAULTS_TABLE . '"';
        $defaults = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_BOARD_CONFIGS_TABLE .
                '" ("board_id", "setting_name", "setting_value", "edit_lock") VALUES (?, ?, ?, ?)');

        foreach ($defaults as $default)
        {
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
    {
    }

    public function update(): void
    {
    }

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_boards_delete');
        $board_id = $_GET['board-id'];
        $domain = new DomainBoard($board_id, $this->database);

        if (!$domain->exists())
        {
            nel_derp(160, _gettext('Board does not appear to exist.'));
        }

        if (!$this->remove_confirmed)
        {
            $this->createInterstitial('remove_warning');
            return;
        }

        if ($this->database->tableExists($domain->reference('uploads_table'), NEL_SQLTYPE))
        {
            $this->database->query('DROP TABLE "' . $domain->reference('uploads_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('uploads_table')]);
        }

        if ($this->database->tableExists($domain->reference('posts_table'), NEL_SQLTYPE))
        {
            $this->database->query('DROP TABLE "' . $domain->reference('posts_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('posts_table')]);
        }

        if ($this->database->tableExists($domain->reference('threads_table'), NEL_SQLTYPE))
        {
            $this->database->query('DROP TABLE "' . $domain->reference('threads_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('threads_table')]);
        }

        if ($this->database->tableExists($domain->reference('archives_table'), NEL_SQLTYPE))
        {
            $this->database->query('DROP TABLE "' . $domain->reference('archives_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('archives_table')]);
        }

        // This should wipe out everything in the board directory
        nel_utilities()->fileHandler()->eraserGun($domain->reference('board_path'));
        $domain->deleteCache();
        // Foreign key constraints allow this to handle any removals from site tables
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_DOMAIN_REGISTRY_TABLE . '" WHERE "domain_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm))
        {
            return;
        }

        switch ($perm)
        {
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
        $board_id = $_GET['board_id'];
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

        switch ($which)
        {
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

        for ($i = 0; $i <= 10; $i ++)
        {
            $prepared = $this->database->prepare('SELECT 1 FROM "' . $this->data_table . '" WHERE "board_id" = ?');
            $result = $this->database->executePreparedFetch($prepared, [$test_id], PDO::FETCH_COLUMN);

            if (!$result)
            {
                $final_id = $test_id;
                break;
            }

            $test_id = $base_id . '_' . nel_random_alphanumeric(4);
        }

        return $final_id;
    }

    // While most engines can handle unicode, there is potential for issues
    // We also have to account for table name max lengths (especially postgresql's tiny 63 byte limit)
    private function generateDBPrefix(string $board_uri): string
    {
        $ascii_prefix = preg_replace('/[^a-zA-Z0-9_]/', '', $board_uri);
        $final_prefix = '';

        for ($i = 0; $i <= 10; $i ++)
        {
            if (strlen($ascii_prefix) <= 0)
            {
                $test_prefix = '_board_' . nel_random_alphanumeric(8);
            }
            else
            {
                $truncated_prefix = substr($ascii_prefix, 0, 12);
                $test_prefix = '_board_' . strtolower($truncated_prefix);
            }

            $prepared = $this->database->prepare('SELECT 1 FROM "' . $this->data_table . '" WHERE "db_prefix" = ?');
            $result = $this->database->executePreparedFetch($prepared, [$test_prefix], PDO::FETCH_COLUMN);

            if (!$result)
            {
                $final_prefix = $test_prefix;
                break;
            }
        }

        return $final_prefix;
    }
}
