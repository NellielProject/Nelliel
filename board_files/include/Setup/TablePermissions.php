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
        $this->table_name = PERMISSIONS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'permission' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'description' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function setup()
    {
        $this->createTable();
        $this->insertDefaults();
    }

    public function createTable(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            permission      VARCHAR(255) NOT NULL,
            description     VARCHAR(255) NOT NULL DEFAULT ''
        ) " . $options . ";";

        return $this->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['perm_site_config', 'Access to site configuration']);
        $this->insertDefaultRow(['perm_manage_templates', 'Access to the site news']);
        $this->insertDefaultRow(['perm_manage_styles', 'Access to the site news']);
        $this->insertDefaultRow(['perm_manage_icon_sets', 'Modify file filters']);
        $this->insertDefaultRow(['perm_manage_users', 'Manage users']);
        $this->insertDefaultRow(['perm_manage_roles', 'Manage roles']);
        $this->insertDefaultRow(['perm_manage_filetypes', 'Manage filetypes']);
        $this->insertDefaultRow(['perm_extract_gettext', 'Extract Gettext strings']);
        $this->insertDefaultRow(['perm_board_defaults', 'Access to the site news']);
        $this->insertDefaultRow(['perm_board_config_lock_override', 'Override board config lock']);
        $this->insertDefaultRow(['perm_manage_news', 'Access to the site news']);
        $this->insertDefaultRow(['perm_manage_file_filters', 'Modify file filters']);
        $this->insertDefaultRow(['perm_regen_cache', 'Regenerate caches']);
        $this->insertDefaultRow(['perm_regen_pages', 'Regenerate pages']);
        $this->insertDefaultRow(['perm_assign_role', 'Assign roles to a user']);
        $this->insertDefaultRow(['perm_board_create', 'Assign roles to a user']);
        $this->insertDefaultRow(['perm_board_delete', 'Assign roles to a user']);
        $this->insertDefaultRow(['perm_board_transfer', 'Assign roles to a user']);
        $this->insertDefaultRow(['perm_board_lock', 'Assign roles to a user']);
        $this->insertDefaultRow(['perm_board_config', 'Access the Board Settings panel']);
        $this->insertDefaultRow(['perm_manage_bans', 'Extract Gettext strings']);
        $this->insertDefaultRow(['perm_board_sticky_posts', 'Sticky/unsticky posts and threads']);
        $this->insertDefaultRow(['perm_board_lock_posts', 'Lock/unlock threads']);
        $this->insertDefaultRow(['perm_board_post_in_locked', 'Post in locked thread']);
        $this->insertDefaultRow(['perm_board_post_as_staff', 'Post as staff']);
        $this->insertDefaultRow(['perm_board_mod_comment', 'Add staff commentary to a post']);
        $this->insertDefaultRow(['perm_board_delete_posts', 'Add staff commentary to a post']);
        $this->insertDefaultRow(['perm_manage_reports', 'Extract Gettext strings']);
        $this->insertDefaultRow(['perm_mod_mode', 'Can use Moderator Mode']);
        $this->insertDefaultRow(['perm_board_delete_posts', 'Add staff commentary to a post']);
    }
}