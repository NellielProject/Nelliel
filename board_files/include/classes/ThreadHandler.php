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

    function __construct($database, Domain $domain)
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
                    $thread = new \Nelliel\Content\ContentThread($this->database, $content_id, $this->domain);
                    $thread->remove();
                    $update_archive = true;
                }
                else if ($content_id->isPost())
                {
                    $post = new \Nelliel\Content\ContentPost($this->database, $content_id, $this->domain);
                    $post->remove();
                }
                else if ($content_id->isContent())
                {
                    $file = new \Nelliel\Content\ContentFile($this->database, $content_id, $this->domain);
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

        $regen = new Regen();
        $regen->threads($this->domain, true, $updates);
        $regen->index($this->domain);
    }
}