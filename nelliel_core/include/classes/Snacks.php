<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use IPTools\IP;
use IPTools\Range;

class Snacks
{
    private $database;
    private $ip_address;
    private $hashed_ip_address;
    private $bans_access;

    function __construct(NellielPDO $database, BansAccess $bans_access)
    {
        $this->database = $database;
        $this->bans_access = $bans_access;

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
            $ban_hammer = new BanHammer($this->database);
            $ban_hammer->modifyData('ban_type', 'SPAMBOT');
            $ban_hammer->modifyData('ip_address_start', $this->ip_address);
            $ban_hammer->modifyData('reason', 'Ur a spambot. Nobody wants any. GTFO!');
            $ban_hammer->modifyData('start_time', time());
            $ban_hammer->modifyData('length', 86400 * 9001);
            $ban_hammer->modifyData('all_boards', 1);
            $ban_hammer->apply();
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

    public function banAppeal()
    {
        $bawww = $_POST['bawww'] ?? null;
        $ban_hash = $_POST['ban_hash'] ?? null;

        if (empty($bawww) || empty($ban_hash))
        {
            return;
        }

        $ban_hammer = new BanHammer($this->database);
        $ban_hammer->loadFromHash($ban_hash);

        if (!$ban_hammer->loadFromHash($ban_hash))
        {
            nel_derp(150, _gettext('Invalid ban ID given.'));
        }

        if ($ban_hammer->getData('ip_type') == BansAccess::RANGE)
        {
            nel_derp(151, _gettext('You cannot appeal a range ban.'));
        }

        if ($this->ip_address !== $ban_hammer->getData('ip_address_start') &&
                $this->hashed_ip_address !== $ban_hammer->getData('hashed_ip_address'))
        {
            nel_derp(152, _gettext('Your IP address does not match the one on the ban.'));
        }

        if (!$ban_hammer->addAppeal($bawww))
        {
            nel_derp(153, _gettext('You have already appealed your ban.'));
        }
    }

    public function applyBan(Domain $domain)
    {
        $this->banAppeal();
        $output_ban_page = new \Nelliel\Output\OutputBanPage($domain, false);
        $bans_range = $this->bans_access->getBansByType(BansAccess::RANGE);

        foreach ($bans_range as $ban_hammer)
        {
            if ($ban_hammer->expired())
            {
                $ban_hammer->remove();
                continue;
            }

            if ($domain->id() === $ban_hammer->getData('board_id'))
            {
                $range = new Range(new IP($ban_hammer->getData('ip_address_start')),
                        new IP($ban_hammer->getData('ip_address_end')));

                if ($range->contains(new IP($this->ip_address)))
                {
                    $output_ban_page->render(['ban_hammer' => $ban_hammer], false);
                    ;
                    nel_clean_exit();
                }
            }
        }

        if (nel_site_domain()->setting('store_unhashed_ip'))
        {
            $bans_ip = $this->bans_access->getBansByIP($this->ip_address);
        }
        else
        {
            $bans_ip = array();
        }

        $bans_hashed = $this->bans_access->getBansByHashedIP($this->hashed_ip_address);
        $bans = array_merge($bans_ip, $bans_hashed);
        $ban_info = null;
        $longest = null;

        foreach ($bans as $ban_hammer)
        {
            if ($ban_hammer->expired())
            {
                $ban_hammer->remove();
                continue;
            }

            if ($domain->id() !== '_site_' && ($domain->id() === $ban_hammer->getData('board_id') || $ban_hammer->getData('all_boards') == 1))
            {
                if (empty($longest))
                {
                    $longest = $ban_hammer;
                }
                else
                {
                    if ($ban_hammer->timeToExpiration() > $longest->timeToExpiration())
                    {
                        $longest = $ban_hammer;
                    }
                }

                continue;
            }
        }

        if (is_null($longest))
        {
            return;
        }

        $output_ban_page->render(['ban_hammer' => $longest], false);
        nel_clean_exit();
    }
}

