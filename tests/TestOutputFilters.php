<?php
require_once 'nelliel_version.php';
require_once '../board_files/include/classes/OutputFilter.php';

class TestOutputFilters extends PHPUnit_Framework_TestCase
{

    function __construct()
    {
    }

    public function testClearWhitespace()
    {
        $filter = new \Nelliel\OutputFilter();
        $whitespace_string = " \n\r\t   ";
        $filter->clearWhitespace($whitespace_string);
        $this->assertTrue($whitespace_string === '');
    }

    public function testNewlinesToArray()
    {
        $filter = new \Nelliel\OutputFilter();
        $newlines_string = "line1\nline2\nline3";
        $text_array = $filter->newlinesToArray($newlines_string);
        $this->assertTrue(count($text_array) === 3);
    }
}