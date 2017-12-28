<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_derp($error_id, $error_message, $error_data = array())
{
    $backtrace = debug_backtrace();
    $diagnostic['error-id'] = (!empty($error_id)) ? $error_id : 0;
    $diagnostic['error-message'] = $error_message;
    $diagnostic['bad-filename'] = (isset($error_data['bad-filename'])) ? $error_data['bad-filename'] : null;

    if(isset($error_data['files']) && !empty($error_data['files']))
    {
        foreach ($files as $file)
        {
            unlink($file['dest']);
        }
    }

    require_once INCLUDE_PATH . 'output/derp.php';
    nel_render_derp($diagnostic);
    nel_clean_exit(null, true);
}
