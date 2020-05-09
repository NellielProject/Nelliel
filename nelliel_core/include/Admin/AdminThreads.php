<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminThreads extends AdminHandler
{

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->validateUser();
    }

    public function actionDispatch(string $action, bool $return)
    {
        if ($action === 'update')
        {
            $this->update();
        }
        else if ($action === 'sticky')
        {
            $this->sticky();
        }
        else if ($action === 'unsticky')
        {
            $this->unsticky();
        }
        else if ($action === 'lock')
        {
            $this->lock();
        }
        else if ($action === 'unlock')
        {
            $this->unlock();
        }
        else if ($action === 'delete')
        {
            $this->remove();
        }
        else if ($action === 'ban-delete')
        {
            $this->remove();
            $bans_admin = new \Nelliel\Admin\AdminBans($this->authorization, $this->domain);
            $bans_admin->actionDispatch('new', false);
        }
        else if ($action === 'expand') // TODO: Figure this out better
        {
            $this->renderPanel();
            return;
        }

        if ($return)
        {
            return;
        }

        $this->renderPanel();
    }

    public function renderPanel()
    {
        if (isset($_GET['action']) && $_GET['action'] === 'expand-thread')
        {
            $content_id = new \Nelliel\ContentID($_GET['content-id']);
            $output_panel = new \Nelliel\Output\OutputPanelThreads($this->domain);
            $output_panel->render(['section' => 'expanded_thread', 'user' => $this->session_user, 'thread_id' => $content_id->thread_id], false);
        }
        else
        {
            $output_panel = new \Nelliel\Output\OutputPanelThreads($this->domain);
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
        $thread_handler = new \Nelliel\ThreadHandler($this->database, $this->domain->id());
        $thread_handler->processContentDeletes();
    }

    public function remove()
    {
        $content_id = new \Nelliel\ContentID($_GET['content-id']);

        if ($content_id->isThread())
        {
            $thread = new \Nelliel\Content\ContentThread($content_id, $this->domain, true);
            $thread->remove(true);
            $archive = new \Nelliel\ArchiveAndPrune($this->domain, new \Nelliel\Utility\FileHandler());
            $archive->updateThreads();
        }
        else if ($content_id->isPost())
        {
            $post = new \Nelliel\Content\ContentPost($content_id, $this->domain, true);
            $post->remove();
        }
        else if ($content_id->isContent())
        {
            $file = new \Nelliel\Content\ContentFile($content_id, $this->domain, true);
            $file->remove();
        }

        $this->regenThread($content_id->thread_id, true);
    }

    public function sticky()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_sticky_posts'))
        {
            nel_derp(351, _gettext('You are not allowed to sticky threads.'));
        }

        $content_id = new \Nelliel\ContentID($_GET['content-id']);

        if ($content_id->isPost())
        {
            $post = new \Nelliel\Content\ContentPost($content_id, $this->domain, true);
            $post->convertToThread();
            $new_content_id = new \Nelliel\ContentID();
            $new_content_id->thread_id = $content_id->post_id;
            $new_content_id->post_id = $content_id->post_id;
            $new_thread = new \Nelliel\Content\ContentThread($new_content_id, $this->domain);
            $new_thread->sticky();
        }
        else
        {
            $thread = new \Nelliel\Content\ContentThread($content_id, $this->domain, true);
            $thread->sticky();
        }

        $this->regenThread($content_id->thread_id, true);
    }

    public function unsticky()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_sticky_posts'))
        {
            nel_derp(352, _gettext('You are not allowed to unsticky threads.'));
        }

        $content_id = new \Nelliel\ContentID($_GET['content-id']);
        $thread = new \Nelliel\Content\ContentThread($content_id, $this->domain, true);
        $thread->sticky();
        $this->regenThread($content_id->thread_id, true);
    }

    public function lock()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_lock_posts'))
        {
            nel_derp(353, _gettext('You are not allowed to lock threads.'));
        }

        $content_id = new \Nelliel\ContentID($_GET['content-id']);
        $thread = new \Nelliel\Content\ContentThread($content_id, $this->domain, true);
        $thread->lock();
        $this->regenThread($content_id->thread_id, true);
    }

    public function unlock()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_lock_posts'))
        {
            nel_derp(354, _gettext('You are not allowed to unlock threads.'));
        }

        $content_id = new \Nelliel\ContentID($_GET['content-id']);
        $thread = new \Nelliel\Content\ContentThread($content_id, $this->domain, true);
        $thread->lock();
        $this->regenThread($content_id->thread_id, true);
    }

    private function regenThread($thread_id, bool $regen_index = false)
    {
        $regen = new \Nelliel\Regen();
        $regen->threads($this->domain, true, [$thread_id]);

        if($regen_index)
        {
            $regen->index($this->domain);
        }
    }
}
