<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Post;
use Nelliel\Content\Thread;
use Nelliel\Content\Upload;
use Nelliel\Domains\Domain;

class OutputModmodeLinks extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function thread(Thread $thread): array
    {
        $options_keys = (array) json_decode($this->domain->setting('thread_mod_options_link_set'));
        $link_set = new LinkSet();
        $base_data = array();
        $base_data['left_bracket'] = $this->getUIText('mod_links_left_bracket');
        $base_data['right_bracket'] = $this->getUIText('mod_links_right_bracket');

        if ($this->session->user()->checkPermission($this->domain, 'perm_modify_content_status')) {
            if (!$thread->getData('locked')) {
                $link_set->addLink('mod_links_lock', $base_data);
                $link_set->addData('mod_links_lock', 'url',
                    nel_build_router_url(
                        [$this->domain->uri(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'lock']));
                $link_set->addData('mod_links_lock', 'text', $this->getUIText('mod_links_lock'));
            } else {
                $link_set->addLink('mod_links_unlock', $base_data);
                $link_set->addData('mod_links_unlock', 'url',
                    nel_build_router_url(
                        [$this->domain->uri(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'unlock']));
                $link_set->addData('mod_links_unlock', 'text', $this->getUIText('mod_links_unlock'));
            }

            if (!$thread->getData('sticky')) {
                $link_set->addLink('mod_links_sticky', $base_data);
                $link_set->addData('mod_links_sticky', 'url',
                    nel_build_router_url(
                        [$this->domain->uri(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'sticky']));
                $link_set->addData('mod_links_sticky', 'text', $this->getUIText('mod_links_sticky'));
            } else {
                $link_set->addLink('mod_links_unsticky', $base_data);
                $link_set->addData('mod_links_unsticky', 'url',
                    nel_build_router_url(
                        [$this->domain->uri(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'unsticky']));
                $link_set->addData('mod_links_unsticky', 'text', $this->getUIText('mod_links_unsticky'));
            }

            if (!$thread->getData('permasage')) {
                $link_set->addLink('mod_links_permasage', $base_data);
                $link_set->addData('mod_links_permasage', 'url',
                    nel_build_router_url(
                        [$this->domain->uri(), 'moderation', 'modmode', $thread->contentID()->getIDString(),
                            'permasage']));
                $link_set->addData('mod_links_permasage', 'text', $this->getUIText('mod_links_permasage'));
            } else {
                $link_set->addLink('mod_links_unpermasage', $base_data);
                $link_set->addData('mod_links_unpermasage', 'url',
                    nel_build_router_url(
                        [$this->domain->uri(), 'moderation', 'modmode', $thread->contentID()->getIDString(),
                            'unpermasage']));
                $link_set->addData('mod_links_unpermasage', 'text', $this->getUIText('mod_links_unpermasage'));
            }

            if (!$thread->getData('cyclic')) {
                $link_set->addLink('mod_links_cyclic', $base_data);
                $link_set->addData('mod_links_cyclic', 'url',
                    nel_build_router_url(
                        [$this->domain->uri(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'cyclic']));
                $link_set->addData('mod_links_cyclic', 'text', $this->getUIText('mod_links_cyclic'));
            } else {
                $link_set->addLink('mod_links_non_cyclic', $base_data);
                $link_set->addData('mod_links_non_cyclic', 'url',
                    nel_build_router_url(
                        [$this->domain->uri(), 'moderation', 'modmode', $thread->contentID()->getIDString(),
                            'non-cyclic']));
                $link_set->addData('mod_links_non_cyclic', 'text', $this->getUIText('mod_links_non_cyclic'));
            }
        }

        if (!$thread->getData('shadow')) {
            if ($this->session->user()->checkPermission($this->domain, 'perm_move_content')) {
                $link_set->addLink('mod_links_move', $base_data);
                $link_set->addData('mod_links_move', 'url',
                    nel_build_router_url(
                        [$this->domain->uri(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'move']));
                $link_set->addData('mod_links_move', 'text', $this->getUIText('mod_links_move'));
            }

            if ($this->session->user()->checkPermission($this->domain, 'perm_merge_threads')) {
                $link_set->addLink('mod_links_merge', $base_data);
                $link_set->addData('mod_links_merge', 'url',
                    nel_build_router_url(
                        [$this->domain->uri(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'merge']));
                $link_set->addData('mod_links_merge', 'text', $this->getUIText('mod_links_merge'));
            }
        }

        return $link_set->build($options_keys);
    }

    public function post(Post $post): array
    {
        if ($this->session->user()->checkPermission($this->domain, 'perm_view_unhashed_ip') &&
            !empty($post->getData('ip_address'))) {
            $ip = nel_convert_ip_from_storage($post->getData('ip_address'));
        } else {
            if (!empty($post->getData('hashed_ip_address'))) {
                $ip = $post->getData('hashed_ip_address');
            } else {
                $ip = $post->getData('visitor_id');
            }
        }

        $this->render_data['mod_ip_address'] = $ip;
        $options_keys = (array) json_decode($this->domain->setting('post_mod_options_link_set'));
        $link_set = new LinkSet();
        $base_data = array();
        $base_data['left_bracket'] = $this->getUIText('mod_links_left_bracket');
        $base_data['right_bracket'] = $this->getUIText('mod_links_right_bracket');

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_bans')) {
            $link_set->addLink('mod_links_ban', $base_data);
            $link_set->addData('mod_links_ban', 'url',
                nel_build_router_url(
                    [$this->domain->uri(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'ban']));
            $link_set->addData('mod_links_ban', 'text', $this->getUIText('mod_links_ban'));

            if ($this->session->user()->checkPermission($this->domain, 'perm_delete_content')) {
                $link_set->addLink('mod_links_ban_and_delete', $base_data);
                $link_set->addData('mod_links_ban_and_delete', 'url',
                    nel_build_router_url(
                        [$this->domain->uri(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'ban-delete']));
                $link_set->addData('mod_links_ban_and_delete', 'text', $this->getUIText('mod_links_ban_and_delete'));
            }
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_delete_content')) {
            $link_set->addLink('mod_links_delete', $base_data);
            $link_set->addData('mod_links_delete', 'url',
                nel_build_router_url(
                    [$this->domain->uri(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'delete']));
            $link_set->addData('mod_links_delete', 'text', $this->getUIText('mod_links_delete'));
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_delete_by_ip')) {
            $link_set->addLink('mod_links_delete_by_ip', $base_data);
            $link_set->addData('mod_links_delete_by_ip', 'url',
                nel_build_router_url(
                    [$this->domain->uri(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'delete-by-ip']));
            $link_set->addData('mod_links_delete_by_ip', 'text', $this->getUIText('mod_links_delete_by_ip'));
        }

        if ($this->session->user()->checkPermission($this->global_domain, 'perm_delete_by_ip')) {
            $link_set->addLink('mod_links_global_delete_by_ip', $base_data);
            $link_set->addData('mod_links_global_delete_by_ip', 'url',
                nel_build_router_url(
                    [$this->domain->uri(), 'moderation', 'modmode', $post->contentID()->getIDString(),
                        'global-delete-by-ip']));
            $link_set->addData('mod_links_global_delete_by_ip', 'text',
                $this->getUIText('mod_links_global_delete_by_ip'));
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_edit_posts')) {
            $link_set->addLink('mod_links_edit', $base_data);
            $link_set->addData('mod_links_edit', 'url',
                nel_build_router_url(
                    [$this->domain->uri(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'edit']));
            $link_set->addData('mod_links_edit', 'text', $this->getUIText('mod_links_edit'));
        }

        if (!$post->getParent()->getData('shadow')) {
            if ($this->session->user()->checkPermission($this->domain, 'perm_move_content')) {
                $link_set->addLink('mod_links_move', $base_data);
                $link_set->addData('mod_links_move', 'url',
                    nel_build_router_url(
                        [$this->domain->uri(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'move']));
                $link_set->addData('mod_links_move', 'text', $this->getUIText('mod_links_move'));
            }
        }

        return $link_set->build($options_keys);
    }

    public function upload(Upload $upload): array
    {
        $is_file = nel_true_empty($upload->getData('embed_url'));
        $options_keys = (array) json_decode($this->domain->setting('upload_mod_options_link_set'));
        $link_set = new LinkSet();
        $base_data = array();
        $base_data['left_bracket'] = $this->getUIText('mod_links_left_bracket');
        $base_data['right_bracket'] = $this->getUIText('mod_links_right_bracket');

        if ($this->session->user()->checkPermission($this->domain, 'perm_delete_content')) {
            $link_set->addLink('mod_links_delete', $base_data);
            $link_set->addData('mod_links_delete', 'url',
                nel_build_router_url(
                    [$this->domain->uri(), 'moderation', 'modmode', $upload->contentID()->getIDString(), 'delete']));
            $link_set->addData('mod_links_delete', 'text', $this->getUIText('mod_links_delete'));
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_move_content')) {
            $link_set->addLink('mod_links_move', $base_data);
            $link_set->addData('mod_links_move', 'url',
                nel_build_router_url(
                    [$this->domain->uri(), 'moderation', 'modmode', $upload->contentID()->getIDString(), 'move']));
            $link_set->addData('mod_links_move', 'text', $this->getUIText('mod_links_move'));
        }

        if ($is_file) {
            $options_keys[] = 'mod_links_spoiler';
            $options_keys[] = 'mod_links_unspoiler';

            if ($this->session->user()->checkPermission($this->domain, 'perm_modify_content_status')) {
                if (!$upload->getData('spoiler')) {
                    $link_set->addLink('mod_links_spoiler', $base_data);
                    $link_set->addData('mod_links_spoiler', 'url',
                        nel_build_router_url(
                            [$this->domain->uri(), 'moderation', 'modmode', $upload->contentID()->getIDString(),
                                'spoiler']));
                    $link_set->addData('mod_links_spoiler', 'text', $this->getUIText('mod_links_spoiler'));
                    $link_set->addLink('mod_links_unspoiler', $base_data);
                } else {
                    $link_set->addData('mod_links_unspoiler', 'url',
                        nel_build_router_url(
                            [$this->domain->uri(), 'moderation', 'modmode', $upload->contentID()->getIDString(),
                                'unspoiler']));
                    $link_set->addData('mod_links_unspoiler', 'text', $this->getUIText('mod_links_unspoiler'));
                }
            }
        }

        return $link_set->build($options_keys);
    }

    private function getUIText(string $id)
    {
        $ui_text = strval($this->domain->setting($id));

        if (!$this->domain->setting('translate_mod_links') || $ui_text === '') {
            return $ui_text;
        }

        return __($ui_text);
    }
}