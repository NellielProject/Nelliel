<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use IPTools\IP;
use IPTools\Range;
use Nelliel\Bans\BanHammer;
use Nelliel\Bans\BansAccess;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputBanPage;

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
        $this->ip_address = nel_request_ip_address();
        $this->hashed_ip_address = nel_request_ip_address(true);
    }

    /**
     * Process a ban appeal.
     */
    public function banAppeal(): void
    {
        $ban_id = intval($_POST['ban_id'] ?? 0);

        if ($ban_id === 0) {
            return;
        }

        if (!$this->domain->setting('allow_ban_appeals')) {
            nel_derp(156, __('Ban appeals are not enabled.'));
        }

        $ban_hammer = new BanHammer($this->database, $ban_id);

        if (!$ban_hammer->getData('appeal_allowed')) {
            nel_derp(160, __('This ban cannot be appealed.'));
        }

        if ($ban_hammer->appealCount() >= $this->domain->setting('max_ban_appeals')) {
            nel_derp(161, __('This ban has reached the maximum number of appeals.'));
        }

        if (($ban_hammer->getData('length') < $this->domain->setting('min_time_before_ban_appeal') ||
            time() - $ban_hammer->getData('start_time') < $this->domain->setting('min_time_before_ban_appeal'))) {
            nel_derp(159, __('Minimum time before appealing this ban has not been reached.'));
        }

        if (($ban_hammer->getData('ban_type') == BansAccess::RANGE ||
            $ban_hammer->getData('ban_type') == BansAccess::HASHED_SUBNET)) {
            if (!$this->domain->setting('allow_ip_range_ban_appeals')) {
                nel_derp(151, __('You cannot appeal a range ban.'));
            }
        }

        if ($this->ip_address !== $ban_hammer->getData('ip_address_start') &&
            $this->hashed_ip_address !== $ban_hammer->getData('hashed_ip_address')) {
            nel_derp(152, __('Your IP address does not match the one on the ban.'));
        }

        if ($ban_hammer->appealPending()) {
            nel_derp(153, __('There is already a pending appeal for this ban.'));
        }

        $ban_hammer->addAppeal();
    }

    /**
     * Apply any bans relevant to the current request.
     */
    public function applyBans(): void
    {
        $ip_info = new IPInfo(nel_request_ip_address());
        $this->banAppeal();
        $this->checkRangeBans();
        $this->checkSubnetBans($ip_info);
        $this->checkIPBans($ip_info);
    }

    /**
     * Check if a ban has expired and optionally delete it.
     */
    public function checkExpired(BanHammer $ban_hammer, bool $delete): bool
    {
        if ($ban_hammer->expired()) {
            if ($this->domain->setting('must_see_ban') && !$ban_hammer->getData('seen')) {
                return false;
            }

            if ($delete) {
                $ban_hammer->delete();
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
        exit(0);
    }

    /**
     * Check through existing range bans to see if any are applicable.
     */
    private function checkRangeBans(): void
    {
        $bans_range = $this->bans_access->getByType(BansAccess::RANGE, $this->domain->id());

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
    private function checkIPBans(IPInfo $ip_info): void
    {
        $hashed_bans = $this->bans_access->getForHashedIP($ip_info->getInfo('hashed_ip_address'));
        $ip_bans = $this->bans_access->getForIP($ip_info->getInfo('unhashed_ip_address'));
        $bans = array_merge($hashed_bans, $ip_bans);
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

    /**
     * Check through existing subnet bans.
     */
    private function checkSubnetBans(IPInfo $ip_info): void
    {
        $small_subnet_bans = $this->bans_access->getForSubnet($ip_info->getInfo('hashed_small_subnet') ?? '');
        $large_subnet_bans = $this->bans_access->getForSubnet($ip_info->getInfo('hashed_large_subnet') ?? '');
        $subnet_bans = array_merge($small_subnet_bans, $large_subnet_bans);
        $longest = null;

        foreach ($subnet_bans as $ban_hammer) {
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

