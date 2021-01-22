<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TablePermissions extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_PERMISSIONS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'permission' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'perm_description' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry               " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            permission          VARCHAR(50) NOT NULL UNIQUE,
            perm_description    TEXT NOT NULL,
            moar                TEXT DEFAULT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['perm_manage_bans', 'Manage bans']);
        $this->insertDefaultRow(['perm_manage_boards', 'Manage boards']);
        $this->insertDefaultRow(['perm_manage_board_config', 'Manage board configuration']);
        $this->insertDefaultRow(['perm_manage_board_config_override', 'Override board config lock']);
        $this->insertDefaultRow(['perm_manage_board_defaults', 'Manage board default config']);
        $this->insertDefaultRow(['perm_manage_filetypes', 'Manage filetypes']);
        $this->insertDefaultRow(['perm_manage_file_filters', 'Manage file filters']);
        $this->insertDefaultRow(['perm_manage_icon_sets', 'Manage icon sets']);
        $this->insertDefaultRow(['perm_manage_ifthens', 'Manage if-thens']);
        $this->insertDefaultRow(['perm_manage_logs', 'Manage logs']);
        $this->insertDefaultRow(['perm_manage_news', 'Manage news']);
        $this->insertDefaultRow(['perm_manage_permissions', 'Manage permissions']);
        $this->insertDefaultRow(['perm_manage_reports', 'Manage reports']);
        $this->insertDefaultRow(['perm_manage_roles', 'Manage roles']);
        $this->insertDefaultRow(['perm_manage_site_config', 'Manage site configuration']);
        $this->insertDefaultRow(['perm_manage_styles', 'Manage styles']);
        $this->insertDefaultRow(['perm_manage_templates', 'Manage templates']);
        $this->insertDefaultRow(['perm_manage_users', 'Manage users']);
        $this->insertDefaultRow(['perm_board_delete_posts', 'Delete posts and threads']);
        $this->insertDefaultRow(['perm_board_post_status', 'Change the status of a thread or post']);
        $this->insertDefaultRow(['perm_board_mod_comment', 'Add staff commentary to a post']);
        $this->insertDefaultRow(['perm_board_mod_mode', 'Access to Moderator Mode']);
        $this->insertDefaultRow(['perm_board_post_as_staff', 'Post as staff (displays capcode)']);
        $this->insertDefaultRow(['perm_board_post_in_locked', 'Post in locked thread']);
        $this->insertDefaultRow(['perm_board_transfer', 'Transfer board ownership']);
        $this->insertDefaultRow(['perm_extract_gettext', 'Extract Gettext strings']);
        $this->insertDefaultRow(['perm_regen_cache', 'Regenerate caches']);
        $this->insertDefaultRow(['perm_regen_pages', 'Regenerate pages']);
        $this->insertDefaultRow(['perm_view_unhashed_ip', 'View unhashed IP addresses']);
    }
}