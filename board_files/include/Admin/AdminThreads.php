<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/thread_panel.php';

class AdminThreads extends AdminHandler
{
    private $domain;

    function __construct($database, $authorization, $domain)
    {
        $this->database = $database;
        $this->authorization = $authorization;
        $this->domain = $domain;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
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
            nel_render_thread_panel_expand($user, $this->domain, $content_id->thread_id);
        }
        else
        {
            nel_render_thread_panel_main($user, $this->domain);
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
        if (!$user->boardPerm($this->domain->id(), 'perm_threads_modify'))
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
            $thread = new \Nelliel\Content\ContentThread(nel_database(), $content_id, $this->domain, true);
            $thread->remove();
            $archive = new \Nelliel\ArchiveAndPrune($this->database, $this->domain, new \Nelliel\FileHandler());
            $archive->updateThreads();
        }
        else if ($content_id->isPost())
        {
            $post = new \Nelliel\Content\ContentPost(nel_database(), $content_id, $this->domain, true);
            $post->remove();
        }
        else if ($content_id->isFile())
        {
            $file = new \Nelliel\Content\ContentFile(nel_database(), $content_id, $this->domain, true);
            $file->remove();
        }

        $regen = new \Nelliel\Regen();
        $regen->threads($this->domain, true, $content_id->thread_id);
        $regen->index($this->domain);
    }

    public function sticky($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_post_sticky'))
        {
            nel_derp(351, _gettext('You are not allowed to modify threads or posts.'));
        }

        $content_id = new \Nelliel\ContentID($_GET['content-id']);

        if ($content_id->isPost())
        {
            $post = new \Nelliel\Content\ContentPost($this->database, $content_id, $this->domain, true);
            $post->convertToThread();
            $new_content_id = new \Nelliel\ContentID();
            $new_content_id->thread_id = $content_id->post_id;
            $new_content_id->post_id = $content_id->post_id;
            $new_thread = new \Nelliel\Content\ContentThread($this->database, $new_content_id, $this->domain);
            $new_thread->sticky();
        }
        else
        {
            $thread = new \Nelliel\Content\ContentThread($this->database, $content_id, $this->domain, true);
            $thread->sticky();
        }
    }

    public function unsticky($user)
    {
        $content_id = new \Nelliel\ContentID($_GET['content-id']);
        $thread = new \Nelliel\Content\ContentThread($this->database, $content_id, $this->domain, true);
        $thread->sticky();
    }

    public function lock($user)
    {
        $content_id = new \Nelliel\ContentID($_GET['content-id']);
        $thread = new \Nelliel\Content\ContentThread($this->database, $content_id, $this->domain, true);
        $thread->lock();
    }

    public function unlock($user)
    {
        $content_id = new \Nelliel\ContentID($_GET['content-id']);
        $thread = new \Nelliel\Content\ContentThread($this->database, $content_id, $this->domain, true);
        $thread->lock();
    }
}
