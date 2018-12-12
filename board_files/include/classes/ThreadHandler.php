<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ThreadHandler
{
    private $database;
    private $domain;

    function __construct($database, $domain)
    {
        $this->database = $database;
        $this->domain = $domain;
    }

    public function processContentDeletes()
    {
        $updates = array();
        $update_archive = false;

        foreach ($_POST as $name => $value)
        {
            if (ContentID::isContentID($name))
            {
                $content_id = new ContentID($name);
            }
            else
            {
                continue;
            }

            if ($value === 'action')
            {
                if ($content_id->isThread())
                {
                    $thread = new \Nelliel\Content\ContentThread($this->database, $content_id, $this->domain->id());
                    $thread->remove();
                    $update_archive = true;
                }
                else if ($content_id->isPost())
                {
                    $post = new \Nelliel\Content\ContentPost($this->database, $content_id, $this->domain->id());
                    $post->remove();
                }
                else if ($content_id->isFile())
                {
                    $file = new \Nelliel\Content\ContentFile($this->database, $content_id, $this->domain->id());
                    $file->remove();
                }
            }

            if (!in_array($content_id->thread_id, $updates))
            {
                array_push($updates, $content_id->thread_id);
            }
        }

        if ($update_archive)
        {
            $archive = new ArchiveAndPrune($this->database, $this->domain, new FileHandler());
            $archive->updateThreads();
        }

        $regen = new \Nelliel\Regen();
        $regen->threads($this->domain->id(), true, $updates);
        $regen->index($this->domain->id());
    }
}