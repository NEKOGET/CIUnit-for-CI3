<?php

/**
 * @group Helper
 */

class HelperTest extends CIUnit_TestCase
{
    public function setUp()
    {
        $this->CI->load->helper('url');
    }
    
    public function testSampleFunction()
    {
        $this->assertEquals('Hi!', "Hi!");
    }
}
