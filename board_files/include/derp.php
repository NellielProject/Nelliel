<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_derp(int $error_id, string $error_message, array $error_data = array())
{
    $backtrace = debug_backtrace();
    $diagnostic = array();

    if(isset($error_data['board_id']) && $error_data['board_id'] !== '')
    {
        $domain = new \Nelliel\DomainBoard($error_data['board_id'], new \Nelliel\CacheHandler(), nel_database(), new \Nelliel\Language\Translator());
    }
    else
    {
        $domain = new \Nelliel\DomainSite(new \Nelliel\CacheHandler(), nel_database(), new \Nelliel\Language\Translator());
    }

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

    $output_derp = new \Nelliel\Output\OutputDerp($domain);

    if (!defined('SETUP_GOOD'))
    {
        $output_derp->renderSimple($diagnostic);
    }
    else
    {
        $output_derp->render(['diagnostic' => $diagnostic]);
    }

    nel_clean_exit();
}
