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
        'owner' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'permission' => PDO::PARAM_STR,
        'description' => PDO::PARAM_STR,
        'owner' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_PERMISSIONS_TABLE;
        $this->column_checks = [
            'permission' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'description' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'owner' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            permission      VARCHAR(50) NOT NULL,
            description     TEXT NOT NULL,
            owner           VARCHAR(50) NOT NULL,
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
        $this->insertDefaultRow(['perm_view_bans', 'View existing bans.', 'nelliel']);
        $this->insertDefaultRow(['perm_add_bans', 'Add new bans.', 'nelliel']);
        $this->insertDefaultRow(['perm_add_range_bans', 'Add new range or subnet bans.', 'nelliel']);
        $this->insertDefaultRow(['perm_modify_bans', 'Modify existing bans.', 'nelliel']);
        $this->insertDefaultRow(['perm_delete_bans', 'Delete existing bans.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_blotter', 'Manage blotter entries.', 'nelliel']);
        $this->insertDefaultRow(['perm_boards_view', 'View full list of boards.', 'nelliel']);
        $this->insertDefaultRow(['perm_boards_add', 'Add new boards.', 'nelliel']);
        $this->insertDefaultRow(['perm_boards_modify', 'Modify existing boards.', 'nelliel']);
        $this->insertDefaultRow(['perm_boards_delete', 'Delete boards.', 'nelliel']);
        $this->insertDefaultRow(['perm_modify_board_config', 'Modify board configuration.', 'nelliel']);
        $this->insertDefaultRow(['perm_override_config_lock', 'Override board configuration locks.', 'nelliel']);
        $this->insertDefaultRow(['perm_modify_board_defaults', 'Modify the default board configuration.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_content_ops', 'Manage content ops.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_embeds', 'Manage embeds.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_filetypes', 'Manage filetypes.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_file_filters', 'Manage file filters.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_image_sets', 'Manage image sets.', 'nelliel']);
        $this->insertDefaultRow(['perm_view_ip_info', 'View info for an IP.', 'nelliel']);
        $this->insertDefaultRow(['perm_add_ip_notes', 'Add new IP notes.', 'nelliel']);
        $this->insertDefaultRow(['perm_delete_ip_notes', 'Delete IP notes.', 'nelliel']);
        $this->insertDefaultRow(['perm_view_public_logs', 'View full logs.', 'nelliel']);
        $this->insertDefaultRow(['perm_view_system_logs', 'View system logs.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_markup', 'Manage markup entries.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_news', 'Manage news entries.', 'nelliel']);
        $this->insertDefaultRow(['perm_noticeboard_view', 'View the staff noticeboard.', 'nelliel']);
        $this->insertDefaultRow(['perm_noticeboard_post', 'Post on the staff noticeboard.', 'nelliel']);
        $this->insertDefaultRow(['perm_noticeboard_delete', 'Delete posts on the staff noticeboard.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_pages', 'Manage static pages.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_plugins', 'Manage plugins.', 'nelliel']);
        $this->insertDefaultRow(['perm_access_plugin_controls', 'Access plugin control panels.', 'nelliel']);
        $this->insertDefaultRow(['perm_view_reports', 'View reports.', 'nelliel']);
        $this->insertDefaultRow(['perm_dismiss_reports', 'Manage reports.', 'nelliel']);
        $this->insertDefaultRow(['perm_view_roles', 'View roles.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_roles', 'Manage roles.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_scripts', 'Manage scripts.', 'nelliel']);
        $this->insertDefaultRow(['perm_modify_site_config', 'Manage site configuration.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_styles', 'Manage styles.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_templates', 'Manage templates.', 'nelliel']);
        $this->insertDefaultRow(['perm_threads_access', 'Access the threads panel.', 'nelliel']);
        $this->insertDefaultRow(['perm_view_users', 'View users.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_users', 'Manage users.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_wordfilters', 'Manage wordfilters.', 'nelliel']);
        $this->insertDefaultRow(['perm_regen_cache', 'Regenerate caches.', 'nelliel']);
        $this->insertDefaultRow(['perm_regen_pages', 'Regenerate pages.', 'nelliel']);
        $this->insertDefaultRow(['perm_regen_overboard', 'Regenerate overboard.', 'nelliel']);
        $this->insertDefaultRow(['perm_extract_gettext', 'Extract Gettext strings.', 'nelliel']);
        $this->insertDefaultRow(['perm_modify_content_status', 'Change the status of posted content.', 'nelliel']);
        $this->insertDefaultRow(['perm_edit_posts', 'Edit posts made by others.', 'nelliel']);
        $this->insertDefaultRow(['perm_delete_by_ip', 'Delete all content from a specific IP.', 'nelliel']);
        $this->insertDefaultRow(['perm_mod_mode', 'Access to Moderator Mode.', 'nelliel']);
        $this->insertDefaultRow(['perm_post_as_staff', 'Post as staff (displays capcode).', 'nelliel']);
        $this->insertDefaultRow(['perm_post_locked_thread', 'Post in a locked thread.', 'nelliel']);
        $this->insertDefaultRow(['perm_post_locked_board', 'Post on a locked board.', 'nelliel']);
        $this->insertDefaultRow(['perm_move_content', 'Move threads to another board.', 'nelliel']);
        $this->insertDefaultRow(['perm_merge_threads', 'Merge threads.', 'nelliel']);
        $this->insertDefaultRow(['perm_search_posts', 'Search through the text of posts.', 'nelliel']);
        $this->insertDefaultRow(['perm_custom_name', 'Post as staff with a custom name.', 'nelliel']);
        $this->insertDefaultRow(['perm_custom_capcode', 'Can use a custom capcode.', 'nelliel']);
        $this->insertDefaultRow(['perm_bypass_renzoku', 'Bypass posting cooldowns.', 'nelliel']);
        $this->insertDefaultRow(['perm_delete_content', 'Delete posts and threads.', 'nelliel']);
        $this->insertDefaultRow(['perm_view_unhashed_ip', 'View unhashed IP addresses.', 'nelliel']);
        $this->insertDefaultRow(['perm_use_private_messages', 'View and send private messages.', 'nelliel']);
        $this->insertDefaultRow(['perm_manage_private_messages', 'Manage all private messages.', 'nelliel']);
        $this->insertDefaultRow(['perm_raw_html', 'Can use raw HTML input.', 'nelliel']);
    }
}