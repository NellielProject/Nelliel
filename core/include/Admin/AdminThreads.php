<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Redirect;
use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Content\Post;
use Nelliel\Content\Thread;
use Nelliel\Content\Upload;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Output\OutputPanelBans;
use Nelliel\Output\OutputPanelThreads;
use PDO;

class AdminThreads extends Admin
{
    private $site_domain;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->site_domain = Domain::getDomainFromID(Domain::SITE);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_threads_access');

        if (isset($_GET['actions']) && $_GET['actions'] === 'expand-thread') {
            $content_id = new ContentID($_GET['content-id']);
            $output_panel = new OutputPanelThreads($this->domain, false);
            $output_panel->render(['section' => 'expanded_thread', 'thread_id' => $content_id->threadID()], false);
        } else {
            $output_panel = new OutputPanelThreads($this->domain, false);
            $output_panel->render(['section' => 'panel'], false);
        }
    }

    public function creator(): void
    {}

    public function add(): void
    {}

    public function editor(ContentID $content_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_edit_posts');
        $post = $content_id->getInstanceFromID($this->domain);
        $output_panel_threads = new OutputPanelThreads($this->domain, true);
        $output_panel_threads->editPost(['post' => $post], false);
    }

    public function update(ContentID $content_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_edit_posts');
        $post = $content_id->getInstanceFromID($this->domain);
        $post->changeData('name', $_POST['not_anonymous'] ?? null);
        $post->changeData('email', $_POST['spam_target'] ?? null);
        $post->changeData('subject', $_POST['verb'] ?? null);
        $post->changeData('comment', $_POST['wordswordswords'] ?? null);
        $post->changeData('regen_cache', 1);
        $post->writeToDatabase();
        $this->regenThread($this->domain, $content_id->threadID(), true);
        $redirect = new Redirect();
        $redirect->doRedirect(true);
        $redirect->URL($_POST['return_url']);
    }

    public function delete(ContentID $content_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_delete_content');
        $content_id->getInstanceFromID($this->domain)->delete();
        $this->regenThread($this->domain, $content_id->threadID(), true);
    }

    public function sticky(ContentID $content_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_content_status');

        if ($content_id->isPost()) {
            $thread = $content_id->getInstanceFromID($this->domain)->convertToThread();
            $thread->toggleSticky();
            $this->regenThread($this->domain, $thread->contentID()->threadID(), true);
        }

        if ($content_id->isThread()) {
            $content_id->getInstanceFromID($this->domain)->toggleSticky();
            $this->regenThread($this->domain, $content_id->threadID(), true);
        }
    }

    public function lock(ContentID $content_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_content_status');

        if ($content_id->isThread()) {
            $content_id->getInstanceFromID($this->domain)->toggleLock();
            $this->regenThread($this->domain, $content_id->threadID(), true);
        }
    }

    public function sage(ContentID $content_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_content_status');

        if ($content_id->isThread()) {
            $content_id->getInstanceFromID($this->domain)->togglePermasage();
            $this->regenThread($this->domain, $content_id->threadID(), true);
        }
    }

    public function cyclic(ContentID $content_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_content_status');

        if ($content_id->isThread()) {
            $content_id->getInstanceFromID($this->domain)->toggleCyclic();
            $this->regenThread($this->domain, $content_id->threadID(), true);
        }
    }

    private function regenThread(DomainBoard $domain, $thread_id, bool $regen_index = false)
    {
        $regen = new Regen();
        $regen->threads($domain, [$thread_id]);

        if ($this->site_domain->setting('overboard_active')) {
            $regen->overboard($this->site_domain);
        }

        if ($regen_index) {
            $regen->index($domain);
        }
    }

    public function banDelete(ContentID $content_id)
    {
        $this->verifyPermissions($this->domain, 'perm_delete_content');
        $content_id = new ContentID($_GET['content-id']);
        $content_instance = $content_id->getInstanceFromID($this->domain);
        $content_instance->delete();
        $this->regenThread($this->domain, $content_id->threadID(), true);
        $ban_ip = $_GET['ban-ip'] ?? '';
        $output_panel = new OutputPanelBans($this->domain, false);
        $output_panel->new(['ban_ip' => $ban_ip], false);
    }

    public function deleteByIP(ContentID $first_content_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_delete_by_ip');
        $post_instance = $first_content_id->getInstanceFromID($this->domain);
        $prepared = $this->database->prepare(
            'SELECT "post_number", "parent_thread" FROM "' . $this->domain->reference('posts_table') .
            '" WHERE "hashed_ip_address" = ?');
        $prepared->bindValue(1, $post_instance->getData('hashed_ip_address'), PDO::PARAM_STR);
        $post_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
        $thread_ids = array();

        foreach ($post_ids as $id) {
            $content_id = new ContentID(ContentID::createIDString($id['parent_thread'], $id['post_number']));
            $content_id->getInstanceFromID($this->domain)->delete();
            $thread_ids[$content_id->threadID()] = true;
        }

        foreach ($thread_ids as $thread_id => $value) {
            $this->regenThread($this->domain, $thread_id, $value);
        }
    }

    public function globalDeleteByIP(ContentID $first_content_id): void
    {
        $this->verifyPermissions(nel_get_cached_domain(Domain::GLOBAL), 'perm_delete_by_ip');
        $post_instance = $first_content_id->getInstanceFromID($this->domain);
        $hashed_ip = $post_instance->getData('hashed_ip_address');
        $query = 'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);

        foreach ($board_ids as $board_id) {
            $board_domain = Domain::getDomainFromID($board_id);
            $prepared = $this->database->prepare(
                'SELECT "post_number", "parent_thread" FROM "' . $board_domain->reference('posts_table') .
                '" WHERE "hashed_ip_address" = ?');
            $prepared->bindValue(1, $hashed_ip, PDO::PARAM_STR);
            $post_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
            $thread_ids = array();

            foreach ($post_ids as $id) {
                $content_id = new ContentID(ContentID::createIDString($id['parent_thread'], $id['post_number']));
                $content_id->getInstanceFromID($board_domain)->delete();
                $thread_ids[$content_id->threadID()] = true;
            }

            foreach ($thread_ids as $thread_id => $value) {
                $this->regenThread($board_domain, $thread_id, $value);
            }
        }
    }

    public function move(ContentID $content_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_move_content');
        $destination_domain = Domain::getDomainFromID($_POST['destination_board'] ?? 0);
        $this->verifyPermissions($destination_domain, 'perm_move_content');
        $keep_shadow = boolval($_POST['keep_shadow'] ?? false);

        if ($content_id->isThread() || $content_id->threadID() === $content_id->postID()) {
            $destination = Domain::getDomainFromID($_POST['destination_board'] ?? 0);
            $thread = new Thread($content_id, $this->domain);
            $thread->move($destination, $keep_shadow);
        }

        if ($content_id->isPost()) {
            if (!$this->domain->setting('allow_moving_replies')) {
                nel_derp(262, _gettext('Individual replies cannot be moved on this board.'));
            }

            $post = new Post($content_id, $this->domain);
            $destination_thread_id = (int) $_POST['destination_thread_id'] ?? 0;

            if ($destination_thread_id !== 0) {
                $new_content_id = new ContentID(ContentID::createIDString($destination_thread_id, 0, 0));
                $new_thread = new Thread($new_content_id, $destination_domain);

                if (!$new_thread->exists()) {
                    nel_derp(260, _gettext('Thread does not exist.'));
                }

                $post->move($new_thread, false);
            } else {
                $new_thread = $post->convertToThread();
                $new_thread->move($destination_domain, $keep_shadow);
            }
        }

        if ($content_id->isUpload()) {
            if (!$this->domain->setting('allow_moving_uploads')) {
                nel_derp(263, _gettext('Uploads cannot be moved on this board.'));
            }

            $upload = new Upload($content_id, $this->domain);
            $destination_post_id = (int) $_POST['destination_post_id'] ?? 0;
            $prepared = $this->database->prepare(
                'SELECT "parent_thread" FROM "' . $destination_domain->reference('posts_table') .
                '" WHERE "post_number" = ?');
            $prepared->bindValue(1, $destination_post_id, PDO::PARAM_INT);
            $thread_id = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
            $new_content_id = new ContentID(ContentID::createIDString($thread_id, $destination_post_id, 0));
            $new_post = new Post($new_content_id, $destination_domain);

            if (!$new_post->exists()) {
                nel_derp(261, _gettext('Post does not exist.'));
            }

            $upload->move($new_post, false);
        }

        $redirect = new Redirect();
        $redirect->doRedirect(true);
        $redirect->URL($_POST['return_url']);
    }

    public function merge(ContentID $incoming_content_id): void
    {
        $target_board = $_POST['target_board'] ?? null;
        $target_thread_id = intval($_POST['target_thread_id'] ?? 0);

        if ($target_thread_id < 1) {
            nel_derp(264, _gettext('No valid target thread specified.'));
        }

        $this->verifyPermissions($this->domain, 'perm_merge_threads');
        $target_domain = Domain::getDomainFromID($target_board);
        $this->verifyPermissions($target_domain, 'perm_merge_threads');
        $keep_shadow = boolval($_POST['keep_shadow'] ?? false);

        $target_content_id = new ContentID('cid_' . $target_thread_id . '_0_0');
        $target_thread = new Thread($target_content_id, $target_domain);
        $incoming_thread = new Thread($incoming_content_id, $this->domain);
        $target_thread->merge($incoming_thread, $keep_shadow);

        $redirect = new Redirect();
        $redirect->doRedirect(true);
        $redirect->URL($_POST['return_url']);
    }

    public function spoiler(ContentID $content_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_content_status');

        if ($content_id->isUpload()) {
            $upload = $content_id->getInstanceFromID($this->domain);
            $upload->toggleSpoiler();
            $this->regenThread($this->domain, $content_id->threadID(), true);
        }
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_threads_access':
                nel_derp(410, _gettext('You cannot access the threads control panel.'), 403);
                break;

            case 'perm_modify_content_status':
                nel_derp(411, _gettext('You are not allowed to change the status of threads or posts.'), 403);
                break;

            case 'perm_edit_posts':
                nel_derp(412, _gettext('You are not allowed to edit posts.'), 403);
                break;

            case 'perm_delete_by_ip':
                nel_derp(413, _gettext('You are not allowed to delete content by IP.'), 403);
                break;

            case 'perm_move_content':
                nel_derp(414, _gettext('You are not allowed to move content on one or both of the selected boards.'), 403);
                break;

            case 'perm_delete_content':
                nel_derp(415, _gettext('You are not allowed to delete content.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
