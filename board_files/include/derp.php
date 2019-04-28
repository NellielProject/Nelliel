<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_derp(int $error_id, string $error_message, array $error_data = array())
{
    $backtrace = debug_backtrace();
    $diagnostic = array();



    $diagnostic['error_id'] = (!empty($error_id)) ? $error_id : 0;
    $diagnostic['error_message'] = (!empty($error_message)) ? $error_message : "I just don't know what went wrong!";
    $diagnostic = nel_plugins()->processHook('nel-derp-happened', [$error_id, $error_message, $error_data], $diagnostic);

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

    if (!defined('SETUP_GOOD'))
    {
        echo _gettext('oh god how did this get in here');
        echo '<br>';
        echo _gettext('Error ID: ') . $diagnostic['error_id'];
        echo '<br>';
        echo _gettext('Error Message: ') . $diagnostic['error_message'];
        die();
    }

    if(isset($error_data['board_id']) && $error_data['board_id'] !== '')
    {
        $domain = new \Nelliel\DomainBoard($error_data['board_id'], nel_database());
    }
    else
    {
        $domain = new \Nelliel\DomainSite(nel_database());
    }

    $output_derp = new \Nelliel\Output\OutputDerp($domain);
    echo $output_derp->render(['diagnostic' => $diagnostic], false);

    nel_clean_exit();
}
