<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainSite;

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
                $content_id->getInstanceFromID($this->domain)->remove();
            }

            if (!in_array($content_id->threadID(), $updates))
            {
                array_push($updates, $content_id->threadID());
            }
        }

        $regen = new Regen();
        $regen->threads($this->domain, true, $updates);
        $this->site_domain = new DomainSite($this->database);

        if ($this->site_domain->setting('overboard_active'))
        {
            $regen->overboard($this->site_domain);
        }

        $regen->index($this->domain);
    }
}