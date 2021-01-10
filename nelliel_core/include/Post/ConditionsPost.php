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

    function __construct(ContentPost $post)
    {
        $this->post = $post;
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
