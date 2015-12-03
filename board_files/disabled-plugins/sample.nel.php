<?php
$id = $plugins->register_plugin('sample', '', '1.0');
$plugins->register_hook_function('before-info-process', 'samplefunc', 10, $id);
$plugins->register_hook_function('before-info-process', 'samplefunc2', 8, $id);
$plugins->register_hook_function('after-info-process', 'samplefunc0', 10, $id);
//$plugins->register_hook_function('after-info-process', 'samplefunc2', 7, $id);
$plugins->register_hook_function('after-info-process', 'samplefunc3', 7, $id);
$plugins->register_hook_function('after-info-process', 'samplefuncX', 6, $id);
$plugins->register_hook_function('after-info-process', 'samplefuncV', 6, $id);
$plugins->register_hook_function('after-info-process', 'samplefuncb', 7, $id);
$plugins->unregister_hook_function('after-info-process', 'samplefuncb', 7, $id);

function samplefunc($input)
{
    echo $input['email'];
    return $input;
}

function samplefunc2($input)
{
    $input['email'] = 'MAILZ';
    return $input;
}