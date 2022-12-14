<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\DomainBoard;

class BoardJSON extends JSON
{
    private $board;

    function __construct(DomainBoard $board)
    {
        $this->board = $board;
    }

    protected function generate(): void
    {
        $raw_data['board_uri'] = $this->board->reference('board_uri');

        if ($this->board->setting('show_name')) {
            $raw_data['name'] = $this->board->setting('name') ?? '';
        }

        if ($this->board->setting('show_description')) {
            $raw_data['description'] = $this->board->setting('description');
        }

        $raw_data['safety_level'] = $this->board->setting('safety_level');
        $raw_data['source_directory'] = $this->board->reference('source_directory');
        $raw_data['preview_directory'] = $this->board->reference('preview_directory');
        $raw_data['page_directory'] = $this->board->reference('page_directory');
        $raw_data['archive_directory'] = $this->board->reference('archive_directory');
        $raw_data['threads_per_page'] = $this->board->setting('threads_per_page');
        $raw_data['cooldowns']['threads'] = $this->board->setting('thread_renzoku');
        $raw_data['cooldowns']['replies'] = $this->board->setting('reply_renzoku');
        $raw_data['cooldowns']['uploads'] = $this->board->setting('upload_renzoku');
        $raw_data['cooldowns']['delete_post'] = $this->board->setting('delete_post_renzoku');
        $raw_data['content_disclaimer'] = $this->board->setting('board_content_disclaimer') ?? '';
        $raw_data['footer_text'] = $this->board->setting('board_footer_text') ?? '';

        $enabled_styles = json_decode($this->board->setting('enabled_styles'), true);

        foreach ($enabled_styles as $style) {
            $raw_data['styles'][] = $style;
        }

        $raw_data['new_post_fields']['name']['op_enabled'] = boolval($this->board->setting('enable_op_name_field'));
        $raw_data['new_post_fields']['name']['op_required'] = boolval($this->board->setting('require_op_name_field'));
        $raw_data['new_post_fields']['name']['reply_enabled'] = boolval(
            $this->board->setting('enable_reply_name_field'));
        $raw_data['new_post_fields']['name']['reply_required'] = boolval(
            $this->board->setting('require_reply_name_field'));
        $raw_data['new_post_fields']['name']['min_length'] = intval($this->board->setting('min_name_length'));
        $raw_data['new_post_fields']['name']['max_length'] = intval($this->board->setting('max_name_length'));
        $raw_data['new_post_fields']['email']['op_enabled'] = boolval($this->board->setting('enable_op_email_field'));
        $raw_data['new_post_fields']['email']['op_required'] = boolval($this->board->setting('require_op_email_field'));
        $raw_data['new_post_fields']['email']['reply_enabled'] = boolval(
            $this->board->setting('enable_reply_email_field'));
        $raw_data['new_post_fields']['email']['reply_required'] = boolval(
            $this->board->setting('require_reply_email_field'));
        $raw_data['new_post_fields']['email']['min_length'] = intval($this->board->setting('min_email_length'));
        $raw_data['new_post_fields']['email']['max_length'] = intval($this->board->setting('max_email_length'));
        $raw_data['new_post_fields']['subject']['op_enabled'] = boolval(
            $this->board->setting('enable_op_subject_field'));
        $raw_data['new_post_fields']['subject']['op_required'] = boolval(
            $this->board->setting('require_op_subject_field'));
        $raw_data['new_post_fields']['subject']['reply_enabled'] = boolval(
            $this->board->setting('enable_reply_subject_field'));
        $raw_data['new_post_fields']['subject']['reply_required'] = boolval(
            $this->board->setting('require_reply_subject_field'));
        $raw_data['new_post_fields']['subject']['min_length'] = intval($this->board->setting('min_subject_length'));
        $raw_data['new_post_fields']['subject']['max_length'] = intval($this->board->setting('max_subject_length'));
        $raw_data['new_post_fields']['comment']['op_enabled'] = boolval(
            $this->board->setting('enable_op_comment_field'));
        $raw_data['new_post_fields']['comment']['op_required'] = boolval(
            $this->board->setting('require_op_comment_field'));
        $raw_data['new_post_fields']['comment']['reply_enabled'] = boolval(
            $this->board->setting('enable_reply_comment_field'));
        $raw_data['new_post_fields']['comment']['reply_required'] = boolval(
            $this->board->setting('require_reply_comment_field'));
        $raw_data['new_post_fields']['comment']['min_length'] = intval($this->board->setting('min_comment_length'));
        $raw_data['new_post_fields']['comment']['max_length'] = intval($this->board->setting('max_comment_length'));
        $raw_data['new_post_fields']['fgsfds']['op_enabled'] = boolval($this->board->setting('enable_fgsfds_field'));
        $raw_data['new_post_fields']['fgsfds']['reply_enabled'] = boolval($this->board->setting('enable_fgsfds_field')); // TODO: Update with separate op/reply
        $raw_data['new_post_fields']['password']['op_enabled'] = boolval($this->board->setting('enable_password_field'));
        $raw_data['new_post_fields']['password']['reply_enabled'] = boolval(
            $this->board->setting('enable_password_field')); // TODO: Update with separate op/reply

        $raw_data['forced_anonymous'] = boolval($this->board->setting('forced_anonymous'));
        $raw_data['allow_no_markup'] = boolval($this->board->setting('allow_no_markup'));
        $raw_data['poster_ids'] = boolval($this->board->setting('show_poster_ids'));

        $raw_data['op_uploads']['allow_files'] = boolval($this->board->setting('allow_op_files'));
        $raw_data['op_uploads']['require_file'] = boolval($this->board->setting('require_op_file'));
        $raw_data['op_uploads']['allow_embeds'] = boolval($this->board->setting('allow_op_embeds'));
        $raw_data['op_uploads']['require_embed'] = boolval($this->board->setting('require_op_embed'));
        $raw_data['op_uploads']['max_files'] = intval($this->board->setting('max_op_files'));
        $raw_data['op_uploads']['max_embeds'] = intval($this->board->setting('max_op_embeds'));
        $raw_data['op_uploads']['max_total'] = intval($this->board->setting('max_op_total_uploads'));
        $raw_data['reply_uploads']['allow_files'] = boolval($this->board->setting('allow_reply_files'));
        $raw_data['reply_uploads']['require_file'] = boolval($this->board->setting('require_reply_file'));
        $raw_data['reply_uploads']['allow_embeds'] = boolval($this->board->setting('allow_reply_embeds'));
        $raw_data['reply_uploads']['require_embed'] = boolval($this->board->setting('require_reply_embed'));
        $raw_data['reply_uploads']['max_files'] = intval($this->board->setting('max_reply_files'));
        $raw_data['reply_uploads']['max_embeds'] = intval($this->board->setting('max_reply_embeds'));
        $raw_data['reply_uploads']['max_total'] = intval($this->board->setting('max_reply_total_uploads'));

        $raw_data['enable_spoilers'] = $this->board->setting('enable_spoilers');
        $raw_data['max_filesize'] = $this->board->setting('max_filesize');

        $raw_data = nel_plugins()->processHook('nel-in-after-board-json', [$this->board], $raw_data);
        $this->raw_data = $raw_data;
        $this->needs_update = false;
    }
}