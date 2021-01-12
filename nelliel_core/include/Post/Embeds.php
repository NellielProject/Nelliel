<?php

namespace Nelliel\Post;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentPost;
use Nelliel\Domains\Domain;
use PDO;

class Embeds
{
    private $domain;
    private $database;
    private $embeds;
    private $processed_embeds = array();
    private $authorization;
    private $session;

    function __construct(Domain $domain, array $embeds, Authorization $authorization, Session $session)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->embeds = $embeds;
        $this->authorization = $authorization;
        $this->session = $session;
    }

    public function process(ContentPost $post): array
    {
        $no_embeds = nel_true_empty($this->embeds[0]);
        $response_to = $post->data('response_to');

        if ($no_embeds)
        {
            return array();
        }
        else
        {
            if (!$this->domain->setting('allow_embeds'))
            {
                nel_derp(26, _gettext('You are not allowed to post embedded content.'));
            }

            if (!$response_to && !$this->domain->setting('allow_op_uploads'))
            {
                nel_derp(39, _gettext('The first post cannot have embeds.'));
            }

            if ($response_to && !$this->domain->setting('allow_reply_uploads'))
            {
                nel_derp(40, _gettext('Replies cannot have embeds.'));
            }
        }

        $embed_count = count($this->embeds);

        if ($embed_count > 1)
        {
            if (!$response_to && !$this->domain->setting('allow_op_multiple'))
            {
                nel_derp(43, _gettext('The first post cannot have multiple embeds.'));
            }

            if ($response_to && !$this->domain->setting('allow_reply_multiple'))
            {
                nel_derp(44, _gettext('You cannot have multiple embeds in a reply.'));
            }
        }

        for ($i = 0; $i < $embed_count; $i ++)
        {
            $embed_url = trim($this->embeds[$i]);

            if (empty($embed_url))
            {
                continue;
            }

            $embed = new \Nelliel\Content\ContentFile(new \Nelliel\Content\ContentID(), $this->domain);

            if ($response_to === 0 && $this->domain->setting('check_op_duplicates'))
            {
                $prepared = $this->database->prepare(
                        'SELECT 1 FROM "' . $this->domain->reference('content_table') .
                        '" WHERE "parent_thread" = "post_ref" AND "embed_url" = ?');
                $prepared->bindValue(1, $embed_url, PDO::PARAM_STR);
            }

            if ($response_to > 0 && $this->domain->setting('check_thread_duplicates'))
            {
                $prepared = $this->database->prepare(
                        'SELECT 1 FROM "' . $this->domain->reference('content_table') .
                        '" WHERE "parent_thread" = ? AND "embed_url" = ?');
                $prepared->bindValue(1, $response_to, PDO::PARAM_INT);
            }

            $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN, true);

            if ($result)
            {
                nel_derp(36, _gettext('Duplicate embed detected.'));
            }

            $embed->changeData('type', 'embed');
            $embed->changeData('format', ''); // TODO: Maybe detect specific services
            $embed->changeData('embed_url', $embed_url);
            $this->processed_embeds[] = $embed;
        }

        return $this->processed_embeds;
    }
}