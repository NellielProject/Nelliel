<?php

namespace Nelliel\Post;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\IfThen;
use Nelliel\NellielPDO;
use Nelliel\Content\ContentPost;

class IfThenPost extends IfThen
{
    private $post;

    function __construct(NellielPDO $database, ContentPost $post)
    {
        parent::__construct($database);
        $this->post = $post;
    }

    public function if(array $if): bool
    {
        $total_conditions = count($if);
        $conditions_met = 0;

        foreach ($if as $key => $condition)
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

    public function then(array $then)
    {
        foreach ($then as $action => $data)
        {
            switch ($action)
            {
                // Don't have actions ready yet
            }
        }
    }
}
