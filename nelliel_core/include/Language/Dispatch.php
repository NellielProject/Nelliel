<?php

namespace Nelliel\Language;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\DomainSite;
use Nelliel\Auth\Authorization;
use Nelliel\Admin\AdminHandler;

class Dispatch
{
    private $domain;
    private $authorization;

    function __construct(Domain $domain, Authorization $authorization)
    {
        $this->domain = $domain;
        $this->authorization = $authorization;
    }

    public function dispatch(array $inputs)
    {
        $session = new \Nelliel\Account\Session();
        $session->loggedInOrError();

        foreach ($inputs['actions'] as $action)
        {
            switch ($action)
            {
                case 'extract-gettext':
                    $language = new \Nelliel\Language\Language();
                    $language->extractLanguageStrings($this->domain, $session->sessionUser(), 'nelliel', LC_MESSAGES);
                    break;
            }
        }

        $output_main_panel = new \Nelliel\Output\OutputPanelMain($this->domain, false);
        $output_main_panel->render(['user' => $session->sessionUser()], false);
    }
}