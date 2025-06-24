<?php
declare(strict_types = 1);

namespace Nelliel\NewPost;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\IPInfo;
use Nelliel\Snacks;
use Nelliel\Bans\BanHammer;
use Nelliel\Bans\BansAccess;
use Nelliel\Checkpoints\Actions;
use Nelliel\Content\Post;
use Nelliel\Domains\Domain;

class ActionsPost implements Actions
{
    private $post;

    function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function do(array $actions)
    {
        foreach ($actions as $action => $data) {
            switch ($action) {
                case 'reject':
                    nel_derp(75, $data['message'] ?? __('Because reasons.'));
                    break;

                case 'ban':
                    if (($data['length'] ?? 0) < 1) {
                        break;
                    }

                    $ban_hammer = new BanHammer(nel_database('core'));
                    $ip_info = new IPInfo(nel_request_ip_address());

                    if ($data['global'] ?? false) {
                        $ban_hammer->modifyData('board_id', Domain::GLOBAL);
                    } else {
                        $ban_hammer->modifyData('board_id', $this->post->domain()->id());
                    }

                    $ban_hammer->modifyData('ban_type', BansAccess::IP);
                    $ban_hammer->modifyData('unhashed_ip_address', $ip_info->getInfo('unhashed_ip_address'));
                    $ban_hammer->modifyData('hashed_ip_address', $ip_info->getInfo('hashed_ip_address'));
                    $ban_hammer->modifyData('start_time', time());
                    $ban_hammer->modifyData('length', $data['length'] ?? 0);
                    $ban_hammer->modifyData('reason', $data['reason'] ?? '');
                    $ban_hammer->modifyData('can_appeal', $data['appeal_allowed'] ?? true);
                    $ban_hammer->apply();
                    $snacks = new Snacks($this->post->domain(), new BansAccess($this->post->domain()->database()));
                    $snacks->banPage($ban_hammer);
                    break;
            }
        }
    }
}
