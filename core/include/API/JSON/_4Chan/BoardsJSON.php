<?php

namespace Nelliel\API\JSON\_4Chan;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\JSON;
use Nelliel\Domains\Domain;
use PDO;

class BoardsJSON extends JSON
{

    function __construct()
    {}

    protected function generate(): void
    {
        $board_ids = nel_database('core')->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);

        foreach ($board_ids as $board_id) {
            $raw_data = array();
            $board = Domain::getDomainFromID($board_id, nel_database('core'));
            $raw_data['board'] = $board->reference('board_uri');
            $raw_data['title'] = $board->setting('name') ?? '';
            $raw_data['ws_board'] = intval($board->setting('safety_level') === 'SFW');
            $raw_data['per_page'] = $board->setting('threads_per_page');
            $raw_data['max_filesize'] = $board->setting('max_filesize');
            $raw_data['max_webm_filesize'] = $board->setting('max_filesize'); // WebM is not treated speshul around here
            $raw_data['max_comment_chars'] = $board->setting('max_comment_length');
            $raw_data['max_webm_duration'] = OVER_9000; // TODO: Implement video length limit
            $raw_data['bump_limit'] = $board->setting('limit_bump_count') ? $board->setting('max_bumps') : $board->setting(
                'max_posts');
            $raw_data['image_limit'] = $board->setting('max_thread_uploads');
            $raw_data['cooldowns']['threads'] = $board->setting('thread_renzoku');
            $raw_data['cooldowns']['replies'] = $board->setting('reply_renzoku');
            $raw_data['cooldowns']['images'] = $board->setting('upload_renzoku'); // Close enough
            $raw_data['meta_description'] = $board->setting('description') ?? '';

            if ($board->setting('enable_spoilers')) {
                $raw_data['spoilers'] = 1;
            }

            // $raw_data['custom_spoilers'] = 0; // Not implemented, maybe later?

            if ($board->setting('max_archive_threads') > 0) {
                $raw_data['is_archived'] = 1;
            }

            // $raw_data['board_flags'] = array(); //TODO: Change this if flags are implemented
            // $raw_data['country_flags'] = 1; //TODO: Change this if flags are implemented

            if ($board->setting('show_poster_id')) {
                $raw_data['user_ids'] = 1;
            }

            // $raw_data['oekaki'] = 0; // TODO: Change when oekaki is implemented
            $raw_data['sjis_tags'] = 1; // TODO: Check if markup enabled/disabled
            // $raw_data['code'] = 0; // TODO: Change if code markup added
            // $raw_data['math_tags'] = 0; // Not implemented

            if ($board->setting('allow_op_files') && $board->setting('allow_reply_files') &&
                $board->setting('allow_op_embeds') && $board->setting('allow_reply_embeds')) {
                $raw_data['text_only'] = 1; // TODO: Add overall allow_uploads setting
            }

            if (!$board->setting('forced_anonymous') &&
                ($board->setting('enable_op_name_field') || $board->setting('enable_reply_name_field'))) {
                $raw_data['forced_anon'] = 1;
            }

            // $raw_data['webm_audio'] = 0; // Not implemented

            if ($board->setting('require_op_subject') || $board->setting('require_reply_subject')) {
                $raw_data['require_subject'] = 1;
            }

            // $raw_data['min_image_width'] = 0; // TODO: Implement
            // $raw_data['min_image_height'] = 0; // TODO: Implement
            $this->raw_data['boards'][] = $raw_data;
        }

        $this->json = json_encode($this->raw_data);
        $this->needs_update = false;
    }
}