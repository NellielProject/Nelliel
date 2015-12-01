<?php

$hooks['sample'][] = 'samplefunc';
$hooks['sample'][] = 'samplefunc2';

function samplefunc($input)
{
    echo "AWESOME";
}

function samplefunc2($input)
{
    echo $input;
    die();
}