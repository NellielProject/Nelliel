<?php
declare(strict_types = 1);

namespace Nelliel\Setup;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Tables\TableBanAppeals;
use Nelliel\Tables\TableBoardDefaults;
use Nelliel\Tables\TableLogs;
use Nelliel\Tables\TableMarkup;
use Nelliel\Tables\TablePermissions;
use Nelliel\Tables\TableRolePermissions;
use Nelliel\Tables\TableSettingOptions;
use Nelliel\Tables\TableSettings;
use Nelliel\Utility\FileHandler;
use PDO;
use Nelliel\Tables\TableR9KContent;
use Nelliel\Tables\TableR9KMutes;
use Nelliel\Tables\TableStatistics;

class BetaMigrations
{
    private $file_handler;
    private $upgrade;

    function __construct(FileHandler $file_handler, Upgrade $upgrade)
    {
        $this->file_handler = $file_handler;
        $this->upgrade = $upgrade;
    }

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
                nel_database('core')->exec('ALTER TABLE "nelliel_filetypes" DROP COLUMN "mime"');

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

                // Update permissions and role permissions table
                $permissions_table = new TablePermissions(nel_database('core'), nel_utilities()->sqlCompatibility());
                $permissions_table->insertDefaults();
                $role_permissions_table = new TableRolePermissions(nel_database('core'),
                    nel_utilities()->sqlCompatibility());
                $role_permissions_table->insertDefaults();

                echo ' - ' . __('Permissions and role permissions tables updated.') . '<br>';

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
                    'show_form_max_filesize', 'show_thumbnailed_message', 'show_video_preview', 'post_date_format'];
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

                // Create ban appeals table
                $ban_appeals_table = new TableBanAppeals(nel_database('core'), nel_utilities()->sqlCompatibility());
                $ban_appeals_table->createTable();

                echo ' - ' . __('Ban appeals table added.') . '<br>';

                // Update bans
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB' || $core_sqltype === 'POSTGRESQL') {
                    nel_database('core')->exec('ALTER TABLE "nelliel_bans" DROP COLUMN appeal');
                    nel_database('core')->exec('ALTER TABLE "nelliel_bans" DROP COLUMN appeal_response');
                    nel_database('core')->exec('ALTER TABLE "nelliel_bans" DROP COLUMN appeal_status');
                }

                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_bans" ADD COLUMN appeal_allowed SMALLINT NOT NULL DEFAULT 0');

                echo ' - ' . __('Updated bans table.') . '<br>';

                // Update users
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_users" ADD COLUMN display_name VARCHAR(255) NOT NULL DEFAULT \'\'');

                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB' || $core_sqltype === 'POSTGRESQL') {
                    nel_database('core')->exec('ALTER TABLE "nelliel_users" DROP COLUMN locked');
                }

                echo ' - ' . __('Updated users table.') . '<br>';

                $migration_count ++;

            case 'v0.9.28':
                echo '<br>' . __('Updating from v0.9.28 to v0.9.29...') . '<br>';

                // Update file filters
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_file_filters" ADD COLUMN enabled SMALLINT NOT NULL DEFAULT 0');

                echo ' - ' . __('Updated file filters table.') . '<br>';

                // Update site and global domain IDs
                $prepared = nel_database('core')->exec(
                    'UPDATE "nelliel_domain_registry" SET "domain_id" = \'site\' WHERE "domain_id" = \'_site_\'');
                $prepared = nel_database('core')->exec(
                    'UPDATE "nelliel_domain_registry" SET "domain_id" = \'global\' WHERE "domain_id" = \'_global_\'');

                echo ' - ' . __('Updated site and global domain IDs.') . '<br>';

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

                echo ' - ' . __('Updated roles table.') . '<br>';

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

                echo ' - ' . __('Updated permissions and role permissions tables.') . '<br>';

                // Update wordfilters table
                nel_database('core')->exec('ALTER TABLE "nelliel_word_filters" RENAME TO nelliel_wordfilters');

                echo ' - ' . __('Updated wordfilters table.') . '<br>';

                // Update log tables
                nel_database('core')->exec('ALTER TABLE "nelliel_logs" RENAME TO nelliel_system_logs');
                nel_database('core')->exec('ALTER TABLE "nelliel_system_logs" DROP COLUMN "channel"');
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_system_logs" ADD COLUMN message_values TEXT NOT NULL DEFAULT \'\'');
                $public_logs_table = new TableLogs(nel_database('core'), nel_utilities()->sqlCompatibility());
                $public_logs_table->tableName('nelliel_public_logs');
                $public_logs_table->createTable();

                echo ' - ' . __('Updated log tables.') . '<br>';

                // Update board settings
                $new_board_settings = ['allow_no_markup', 'allow_op_thread_moderation', 'mod_links_move',
                    'allow_moving_replies', 'allow_moving_uploads', 'mod_links_spoiler', 'mod_links_unspoiler'];
                $this->newBoardSettings($new_board_settings);

                $old_board_setting_names = ['mod_links_edit_post'];
                $new_board_setting_names = ['mod_links_edit'];
                $this->renameBoardSettings($old_board_setting_names, $new_board_setting_names);

                echo ' - ' . __('Board settings updated.') . '<br>';

                // Update site settings
                $new_site_settings = ['max_page_regen_time'];
                $this->newSiteSettings($new_site_settings);

                echo ' - ' . __('Site settings updated.') . '<br>';

                $migration_count ++;

            case 'v0.9.29':
                echo '<br>' . __('Updating from v0.9.29 to ???...') . '<br>';

                // Update site settings
                $new_site_settings = ['pm_snippet_length'];
                $this->newSiteSettings($new_site_settings);

                echo ' - ' . __('Site settings updated.') . '<br>';

                // Update board settings
                $new_board_settings = ['allow_shadow_message', 'shadow_message_override', 'r9k_enable_board',
                    'r9k_global_unoriginal_check', 'r9k_strip_repeating', 'r9k_include_unicode_letters',
                    'r9k_unoriginal_mute', 'r9k_global_mute_check', 'r9k_mute_time_range', 'r9k_mute_base_number',
                    'upload_renzoku'];
                $this->newBoardSettings($new_board_settings);

                echo ' - ' . __('Board settings updated.') . '<br>';

                // Update thread tables
                $db_prefixes = nel_database('core')->executeFetchAll('SELECT "db_prefix" FROM "nelliel_board_data"',
                    PDO::FETCH_COLUMN);

                foreach ($db_prefixes as $prefix) {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_threads' . '" ADD COLUMN shadow SMALLINT NOT NULL DEFAULT 0');
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_threads' . '" ADD COLUMN bump_count SMALLINT NOT NULL DEFAULT 0');
                }

                echo ' - ' . __('Thread tables updated.') . '<br>';

                // Update post tables
                $db_prefixes = nel_database('core')->executeFetchAll('SELECT "db_prefix" FROM "nelliel_board_data"',
                    PDO::FETCH_COLUMN);

                foreach ($db_prefixes as $prefix) {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_posts' . '" ADD COLUMN shadow SMALLINT NOT NULL DEFAULT 0');
                    nel_database('core')->exec('ALTER TABLE "' . $prefix . '_posts' . '" DROP COLUMN "content_hash"');
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

                // Update permissions and role permissions table
                $permissions_table = new TablePermissions(nel_database('core'), nel_utilities()->sqlCompatibility());
                $permissions_table->insertDefaults();
                $role_permissions_table = new TableRolePermissions(nel_database('core'),
                    nel_utilities()->sqlCompatibility());
                $role_permissions_table->insertDefaults();

                echo ' - ' . __('Permissions and role permissions tables updated.') . '<br>';

                // Create R9K content and mutes tables
                $r9k_content_table = new TableR9KContent($this->database, $this->sql_compatibility);
                $r9k_content_table->createTable();
                $r9k_mutes_table = new TableR9KMutes($this->database, $this->sql_compatibility);
                $r9k_mutes_table->createTable();

                echo ' - ' . __('R9K content and mutes tables added.') . '<br>';

                // Create statistics table
                $statistics_table = new TableStatistics(nel_database('core'), nel_utilities()->sqlCompatibility());
                $statistics_table->createTable();

                echo ' - ' . __('Statistics table added.') . '<br>';

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

    private function newSiteSettings(array $names): void
    {
        $settings_table = new TableSettings(nel_database('core'), nel_utilities()->sqlCompatibility());
        $settings_table->insertDefaults();
        $setting_options_table = new TableSettingOptions(nel_database('core'), nel_utilities()->sqlCompatibility());
        $setting_options_table->insertDefaults();
        $this->copyToSiteConfig($names);
    }

    private function newBoardSettings(array $names): void
    {
        $settings_table = new TableSettings(nel_database('core'), nel_utilities()->sqlCompatibility());
        $settings_table->insertDefaults();
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
            $site_config_update->bindValue(':source_name', $source_names[$i]);
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
}