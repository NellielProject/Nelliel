<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TablePermissions extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_PERMISSIONS_TABLE;
        $this->column_types = [
            'permission' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'description' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'permission' => ['row_check' => true, 'auto_inc' => false],
            'description' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            permission      VARCHAR(50) NOT NULL,
            description     TEXT NOT NULL,
            moar            TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (permission)
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['perm_bans_view', 'View existing bans.']);
        $this->insertDefaultRow(['perm_bans_add', 'Add new bans.']);
        $this->insertDefaultRow(['perm_bans_modify', 'Modify existing bans.']);
        $this->insertDefaultRow(['perm_bans_delete', 'Delete existing bans.']);
        $this->insertDefaultRow(['perm_blotter_manage', 'Manage blotter entries.']);
        $this->insertDefaultRow(['perm_boards_view', 'View full list of boards.']);
        $this->insertDefaultRow(['perm_boards_add', 'Add new boards.']);
        $this->insertDefaultRow(['perm_boards_modify', 'Modify existing boards.']);
        $this->insertDefaultRow(['perm_boards_delete', 'Delete boards.']);
        $this->insertDefaultRow(['perm_board_config_modify', 'Modify board configuration.']);
        $this->insertDefaultRow(['perm_board_config_override', 'Override board configuration locks.']);
        $this->insertDefaultRow(['perm_board_defaults_modify', 'Modify the default board configuration.']);
        $this->insertDefaultRow(['perm_content_ops_manage', 'Manage content ops.']);
        $this->insertDefaultRow(['perm_embeds_manage', 'Manage embeds.']);
        $this->insertDefaultRow(['perm_filetypes_manage', 'Manage filetypes.']);
        $this->insertDefaultRow(['perm_file_filters_manage', 'Manage file filters.']);
        $this->insertDefaultRow(['perm_image_sets_manage', 'Manage image sets.']);
        $this->insertDefaultRow(['perm_ip_notes_view', 'View IP notes.']);
        $this->insertDefaultRow(['perm_ip_notes_add', 'Add new IP notes.']);
        $this->insertDefaultRow(['perm_ip_notes_delete', 'Delete IP notes.']);
        $this->insertDefaultRow(['perm_logs_view', 'View full logs.']);
        $this->insertDefaultRow(['perm_logs_manage', 'Manage logs.']);
        $this->insertDefaultRow(['perm_news_manage', 'Manage news entries.']);
        $this->insertDefaultRow(['perm_noticeboard_view', 'View the staff noticeboard.']);
        $this->insertDefaultRow(['perm_noticeboard_post', 'Post on the staff noticeboard.']);
        $this->insertDefaultRow(['perm_noticeboard_delete', 'Delete posts on the staff noticeboard.']);
        $this->insertDefaultRow(['perm_pages_manage', 'Manage static pages.']);
        $this->insertDefaultRow(['perm_permissions_manage', 'Manage permissions.']);
        $this->insertDefaultRow(['perm_plugins_manage', 'Manage static pages.']);
        $this->insertDefaultRow(['perm_reports_view', 'View reports.']);
        $this->insertDefaultRow(['perm_reports_dismiss', 'Manage reports.']);
        $this->insertDefaultRow(['perm_roles_view', 'View roles.']);
        $this->insertDefaultRow(['perm_roles_manage', 'Manage roles.']);
        $this->insertDefaultRow(['perm_site_config_modify', 'Manage site configuration.']);
        $this->insertDefaultRow(['perm_styles_manage', 'Manage styles.']);
        $this->insertDefaultRow(['perm_templates_manage', 'Manage templates.']);
        $this->insertDefaultRow(['perm_threads_access', 'Access the threads panel.']);
        $this->insertDefaultRow(['perm_users_view', 'View users.']);
        $this->insertDefaultRow(['perm_users_manage', 'Manage users.']);
        $this->insertDefaultRow(['perm_wordfilters_manage', 'Manage wordfilters.']);
        $this->insertDefaultRow(['perm_regen_cache', 'Regenerate caches.']);
        $this->insertDefaultRow(['perm_regen_pages', 'Regenerate pages.']);
        $this->insertDefaultRow(['perm_regen_overboard', 'Regenerate overboard.']);
        $this->insertDefaultRow(['perm_extract_gettext', 'Extract Gettext strings.']);
        $this->insertDefaultRow(['perm_post_status', 'Change the status of a thread or post.']);
        $this->insertDefaultRow(['perm_post_type', 'Change the type of a thread or post.']);
        $this->insertDefaultRow(['perm_edit_posts', 'Edit posts made by others.']);
        $this->insertDefaultRow(['perm_delete_by_ip', 'Delete all content from a specific IP.']);
        $this->insertDefaultRow(['perm_mod_mode', 'Access to Moderator Mode.']);
        $this->insertDefaultRow(['perm_post_as_staff', 'Post as staff (displays capcode).']);
        $this->insertDefaultRow(['perm_post_locked_thread', 'Post in a locked thread.']);
        $this->insertDefaultRow(['perm_post_locked_board', 'Post on a locked board.']);
        $this->insertDefaultRow(['perm_move_threads', 'Move threads to another board.']);
        $this->insertDefaultRow(['perm_merge_threads', 'Merge threads.']);
        $this->insertDefaultRow(['perm_search_posts', 'Search through the text of posts.']);
        $this->insertDefaultRow(['perm_custom_name', 'Post as staff with a custom name.']);
        $this->insertDefaultRow(['perm_custom_capcode', 'Can use a custom capcode.']);
        $this->insertDefaultRow(['perm_bypass_renzoku', 'Bypass posting cooldowns.']);
        $this->insertDefaultRow(['perm_delete_posts', 'Delete posts and threads.']);
        $this->insertDefaultRow(['perm_view_unhashed_ip', 'View unhashed IP addresses.']);
        $this->insertDefaultRow(['perm_private_messages_use', 'View and send private messages.']);
        $this->insertDefaultRow(['perm_raw_html', 'Can use raw HTML input.']);
    }
}