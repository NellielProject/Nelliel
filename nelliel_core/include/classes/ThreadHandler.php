<?php

declare(strict_types=1);

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
        $deletes = array();

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
                $deletes[] = $content_id->getInstanceFromID($this->domain);
            }

            if (!in_array($content_id->threadID(), $updates))
            {
                array_push($updates, $content_id->threadID());
            }
        }

        $delete_count = count($deletes);

        if ($delete_count > nel_site_domain()->setting('max_delete_items'))
        {
            nel_derp(131,
                    sprintf(_gettext('You are trying to delete too many items at once. Limit is %d.'),
                            nel_site_domain()->setting('max_delete_items')));
        }

        foreach ($deletes as $delete)
        {
            $delete->remove();
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