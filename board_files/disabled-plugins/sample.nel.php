<?php
$id = $plugins->register_plugin('sample', '', '1.0');
$plugins->register_hook_function('before-info-processing', 'samplefunc', 10, $id);
$plugins->register_hook_function('before-info-processing', 'samplefunc2', 8, $id);
$plugins->register_hook_function('after-info-processing', 'samplefunc0', 10, $id);
//$plugins->register_hook_function('after-info-process', 'samplefunc2', 7, $id);
$plugins->register_hook_function('after-info-processing', 'samplefunc3', 7, $id);
$plugins->register_hook_function('after-info-processing', 'samplefuncX', 6, $id);
$plugins->register_hook_function('after-info-processing', 'samplefuncV', 6, $id);
$plugins->register_hook_function('after-info-processing', 'samplefuncb', 7, $id);
$plugins->unregister_hook_function('after-info-processing', 'samplefuncb', 7, $id);

function samplefunc($input)
{
    $input['name'] = 'HERPDERP';
    return $input;
}

function samplefunc2($input)
{
    $input['email'] = 'MAILZ';
    return $input;
}