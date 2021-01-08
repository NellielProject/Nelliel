<?php

namespace Nelliel\Language;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Render\OutputPanelMain;

class Dispatch
{
    private $domain;
    private $authorization;
    private $session;

    function __construct(Domain $domain, Authorization $authorization, Session $session)
    {
        $this->domain = $domain;
        $this->authorization = $authorization;
        $this->session = $session;
    }

    public function dispatch(array $inputs)
    {
        $this->session->loggedInOrError();

        foreach ($inputs['actions'] as $action)
        {
            switch ($action)
            {
                case 'extract-gettext':
                    $language = new Language();
                    $language->extractLanguageStrings($this->domain, $this->session->user(), 'nelliel',
                            LC_MESSAGES);
                    break;
            }
        }

        $output_main_panel = new OutputPanelMain($this->domain, false);
        $output_main_panel->render([], false);
    }
}