<?php
use Nelliel\Render\Filter;
use \PHPUnit\Framework\TestCase;

require_once 'nelliel_version.php';
require_once __DIR__ . '/../nelliel_core/include/classes/OutputFilter.php';

class OutputFiltersTest extends TestCase
{
    public function testCleanAndEncode()
    {
        $filter = new Filter();
        $whitespace_string = " \n\r\t   ";
        $untrimmed_string = "  string ";
        $filter->cleanAndEncode($whitespace_string);
        $this->assertTrue($whitespace_string === '');
        $filter->cleanAndEncode($untrimmed_string);
        $this->assertTrue($untrimmed_string === 'string');
    }

    public function testNewlinesToArray()
    {
        $filter = new Filter();
        $newlines_string = "line1\nline2\nline3";
        $text_array = $filter->newlinesToArray($newlines_string);
        $this->assertCount(3, $text_array);
    }
}