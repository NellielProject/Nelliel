<?php
use \Nelliel\Content\ContentID;
use \PHPUnit\Framework\TestCase;

require_once 'nelliel_version.php';
require_once __DIR__ . '/../core/include/classes/ContentID.php';

class ContentIDTest extends TestCase
{
    public function testIsContentID()
    {
        $this->assertTrue(ContentID::isContentID('cid_1_2_3'));
        $this->assertFalse(ContentID::isContentID('cid_1_2_R'));
    }

    public function testCreateIDString()
    {
        $this->assertTrue(ContentID::createIDString(1,2,3) === 'cid_1_2_3');
    }

    public function testParseIDString()
    {
        $parsed = ContentID::parseIDString('cid_1_2_3');
        $this->assertCount(3, $parsed);
        $this->assertEquals($parsed['thread'], 1);
        $this->assertEquals($parsed['post'], 2);
        $this->assertEquals($parsed['order'], 3);
    }

    public function testGetIDString()
    {
        $id_string = 'cid_1_2_3';
        $content_id = new ContentID($id_string);
        $this->assertEquals($content_id->getIDString(), $id_string);
    }

    public function testIsThread()
    {
        $content_id = new ContentID('cid_1_0_0');
        $this->assertTrue($content_id->isThread());
    }

    public function testIsPost()
    {
        $content_id = new ContentID('cid_1_2_0');
        $this->assertTrue($content_id->isPost());
    }

    public function testIsContent()
    {
        $content_id = new ContentID('cid_1_2_3');
        $this->assertTrue($content_id->isContent());
    }
}