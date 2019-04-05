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
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session(true);
        $user = $session->sessionUser();

        if ($inputs['action'] === 'update')
        {
            $this->update($user);
        }
        else if ($inputs['action'] === 'sticky')
        {
            $this->sticky($user);
        }
        else if ($inputs['action'] === 'unsticky')
        {
            $this->unsticky($user);
        }
        else if ($inputs['action'] === 'lock')
        {
            $this->lock($user);
        }
        else if ($inputs['action'] === 'unlock')
        {
            $this->unlock($user);
        }
        else if ($inputs['action'] === 'delete')
        {
            $this->remove($user);
        }
        else if ($inputs['action'] === 'ban-delete')
        {
            $this->remove($user);
            return;
        }
        else if ($inputs['action'] === 'expand')
        {
            $this->renderPanel($user);
            return;
        }

        $this->renderPanel($user);
    }

    public function renderPanel($user)
    {
        if (isset($_GET['action']) && $_GET['action'] === 'expand-thread')
        {
            $content_id = new \Nelliel\ContentID($_GET['content-id']);
            $output_panel = new \Nelliel\Output\OutputPanelThreads($this->domain);
            $output_panel->render(['section' => 'expanded_thread', 'user' => $user, 'thread_id' => $content_id->thread_id]);
        }
        else
        {
            $output_panel = new \Nelliel\Output\OutputPanelThreads($this->domain);
            $output_panel->render(['section' => 'panel', 'user' => $user]);
        }
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
    }

    public function editor($user)
    {
    }

    public function update($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_threads_modify'))
        {
            nel_derp(351, _gettext('You are not allowed to modify threads or posts.'));
        }

        $thread_handler = new \Nelliel\ThreadHandler($this->database, $this->domain->id());
        $thread_handler->processContentDeletes();
    }

    public function remove($user)
    {
        $content_id = new \Nelliel\ContentID($_GET['content-id']);

        if ($content_id->isThread())
        {
            $thread = new \Nelliel\Content\ContentThread($content_id, $this->domain, true);
            $thread->remove();
            $archive = new \Nelliel\ArchiveAndPrune($this->database, $this->domain, new \Nelliel\FileHandler());
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

    public function sticky($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_post_sticky'))
        {
            nel_derp(351, _gettext('You are not allowed to modify threads or posts.'));
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

    public function unsticky($user)
    {
        $content_id = new \Nelliel\ContentID($_GET['content-id']);
        $thread = new \Nelliel\Content\ContentThread($content_id, $this->domain, true);
        $thread->sticky();
        $this->regenThread($content_id->thread_id, true);
    }

    public function lock($user)
    {
        $content_id = new \Nelliel\ContentID($_GET['content-id']);
        $thread = new \Nelliel\Content\ContentThread($content_id, $this->domain, true);
        $thread->lock();
        $this->regenThread($content_id->thread_id, true);
    }

    public function unlock($user)
    {
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
