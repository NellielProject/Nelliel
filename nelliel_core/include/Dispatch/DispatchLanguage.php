<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Language\Language;
use Nelliel\Output\OutputPanelMain;

class DispatchLanguage extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
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
                    $language->extractLanguageStrings($this->domain, $this->session->user(), 'nelliel', 'LC_MESSAGES');
                    break;
            }
        }

        $output_main_panel = new OutputPanelMain($this->domain, false);
        $output_main_panel->render([], false);
    }
}