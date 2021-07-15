<?php
declare(strict_types = 1);

namespace Nelliel\Modules\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Modules\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainSite;
use PDO;

class AdminThreads extends Admin
{
    private $site_domain;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->site_domain = new DomainSite($this->database);
    }

    public function renderPanel()
    {
        $this->verifyAccess($this->domain);

        if (isset($_GET['actions']) && $_GET['actions'] === 'expand-thread')
        {
            $content_id = new ContentID($_GET['content-id']);
            $output_panel = new \Nelliel\Render\OutputPanelThreads($this->domain, false);
            $output_panel->render(['section' => 'expanded_thread', 'thread_id' => $content_id->threadID()], false);
        }
        else
        {
            $output_panel = new \Nelliel\Render\OutputPanelThreads($this->domain, false);
            $output_panel->render(['section' => 'panel'], false);
        }
    }

    public function creator()
    {
        $this->verifyAccess($this->domain);
    }

    public function add()
    {
        $this->verifyAction($this->domain);
    }

    public function editor()
    {
        $this->verifyAccess($this->domain);
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

    public function enable()
    {
        $this->verifyAction($this->domain);
    }

    public function disable()
    {
        $this->verifyAction($this->domain);
    }

    public function makeDefault()
    {
        $this->verifyAction($this->domain);
    }

    public function sticky()
    {
        $this->verifyAction($this->domain);
        $content_id = new ContentID($_GET['content-id']);

        if ($content_id->isThread() || $content_id->isPost())
        {
            $content_id->getInstanceFromID($this->domain)->sticky();
            $this->regenThread($content_id->threadID(), true);
        }
    }

    public function lock()
    {
        $this->verifyAction($this->domain);
        $content_id = new ContentID($_GET['content-id']);

        if ($content_id->isThread())
        {
            $content_id->getInstanceFromID($this->domain)->lock();
            $this->regenThread($content_id->threadID(), true);
        }
    }

    public function permasage()
    {
        $this->verifyAction($this->domain);
        $content_id = new ContentID($_GET['content-id']);

        if ($content_id->isThread())
        {
            $content_id->getInstanceFromID($this->domain)->sage();
            $this->regenThread($content_id->threadID(), true);
        }
    }

    public function cyclic()
    {
        $this->verifyAction($this->domain);
        $content_id = new ContentID($_GET['content-id']);

        if ($content_id->isThread())
        {
            $content_id->getInstanceFromID($this->domain)->cyclic();
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
        $content_instance->remove();
        $this->regenThread($content_id->threadID(), true);
        $ban_ip = $_GET['ban-ip'] ?? '';
        $output_panel = new \Nelliel\Render\OutputPanelBans($this->domain, false);
        $output_panel->new(['ban_ip' => $ban_ip], false);
        $this->outputMain(false);
    }

    public function removeByIP()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_delete_by_ip'))
        {
            nel_derp(462, _gettext('You are not allowed to delete by IP.'));
        }

        $first_content_id = new ContentID($_GET['content-id']);
        $post_instance = $first_content_id->getInstanceFromID($this->domain);
        $post_instance->loadFromDatabase();
        $prepared = $this->database->prepare(
                'SELECT "post_number", "parent_thread" FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "hashed_ip_address" = ?');
        $prepared->bindValue(1, nel_prepare_hash_for_storage($post_instance->data('hashed_ip_address')), PDO::PARAM_LOB);
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

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_threads'))
        {
            nel_derp(460, _gettext('You do not have access to the Threads panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_threads'))
        {
            nel_derp(461, _gettext('You are not allowed to manage threads or posts.'));
        }
    }
}
