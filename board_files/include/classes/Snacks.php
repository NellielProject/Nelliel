<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/ban_page.php';

class Snacks
{
    private $database;
    private $ban_hammer;

    function __construct($database, BanHammer $ban_hammer)
    {
        $this->database = $database;
        $this->ban_hammer = $ban_hammer;
    }

    public function checkHoneypot(Domain $domain)
    {
        if (!empty($_POST[BASE_HONEYPOT_FIELD1 . '_' . $domain->id()]) ||
                !empty($_POST[BASE_HONEYPOT_FIELD2 . '_' . $domain->id()]) ||
                !empty($_POST[BASE_HONEYPOT_FIELD3 . '_' . $domain->id()]))
        {
            $ban_input['type'] = 'SPAMBOT';
            $ban_input['ip_address_start'] = $_SERVER['REMOTE_ADDR'];
            $ban_input['reason'] = 'Ur a spambot. Nobody wants any. GTFO!';
            $ban_input['start_time'] = time();
            $ban_input['length'] = 86400 * 9001;
            $ban_input['all_boards'] = 1;
            $this->ban_hammer->addBan($ban_input);
            $this->applyBan($domain);
        }
    }

    public function fileHashIsBanned($file_hash, $hash_type)
    {
        $banned_hashes = nel_parameters_and_data()->fileFilters();

        if (!isset($banned_hashes[$hash_type]))
        {
            return false;
        }

        return in_array($file_hash, $banned_hashes[$hash_type]);
    }

    public function banAppeal($board_id, $ban_info)
    {
        $bawww = $_POST['ban_appeal'];

        if (empty($bawww))
        {
            return;
        }

        if ($_SERVER['REMOTE_ADDR'] != @inet_ntop($ban_info['ip_address_start']))
        {
            nel_derp(150, _gettext('Your ip address does not match the one listed in the ban.'));
        }

        if ($ban_info['appeal_status'] > 0)
        {
            nel_derp(151, _gettext('You have already appealed your ban.'));
        }

        $prepared = $this->database->prepare(
                'UPDATE "' . BANS_TABLE . '" SET "appeal" = ?, "appeal_status" = 1 WHERE "ban_id" = ?');
        $this->database->executePrepared($prepared, [$bawww, $ban_info['ban_id']]);
    }

    public function applyBan(Domain $domain)
    {
        $bans = $this->ban_hammer->getBansByIp($_SERVER['REMOTE_ADDR']);
        $ban_info = null;

        if (empty($bans))
        {
            return;
        }

        foreach ($bans as $ban)
        {
            $length = $ban['length'] + $ban['start_time'];

            if (time() >= $length)
            {
                $this->ban_hammer->removeBan($domain, $ban['ban_id'], true);
                continue;
            }

            if ($ban['all_boards'] != 0)
            {
                if (is_null($ban_info))
                {
                    $ban_info = $ban;
                }

                continue;
            }

            if (empty($domain->id()))
            {
                break;
            }

            if ($domain->id() === $ban['board_id'])
            {
                $ban_info = $ban;
                continue;
            }
        }

        if (is_null($ban_info))
        {
            return;
        }

        if (!empty($inputs) && $inputs['module'] === 'ban-page')
        {
            if ($inputs['action'] === 'add-appeal')
            {
                $this->banAppeal($inputs['board_id'], $ban_info);
                $ban_info = $this->ban_hammer->getBanById($ban_info['ban_id']);
            }
        }

        nel_render_ban_page($domain, $ban_info);
        nel_clean_exit();
    }
}

