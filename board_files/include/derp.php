<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/derp.php';

function nel_derp($error_id, $error_message, $error_data = array())
{
    $backtrace = debug_backtrace();
    $board_id = (isset($error_data['board_id'])) ? $error_data['board_id'] : null;
    $diagnostic['error-id'] = (!empty($error_id)) ? $error_id : 0;
    $diagnostic['error-message'] = $error_message;

    if (!empty($error_data))
    {
        $diagnostic['bad-filename'] = (isset($error_data['bad-filename'])) ? $error_data['bad-filename'] : null;
        $remove_files = isset($error_data['remove_files']) && $error_data['remove_files'] === true;

        if ($remove_files && isset($error_data['files']))
        {
            foreach ($error_data['files'] as $file)
            {
                unlink($file['location']);
            }
        }
    }

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
