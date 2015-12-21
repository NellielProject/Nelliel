<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Error Handling
//
function nel_derp($error_id, $error_data)
{
    static $diagnostic;
    
    if ($error_id === 'retrieve')
    {
        return $diagnostic[$error_data];
    }
    
    if ($error_id === 'update')
    {
        $diagnostic[$error_data[0]] = $error_data[1];
        return;
    }
    
    $diagnostic['error-id'] = $error_id;
    $diagnostic['error-message'] = nel_stext('ERROR_' . $error_id);
    $diagnostic['origin'] = $error_data['origin'];
    
    if (!is_null($error_data['files']))
    {
        $diagnostic['bad-filename'] = $error_data['bad-filename'];
        $diagnostic['files'] = $error_data['files'];
        
        foreach ($diagnostic['files'] as $file)
        {
            unlink($file['dest']);
        }
    }
    
    $dat = nel_render_header(array(), 'DERP', array());
    $dat .= nel_parse_template('derp.tpl', FALSE);
    $dat .= nel_render_footer(FALSE, FALSE, FALSE, FALSE);
    echo $dat;
    die();
}

function nel_get_derp($which_data)
{
    return nel_derp('retrieve', $which_data);
}

function nel_update_derp($which_data, $update)
{
    nel_derp('update', array($which_data, $update));
}

?>