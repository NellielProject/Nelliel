<?php
declare(strict_types = 1);

namespace Nelliel\Setup;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Overboard;
use Nelliel\Tables\TableBanAppeals;
use Nelliel\Tables\TableBans;
use Nelliel\Tables\TableBoardDefaults;
use Nelliel\Tables\TableIPInfo;
use Nelliel\Tables\TableLogs;
use Nelliel\Tables\TableMarkup;
use Nelliel\Tables\TableNews;
use Nelliel\Tables\TableNoticeboard;
use Nelliel\Tables\TableOverboard;
use Nelliel\Tables\TablePermissions;
use Nelliel\Tables\TablePosts;
use Nelliel\Tables\TableR9KContent;
use Nelliel\Tables\TableR9KMutes;
use Nelliel\Tables\TableReports;
use Nelliel\Tables\TableRolePermissions;
use Nelliel\Tables\TableScripts;
use Nelliel\Tables\TableSettingOptions;
use Nelliel\Tables\TableSettings;
use Nelliel\Tables\TableStatistics;
use Nelliel\Tables\TableThreads;
use Nelliel\Tables\TableUploads;
use Nelliel\Utility\FileHandler;
use PDO;
use Nelliel\Tables\TableIPNotes;

class BetaMigrations
{
    private $file_handler;
    private $upgrade;
    private $setting_defaults_inserted = false;

    function __construct(FileHandler $file_handler, Upgrade $upgrade)
    {
        $this->file_handler = $file_handler;
        $this->upgrade = $upgrade;
    }

    // NOTES
    // Hardcode table names for their value in a given version as the constants may change later on.
    // SQLite does not support DROP COLUMN until recent versions. When SQLite is selected, just set the column empty or null to indicate it is unused.
    // SQLite does not handle automatically filling values in NOT NULL columns. If adding a column, include an empty default if a default is not already given.
    public function doMigrations(): int
    {
        $migration_count = 0;
        $core_sqltype = nel_database('core')->config()['sqltype'];

        switch ($this->upgrade->installedVersion()) {
            case 'v0.9.25':
                echo '<br>' . __('Updating from v0.9.25 to v0.9.26...') . '<br>';

                // Update setting options table
                nel_database('core')->exec('ALTER TABLE "nelliel_menu_data" RENAME TO nelliel_setting_options');
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_setting_options" ADD COLUMN raw_output SMALLINT NOT NULL DEFAULT 0');

                echo ' - ' . __('Setting options table updated.') . '<br>';

                // Update filetypes table
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_filetypes" ADD COLUMN mimetypes TEXT NOT NULL DEFAULT \'\'');
                nel_database('core')->exec('UPDATE "nelliel_filetypes" SET "mimetypes" = "mime"');

                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB' || $core_sqltype === 'POSTGRESQL') {
                    nel_database('core')->exec('ALTER TABLE "nelliel_filetypes" DROP COLUMN "mime"');
                } else {
                    nel_database('core')->exec('UPDATE "nelliel_filetypes" SET "mime" = \'\'');
                }

                $old_data = nel_database('core')->executeFetchAll(
                    'SELECT "format", "mimetypes" FROM "nelliel_filetypes"', PDO::FETCH_ASSOC);

                $multiples = ['bmp' => '["image/bmp", "image/x-bmp"]', 'tgs' => '["image/targa", "image/x-tga"]',
                    'pict' => '["image/pict", "image/x-pict"]', 'aiff' => '["audio/aiff", "audio/x-aiff"]',
                    'm4a' => '["audio/mp4", "audio/x-m4a"]', 'flac' => '["audio/flac", "audio/x-flac"]',
                    'midi' => '["audio/midi", "audio/x-midi"]', 'rtf' => '["text/rtf", "application/rtf"]',
                    'doc' => '["application/vnd.ms-word", "application/msword"]',
                    'gzip' => '["application/gzip", "application/x-gzip"]',
                    'rar' => '["application/vnd.rar", "application/x-rar-compressed"]',
                    'stuffit' => '["application/x-stuffit", "application/x-sit"]',
                    'swf' => '["application/vnd.adobe.flash-movie", "application/x-shockwave-flash"]'];

                foreach ($old_data as $data) {
                    $new_value = '["' . $data['mimetypes'] . '"]';

                    if (array_key_exists($data['format'], $multiples)) {
                        $new_value = $multiples[$data['format']];
                    }

                    $prepared = nel_database('core')->prepare(
                        'UPDATE "nelliel_filetypes" SET "mimetypes" = :mimetypes WHERE "format" = :format');
                    $prepared->bindValue(':mimetypes', $new_value, PDO::PARAM_STR);
                    $prepared->bindValue(':format', $data['format'], PDO::PARAM_STR);
                    nel_database('core')->executePrepared($prepared, null);
                }

                nel_database('core')->exec(
                    'UPDATE "nelliel_filetypes" SET "extensions" = \'["3gp", "3gpp"]\' WHERE "format" = \'3gp\'');

                echo ' - ' . __('Filetypes table updated.') . '<br>';

                // Update users table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec(
                        'ALTER TABLE "nelliel_users" CHANGE COLUMN user_password password VARCHAR(255) NOT NULL');
                } else {
                    nel_database('core')->exec('ALTER TABLE "nelliel_users" RENAME COLUMN user_password TO password');
                }

                $usernames = nel_database('core')->executeFetchAll('SELECT "username" FROM "nelliel_users"',
                    PDO::FETCH_COLUMN);
                $prepared = nel_database('core')->prepare(
                    'UPDATE "nelliel_users" SET "username" = :username_lower WHERE "username" = :username');

                foreach ($usernames as $username) {
                    $username_lower = utf8_strtolower($username);
                    $prepared->bindValue(':username_lower', $username_lower, PDO::PARAM_STR);
                    $prepared->bindValue(':username', $username, PDO::PARAM_STR);
                    nel_database('core')->executePrepared($prepared, null);
                }

                echo ' - ' . __('Users table updated.') . '<br>';

                // Update archive table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    $prefixes = nel_database('core')->executeFetchAll('SELECT "db_prefix" FROM "nelliel_board_data"',
                        PDO::FETCH_COLUMN);

                    foreach ($prefixes as $prefix) {
                        nel_database('core')->exec(
                            'ALTER TABLE "' . $prefix . '_archives' . '" MODIFY COLUMN thread_data LONGTEXT NOT NULL');
                    }

                    echo ' - ' . __('Archive tables updated.') . '<br>';
                }

                // Update settings and config tables
                $ui_removals = ['ui_delimiter_left', 'ui_delimiter_right', 'ui_hide_thread', 'ui_show_thread',
                    'ui_hide_post', 'ui_show_post', 'ui_hide_file', 'ui_show_file', 'ui_hide_embed', 'ui_show_embed',
                    'ui_cite_post', 'ui_reply_to_thread', 'ui_more_file_info', 'ui_less_file_info', 'ui_expand_thread',
                    'ui_collapse_thread'];
                $this->removeBoardSettings($ui_removals);

                $mod_links_old_names = ['ui_mod_ban', 'ui_mod_delete', 'ui_mod_delete_by_ip',
                    'ui_mod_global_delete_by_ip', 'ui_mod_ban_and_delete', 'ui_mod_lock', 'ui_mod_unlock',
                    'ui_mod_sticky', 'ui_mod_unsticky', 'ui_mod_permasage', 'ui_mod_unpermasage', 'ui_mod_cyclic',
                    'ui_mod_non_cyclic', 'ui_mod_edit_post'];
                $mod_links_new_names = ['mod_links_ban', 'mod_links_delete', 'mod_links_delete_by_ip',
                    'mod_links_global_delete_by_ip', 'mod_links_ban_and_delete', 'mod_links_lock', 'mod_links_unlock',
                    'mod_links_sticky', 'mod_links_unsticky', 'mod_links_permasage', 'mod_links_unpermasage',
                    'mod_links_cyclic', 'mod_links_non_cyclic', 'mod_links_edit'];
                $this->renameBoardSettings($mod_links_old_names, $mod_links_new_names);

                $new_board_settings = ['mod_links_delimiter_left', 'mod_links_delimiter_right', 'enable_index',
                    'enable_catalog', 'display_allowed_filetypes', 'display_allowed_embeds', 'display_form_max_filesize',
                    'display_thumbnailed_message'];
                $this->newBoardSettings($new_board_settings);

                $new_site_textareas = ['description'];
                $new_board_textareas = ['description'];

                foreach ($new_site_textareas as $setting_name) {
                    $prepared = nel_database('core')->prepare(
                        'UPDATE "nelliel_settings" SET "input_attributes" = :textarea WHERE "setting_name" = :setting_name AND "setting_category" = \'site\'');
                    $prepared->bindValue(':textarea', '{"type":"textarea"}', PDO::PARAM_STR);
                    $prepared->bindValue(':setting_name', $setting_name);
                    nel_database('core')->executePrepared($prepared, null);
                }

                foreach ($new_board_textareas as $setting_name) {
                    $prepared = nel_database('core')->prepare(
                        'UPDATE "nelliel_settings" SET "input_attributes" = :textarea WHERE "setting_name" = :setting_name AND "setting_category" = \'board\'');
                    $prepared->bindValue(':textarea', '{"type":"textarea"}', PDO::PARAM_STR);
                    $prepared->bindValue(':setting_name', $setting_name);
                    nel_database('core')->executePrepared($prepared, null);
                }

                echo ' - ' . __('Settings and board config tables updated.') . '<br>';

                // Update thread tables
                $db_prefixes = nel_database('core')->executeFetchAll('SELECT "db_prefix" FROM "nelliel_board_data"',
                    PDO::FETCH_COLUMN);

                foreach ($db_prefixes as $prefix) {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_threads' . '" ADD COLUMN regen_cache SMALLINT NOT NULL DEFAULT 0');
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_threads' . '" ADD COLUMN cache TEXT DEFAULT NULL');
                }

                echo ' - ' . __('Thread tables updated.') . '<br>';

                // Update permissions and role permissions table
                $permissions_table = new TablePermissions(nel_database('core'), nel_utilities()->sqlCompatibility());
                $permissions_table->insertDefaultRow(['perm_plugins_manage', 'Manage plugins.']);
                $role_permissions_table = new TableRolePermissions(nel_database('core'),
                    nel_utilities()->sqlCompatibility());
                $role_permissions_table->insertDefaultRow(['SITE_ADMIN', 'perm_plugins_manage', 1]);

                echo ' - ' . __('Permissions and role permissions tables updated.') . '<br>';

                $migration_count ++;

            case 'v0.9.26':
                echo '<br>' . __('Updating from v0.9.26 to v0.9.27...') . '<br>';

                // Update post tables
                $db_prefixes = nel_database('core')->executeFetchAll('SELECT "db_prefix" FROM "nelliel_board_data"',
                    PDO::FETCH_COLUMN);

                foreach ($db_prefixes as $prefix) {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_posts' .
                        '" ADD COLUMN visitor_id VARCHAR(128) NOT NULL DEFAULT \'\'');
                }

                echo ' - ' . __('Post tables updated.') . '<br>';

                // Update bans table
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_bans" ADD COLUMN visitor_id VARCHAR(128) NOT NULL DEFAULT \'\'');
                echo ' - ' . __('Bans table updated.') . '<br>';

                // Update logs table
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_logs" ADD COLUMN visitor_id VARCHAR(128) NOT NULL DEFAULT \'\'');
                echo ' - ' . __('Logs table updated.') . '<br>';

                // Update reports table
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_reports" ADD COLUMN visitor_id VARCHAR(128) NOT NULL DEFAULT \'\'');
                echo ' - ' . __('Reports table updated.') . '<br>';

                $new_board_settings = ['post_backlinks_header', 'post_backlinks_footer', 'post_backlinks_label',
                    'show_download_link', 'download_original_name', 'spoiler_display_name'];
                $this->newBoardSettings($new_board_settings);

                $board_setting_removals = ['display_post_backlinks'];
                $this->removeBoardSettings($board_setting_removals);

                echo ' - ' . __('Settings and board config tables updated.') . '<br>';

                // Update plugins table
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_plugins" ADD COLUMN initializer VARCHAR(255) NOT NULL DEFAULT \'\'');
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_plugins" ADD COLUMN parsed_ini TEXT NOT NULL DEFAULT \'\'');

                echo ' - ' . __('Plugins table updated.') . '<br>';

                // Update permissions and role permissions tables
                $permissions_table = new TablePermissions(nel_database('core'), nel_utilities()->sqlCompatibility());
                $permissions_table->insertDefaults();
                $this->addRolePermission('perm_pages_manage');

                echo ' - ' . __('Permissions and role permissions tables updated.') . '<br>';

                // if (version_compare(NELLIEL_VERSION, 'v0.9.30', '<')) {
                // Update permissions and role permissions table
                // $permissions_table = new TablePermissions(nel_database('core'), nel_utilities()->sqlCompatibility());
                // $permissions_table->insertDefaultRow(['perm_plugins_manage', 'Manage static pages.']);
                // $role_permissions_table = new TableRolePermissions(nel_database('core'),
                // nel_utilities()->sqlCompatibility());
                // $role_permissions_table->insertDefaultRow(['SITE_ADMIN', 'perm_plugins_manage', 1]);
                // $this->addPermission('perm_plugins_manage', 'Manage static pages.');

                // echo ' - ' . __('Permissions and role permissions tables updated.') . '<br>';
                // }

                // Update core template info
                $template_instance = nel_site_domain()->frontEndData()->getTemplate('template-nelliel-basic');
                $enabled = $template_instance->enabled();
                $template_instance->install(true);
                $template_instance->enable($enabled);

                echo ' - ' . __('Template info updated.') . '<br>';

                // Update core style info
                $core_styles = ['style-nelliel', 'style-nelliel-2', 'style-nelliel-classic', 'style-futaba',
                    'style-burichan', 'style-nigra'];

                foreach ($core_styles as $style) {
                    $style_instance = nel_site_domain()->frontEndData()->getStyle($style);
                    $enabled = $style_instance->enabled();
                    $style_instance->install(true);
                    $style_instance->enable($enabled);
                }

                echo ' - ' . __('Style info updated.') . '<br>';

                // Update core image set info
                $image_set_instance = nel_site_domain()->frontEndData()->getImageSet('images-nelliel-basic');
                $enabled = $image_set_instance->enabled();
                $image_set_instance->install(true);
                $image_set_instance->enable($enabled);

                echo ' - ' . __('Image set info updated.') . '<br>';

                $migration_count ++;

            case 'v0.9.27':
                echo '<br>' . __('Updating from v0.9.27 to v0.9.28...') . '<br>';

                // Update core image set info
                $image_set_instance = nel_site_domain()->frontEndData()->getImageSet('images-nelliel-basic');
                $enabled = $image_set_instance->enabled();
                $image_set_instance->install(true);
                $image_set_instance->enable($enabled);

                echo ' - ' . __('Image set info updated.') . '<br>';

                // Update board settings
                $new_board_settings = ['max_reply_preview_display_width', 'max_reply_preview_display_height',
                    'max_reply_embed_display_width', 'max_reply_embed_display_height', 'max_reply_multi_display_width',
                    'max_reply_multi_display_height', 'enable_reply_name_field', 'require_reply_name',
                    'enable_reply_email_field', 'require_reply_email', 'enable_reply_subject_field',
                    'require_reply_subject', 'enable_reply_comment_field', 'require_reply_comment', 'show_poster_name',
                    'show_tripcodes', 'show_capcode', 'show_post_subject', 'show_user_comments', 'show_mod_comments'];
                $this->newBoardSettings($new_board_settings);

                $old_board_setting_names = ['max_preview_display_width', 'max_preview_display_height',
                    'max_embed_display_width', 'max_embed_display_height', 'max_multi_display_width',
                    'max_multi_display_height', 'enable_name_field', 'require_name', 'enable_email_field',
                    'require_email', 'enable_subject_field', 'require_subject', 'enable_comment_field',
                    'require_comment', 'display_render_timer', 'display_poster_id', 'display_static_preview',
                    'display_animated_preview', 'display_original_name', 'display_allowed_filetypes',
                    'display_allowed_embeds', 'display_form_max_filesize', 'display_thumbnailed_message',
                    'display_video_preview', 'date_format'];
                $new_board_setting_names = ['max_op_preview_display_width', 'max_op_preview_display_height',
                    'max_op_embed_display_width', 'max_op_embed_display_height', 'max_op_multi_display_width',
                    'max_op_multi_display_height', 'enable_op_name_field', 'require_op_name', 'enable_op_email_field',
                    'require_op_email', 'enable_op_subject_field', 'require_op_subject', 'enable_op_comment_field',
                    'require_op_comment', 'show_render_timer', 'show_poster_id', 'show_static_preview',
                    'show_animated_preview', 'show_original_name', 'show_allowed_filetypes', 'show_allowed_embeds',
                    'show_form_max_filesize', 'show_thumbnailed_message', 'show_video_preview', 'post_time_format'];
                $this->renameBoardSettings($old_board_setting_names, $new_board_setting_names);

                echo ' - ' . __('Board settings updated.') . '<br>';

                // Update site settings
                $new_site_settings = ['visitor_id_lifespan'];
                $this->newSiteSettings($new_site_settings);

                $old_site_setting_names = ['display_render_timer'];
                $new_site_setting_names = ['show_render_timer'];
                $this->renameSiteSettings($old_site_setting_names, $new_site_setting_names);

                $old_site_settings = ['must_see_ban', 'allow_ban_appeals', 'min_time_before_ban_appeal',
                    'ban_page_extra_text'];
                $this->removeSiteSettings($old_site_settings);

                echo ' - ' . __('Site settings updated.') . '<br>';

                if (version_compare(NELLIEL_VERSION, 'v0.9.30', '<')) {
                    // Create ban appeals table and update bans
                    $bans_data = nel_database('core')->executeFetchAll('SELECT * FROM "nelliel_bans"', PDO::FETCH_ASSOC);
                    nel_database('core')->exec('DROP TABLE "nelliel_bans"');
                    $bans_table = new TableBans(nel_database('core'), nel_utilities()->sqlCompatibility());
                    $bans_table->createTable();
                    $ban_appeals_table = new TableBanAppeals(nel_database('core'), nel_utilities()->sqlCompatibility());
                    $ban_appeals_table->createTable();

                    $bans_insert = nel_database('core')->prepare(
                        'INSERT INTO "' . NEL_BANS_TABLE .
                        '" ("ban_id", "board_id", "creator", "ip_type", "ip_address_start", "ip_address_end", "hashed_ip_address", "visitor_id", "reason", "start_time", "length", "seen", "appeal_allowed")
VALUES (:ban_id, :board_id, :creator, :ip_type, :ip_address_start, :ip_address_end, :hashed_ip_address, :visitor_id, :reason, :start_time, :length, :seen, :appeal_allowed)');
                    $appeal_insert = nel_database('core')->prepare(
                        'INSERT INTO "' . NEL_BAN_APPEALS_TABLE .
                        '" ("ban_id", "time", "appeal", "response", "pending", "denied")
VALUES (:ban_id, :time, :appeal, :response, :pending, :denied)');

                    foreach ($bans_data as $data) {
                        $bans_insert->bindValue(':ban_id', $data['ban_id'], PDO::PARAM_STR);
                        $bans_insert->bindValue(':board_id', $data['board_id'], PDO::PARAM_STR);
                        $bans_insert->bindValue(':creator', $data['creator'], PDO::PARAM_STR);
                        $bans_insert->bindValue(':ip_type', $data['ip_type'], PDO::PARAM_STR);
                        $bans_insert->bindValue(':ip_address_start', $data['ip_address_start'], PDO::PARAM_STR);
                        $bans_insert->bindValue(':ip_address_end', $data['ip_address_end'], PDO::PARAM_STR);
                        $bans_insert->bindValue(':hashed_ip_address', $data['hashed_ip_address'], PDO::PARAM_STR);
                        $bans_insert->bindValue(':visitor_id', $data['visitor_id'], PDO::PARAM_STR);
                        $bans_insert->bindValue(':reason', $data['reason'], PDO::PARAM_STR);
                        $bans_insert->bindValue(':start_time', $data['start_time'], PDO::PARAM_INT);
                        $bans_insert->bindValue(':length', $data['length'], PDO::PARAM_INT);
                        $bans_insert->bindValue(':seen', $data['seen'], PDO::PARAM_INT);
                        $appeal_allowed = $data['appeal_status'] != 2 ? 1 : 0;
                        $bans_insert->bindValue(':appeal_allowed', $appeal_allowed, PDO::PARAM_INT);
                        nel_database('core')->executePrepared($bans_insert);

                        if ($data['appeal_status'] > 0) {
                            $pending = $data['appeal_status'] == 1 ? 1 : 0;
                            $denied = $data['appeal_status'] == 2 ? 1 : 0;
                            $appeal_insert->bindValue(':ban_id', $data['ban_id'], PDO::PARAM_STR);
                            $appeal_insert->bindValue(':time', time(), PDO::PARAM_INT);
                            $appeal_insert->bindValue(':appeal', $data['appeal'], PDO::PARAM_STR);
                            $appeal_insert->bindValue(':response', $data['appeal_response'], PDO::PARAM_STR);
                            $appeal_insert->bindValue(':pending', $pending, PDO::PARAM_INT);
                            $appeal_insert->bindValue(':denied', $denied, PDO::PARAM_INT);
                            nel_database('core')->executePrepared($appeal_insert);
                        }
                    }

                    echo ' - ' . __('Added ban appeals table and updated bans table.') . '<br>';
                }

                // Update users
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_users" ADD COLUMN display_name VARCHAR(255) NOT NULL DEFAULT \'\'');

                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB' || $core_sqltype === 'POSTGRESQL') {
                    nel_database('core')->exec('ALTER TABLE "nelliel_users" DROP COLUMN locked');
                } else {
                    nel_database('core')->exec('UPDATE "nelliel_users" SET "locked" = 0');
                }

                echo ' - ' . __('Users table updated.') . '<br>';

                $migration_count ++;

            case 'v0.9.28':
                echo '<br>' . __('Updating from v0.9.28 to v0.9.29...') . '<br>';

                // Update file filters
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_file_filters" ADD COLUMN enabled SMALLINT NOT NULL DEFAULT 0');

                echo ' - ' . __('File filters table updated.') . '<br>';

                // Update site and global domain IDs
                $prepared = nel_database('core')->exec(
                    'UPDATE "nelliel_domain_registry" SET "domain_id" = \'site\' WHERE "domain_id" = \'_site_\'');
                $prepared = nel_database('core')->exec(
                    'UPDATE "nelliel_domain_registry" SET "domain_id" = \'global\' WHERE "domain_id" = \'_global_\'');

                echo ' - ' . __('Site and global domain IDs updated.') . '<br>';

                // Update roles table
                $prepared = nel_database('core')->exec(
                    'UPDATE "nelliel_roles" SET "role_id" = \'site_admin\' WHERE "role_id" = \'SITE_ADMIN\'');
                $prepared = nel_database('core')->exec(
                    'UPDATE "nelliel_roles" SET "role_id" = \'board_owner\' WHERE "role_id" = \'BOARD_OWNER\'');
                $prepared = nel_database('core')->exec(
                    'UPDATE "nelliel_roles" SET "role_id" = \'moderator\' WHERE "role_id" = \'MODERATOR\'');
                $prepared = nel_database('core')->exec(
                    'UPDATE "nelliel_roles" SET "role_id" = \'janitor\' WHERE "role_id" = \'JANITOR\'');
                $prepared = nel_database('core')->exec(
                    'UPDATE "nelliel_roles" SET "role_id" = \'basic_user\' WHERE "role_id" = \'BASIC_USER\'');

                echo ' - ' . __('Roles table updated.') . '<br>';

                // Update permissions and role permissions tables
                $permissions = ['perm_word_filters_manage' => 'perm_manage_wordfilters',
                    'perm_move_threads' => 'perm_move_content', 'perm_post_status' => 'perm_modify_content_status',
                    'perm_post_edit' => 'perm_edit_posts', 'perm_delete_posts' => 'perm_delete_content',
                    'perm_logs_manage' => 'perm_view_system_logs', 'perm_logs_view' => 'perm_view_public_logs',
                    'perm_news_manage' => 'perm_manage_news', 'perm_plugins_manage' => 'perm_manage_plugins',
                    'perm_permissions_manage' => 'perm_manage_permissions',
                    'perm_blotter_manage' => 'perm_manage_blotter',
                    'perm_private_messages_use' => 'perm_use_private_messages',
                    'perm_reports_view' => 'perm_view_reports', 'perm_reports_dismiss' => 'perm_dismiss_reports',
                    'perm_pages_manage' => 'perm_manage_pages', 'perm_image_sets_manage' => 'perm_manage_imsage_sets',
                    'perm_embeds_manage' => 'perm_manage_embeds', 'perm_content_ops_manage' => 'perm_manage_content_ops',
                    'perm_styles_manage' => 'perm_manage_styles', 'perm_templates_manage' => 'perm_manage_templates',
                    'perm_filetypes_manage' => 'perm_manage_filetypes',
                    'perm_file_filters_manage' => 'perm_manage_file_filters', 'perm_users_view', 'perm_view_users',
                    'perm_users_manage' => 'perm_manage_users', 'perm_roles_view' => 'perm_view_roles',
                    'perm_roles_manage' => 'perm_manage_roles', 'perm_site_config_modify' => 'perm_modify_site_config',
                    'perm_board_config_modify' => 'perm_modify_board_config',
                    'perm_board_config_override' => 'perm_override_config_lock',
                    'perm_board_defaults_modify' => 'perm_modify_board_defaults'];
                $permission_update = nel_database('core')->prepare(
                    'UPDATE "nelliel_permissions" SET "permission" = :new WHERE "permission" = :old');

                foreach ($permissions as $old => $new) {
                    $permission_update->bindValue(':new', $new);
                    $permission_update->bindValue(':old', $old);
                    nel_database('core')->executePrepared($permission_update);
                }

                echo ' - ' . __('Permissions and role permissions tables updated.') . '<br>';

                // Update wordfilters table
                nel_database('core')->exec('ALTER TABLE "nelliel_word_filters" RENAME TO nelliel_wordfilters');

                echo ' - ' . __('Wordfilters table updated.') . '<br>';

                // Update log tables
                nel_database('core')->exec('ALTER TABLE "nelliel_logs" RENAME TO nelliel_system_logs');
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_system_logs" ADD COLUMN message_values TEXT NOT NULL DEFAULT \'\'');

                $ips = nel_database('core')->executeFetchAll('SELECT "ip_address" FROM "nelliel_system_logs"',
                    PDO::FETCH_ASSOC);

                foreach ($ips as $ip) {
                    if ($ip['ip_address'] !== nel_prepare_ip_for_storage($ip['ip_address'])) {
                        $ip_fix = nel_database('core')->prepare(
                            'UPDATE "nelliel_system_logs" SET "ip_address" = ? WHERE "ip_address" = ?');
                        $ip_fix->bindValue(1, nel_prepare_ip_for_storage($ip['ip_address']));
                        $ip_fix->bindValue(2, $ip['ip_address']);
                        nel_database('core')->executePrepared($ip_fix);
                    }
                }

                $public_logs_table = new TableLogs(nel_database('core'), nel_utilities()->sqlCompatibility());
                $public_logs_table->tableName('nelliel_public_logs');
                $public_logs_table->createTable();

                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB' || $core_sqltype === 'POSTGRESQL') {
                    nel_database('core')->exec('ALTER TABLE "nelliel_system_logs" DROP COLUMN "channel"');
                } else {
                    nel_database('core')->exec('UPDATE "nelliel_system_logs" SET "channel" = \'\'');
                }

                echo ' - ' . __('Log tables updated.') . '<br>';

                // Update board settings
                $new_board_settings = ['allow_no_markup', 'allow_op_thread_moderation', 'mod_links_move',
                    'allow_moving_replies', 'allow_moving_uploads', 'mod_links_spoiler', 'mod_links_unspoiler'];
                $this->newBoardSettings($new_board_settings);

                $old_board_setting_names = ['mod_links_delimiter_left', 'mod_links_delimiter_right'];
                $new_board_setting_names = ['mod_links_left_bracket', 'mod_links_right_bracket'];
                $this->renameBoardSettings($old_board_setting_names, $new_board_setting_names);

                $this->removeBoardSettings(['mod_links_edit_post']);

                echo ' - ' . __('Board settings updated.') . '<br>';

                // Update site settings
                $new_site_settings = ['max_page_regen_time'];
                $this->newSiteSettings($new_site_settings);

                echo ' - ' . __('Site settings updated.') . '<br>';

                $migration_count ++;

            case 'v0.9.29':
                echo '<br>' . __('Updating from v0.9.29 to v0.9.30...') . '<br>';

                // Update settings table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec(
                        'ALTER TABLE "nelliel_settings" MODIFY "default_value" LONGTEXT DEFAULT NULL');

                    echo ' - ' . __('Settings table updated.') . '<br>';
                }

                // Update setting options table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec(
                        'ALTER TABLE "nelliel_setting_options" MODIFY "menu_data" LONGTEXT DEFAULT NULL');

                    echo ' - ' . __('Setting options table updated.') . '<br>';
                }

                // Update site and table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec(
                        'ALTER TABLE "nelliel_settings" MODIFY "default_value" LONGTEXT DEFAULT NULL');

                    echo ' - ' . __('Site config updated.') . '<br>';
                }

                // Update site config table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec(
                        'ALTER TABLE "nelliel_site_config" MODIFY "setting_value" LONGTEXT DEFAULT NULL');

                    echo ' - ' . __('Site config table updated.') . '<br>';
                }

                // Update site settings
                $new_site_settings = ['pm_snippet_length', 'min_time_between_site_stat_updates',
                    'min_time_between_board_stat_updates', 'enable_captchas', 'use_native_captcha', 'overboard_name',
                    'overboard_catalog', 'sfw_overboard_name', 'sfw_overboard_catalog', 'private_message_time_format',
                    'blotter_time_format', 'news_time_format', 'control_panel_list_time_format', 'time_zone',
                    'show_bottom_banners', 'show_top_styles', 'show_bottom_styles'];
                $this->newSiteSettings($new_site_settings);

                $old_site_setting_names = ['show_banners'];
                $new_site_setting_names = ['show_top_banners'];
                $this->renameSiteSettings($old_site_setting_names, $new_site_setting_names);

                $removed_site_settings = ['recaptcha_site_key', 'recaptcha_sekrit_key', 'recaptcha_type',
                    'use_login_recaptcha', 'use_register_recaptcha', 'use_post_recaptcha', 'use_report_recaptcha'];
                $this->removeSiteSettings($removed_site_settings);

                echo ' - ' . __('Site settings updated.') . '<br>';

                // Update board configs and defaults tables
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec(
                        'ALTER TABLE "nelliel_board_configs" MODIFY "setting_value" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec(
                        'ALTER TABLE "nelliel_board_defaults" MODIFY "setting_value" LONGTEXT DEFAULT NULL');

                    echo ' - ' . __('Board configs and defaults tables updated.') . '<br>';
                }

                // Update board settings
                $new_board_settings = ['allow_shadow_message', 'shadow_message_override', 'r9k_enable_board',
                    'r9k_global_unoriginal_check', 'r9k_strip_repeating', 'r9k_include_unicode_letters',
                    'r9k_unoriginal_mute', 'r9k_global_mute_check', 'r9k_mute_time_range', 'r9k_mute_base_number',
                    'upload_renzoku', 'scale_upload_filesize_units', 'scale_new_post_filesize_units',
                    'display_iec_filesize_units', 'binary_filesize_conversion', 'filesize_precision',
                    'filesize_unit_prefix', 'auto_archive_min_replies', 'mod_links_merge', 'catalog_tile_width',
                    'catalog_tile_height', 'catalog_show_multiple_uploads', 'catalog_first_preview_full_size',
                    'catalog_max_multi_preview_display_width', 'catalog_max_multi_preview_display_height',
                    'catalog_max_uploads_row', 'first_preview_own_row', 'show_index_link', 'catalog_nav_top',
                    'catalog_nav_bottom', 'content_links_reply', 'content_links_expand_thread',
                    'content_links_collapse_thread', 'content_links_show_thread', 'content_links_hide_thread',
                    'content_links_show_post', 'content_links_hide_post', 'content_links_show_file',
                    'content_links_hide_file', 'content_links_show_embed', 'content_links_hide_embed',
                    'content_links_show_upload', 'content_links_hide_upload', 'content_links_cite_post',
                    'content_links_download_file', 'content_links_first_posts', 'content_links_last_posts', 'time_zone',
                    'show_bottom_banners', 'show_top_banners_on_boards', 'show_bottom_banners_on_boards',
                    'show_top_styles', 'show_bottom_styles', 'enable_uploads'];
                $this->newBoardSettings($new_board_settings);

                $old_board_setting_names = ['max_catalog_display_width', 'max_catalog_display_height',
                    'ban_page_date_format', 'show_banners'];
                $new_board_setting_names = ['catalog_max_preview_display_width', 'catalog_max_preview_display_height',
                    'ban_page_time_format', 'show_top_banners'];
                $this->renameBoardSettings($old_board_setting_names, $new_board_setting_names);

                $removed_board_settings = ['post_date_format'];
                $this->removeBoardSettings($removed_board_settings);

                echo ' - ' . __('Board settings updated.') . '<br>';

                // Update thread tables
                $db_prefixes = nel_database('core')->executeFetchAll('SELECT "db_prefix" FROM "nelliel_board_data"',
                    PDO::FETCH_COLUMN);

                foreach ($db_prefixes as $prefix) {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_threads' . '" ADD COLUMN shadow SMALLINT NOT NULL DEFAULT 0');
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_threads' . '" ADD COLUMN bump_count SMALLINT NOT NULL DEFAULT 0');
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_threads' . '" ADD COLUMN salt VARCHAR(255) NOT NULL DEFAULT \'\'');
                }

                echo ' - ' . __('Thread tables updated.') . '<br>';

                // Update post tables
                $db_prefixes = nel_database('core')->executeFetchAll('SELECT "db_prefix" FROM "nelliel_board_data"',
                    PDO::FETCH_COLUMN);

                foreach ($db_prefixes as $prefix) {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_posts' . '" ADD COLUMN shadow SMALLINT NOT NULL DEFAULT 0');

                    if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB' || $core_sqltype === 'POSTGRESQL') {
                        nel_database('core')->exec(
                            'ALTER TABLE "' . $prefix . '_posts' . '" DROP COLUMN "content_hash"');
                    } else {
                        nel_database('core')->exec('UPDATE "' . $prefix . '_posts' . '" SET "content_hash" = \'\'');
                    }

                    if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                        nel_database('core')->exec(
                            'ALTER TABLE "' . $prefix . '_posts' . '" MODIFY comment LONGTEXT DEFAULT NULL');
                        nel_database('core')->exec(
                            'ALTER TABLE "' . $prefix . '_posts' . '" MODIFY cache LONGTEXT DEFAULT NULL');
                    }
                }

                echo ' - ' . __('Post tables updated.') . '<br>';

                // Update upload tables
                $db_prefixes = nel_database('core')->executeFetchAll('SELECT "db_prefix" FROM "nelliel_board_data"',
                    PDO::FETCH_COLUMN);

                foreach ($db_prefixes as $prefix) {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_uploads' . '" ADD COLUMN shadow SMALLINT NOT NULL DEFAULT 0');
                }

                echo ' - ' . __('Upload tables updated.') . '<br>';

                // Create markup table
                $markup_table = new TableMarkup(nel_database('core'), nel_utilities()->sqlCompatibility());
                $markup_table->createTable();

                echo ' - ' . __('Markup table added.') . '<br>';

                // Update permissions and role permissions tables
                $permissions_table = new TablePermissions(nel_database('core'), nel_utilities()->sqlCompatibility());
                $permissions_table->insertDefaults();
                $this->addRolePermission('perm_plugins_manage');
                $this->addRolePermission('perm_manage_markup');
                $this->addRolePermission('perm_manage_private_messages');
                $this->addRolePermission('perm_manage_scripts');

                echo ' - ' . __('Permissions and role permissions tables updated.') . '<br>';

                /*
                 * if (version_compare(NELLIEL_VERSION, 'v0.9.30', '<')) {
                 * // Update permissions and role permissions table
                 * $permissions_table = new TablePermissions(nel_database('core'), nel_utilities()->sqlCompatibility());
                 * $permissions_table->insertDefaults();
                 * $role_permissions_table = new TableRolePermissions(nel_database('core'),
                 * nel_utilities()->sqlCompatibility());
                 * $role_permissions_table->insertDefaults();
                 *
                 * echo ' - ' . __('Permissions and role permissions tables updated.') . '<br>';
                 * }
                 */

                // Create R9K content and mutes tables
                $r9k_content_table = new TableR9KContent(nel_database('core'), nel_utilities()->sqlCompatibility());
                $r9k_content_table->createTable();
                $r9k_mutes_table = new TableR9KMutes(nel_database('core'), nel_utilities()->sqlCompatibility());
                $r9k_mutes_table->createTable();

                echo ' - ' . __('R9K content and mutes tables added.') . '<br>';

                // Create statistics table
                $statistics_table = new TableStatistics(nel_database('core'), nel_utilities()->sqlCompatibility());
                $statistics_table->createTable();

                echo ' - ' . __('Statistics table added.') . '<br>';

                // Create scripts table
                $scripts_table = new TableScripts(nel_database('core'), nel_utilities()->sqlCompatibility());
                $scripts_table->createTable();

                echo ' - ' . __('Scripts table added.') . '<br>';

                // Update overboard table
                nel_database('core')->exec('DROP TABLE "nelliel_overboard"');
                $overboard_table = new TableOverboard(nel_database('core'), nel_utilities()->sqlCompatibility());
                $overboard_table->createTable();
                $overboard = new Overboard(nel_database('core'));
                $overboard->rebuild();

                echo ' - ' . __('Overboard table updated.') . '<br>';

                // Update news table
                nel_database('core')->exec('ALTER TABLE "nelliel_news" RENAME TO nelliel_old_news');
                $news_table = new TableNews(nel_database('core'), nel_utilities()->sqlCompatibility());
                $news_table->createTable();
                nel_database('core')->exec('INSERT INTO "nelliel_news" SELECT * FROM "nelliel_old_news"');
                nel_database('core')->exec('DROP TABLE "nelliel_old_news"');

                echo ' - ' . __('News table updated.') . '<br>';

                // Update pages table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec('ALTER TABLE "nelliel_pages" MODIFY "text" LONGTEXT DEFAULT NULL');

                    echo ' - ' . __('Pages table updated.') . '<br>';
                }

                // Update cache table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec('ALTER TABLE "nelliel_cache" MODIFY "cache_data" LONGTEXT DEFAULT NULL');

                    echo ' - ' . __('Cache table updated.') . '<br>';
                }

                // Update noticeboard table
                nel_database('core')->exec('ALTER TABLE "nelliel_noticeboard" RENAME TO nelliel_old_noticeboard');
                $noticeboard_table = new TableNoticeboard(nel_database('core'), nel_utilities()->sqlCompatibility());
                $noticeboard_table->createTable();
                nel_database('core')->exec('INSERT INTO "nelliel_noticeboard" SELECT * FROM "nelliel_old_noticeboard"');
                nel_database('core')->exec('DROP TABLE "nelliel_old_noticeboard"');

                echo ' - ' . __('Noticeboard table updated.') . '<br>';

                if (version_compare(NELLIEL_VERSION, 'v0.9.30', '=')) {
                    // Update log tables
                    nel_database('core')->exec('ALTER TABLE "nelliel_system_logs" RENAME TO nelliel_old_system_logs');
                    $system_logs_table = new TableLogs(nel_database('core'), nel_utilities()->sqlCompatibility());
                    $system_logs_table->tableName('nelliel_system_logs');
                    $system_logs_table->createTable();
                    nel_database('core')->exec('INSERT INTO "nelliel_system_logs" SELECT * FROM "nelliel_system_logs"');
                    nel_database('core')->exec('DROP TABLE "nelliel_system_logs"');

                    nel_database('core')->exec('ALTER TABLE "nelliel_public_logs" RENAME TO nelliel_old_public_logs');
                    $public_logs_table = new TableLogs(nel_database('core'), nel_utilities()->sqlCompatibility());
                    $public_logs_table->tableName('nelliel_public_logs');
                    $public_logs_table->createTable();
                    nel_database('core')->exec('INSERT INTO "nelliel_public_logs" SELECT * FROM "nelliel_public_logs"');
                    nel_database('core')->exec('DROP TABLE "nelliel_public_logs"');

                    echo ' - ' . __('Log tables updated.') . '<br>';
                }

                $migration_count ++;

            case 'v0.9.30':
                echo '<br>' . __('Updating from v0.9.30 to ???...') . '<br>';

                // Update site settings
                $new_site_settings = ['error_message_header', 'ipv6_identification_cidr', 'ipv4_small_subnet_cidr',
                    'ipv4_large_subnet_cidr', 'ipv6_small_subnet_cidr', 'ipv6_large_subnet_cidr', 'error_image_set',
                    'error_image_max_size', 'show_error_images'];
                $this->newSiteSettings($new_site_settings);

                $removed_site_settings = ['post_password_algorithm'];
                $this->removeSiteSettings($removed_site_settings);

                echo ' - ' . __('Site settings updated.') . '<br>';

                // Update board settings
                $new_board_settings = ['list_all_dice_rolls', 'preview_lazy_loading'];
                $this->newBoardSettings($new_board_settings);

                echo ' - ' . __('Board settings updated.') . '<br>';

                // Update moar database columns
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec('ALTER TABLE "nelliel_bans" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_blotter" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_board_data" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_cache" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_capcodes" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_captcha" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_content_ops" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec(
                        'ALTER TABLE "nelliel_domain_registry" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_embeds" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_file_filters" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_filetypes" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_ip_notes" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_markup" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_news" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_noticeboard" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_overboard" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_pages" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_permissions" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec(
                        'ALTER TABLE "nelliel_private_messages" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_r9k_content" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_r9k_mutes" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_reports" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_roles" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_scripts" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec(
                        'ALTER TABLE "nelliel_setting_options" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_settings" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_statistics" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_users" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    nel_database('core')->exec('ALTER TABLE "nelliel_wordfilters" MODIFY "moar" LONGTEXT DEFAULT NULL');

                    $db_prefixes = nel_database('core')->executeFetchAll('SELECT "db_prefix" FROM "nelliel_board_data"',
                        PDO::FETCH_COLUMN);

                    foreach ($db_prefixes as $prefix) {
                        nel_database('core')->exec(
                            'ALTER TABLE "' . $prefix . '_threads' . '" MODIFY "moar" LONGTEXT DEFAULT NULL');
                        nel_database('core')->exec(
                            'ALTER TABLE "' . $prefix . '_posts' . '" MODIFY "moar" LONGTEXT DEFAULT NULL');
                        nel_database('core')->exec(
                            'ALTER TABLE "' . $prefix . '_uploads' . '" MODIFY "moar" LONGTEXT DEFAULT NULL');
                        nel_database('core')->exec(
                            'ALTER TABLE "' . $prefix . '_archives' . '" MODIFY "moar" LONGTEXT DEFAULT NULL');
                    }

                    echo ' - ' . __('Moar database columns updated.') . '<br>';
                }

                // Add moar moar columns
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_board_configs" ADD COLUMN moar ' .
                    nel_utilities()->sqlCompatibility()->textType('LONGTEXT') . ' DEFAULT NULL');
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_board_defaults" ADD COLUMN moar ' .
                    nel_utilities()->sqlCompatibility()->textType('LONGTEXT') . ' DEFAULT NULL');
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_image_sets" ADD COLUMN moar ' .
                    nel_utilities()->sqlCompatibility()->textType('LONGTEXT') . ' DEFAULT NULL');
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_plugins" ADD COLUMN moar ' .
                    nel_utilities()->sqlCompatibility()->textType('LONGTEXT') . ' DEFAULT NULL');
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_site_config" ADD COLUMN moar ' .
                    nel_utilities()->sqlCompatibility()->textType('LONGTEXT') . ' DEFAULT NULL');
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_styles" ADD COLUMN moar ' .
                    nel_utilities()->sqlCompatibility()->textType('LONGTEXT') . ' DEFAULT NULL');
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_templates" ADD COLUMN moar ' .
                    nel_utilities()->sqlCompatibility()->textType('LONGTEXT') . ' DEFAULT NULL');

                echo ' - ' . __('Moar moar columns added to database.') . '<br>';

                // Update permissions and role permissions tables
                $permissions = ['perm_ip_notes_add' => 'perm_add_ip_notes',
                    'perm_ip_notes_delete' => 'perm_delete_ip_notes'];
                $permission_update = nel_database('core')->prepare(
                    'UPDATE "nelliel_permissions" SET "permission" = :new WHERE "permission" = :old');

                foreach ($permissions as $old => $new) {
                    $permission_update->bindValue(':new', $new);
                    $permission_update->bindValue(':old', $old);
                    nel_database('core')->executePrepared($permission_update);
                }

                $permissions_table = new TablePermissions(nel_database('core'), nel_utilities()->sqlCompatibility());
                $permissions_table->insertDefaults();
                $role_permissions_table = new TableRolePermissions(nel_database('core'),
                    nel_utilities()->sqlCompatibility());
                $role_permissions_table->insertDefaults();

                echo ' - ' . __('Updated permissions and role permissions tables.') . '<br>';

                // Add IP info table
                $ip_info_table = new TableIPInfo(nel_database('core'), nel_utilities()->sqlCompatibility());
                $ip_info_table->createTable();

                echo ' - ' . __('Added IP info table.') . '<br>';

                // Update thread, post and upload tables
                $db_prefixes = nel_database('core')->executeFetchAll('SELECT "db_prefix" FROM "nelliel_board_data"',
                    PDO::FETCH_COLUMN);

                foreach ($db_prefixes as $prefix) {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_threads' . '" RENAME TO ' . $prefix . '_threads_old');
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_posts' . '" RENAME TO ' . $prefix . '_posts_old');
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_uploads' . '" RENAME TO ' . $prefix . '_uploads_old');
                    nel_database('core')->exec('DROP INDEX "ix_' . $prefix . '_posts__parent_thread"');
                    nel_database('core')->exec('DROP INDEX "ix_' . $prefix . '_posts__hashed_ip_address"');
                    nel_database('core')->exec('DROP INDEX "ix_' . $prefix . '_uploads__post_ref"');
                    nel_database('core')->exec('DROP INDEX "ix_' . $prefix . '_uploads__filename"');
                    nel_database('core')->exec('DROP INDEX "ix_' . $prefix . '_uploads__static_preview_name"');
                    nel_database('core')->exec('DROP INDEX "ix_' . $prefix . '_uploads__animated_preview_name"');
                    nel_database('core')->exec('DROP INDEX "ix_' . $prefix . '_uploads__md5"');

                    $threads_table = new TableThreads(nel_database('core'), nel_utilities()->sqlCompatibility());
                    $threads_table->tableName($prefix . '_threads');
                    $threads_table->createTable();
                    $posts_table = new TablePosts(nel_database('core'), nel_utilities()->sqlCompatibility());
                    $posts_table->tableName($prefix . '_posts');
                    $posts_table->createTable(['threads_table' => $prefix . '_threads']);
                    $uploads_table = new TableUploads(nel_database('core'), nel_utilities()->sqlCompatibility());
                    $uploads_table->tableName($prefix . '_uploads');
                    $uploads_table->createTable(
                        ['threads_table' => $prefix . '_threads', 'posts_table' => $prefix . '_posts']);

                    $ips = nel_database('core')->executeFetchAll(
                        'SELECT "hashed_ip_address", "ip_address" FROM "' . $prefix . '_posts_old"', PDO::FETCH_ASSOC);
                    $ip_transfer = nel_database('core')->prepare(
                        'INSERT INTO "nelliel_ip_info" ("hashed_ip_address", "ip_address") VALUES (?, ?)');

                    foreach ($ips as $ip) {
                        if (!nel_database('core')->rowExists('nelliel_ip_info', ['hashed_ip_address'],
                            [$ip['hashed_ip_address']]) &&
                            !nel_database('core')->rowExists('nelliel_ip_info', ['ip_address'], [$ip['ip_address']])) {
                            $ip_transfer->bindValue(1, $ip['hashed_ip_address']);
                            $ip_transfer->bindValue(2, $ip['ip_address']);
                            nel_database('core')->executePrepared($ip_transfer);
                        }
                    }

                    nel_database('core')->exec(
                        'INSERT INTO "' . $prefix .
                        '_threads"
                        SELECT "thread_id", "bump_time", "bump_time_milli", "last_update", "last_update_milli", "post_count",
                            "bump_count", "total_uploads", "file_count", "embed_count", "permasage", "sticky", "cyclic", "old", "preserve", "locked",
                            "shadow", "slug", "salt", "regen_cache", "cache", "moar"
                        FROM "' . $prefix . '_threads_old"');
                    nel_database('core')->exec(
                        'INSERT INTO "' . $prefix .
                        '_posts"
                        SELECT "post_number", "parent_thread", "reply_to", "name", "password", "tripcode", "secure_tripcode", "capcode", "email", "subject", "comment",
                            "hashed_ip_address", "ip_address", "visitor_id", "post_time", "post_time_milli", "total_uploads", "file_count", "embed_count", "op",
                            "sage", "shadow", "username", "mod_comment", "regen_cache", "cache", "moar"
                        FROM "' . $prefix . '_posts_old"');
                    nel_database('core')->exec(
                        'INSERT INTO "' . $prefix .
                        '_uploads"
                        SELECT "upload_id", "parent_thread", "post_ref", "upload_order", "category", "format", "mime", "filename", "extension",
                            "original_filename", "display_width", "display_height", "static_preview_name", "animated_preview_name", "preview_width",
                            "preview_height", "filesize", "md5", "sha1", "sha256", "sha512", "embed_url", "spoiler", "deleted", "shadow",
                            "exif", "regen_cache", "cache", "moar"
                        FROM "' . $prefix . '_uploads_old"');

                    nel_database('core')->exec('DROP TABLE "' . $prefix . '_uploads_old"');
                    nel_database('core')->exec('DROP TABLE "' . $prefix . '_posts_old"');
                    nel_database('core')->exec('DROP TABLE "' . $prefix . '_threads_old"');
                }

                echo ' - ' . __('Thread, post and upload tables updated.') . '<br>';

                // Update bans table
                nel_database('core')->exec('ALTER TABLE "nelliel_bans" RENAME TO nelliel_bans_old');

                $appeals_exist = nel_database('core')->tableExists('nelliel_ban_appeals');

                if ($appeals_exist) {
                    nel_database('core')->exec('ALTER TABLE "nelliel_ban_appeals" RENAME TO nelliel_ban_appeals_old');
                }

                nel_database('core')->exec('DROP INDEX "hashed_ip_address"');

                $threads_table = new TableBans(nel_database('core'), nel_utilities()->sqlCompatibility());
                $threads_table->createTable();
                $threads_table = new TableBanAppeals(nel_database('core'), nel_utilities()->sqlCompatibility());
                $threads_table->createTable();

                $ips = nel_database('core')->executeFetchAll(
                    'SELECT "hashed_ip_address", "ip_address" FROM "nelliel_bans_old"', PDO::FETCH_ASSOC);
                $ip_transfer = nel_database('core')->prepare(
                    'INSERT INTO "nelliel_ip_info" ("hashed_ip_address", "ip_address") VALUES (?, ?)');

                foreach ($ips as $ip) {
                    if (!nel_database('core')->rowExists('nelliel_ip_info', ['hashed_ip_address', 'ip_address'],
                        [$ip['hashed_ip_address'], nel_prepare_ip_for_storage($ip['ip_address'])])) {
                        $ip_transfer->bindValue(1, $ip['hashed_ip_address']);
                        $ip_transfer->bindValue(2, nel_prepare_ip_for_storage($ip['ip_address']));
                        nel_database('core')->executePrepared($ip_transfer);
                    }
                }

                nel_database('core')->exec('INSERT INTO "nelliel_bans" SELECT * FROM "nelliel_bans_old"');

                if ($appeals_exist) {
                    nel_database('core')->exec(
                        'INSERT INTO "nelliel_ban_appeals" SELECT * FROM "nelliel_ban_appeals_old"');
                    nel_database('core')->exec('DROP TABLE "nelliel_ban_appeals_old"');
                }

                nel_database('core')->exec('DROP TABLE "nelliel_bans_old"');

                echo ' - ' . __('Bans table updated.') . '<br>';

                // Update log tables
                nel_database('core')->exec('ALTER TABLE "nelliel_system_logs" RENAME TO nelliel_system_logs_old');
                nel_database('core')->exec('ALTER TABLE "nelliel_public_logs" RENAME TO nelliel_public_logs_old');

                $logs_table = new TableLogs(nel_database('core'), nel_utilities()->sqlCompatibility());
                $logs_table->tableName('nelliel_system_logs');
                $logs_table->createTable();
                $logs_table->tableName('nelliel_public_logs');
                $logs_table->createTable();

                $system_ips = nel_database('core')->executeFetchAll(
                    'SELECT "hashed_ip_address", "ip_address" FROM "nelliel_system_logs_old"', PDO::FETCH_ASSOC);
                $public_ips = nel_database('core')->executeFetchAll(
                    'SELECT "hashed_ip_address", "ip_address" FROM "nelliel_public_logs_old"', PDO::FETCH_ASSOC);
                $ips = array_merge($system_ips, $public_ips);
                $ip_transfer = nel_database('core')->prepare(
                    'INSERT INTO "nelliel_ip_info" ("hashed_ip_address", "ip_address") VALUES (?, ?)');

                foreach ($ips as $ip) {
                    // Earlier log entries may not have a properly encoded IP
                    if (!nel_database('core')->rowExists('nelliel_ip_info', ['hashed_ip_address'],
                        [$ip['hashed_ip_address']]) &&
                        !nel_database('core')->rowExists('nelliel_ip_info', ['ip_address'], [$ip['ip_address']])) {
                        $ip_transfer->bindValue(1, $ip['hashed_ip_address'], PDO::PARAM_STR);
                        $ip_transfer->bindValue(2, nel_prepare_ip_for_storage($ip['ip_address']), PDO::PARAM_LOB);
                        nel_database('core')->executePrepared($ip_transfer);
                    } else {
                        $prepared = nel_database('core')->prepare(
                            'UPDATE "nelliel_system_logs" SET "ip_address" = NULL, "hashed_ip_address" = NULL WHERE "hashed_ip_address" = ?');
                        $prepared->bindValue(1, $ip['hashed_ip_address'], PDO::PARAM_STR);
                        nel_database('core')->executePrepared($prepared);
                    }
                }

                nel_database('core')->exec(
                    'INSERT INTO "nelliel_system_logs"
                    SELECT "log_id", "level", "event", "message", "message_values", "time", "domain_id", "username", "hashed_ip_address", "ip_address", "visitor_id", "moar"
                    FROM "nelliel_system_logs_old"');
                nel_database('core')->exec(
                    'INSERT INTO "nelliel_public_logs"
                    SELECT "log_id", "level", "event", "message", "message_values", "time", "domain_id", "username", "hashed_ip_address", "ip_address", "visitor_id", "moar"
                    FROM "nelliel_public_logs_old"');
                nel_database('core')->exec('DROP TABLE "nelliel_system_logs_old"');
                nel_database('core')->exec('DROP TABLE "nelliel_public_logs_old"');

                echo ' - ' . __('Log tables updated.') . '<br>';

                // Update reports table
                nel_database('core')->exec('ALTER TABLE "nelliel_reports" RENAME TO nelliel_reports_old');

                $threads_table = new TableReports(nel_database('core'), nel_utilities()->sqlCompatibility());
                $threads_table->createTable();

                $ips = nel_database('core')->executeFetchAll(
                    'SELECT "hashed_ip_address", "ip_address" FROM "nelliel_reports_old"', PDO::FETCH_ASSOC);
                $ip_transfer = nel_database('core')->prepare(
                    'INSERT INTO "nelliel_ip_info" ("hashed_ip_address", "ip_address") VALUES (?, ?)');

                foreach ($ips as $ip) {
                    if (!nel_database('core')->rowExists('nelliel_ip_info', ['hashed_ip_address', 'ip_address'],
                        [$ip['hashed_ip_address'], nel_prepare_ip_for_storage($ip['ip_address'])])) {
                        $ip_transfer->bindValue(1, $ip['hashed_ip_address']);
                        $ip_transfer->bindValue(2, nel_prepare_ip_for_storage($ip['ip_address']));
                        nel_database('core')->executePrepared($ip_transfer);
                    }
                }

                nel_database('core')->exec(
                    'INSERT INTO "nelliel_reports"
                    SELECT "report_id", "board_id", "content_id", "hashed_reporter_ip", "reporter_ip", "visitor_id", "reason", "moar" FROM "nelliel_reports_old"');
                nel_database('core')->exec('DROP TABLE "nelliel_reports_old"');

                echo ' - ' . __('Reports table updated.') . '<br>';

                // Update permissions and role permissions tables
                $permissions = ['perm_bans_view' => 'perm_view_bans', 'perm_bans_add' => 'perm_add_bans',
                    'perm_bans_modify' => 'perm_modify_bans', 'perm_bans_delete' => 'perm_delete_bans'];
                $permission_update = nel_database('core')->prepare(
                    'UPDATE "nelliel_permissions" SET "permission" = :new WHERE "permission" = :old');
                $permissions_table = new TablePermissions(nel_database('core'), nel_utilities()->sqlCompatibility());
                $permissions_table->insertDefaults();
                $this->addRolePermission('perm_add_range_bans');

                echo ' - ' . __('Permissions and role permissions tables updated.') . '<br>';

                // Update IP notes table
                nel_database('core')->exec('DROP TABLE "nelliel_ip_notes"'); // Simplest because we never used it before
                $ip_notes_table = new TableIPNotes(nel_database('core'), nel_utilities()->sqlCompatibility());
                $ip_notes_table->createTable();

                echo ' - ' . __('IP notes table updated.') . '<br>';

                // Update core image set info
                $image_set_instance = nel_site_domain()->frontEndData()->getImageSet('images-nelliel-basic');
                $enabled = $image_set_instance->enabled();
                $image_set_instance->install(true);
                $image_set_instance->enable($enabled);

                echo ' - ' . __('Image set info updated.') . '<br>';

                // Update overboard table
                nel_database('core')->exec('DROP TABLE "nelliel_overboard"');
                $overboard_table = new TableOverboard(nel_database('core'), nel_utilities()->sqlCompatibility());
                $overboard_table->createTable();
                $overboard = new Overboard(nel_database('core'));
                $overboard->rebuild();

                echo ' - ' . __('Overboard table updated.') . '<br>';

                $migration_count ++;
        }

        return $migration_count;
    }

    private function copyToSiteConfig(array $setting_names): void
    {
        $prepared = nel_database('core')->prepare(
            'INSERT INTO "' . NEL_SITE_CONFIG_TABLE .
            '" ("setting_name", "setting_value") SELECT "setting_name", "default_value" FROM "' . NEL_SETTINGS_TABLE .
            '" WHERE "setting_name" = ? AND "setting_category" = \'site\'');

        foreach ($setting_names as $name) {
            $prepared->bindValue(1, $name, PDO::PARAM_STR);
            nel_database('core')->executePrepared($prepared);
        }
    }

    private function copyToBoardConfig(string $board_id, array $setting_names): void
    {
        $prepared = nel_database('core')->prepare(
            'INSERT INTO "' . NEL_BOARD_CONFIGS_TABLE . '" ("board_id", "setting_name", "setting_value") SELECT \'' .
            $board_id . '\', "setting_name", "setting_value" FROM "' . NEL_BOARD_DEFAULTS_TABLE .
            '" WHERE "setting_name" = ?');

        foreach ($setting_names as $name) {
            $prepared->bindValue(1, $name, PDO::PARAM_STR);
            nel_database('core')->executePrepared($prepared);
        }
    }

    private function getAllBoardIDs(): array
    {
        $query = 'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"';
        $board_ids = nel_database('core')->executeFetchAll($query, PDO::FETCH_COLUMN);
        return $board_ids;
    }

    private function newSiteSettings(array $names, bool $reinsert = false): void
    {
        if (!$this->setting_defaults_inserted || $reinsert) {
            $settings_table = new TableSettings(nel_database('core'), nel_utilities()->sqlCompatibility());
            $settings_table->insertDefaults();
            $this->setting_defaults_inserted = true;
        }

        $setting_options_table = new TableSettingOptions(nel_database('core'), nel_utilities()->sqlCompatibility());
        $setting_options_table->insertDefaults();
        $this->copyToSiteConfig($names);
    }

    private function newBoardSettings(array $names, bool $reinsert = false): void
    {
        if (!$this->setting_defaults_inserted || $reinsert) {
            $settings_table = new TableSettings(nel_database('core'), nel_utilities()->sqlCompatibility());
            $settings_table->insertDefaults();
            $this->setting_defaults_inserted = true;
        }

        $setting_options_table = new TableSettingOptions(nel_database('core'), nel_utilities()->sqlCompatibility());
        $setting_options_table->insertDefaults();
        $board_defaults_table = new TableBoardDefaults(nel_database('core'), nel_utilities()->sqlCompatibility());
        $board_defaults_table->insertDefaults();
        $board_ids = $this->getAllBoardIDs();

        foreach ($board_ids as $id) {
            $this->copyToBoardConfig($id, $names);
        }
    }

    // We do renames this way for the main table because inserting defaults will add the new names already
    // We could get duplicate errors trying to rename directly
    private function renameSiteSettings(array $source_names, array $target_names): void
    {
        $this->newSiteSettings($target_names);

        $site_config_select = nel_database('core')->prepare(
            'SELECT "setting_value" FROM "' . NEL_SITE_CONFIG_TABLE . '" WHERE "setting_name" = :source_name');
        $site_config_update = nel_database('core')->prepare(
            'UPDATE "' . NEL_SITE_CONFIG_TABLE . '" SET "setting_value" = :new_value WHERE "setting_name" = :target_name');
        $name_count = count($source_names);

        for ($i = 0; $i < $name_count; $i ++) {
            $site_config_select->bindValue(':source_name', $source_names[$i]);
            $value = nel_database('core')->executePreparedFetch($site_config_select, null, PDO::FETCH_COLUMN);
            $site_config_update->bindValue(':new_value', $value);
            $site_config_update->bindValue(':target_name', $target_names[$i]);
            nel_database('core')->executePrepared($site_config_update);
        }

        $this->removeSiteSettings($source_names);
    }

    private function renameBoardSettings(array $source_names, array $target_names): void
    {
        $this->newBoardSettings($target_names);

        $board_defaults_select = nel_database('core')->prepare(
            'SELECT "setting_value" FROM "' . NEL_BOARD_DEFAULTS_TABLE . '" WHERE "setting_name" = :source_name');
        $board_defaults_update = nel_database('core')->prepare(
            'UPDATE "' . NEL_BOARD_DEFAULTS_TABLE .
            '" SET "setting_value" = :new_value WHERE "setting_name" = :target_name');
        $board_configs_select = nel_database('core')->prepare(
            'SELECT "setting_value" FROM "' . NEL_BOARD_CONFIGS_TABLE .
            '" WHERE "setting_name" = :source_name AND "board_id" = :board_id');
        $board_configs_update = nel_database('core')->prepare(
            'UPDATE "' . NEL_BOARD_CONFIGS_TABLE .
            '" SET "setting_value" = :new_value WHERE "setting_name" = :target_name AND "board_id" = :board_id');
        $name_count = count($source_names);
        $board_ids = $this->getAllBoardIDs();

        for ($i = 0; $i < $name_count; $i ++) {
            $board_defaults_select->bindValue(':source_name', $source_names[$i]);
            $value = nel_database('core')->executePreparedFetch($board_defaults_select, null, PDO::FETCH_COLUMN);
            $board_defaults_update->bindValue(':new_value', $value);
            $board_defaults_update->bindValue(':target_name', $target_names[$i]);
            nel_database('core')->executePrepared($board_defaults_update);
        }

        foreach ($board_ids as $board_id) {
            for ($i = 0; $i < $name_count; $i ++) {
                $board_configs_select->bindValue(':source_name', $source_names[$i]);
                $board_configs_select->bindValue(':board_id', $board_id);
                $value = nel_database('core')->executePreparedFetch($board_configs_select, null, PDO::FETCH_COLUMN);
                $board_configs_update->bindValue(':new_value', $value);
                $board_configs_update->bindValue(':target_name', $target_names[$i]);
                $board_configs_update->bindValue(':board_id', $board_id);
                nel_database('core')->executePrepared($board_configs_update);
            }
        }

        $this->removeBoardSettings($source_names);
    }

    private function removeSiteSettings(array $names): void
    {
        $settings_delete = nel_database('core')->prepare(
            'DELETE FROM "' . NEL_SETTINGS_TABLE . '" WHERE "setting_name" = :name AND "setting_category" = \'site\'');
        $site_config_delete = nel_database('core')->prepare(
            'DELETE FROM "' . NEL_SITE_CONFIG_TABLE . '" WHERE "setting_name" = :name');
        $name_count = count($names);

        for ($i = 0; $i < $name_count; $i ++) {
            $settings_delete->bindValue(':name', $names[$i]);
            nel_database('core')->executePrepared($settings_delete);

            $site_config_delete->bindValue(':name', $names[$i]);
            nel_database('core')->executePrepared($site_config_delete);
        }
    }

    private function removeBoardSettings(array $names): void
    {
        $settings_delete = nel_database('core')->prepare(
            'DELETE FROM "' . NEL_SETTINGS_TABLE . '" WHERE "setting_name" = :name AND "setting_category" = \'board\'');
        $board_defaults_delete = nel_database('core')->prepare(
            'DELETE FROM "' . NEL_BOARD_DEFAULTS_TABLE . '" WHERE "setting_name" = :name');
        $board_configs_delete = nel_database('core')->prepare(
            'DELETE FROM "' . NEL_BOARD_CONFIGS_TABLE . '" WHERE "setting_name" = :name');
        $name_count = count($names);

        for ($i = 0; $i < $name_count; $i ++) {
            $settings_delete->bindValue(':name', $names[$i]);
            nel_database('core')->executePrepared($settings_delete);

            $board_defaults_delete->bindValue(':name', $names[$i]);
            nel_database('core')->executePrepared($board_defaults_delete);

            $board_configs_delete->bindValue(':name', $names[$i]);
            nel_database('core')->executePrepared($board_configs_delete);
        }
    }

    private function addRolePermission(string $permission)
    {
        $role_ids = nel_database('core')->executeFetchAll('SELECT "role_id" FROM "nelliel_roles"', PDO::FETCH_COLUMN);
        $add_role_permission = nel_database('core')->prepare(
            'INSERT INTO "nelliel_role_permissions"
                    ("role_id", "permission", "perm_setting") VALUES (?, ?, 0)');

        foreach ($role_ids as $role_id) {
            if (nel_database('core')->rowExists('nelliel_role_permissions', ['role_id', 'permission'],
                [$role_id, $permission])) {
                continue;
            }

            $add_role_permission->bindValue(1, $role_id);
            $add_role_permission->bindValue(2, $permission);
            nel_database('core')->executePrepared($add_role_permission);
        }
    }
}