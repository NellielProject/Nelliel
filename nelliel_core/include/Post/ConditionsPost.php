<?php

namespace Nelliel\Post;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

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
                case 'poster_name':
                    $met = preg_match($condition, $this->post->data('poster_name'));
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
                    $met = preg_match($condition, $this->post->data('tripcode'));
                    break;

                case 'secure_tripcode':
                    $met = preg_match($condition, $this->post->data('secure_tripcode'));
                    break;

                case 'has_files':
                    $met = $condition === ($this->post->data('has_content') == 1);
                    break;

                case 'file_count':
                    $met = $condition === $this->post->data('content_count');
                    break;

                case 'is_op':
                    $met = $condition === ($this->post->data('op') == 1);
                    break;

                case 'is_saged':
                    $met = $condition === ($this->post->data('sage') == 1);
                    break;

                case 'staff_post':
                    $met = $condition === !nel_true_empty($this->post->data('staff_post_id'));
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
