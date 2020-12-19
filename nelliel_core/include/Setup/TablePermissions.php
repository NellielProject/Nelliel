<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TablePermissions extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_PERMISSIONS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'permission' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'description' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            permission      VARCHAR(50) NOT NULL UNIQUE,
            description     TEXT NOT NULL,
            moar            TEXT DEFAULT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['perm_manage_bans', 'Manage bans']);
        $this->insertDefaultRow(['perm_manage_boards', 'Manage boards']);
        $this->insertDefaultRow(['perm_manage_board_defaults', 'View and modify board default values']);
        $this->insertDefaultRow(['perm_manage_filetypes', 'Manage filetypes']);
        $this->insertDefaultRow(['perm_manage_file_filters', 'Manage file filters']);
        $this->insertDefaultRow(['perm_manage_icon_sets', 'Manage icon sets']);
        $this->insertDefaultRow(['perm_manage_logs', 'Access to the logs']);
        $this->insertDefaultRow(['perm_manage_news', 'Access to the news']);
        $this->insertDefaultRow(['perm_manage_permissions', 'Add or remove permissions']);
        $this->insertDefaultRow(['perm_manage_reports', 'Manage reports']);
        $this->insertDefaultRow(['perm_manage_roles', 'Manage roles']);
        $this->insertDefaultRow(['perm_manage_site_config', 'Access to site configuration']);
        $this->insertDefaultRow(['perm_manage_styles', 'Manage styles']);
        $this->insertDefaultRow(['perm_manage_templates', 'Manage templates']);
        $this->insertDefaultRow(['perm_manage_users', 'Manage users']);
        $this->insertDefaultRow(['perm_board_config', 'Access the Board Settings panel']);
        $this->insertDefaultRow(['perm_board_config_lock_override', 'Override board config lock']);
        $this->insertDefaultRow(['perm_board_create', 'Create a board']);
        $this->insertDefaultRow(['perm_board_delete', 'Delete a board']);
        $this->insertDefaultRow(['perm_board_delete_posts', 'Delete posts and threads']);
        $this->insertDefaultRow(['perm_board_lock', 'Lock a board']);
        $this->insertDefaultRow(['perm_board_lock_posts', 'Lock/unlock posts and threads']);
        $this->insertDefaultRow(['perm_board_mod_comment', 'Add staff commentary to a post']);
        $this->insertDefaultRow(['perm_board_mod_mode', 'Access to Moderator Mode']);
        $this->insertDefaultRow(['perm_board_post_as_staff', 'Post as staff (displays capcode)']);
        $this->insertDefaultRow(['perm_board_post_in_locked', 'Post in locked thread']);
        $this->insertDefaultRow(['perm_board_sticky_posts', 'Sticky/unsticky posts and threads']);
        $this->insertDefaultRow(['perm_board_transfer', 'Transfer board ownership']);
        $this->insertDefaultRow(['perm_extract_gettext', 'Extract Gettext strings']);
        $this->insertDefaultRow(['perm_regen_cache', 'Regenerate caches']);
        $this->insertDefaultRow(['perm_regen_pages', 'Regenerate pages']);
        $this->insertDefaultRow(['perm_view_unhashed_ip', 'View unhashed IP addresses']);
    }
}