<?php
defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Domains\Domain;

function nel_early_derp(int $error_id, string $error_message, array $error_data = array())
{
    echo _gettext('oh god how did this get in here');
    echo '<br>';
    echo _gettext('Error ID: ') . $error_id;
    echo '<br>';
    echo _gettext('Error Message: ') . $error_message;
    die();
}

function nel_derp(int $error_id, string $error_message, array $error_data = array())
{
    static $already_derping;

    if(!$already_derping)
    {
        $already_derping = true;
    }
    else
    {
        return;
    }

    if(!defined('NEL_SETUP_GOOD'))
    {
        nel_early_derp($error_id, $error_message, $error_data);
    }

    $session = new Session();
    $session->ignore(true);
    $redirect = new \Nelliel\Redirect();
    $redirect->doRedirect(false);
    $backtrace = debug_backtrace();
    $diagnostic = array();
    $diagnostic['error_id'] = (!empty($error_id)) ? $error_id : 0;
    $diagnostic['error_message'] = (!empty($error_message)) ? $error_message : "I just don't know what went wrong!";

    if (!empty($error_data))
    {
        $diagnostic['bad_filename'] = (isset($error_data['bad_filename'])) ? $error_data['bad_filename'] : null;
        $remove_files = isset($error_data['remove_files']) && $error_data['remove_files'] === true;

        if ($remove_files && isset($error_data['files']))
        {
            foreach ($error_data['files'] as $file)
            {
                unlink($file['location']);
            }
        }
    }

    if(isset($error_data['board_id']) && $error_data['board_id'] !== Domain::SITE)
    {
        $domain = new \Nelliel\Domains\DomainBoard($error_data['board_id'], nel_database('core'));
    }
    else
    {
        $domain = new \Nelliel\Domains\DomainSite(nel_database('core'));
    }

    $output_derp = new \Nelliel\Output\OutputDerp($domain, false);
    echo $output_derp->render(['diagnostic' => $diagnostic], false);

    nel_clean_exit(false);
}
