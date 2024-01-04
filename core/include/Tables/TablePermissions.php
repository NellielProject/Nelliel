<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TablePermissions extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'permission' => 'string',
        'description' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'permission' => PDO::PARAM_STR,
        'description' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_PERMISSIONS_TABLE;
        $this->column_checks = [
            'permission' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'description' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            permission      VARCHAR(50) NOT NULL,
            description     TEXT NOT NULL,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (permission)
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['perm_view_bans', 'View existing bans.']);
        $this->insertDefaultRow(['perm_add_bans', 'Add new bans.']);
        $this->insertDefaultRow(['perm_add_range_bans', 'Add new range or subnet bans.']);
        $this->insertDefaultRow(['perm_modify_bans', 'Modify existing bans.']);
        $this->insertDefaultRow(['perm_delete_bans', 'Delete existing bans.']);
        $this->insertDefaultRow(['perm_manage_blotter', 'Manage blotter entries.']);
        $this->insertDefaultRow(['perm_boards_view', 'View full list of boards.']);
        $this->insertDefaultRow(['perm_boards_add', 'Add new boards.']);
        $this->insertDefaultRow(['perm_boards_modify', 'Modify existing boards.']);
        $this->insertDefaultRow(['perm_boards_delete', 'Delete boards.']);
        $this->insertDefaultRow(['perm_modify_board_config', 'Modify board configuration.']);
        $this->insertDefaultRow(['perm_override_config_lock', 'Override board configuration locks.']);
        $this->insertDefaultRow(['perm_modify_board_defaults', 'Modify the default board configuration.']);
        $this->insertDefaultRow(['perm_manage_content_ops', 'Manage content ops.']);
        $this->insertDefaultRow(['perm_manage_embeds', 'Manage embeds.']);
        $this->insertDefaultRow(['perm_manage_filetypes', 'Manage filetypes.']);
        $this->insertDefaultRow(['perm_manage_file_filters', 'Manage file filters.']);
        $this->insertDefaultRow(['perm_manage_image_sets', 'Manage image sets.']);
        $this->insertDefaultRow(['perm_view_ip_info', 'View info for an IP.']);
        $this->insertDefaultRow(['perm_add_ip_notes', 'Add new IP notes.']);
        $this->insertDefaultRow(['perm_delete_ip_notes', 'Delete IP notes.']);
        $this->insertDefaultRow(['perm_view_public_logs', 'View full logs.']);
        $this->insertDefaultRow(['perm_view_system_logs', 'View system logs.']);
        $this->insertDefaultRow(['perm_manage_markup', 'Manage markup entries.']);
        $this->insertDefaultRow(['perm_manage_news', 'Manage news entries.']);
        $this->insertDefaultRow(['perm_noticeboard_view', 'View the staff noticeboard.']);
        $this->insertDefaultRow(['perm_noticeboard_post', 'Post on the staff noticeboard.']);
        $this->insertDefaultRow(['perm_noticeboard_delete', 'Delete posts on the staff noticeboard.']);
        $this->insertDefaultRow(['perm_manage_pages', 'Manage static pages.']);
        $this->insertDefaultRow(['perm_manage_permissions', 'Manage permissions.']);
        $this->insertDefaultRow(['perm_manage_plugins', 'Manage plugins.']);
        $this->insertDefaultRow(['perm_access_plugin_controls', 'Access plugin control panels.']);
        $this->insertDefaultRow(['perm_view_reports', 'View reports.']);
        $this->insertDefaultRow(['perm_dismiss_reports', 'Manage reports.']);
        $this->insertDefaultRow(['perm_view_roles', 'View roles.']);
        $this->insertDefaultRow(['perm_manage_roles', 'Manage roles.']);
        $this->insertDefaultRow(['perm_manage_scripts', 'Manage scripts.']);
        $this->insertDefaultRow(['perm_modify_site_config', 'Manage site configuration.']);
        $this->insertDefaultRow(['perm_manage_styles', 'Manage styles.']);
        $this->insertDefaultRow(['perm_manage_templates', 'Manage templates.']);
        $this->insertDefaultRow(['perm_threads_access', 'Access the threads panel.']);
        $this->insertDefaultRow(['perm_view_users', 'View users.']);
        $this->insertDefaultRow(['perm_manage_users', 'Manage users.']);
        $this->insertDefaultRow(['perm_manage_wordfilters', 'Manage wordfilters.']);
        $this->insertDefaultRow(['perm_regen_cache', 'Regenerate caches.']);
        $this->insertDefaultRow(['perm_regen_pages', 'Regenerate pages.']);
        $this->insertDefaultRow(['perm_regen_overboard', 'Regenerate overboard.']);
        $this->insertDefaultRow(['perm_extract_gettext', 'Extract Gettext strings.']);
        $this->insertDefaultRow(['perm_modify_content_status', 'Change the status of posted content.']);
        $this->insertDefaultRow(['perm_edit_posts', 'Edit posts made by others.']);
        $this->insertDefaultRow(['perm_delete_by_ip', 'Delete all content from a specific IP.']);
        $this->insertDefaultRow(['perm_mod_mode', 'Access to Moderator Mode.']);
        $this->insertDefaultRow(['perm_post_as_staff', 'Post as staff (displays capcode).']);
        $this->insertDefaultRow(['perm_post_locked_thread', 'Post in a locked thread.']);
        $this->insertDefaultRow(['perm_post_locked_board', 'Post on a locked board.']);
        $this->insertDefaultRow(['perm_move_content', 'Move threads to another board.']);
        $this->insertDefaultRow(['perm_merge_threads', 'Merge threads.']);
        $this->insertDefaultRow(['perm_search_posts', 'Search through the text of posts.']);
        $this->insertDefaultRow(['perm_custom_name', 'Post as staff with a custom name.']);
        $this->insertDefaultRow(['perm_custom_capcode', 'Can use a custom capcode.']);
        $this->insertDefaultRow(['perm_bypass_renzoku', 'Bypass posting cooldowns.']);
        $this->insertDefaultRow(['perm_delete_content', 'Delete posts and threads.']);
        $this->insertDefaultRow(['perm_view_unhashed_ip', 'View unhashed IP addresses.']);
        $this->insertDefaultRow(['perm_use_private_messages', 'View and send private messages.']);
        $this->insertDefaultRow(['perm_manage_private_messages', 'Manage all private messages.']);
        $this->insertDefaultRow(['perm_raw_html', 'Can use raw HTML input.']);
    }
}