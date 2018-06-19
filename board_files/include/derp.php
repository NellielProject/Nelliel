<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_derp($error_id, $error_message, $board_id = null, $error_data = array())
{
    $backtrace = debug_backtrace();
    $diagnostic['error-id'] = (!empty($error_id)) ? $error_id : 0;
    $diagnostic['error-message'] = $error_message;
    $diagnostic['bad-filename'] = (isset($error_data['bad-filename'])) ? $error_data['bad-filename'] : null;

    if (isset($error_data['remove_files']) && $error_data['remove_files'] === true)
    {
        if (isset($error_data['files']))
        {
            foreach ($error_data['files'] as $file)
            {
                unlink($file['location']);
            }
        }
    }

    require_once INCLUDE_PATH . 'output/derp.php';

    if (is_null($board_id))
    {
        nel_render_derp($diagnostic);
    }
    else
    {
        nel_render_board_derp($board_id, $diagnostic);
    }

    nel_clean_exit();
}
