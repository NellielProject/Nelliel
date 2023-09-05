<?php
declare(strict_types = 1);

namespace Nelliel\NewPost;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Checkpoints\Conditions;
use Nelliel\Content\Post;

class ConditionsPost implements Conditions
{
    private $post;
    private $files;

    function __construct(Post $post, array $files)
    {
        $this->post = $post;
        $this->files = $files;
    }

    public function check(array $conditions): bool
    {
        $total_conditions = count($conditions);
        $conditions_met = 0;

        foreach ($conditions as $key => $condition) {
            $met = false;

            switch ($key) {
                case 'function-post':
                    if (is_callable($condition)) {
                        $met = $condition($this->post, $this->files);
                    }

                    break;

                case 'board_':
                    $met = utf8_strtolower($condition) === utf8_strtolower($this->post->domain()->uri());
                    break;

                case 'name':
                    $met = preg_match($condition, $this->post->data('name'));
                    break;

                case 'email':
                    $met = preg_match($condition, $this->post->data('email'));
                    break;

                case 'subject':
                    $met = preg_match($condition, $this->post->data('subject'));
                    break;

                case 'comment':
                    $met = preg_match($condition, $this->post->data('comment'));
                    break;

                case 'original_comment':
                    $met = preg_match($condition, $this->post->data('original_comment'));
                    break;

                case 'tripcode':
                    $met = $condition === $this->post->data('tripcode');
                    break;

                case 'secure_tripcode':
                    $met = $condition === $this->post->data('secure_tripcode');
                    break;

                case 'ip_address':
                    $met = preg_match($condition, $this->post->data('ip_address'));
                    break;

                case 'hashed_ip_address':
                    $met = preg_match($condition, $this->post->data('hashed_ip_address'));
                    break;

                case 'has_uploads':
                    $met = $condition === $this->post->data('total_uploads') > 0;
                    break;

                case 'total_uploads':
                    $met = $condition === $this->post->data('total_uploads');
                    break;

                case 'file_count':
                    $met = $condition === $this->post->data('file_count');
                    break;

                case 'embed_count':
                    $met = $condition === $this->post->data('embed_count');
                    break;

                case 'is_op':
                    $met = $condition === $this->post->data('op');
                    break;

                case 'is_saged':
                    $met = $condition === $this->post->data('sage');
                    break;
            }

            // If any condition is not met (including unhandled ones) we stop early
            if (!$met) {
                break;
            }

            $conditions_met ++;
        }

        return $conditions_met === $total_conditions;
    }
}
