<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use IPTools\IP;
use IPTools\Range;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputBanPage;
use PDO;

class Snacks
{
    private $domain;
    private $database;
    private $ip_address;
    private $hashed_ip_address;
    private $bans_access;
    private $file_hashes;

    function __construct(Domain $domain, BansAccess $bans_access)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->bans_access = $bans_access;

        if (nel_site_domain()->setting('store_unhashed_ip')) {
            $this->ip_address = nel_request_ip_address();
        } else {
            $this->ip_address = null;
        }

        $this->hashed_ip_address = nel_request_ip_address(true);
    }

    /**
     * Check if the provided file hash is banned.
     */
    public function fileHashIsBanned(string $file_hash, string $hash_type): bool
    {
        if (empty($this->file_filters[$this->domain->id()])) {
            $loaded = false;

            if (!$loaded) {
                $prepared = $this->database->prepare(
                    'SELECT "hash_type", "file_hash" FROM "nelliel_file_filters" WHERE "board_id" = ? OR "board_id" = ?');
                $filters = $this->database->executePreparedFetchAll($prepared, [$this->domain->id(), Domain::GLOBAL],
                    PDO::FETCH_ASSOC);

                foreach ($filters as $filter) {
                    $this->file_filters[$this->domain->id()][$filter['hash_type']][] = $filter['file_hash'];
                }
            }
        }

        if (!isset($this->file_filters[$this->domain->id()][$hash_type])) {
            return false;
        }

        return in_array($file_hash, $this->file_filters[$this->domain->id()][$hash_type]);
    }

    /**
     * Process a ban appeal.
     */
    public function banAppeal(): void
    {
        $bawww = $_POST['bawww'] ?? null;
        $ban_id = $_POST['ban_id'] ?? null;

        if (empty($bawww) || empty($ban_id)) {
            return;
        }

        if (!$this->domain->setting('allow_ban_appeals')) {
            nel_derp(156, __('Ban appeals are not enabled.'));
        }

        $ban_hammer = new BanHammer($this->database);

        if (($ban_hammer->getData('length') < $this->domain->setting('min_time_before_ban_appeal') ||
            time() - $ban_hammer->getData('start_time') < $this->domain->setting('min_time_before_ban_appeal'))) {
            nel_derp(159, __('Minimum time before you can appeal has not been reached or ban is too short for appeals.'));
        }

        if (!$ban_hammer->loadFromID($ban_id)) {
            nel_derp(150, __('Invalid ban ID given.'));
        }

        if ($ban_hammer->getData('ip_type') == BansAccess::RANGE) {
            nel_derp(151, __('You cannot appeal a range ban.'));
        }

        if ($this->ip_address !== $ban_hammer->getData('ip_address_start') &&
            $this->hashed_ip_address !== $ban_hammer->getData('hashed_ip_address')) {
            nel_derp(152, __('Your IP address does not match the one on the ban.'));
        }

        if (!$ban_hammer->addAppeal($bawww)) {
            nel_derp(153, __('You have already appealed your ban.'));
        }
    }

    /**
     * Apply any bans relevant to the current request.
     */
    public function applyBan(): void
    {
        $this->banAppeal();
        $this->checkRangeBans();
        $this->checkIPBans();
    }

    /**
     * Check if a ban has expired and optionally remove it.
     */
    public function checkExpired(BanHammer $ban_hammer, bool $remove): bool
    {
        if ($ban_hammer->expired()) {
            if ($this->domain->setting('must_see_ban') && !$ban_hammer->getData('seen')) {
                return false;
            }

            if ($remove) {
                $ban_hammer->remove();
            }

            return true;
        }

        return false;
    }

    /**
     * Output the ban page.
     */
    public function banPage(BanHammer $ban_hammer): void
    {
        $ban_hammer->modifyData('seen', 1);
        $ban_hammer->apply();
        $output_ban_page = new OutputBanPage($this->domain, false);
        $output_ban_page->render(['ban_hammer' => $ban_hammer], false);
        nel_clean_exit();
    }

    /**
     * Check through existing range bans to see if any are applicable.
     */
    private function checkRangeBans(): void
    {
        $bans_range = $this->bans_access->getBansByType(BansAccess::RANGE, $this->domain->id());

        foreach ($bans_range as $ban_hammer) {
            if ($this->checkExpired($ban_hammer, true)) {
                continue;
            }

            if ($ban_hammer->getData('board_id') === Domain::GLOBAL ||
                $ban_hammer->getData('board_id') === $this->domain->id()) {
                $range = new Range(new IP($ban_hammer->getData('ip_address_start')),
                    new IP($ban_hammer->getData('ip_address_end')));

                if ($range->contains(new IP($this->ip_address))) {
                    $this->banPage($ban_hammer);
                }
            }
        }
    }

    /**
     * Check through existing IP bans to see if any are applicable.
     */
    private function checkIPBans(): void
    {
        if (nel_site_domain()->setting('store_unhashed_ip')) {
            $bans_ip = $this->bans_access->getBansByIP($this->ip_address);
        } else {
            $bans_ip = array();
        }

        $bans_hashed = $this->bans_access->getBansByHashedIP($this->hashed_ip_address);
        $bans = array_merge($bans_ip, $bans_hashed);
        $longest = null;

        foreach ($bans as $ban_hammer) {
            if ($this->checkExpired($ban_hammer, true)) {
                continue;
            }

            if ($ban_hammer->getData('board_id') === Domain::GLOBAL ||
                $ban_hammer->getData('board_id') === $this->domain->id()) {
                if (empty($longest) || $ban_hammer->timeToExpiration() > $longest->timeToExpiration()) {
                    $longest = $ban_hammer;
                }

                continue;
            }
        }

        if (is_null($longest)) {
            return;
        }

        $this->banPage($longest);
    }
}

