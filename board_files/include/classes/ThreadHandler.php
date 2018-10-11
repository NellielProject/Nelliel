<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ThreadHandler
{
    private $dbh;
    private $board_id;

    function __construct($board_id)
    {
        $this->dbh = nel_database();
        $this->board_id = $board_id;
    }

    public function processContentDeletes()
    {
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);
        $returned_list = array();
        $update_archive = false;

        foreach ($_POST as $name => $value)
        {
            if (\Nelliel\ContentID::isContentID($name))
            {
                $content_id = new \Nelliel\ContentID($name);
            }
            else
            {
                continue;
            }

            if ($value === 'action')
            {
                if ($content_id->isThread())
                {
                    $thread = new \Nelliel\ContentThread($this->dbh, $content_id, $this->board_id);
                    $thread->remove();
                    $update_archive = true;
                }
                else if ($content_id->isPost())
                {
                    $post = new \Nelliel\ContentPost($this->dbh, $content_id, $this->board_id);
                    $post->remove();
                }
                else if ($content_id->isFile())
                {
                    $file = new \Nelliel\ContentFile($this->dbh, $content_id, $this->board_id);
                    $file->remove();
                }
            }

            if (!in_array($content_id->thread_id, $returned_list))
            {
                array_push($returned_list, $content_id->thread_id);
            }
        }

        if ($update_archive)
        {
            $archive = new \Nelliel\ArchiveAndPrune($this->board_id);
            $archive->updateAllArchiveStatus();

            if ($board_settings['old_threads'] === 'ARCHIVE')
            {
                $archive->moveThreadsToArchive();
                $archive->moveThreadsFromArchive();
            }
            else if ($board_settings['old_threads'] === 'PRUNE')
            {
                $archive->pruneThreads();
            }
        }

        return $returned_list;
    }
}