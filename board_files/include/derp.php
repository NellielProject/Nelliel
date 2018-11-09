<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/derp.php';

function nel_derp($error_id, $error_message, $error_data = array())
{
    $backtrace = debug_backtrace();
    $diagnostic = array();
    $board_id = (isset($error_data['board_id'])) ? $error_data['board_id'] : null;
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

    nel_render_derp($diagnostic, $board_id);
    nel_clean_exit();
}
