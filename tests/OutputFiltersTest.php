<?php
use \PHPUnit\Framework\TestCase;

require_once 'nelliel_version.php';
require_once __DIR__ . '/../nelliel_core/include/classes/OutputFilter.php';

class OutputFiltersTest extends TestCase
{
    public function testCleanAndEncode()
    {
        $filter = new \Nelliel\OutputFilter();
        $whitespace_string = " \n\r\t   ";
        $untrimmed_string = "  string ";
        $filter->cleanAndEncode($whitespace_string);
        $this->assertTrue($whitespace_string === '');
        $filter->cleanAndEncode($untrimmed_string);
        $this->assertTrue($untrimmed_string === 'string');
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
        $this->assertCount(3, $text_array);
    }
}