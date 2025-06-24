<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;

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
        $threads = array();
        $posts = array();
        $uploads = array();
        $delete_count = 0;
        $max_deletes = nel_get_cached_domain(Domain::SITE)->setting('max_delete_items');

        foreach ($_POST as $name => $value) {

            if (ContentID::isContentID($name)) {
                $content_id = new ContentID($name);
            } else {
                continue;
            }

            if ($value === 'action') {
                $delete_count ++;

                if ($delete_count > $max_deletes) {
                    nel_derp(131,
                        sprintf(_gettext('You are trying to delete too many items at once. Limit is %d.'), $max_deletes));
                }

                if ($content_id->isThread()) {
                    $threads[] = $content_id;
                } else if ($content_id->isPost()) {
                    $posts[] = $content_id;
                } else if ($content_id->isUpload()) {
                    $uploads[] = $content_id;
                }
            }
        }

        $delete_function = function (ContentID $content_id) use (&$updates) {
            $instance = $content_id->getInstanceFromID($this->domain);

            if (!$instance->exists()) {
                return;
            }

            $instance->delete();

            if (!in_array($content_id->threadID(), $updates)) {
                array_push($updates, $content_id->threadID());
            }
        };

        foreach ($threads as $content_id) {
            $delete_function($content_id);
        }

        foreach ($posts as $content_id) {
            $delete_function($content_id);
        }

        foreach ($uploads as $content_id) {
            $delete_function($content_id);
        }

        $regen = new Regen();
        $regen->threads($this->domain, $updates);
        $this->site_domain = Domain::getDomainFromID(Domain::SITE);

        if ($this->site_domain->setting('overboard_active')) {
            $regen->overboard($this->site_domain);
        }

        $regen->index($this->domain);
    }
}