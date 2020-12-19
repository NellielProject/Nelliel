<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\DomainSite;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Content\ContentThread;
use Nelliel\Content\ContentPost;
use Nelliel\Content\ContentFile;

class AdminThreads extends AdminHandler
{
    private $site_domain;

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->site_domain = new DomainSite($this->database);
        $this->validateUser();
    }

    public function renderPanel()
    {
        if (isset($_GET['actions']) && $_GET['actions'] === 'expand-thread')
        {
            $content_id = new ContentID($_GET['content-id']);
            $output_panel = new \Nelliel\Output\OutputPanelThreads($this->domain, false);
            $output_panel->render(
                    ['section' => 'expanded_thread', 'user' => $this->session_user,
                        'thread_id' => $content_id->threadID()], false);
        }
        else
        {
            $output_panel = new \Nelliel\Output\OutputPanelThreads($this->domain, false);
            $output_panel->render(['section' => 'panel', 'user' => $this->session_user], false);
        }
    }

    public function creator()
    {
    }

    public function add()
    {
    }

    public function editor()
    {
    }

    public function update()
    {
        $thread_handler = new \Nelliel\ThreadHandler($this->database, $this->domain);
        $thread_handler->processContentDeletes();
    }

    public function remove()
    {
        $content_id = new ContentID($_GET['content-id']);
        $content_id->getInstanceFromID($this->domain)->remove();
        $this->regenThread($content_id->threadID(), true);
    }

    public function sticky()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_sticky_posts'))
        {
            nel_derp(351, _gettext('You are not allowed to sticky threads.'));
        }

        $content_id = new ContentID($_GET['content-id']);

        if ($content_id->isThread() || $content_id->isPost())
        {
            $content_id->getInstanceFromID($this->domain)->sticky();
            $this->regenThread($content_id->threadID(), true);
        }
    }

    public function unsticky()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_sticky_posts'))
        {
            nel_derp(352, _gettext('You are not allowed to unsticky threads.'));
        }

        $content_id = new ContentID($_GET['content-id']);

        if ($content_id->isThread() || $content_id->isPost())
        {
            $content_id->getInstanceFromID($this->domain)->unsticky();
            $this->regenThread($content_id->threadID(), true);
        }
    }

    public function lock()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_lock_posts'))
        {
            nel_derp(353, _gettext('You are not allowed to lock threads.'));
        }

        $content_id = new ContentID($_GET['content-id']);

        if ($content_id->isThread())
        {
            $content_id->getInstanceFromID($this->domain)->lock();
            $this->regenThread($content_id->threadID(), true);
        }
    }

    public function unlock()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_lock_posts'))
        {
            nel_derp(354, _gettext('You are not allowed to unlock threads.'));
        }

        $content_id = new ContentID($_GET['content-id']);

        if ($content_id->isThread())
        {
            $content_id->getInstanceFromID($this->domain)->lock();
            $this->regenThread($content_id->threadID(), true);
        }
    }

    private function regenThread($thread_id, bool $regen_index = false)
    {
        $regen = new \Nelliel\Regen();
        $regen->threads($this->domain, true, [$thread_id]);

        if ($this->site_domain->setting('overboard_active'))
        {
            $regen->overboard($this->site_domain);
        }

        if ($regen_index)
        {
            $regen->index($this->domain);
        }
    }

    public function banDelete()
    {
        $content_id = new ContentID($_GET['content-id']);
        $content_instance = $content_id->getInstanceFromID($this->domain);
        $content_instance->loadFromDatabase();
        $ip_start = $content_instance->data('ip_address');
        $hashed_ip = $content_instance->data('hashed_ip_address');
        $ban_type = 'CONTENT';
        $content_instance->remove();
        $this->regenThread($content_id->threadID(), true);
        $output_panel = new \Nelliel\Output\OutputPanelBans($this->domain, false);
        $output_panel->render(
                ['section' => 'add', 'user' => $this->session_user, 'ip_start' => $ip_start, 'hashed_ip' => $hashed_ip,
                    'ban_type' => $ban_type], false);
        $this->outputMain(false);
    }

    private function verifyAccess()
    {
    }
}
