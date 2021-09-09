<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Redirect;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainSite;
use Nelliel\Output\OutputPanelThreads;
use PDO;

class AdminThreads extends Admin
{
    private $site_domain;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->site_domain = new DomainSite($this->database);
    }

    public function dispatch(array $inputs): void
    {
        if ($inputs['subsection'] === 'panel')
        {
            $this->outputMain(true);
        }
        else
        {
            $this->outputMain(false);
        }

        parent::dispatch($inputs);

        // TODO: Refine this whenever we get threads panel updated
        foreach ($inputs['actions'] as $action)
        {
            switch ($action)
            {
                case 'sticky':
                    $this->sticky();
                    break;

                case 'lock':
                    $this->lock();
                    break;

                case 'delete':
                    $this->remove();
                    break;

                case 'delete-by-ip':
                    $this->removeByIP();
                    break;

                case 'ban':
                    $admin_bans = new \Nelliel\Admin\AdminBans($this->authorization, $this->domain, $this->session,
                            $inputs);
                    $admin_bans->creator();
                    break;

                case 'sage':
                    $this->permasage();
                    break;

                case 'edit-post':
                    $this->editPost();
                    break;

                case 'update-post':
                    $this->updatePost();
                    break;

                case 'cyclic':
                    $this->cyclic();
                    break;

                case 'expand':
                    ; // TODO: Figure this out better
                    break;

                case 'bandelete':
                    $this->banDelete();
                    break;
            }
        }
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_threads_access');

        if (isset($_GET['actions']) && $_GET['actions'] === 'expand-thread')
        {
            $content_id = new ContentID($_GET['content-id']);
            $output_panel = new \Nelliel\Output\OutputPanelThreads($this->domain, false);
            $output_panel->render(['section' => 'expanded_thread', 'thread_id' => $content_id->threadID()], false);
        }
        else
        {
            $output_panel = new \Nelliel\Output\OutputPanelThreads($this->domain, false);
            $output_panel->render(['section' => 'panel'], false);
        }
    }

    public function creator(): void
    {
    }

    public function add(): void
    {
    }

    public function editor(): void
    {
    }

    public function update(): void
    {
        $thread_handler = new \Nelliel\ThreadHandler($this->database, $this->domain);
        $thread_handler->processContentDeletes();
    }

    public function remove(): void
    {
        $content_id = new ContentID($_GET['content-id']);
        $content_id->getInstanceFromID($this->domain)->remove();
        $this->regenThread($content_id->threadID(), true);
    }

    public function sticky()
    {
        $this->verifyPermissions($this->domain, 'perm_post_type');
        $content_id = new ContentID($_GET['content-id']);

        if ($content_id->isPost())
        {
            $thread = $content_id->getInstanceFromID($this->domain)->convertToThread();
            $thread->toggleSticky();
            $this->regenThread($thread->contentID()->threadID(), true);
        }

        if ($content_id->isThread())
        {
            $content_id->getInstanceFromID($this->domain)->toggleSticky();
            $this->regenThread($content_id->threadID(), true);
        }
    }

    public function lock()
    {
        $this->verifyPermissions($this->domain, 'perm_post_status');
        $content_id = new ContentID($_GET['content-id']);

        if ($content_id->isThread())
        {
            $content_id->getInstanceFromID($this->domain)->toggleLock();
            $this->regenThread($content_id->threadID(), true);
        }
    }

    public function permasage()
    {
        $this->verifyPermissions($this->domain, 'perm_post_status');
        $content_id = new ContentID($_GET['content-id']);

        if ($content_id->isThread())
        {
            $content_id->getInstanceFromID($this->domain)->toggleSage();
            $this->regenThread($content_id->threadID(), true);
        }
    }

    public function cyclic()
    {
        $this->verifyPermissions($this->domain, 'perm_post_type');
        $content_id = new ContentID($_GET['content-id']);

        if ($content_id->isThread())
        {
            $content_id->getInstanceFromID($this->domain)->toggleCyclic();
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
        $content_instance->remove();
        $this->regenThread($content_id->threadID(), true);
        $ban_ip = $_GET['ban-ip'] ?? '';
        $output_panel = new \Nelliel\Output\OutputPanelBans($this->domain, false);
        $output_panel->new(['ban_ip' => $ban_ip], false);
        $this->outputMain(false);
    }

    public function removeByIP()
    {
        $this->verifyPermissions($this->domain, 'perm_delete_by_ip');
        $first_content_id = new ContentID($_GET['content-id']);
        $post_instance = $first_content_id->getInstanceFromID($this->domain);
        $prepared = $this->database->prepare(
                'SELECT "post_number", "parent_thread" FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "hashed_ip_address" = ?');
        $prepared->bindValue(1, $post_instance->data('hashed_ip_address'), PDO::PARAM_STR);
        $post_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
        $thread_ids = array();

        foreach ($post_ids as $id)
        {
            $content_id = new ContentID(ContentID::createIDString($id['parent_thread'], $id['post_number']));
            $content_id->getInstanceFromID($this->domain)->remove();
            $thread_ids[$content_id->threadID()] = true;
        }

        foreach ($thread_ids as $thread_id => $value)
        {
            $this->regenThread($thread_id, $value);
        }
    }

    public function editPost()
    {
        $this->verifyPermissions($this->domain, 'perm_edit_posts');
        $content_id = new ContentID($_GET['content-id']);
        $post = $content_id->getInstanceFromID($this->domain);
        $output_panel_threads = new OutputPanelThreads($this->domain, true);
        $output_panel_threads->editPost(['post' => $post], false);
        $this->outputMain(false);
    }

    public function updatePost()
    {
        $this->verifyPermissions($this->domain, 'perm_edit_posts');
        $content_id = new ContentID($_GET['content-id']);
        $post = $content_id->getInstanceFromID($this->domain);
        $post->changeData('name', $_POST['not_anonymous'] ?? null);
        $post->changeData('email', $_POST['spam_target'] ?? null);
        $post->changeData('subject', $_POST['verb'] ?? null);
        $post->changeData('comment', $_POST['wordswordswords'] ?? null);
        $post->changeData('regen_cache', 1);
        $post->writeToDatabase();
        $this->regenThread($content_id->threadID(), true);
        $redirect = new Redirect();
        $redirect->doRedirect(true);
        $redirect->changeURL($_POST['return_url']);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm))
        {
            return;
        }

        switch ($perm)
        {
            case 'perm_threads_access':
                nel_derp(410, _gettext('You cannot access the threads control panel.'));
                break;

            case 'perm_post_status':
                nel_derp(411, _gettext('You are not allowed to change the status of threads or posts.'));
                break;

            case 'perm_post_type':
                nel_derp(412, _gettext('You are not allowed to change the type of threads or posts.'));
                break;

            case 'perm_edit_posts':
                nel_derp(413, _gettext('You are not allowed to edit posts.'));
                break;

            case 'perm_delete_by_ip':
                nel_derp(414, _gettext('You are not allowed to delete content by IP.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
