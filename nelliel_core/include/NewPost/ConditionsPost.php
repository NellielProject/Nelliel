<?php
declare(strict_types = 1);

namespace Nelliel\NewPost;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\IfThens\Conditions;
use Nelliel\Content\ContentPost;

class ConditionsPost implements Conditions
{
    private $post;
    private $files;

    function __construct(ContentPost $post, array $files)
    {
        $this->post = $post;
        $this->files = $files;
    }

    public function check(array $conditions): bool
    {
        $total_conditions = count($conditions);
        $conditions_met = 0;

        foreach ($conditions as $key => $condition)
        {
            $met = false;

            switch ($key)
            {
                case 'function-post':
                    if (is_callable($condition))
                    {
                        $met = $condition($this->post, $this->files);
                    }

                    break;

                case 'board_id':
                    $met = $condition === $this->post->domain()->id();
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

                case 'tripcode':
                    $met = $condition === $this->post->data('tripcode');
                    break;

                case 'secure_tripcode':
                    $met = $condition === $this->post->data('secure_tripcode');
                    break;

                case 'has_content':
                    $met = $condition === boolval($this->post->data('has_content'));
                    break;

                case 'total_content':
                    $met = $condition === intval($this->post->data('total_content'));
                    break;

                case 'file_count':
                    $met = $condition === intval($this->post->data('file_count'));
                    break;

                case 'embed_count':
                    $met = $condition === intval($this->post->data('embed_count'));
                    break;

                case 'is_op':
                    $met = $condition === boolval($this->post->data('op'));
                    break;

                case 'is_saged':
                    $met = $condition === boolval($this->post->data('sage'));
                    break;
            }

            // If any condition is not met (including unhandled ones) we stop early
            if (!$met)
            {
                break;
            }

            $conditions_met ++;
        }

        return $conditions_met === $total_conditions;
    }
}
