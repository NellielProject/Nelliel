<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Redirect;
use Nelliel\Account\Session;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Domains\DomainSite;
use Nelliel\Output\OutputDerp;

function nel_early_derp(int $error_id, string $error_message, array $context = array())
{
    echo _gettext('oh god how did this get in here');
    echo '<br>';
    echo _gettext('Error') . ' ' . $error_id;
    echo '<br>';
    echo $error_message;
    die();
}

function nel_derp(int $error_id, string $error_message, int $response_code = 0, array $context = array())
{
    static $already_derping;

    if (!$already_derping) {
        $already_derping = true;
    } else {
        return;
    }

    if (!defined('NEL_SETUP_GOOD')) {
        nel_early_derp($error_id, $error_message, $context);
    }

    $session = new Session();
    $session->ignore(true);
    $redirect = new Redirect();
    $redirect->doRedirect(false);
    $backtrace = debug_backtrace();
    $diagnostic = array();
    $diagnostic['error_id'] = (!empty($error_id)) ? $error_id : 0;
    $diagnostic['error_message'] = (!empty($error_message)) ? $error_message : __("I just don't know what went wrong!");

    if (!empty($context)) {
        $diagnostic['bad_filename'] = $context['bad_filename'] ?? null;
        $remove_files = boolval($context['remove_files'] ?? false);

        if ($remove_files && isset($context['files'])) {
            foreach ($context['files'] as $file) {
                unlink($file['location']);
            }
        }
    }

    if (isset($context['board_id']) && $context['board_id'] !== Domain::SITE) {
        $domain = new DomainBoard($context['board_id'], nel_database('core'));
    } else {
        $domain = new DomainSite(nel_database('core'));
    }

    $output_derp = new OutputDerp($domain, false);
    $parameters = ['context' => $context, 'diagnostic' => $diagnostic];
    $response_code = $response_code !== 0 ? $response_code : http_response_code();
    http_response_code($response_code);
    echo $output_derp->render($parameters, false);

    nel_clean_exit();
}
