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

class Embeds
{
    private $domain;
    private $embeds;
    private $processed_embeds = array();
    private $authorization;
    private $session;

    function __construct(Domain $domain, array $embeds, Authorization $authorization, Session $session)
    {
        $this->domain = $domain;
        $this->embeds = $embeds;
        $this->authorization = $authorization;
        $this->session = $session;
    }

    public function process(ContentPost $post): array
    {
        $embed_count = count($this->embeds);

        for ($i = 0; $i < $embed_count; $i ++)
        {
            $embed = new \Nelliel\Content\ContentFile(new \Nelliel\Content\ContentID(), $this->domain);
            $embed->changeData('type', 'embed');
            $embed->changeData('format', ''); // TODO: Maybe detect specific services
            $embed->changeData('embed_url', trim($this->embeds[$i]));
            $this->processed_embeds[] = $embed;
        }

        return $this->processed_embeds;
    }
}