<?php

$hooks['before-info-process'][] = 'samplefunc';
$hooks['after-info-process'][] = 'samplefunc2';

function samplefunc($input)
{
    var_dump($input);
    echo "AWESOME  " . $input['email'];
}

function samplefunc2($input)
{
    var_dump($input);
    echo $input['name'];
    die();
}