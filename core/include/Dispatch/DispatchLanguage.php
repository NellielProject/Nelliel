<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

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
        $this->session->loggedInOrError();
        $this->domain = nel_site_domain(); // Remove if we support board language functions later on
    }

    public function dispatch(array $inputs)
    {
        switch ($inputs['section']) {
            case 'gettext':
                switch ($inputs['action']) {
                    case 'extract':
                        $language = new Language();
                        $language->extractLanguageStrings($this->domain, $this->session->user(), 'nelliel', LC_MESSAGES);
                        break;
                }
        }

        $output_main_panel = new OutputPanelMain($this->domain, false);
        $output_main_panel->render([], false);
    }
}