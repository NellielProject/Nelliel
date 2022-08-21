<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use DateInterval;
use DateTime;
use PDO;
use Nelliel\BansAccess;

class OutputBanPage extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('banned');
        $ban_hammer = $parameters['ban_hammer'];
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['ban_board'] = ($ban_hammer->getData('board_id') === Domain::GLOBAL) ? _gettext('All Boards') : $ban_hammer->getData(
            'board_id');
        $this->render_data['ban_time'] = date($this->domain->setting('ban_page_date_format'),
            intval($ban_hammer->getData('start_time')));
        $this->render_data['ban_id'] = $ban_hammer->getData('ban_id');
        $ban_expire = $ban_hammer->getData('length') + $ban_hammer->getData('start_time');
        $expire_interval = ($ban_expire - time() >= 0) ? $ban_expire - time() : 0;
        $dt = new DateTime();
        $dt->add(new DateInterval('PT' . ($expire_interval) . 'S'));
        $interval = $dt->diff(new DateTime());
        $duration = '';

        if ($interval->d > 0) {
            $duration .= $interval->format('%a days %h hours');
        } else if ($interval->h > 0) {
            $duration .= $interval->format('%h hours %i minutes');
        } else {
            $duration .= $interval->format('%i minutes');
        }

        if ($this->domain->setting('show_ban_mod_name')) {
            $this->render_data['creator'] = $ban_hammer->getData('creator');
        }

        $this->render_data['ban_length'] = $duration;
        $this->render_data['ban_expiration'] = date("F jS, Y H:i e", intval($ban_expire));
        $this->render_data['ban_reason'] = $ban_hammer->getData('reason');
        $this->render_data['ban_ip'] = nel_request_ip_address();

        if (!nel_true_empty($this->domain->setting('ban_page_extra_text'))) {
            $this->render_data['extra_text'] = $this->domain->setting('ban_page_extra_text');
        }

        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_BAN_APPEALS_TABLE . '" WHERE "ban_id" = ? ORDER BY "time" DESC');
        $appeals = $this->database->executePreparedFetchAll($prepared, [$ban_hammer->getData('ban_id')],
            PDO::FETCH_ASSOC);
        $pending_appeal = false;
        $previous_appeal = false;
        $this->render_data['appealed'] = count($appeals) > 0;
        $this->render_data['pending'] = false;

        foreach ($appeals as $appeal) {
            if (!$pending_appeal && $appeal['pending'] == 1) {
                $this->render_data['pending'] = true;
                $pending_appeal = true;
                continue;
            }

            if (!$previous_appeal && $appeal['pending'] == 0) {
                $previous_appeal = true;
                $this->render_data['previous_response'] = $appeal['response'];
                $this->render_data['previous_denied'] = $appeal['denied'] == 1;
                $this->render_data['previous_responded'] = !nel_true_empty($appeal['response']);
                $this->render_data['responded'] = !nel_true_empty($appeal['response']);
                $this->render_data['show_response'] = !nel_true_empty($appeal['response']) && $appeal['pending'] == 0;
                $this->render_data['previous_modified'] = $appeal['pending'] == 0 && $appeal['denied'] == 0;
                $this->render_data['reviewed'] = $appeal['pending'] == 0;
                continue;
            }
        }

        if ($this->domain->setting('allow_ban_appeals')) {
            if (!$ban_hammer->getData('appeal_allowed')) {
                $this->render_data['not_this_ban'] = true;
            } else if ($ban_hammer->appealCount() >= $this->domain->setting('max_ban_appeals')) {
                $this->render_data['max_appeals'] = true;
            } else if ($ban_hammer->getData('length') < $this->domain->setting('min_time_before_ban_appeal') ||
                time() - $ban_hammer->getData('start_time') < $this->domain->setting('min_time_before_ban_appeal')) {
                $this->render_data['min_time_not_met'] = true;
            } else if ($ban_hammer->getData('ip_type') == BansAccess::RANGE &&
                !$this->domain->setting('allow_ip_range_ban_appeals')) {
                $this->render_data['no_ip_range'] = true;
            } else {
                $this->render_data['appeal_allowed'] = true;
            }
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}