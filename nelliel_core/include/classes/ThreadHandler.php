<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;

class ThreadHandler
{
    private $database;
    private $domain;

    function __construct(Domain $domain)
    {
        $this->database = $domain->database();
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
                    $thread = new \Nelliel\Content\ContentThread($content_id, $this->domain);
                    $thread->remove();
                    $update_archive = true;
                }
                else if ($content_id->isPost())
                {
                    $post = new \Nelliel\Content\ContentPost($content_id, $this->domain);
                    $post->remove();
                }
                else if ($content_id->isContent())
                {
                    $file = new \Nelliel\Content\ContentFile($content_id, $this->domain);
                    $file->remove();
                }
            }

            if (!in_array($content_id->threadID(), $updates))
            {
                array_push($updates, $content_id->threadID());
            }
        }

        if ($update_archive)
        {
            $archive = new ArchiveAndPrune($this->domain, new \Nelliel\Utility\FileHandler());
            $archive->updateThreads();
        }

        $regen = new Regen();
        $regen->threads($this->domain, true, $updates);
        $this->site_domain = new \Nelliel\DomainSite($this->database);

        if($this->site_domain->setting('overboard_active'))
        {
            $regen->overboard($this->site_domain);
        }

        $regen->index($this->domain);
    }
}