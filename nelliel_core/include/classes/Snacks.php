<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Snacks
{
    private $database;
    private $ban_hammer;
    private $ip_address;
    private $hashed_ip_address;

    function __construct(NellielPDO $database, BanHammer $ban_hammer)
    {
        $this->database = $database;
        $this->ban_hammer = $ban_hammer;

        if (nel_site_domain()->setting('store_unhashed_ip'))
        {
            $this->ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        }
        else
        {
            $this->ip_address = null;
        }

        $this->hashed_ip_address = hash('sha256', $_SERVER['REMOTE_ADDR']);
    }

    public function checkHoneypot(Domain $domain)
    {
        if (!empty($_POST[NEL_BASE_HONEYPOT_FIELD1 . '_' . $domain->id()]) ||
                !empty($_POST[NEL_BASE_HONEYPOT_FIELD2 . '_' . $domain->id()]) ||
                !empty($_POST[NEL_BASE_HONEYPOT_FIELD3 . '_' . $domain->id()]))
        {
            $ban_input['type'] = 'SPAMBOT';
            $ban_input['ip_address_start'] = $this->ip_address;
            $ban_input['hashed_ip_address'] = $this->hashed_ip_address;
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
        $site_domain = new DomainSite($this->database);
        $banned_hashes = $site_domain->fileFilters();

        if (!isset($banned_hashes[$hash_type]))
        {
            return false;
        }

        return in_array($file_hash, $banned_hashes[$hash_type]);
    }

    public function banAppeal($board_id, $ban_info)
    {
        $bawww = $_POST['bawww'];

        if (empty($bawww))
        {
            return;
        }

        if ($this->ip_address != @inet_ntop($ban_info['ip_address_start']) &&
                $this->hashed_ip_address != bin2hex($ban_info['hashed_ip_address']))
        {
            nel_derp(150,
                    _gettext(
                            'Your IP address does not match the one listed in the ban or you are trying to appeal a range ban.'));
        }

        if ($ban_info['appeal_status'] > 0)
        {
            nel_derp(151, _gettext('You have already appealed your ban.'));
        }

        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_BANS_TABLE . '" SET "appeal" = ?, "appeal_status" = 1 WHERE "ban_id" = ?');
        $this->database->executePrepared($prepared, [$bawww, $ban_info['ban_id']]);
    }

    public function applyBan(Domain $domain, array $inputs)
    {
        if ($domain->id() === '_site_')
        {
            return;
        }

        $bans = $this->ban_hammer->getBansByIp($this->ip_address, $this->hashed_ip_address);
        $ban_info = null;

        foreach ($bans as $ban)
        {
            // TODO: We can probably set up a general clear expired bans method in BanHammer
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
            if ($inputs['actions'][0] === 'add-appeal')
            {
                $this->banAppeal($inputs['board_id'], $ban_info);
                $ban_info = $this->ban_hammer->getBanById($ban_info['ban_id']);
            }
        }

        $output_ban_page = new \Nelliel\Output\OutputBanPage($domain, false);
        $output_ban_page->render(['ban_info' => $ban_info], false);
        nel_clean_exit();
    }
}

