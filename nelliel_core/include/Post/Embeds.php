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
        $embed_count = count($this->embeds);

        if ($embed_count > 0 && !$this->domain->setting('allow_embeds'))
        {
            nel_derp(38, _gettext('You are not allowed to post embedded content.'));
        }

        for ($i = 0; $i < $embed_count; $i ++)
        {
            $embed_url = trim($this->embeds[$i]);
            $embed = new \Nelliel\Content\ContentFile(new \Nelliel\Content\ContentID(), $this->domain);
            $response_to = $post->data('response_to');

            if ($this->domain->setting('check_embed_duplicates'))
            {
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