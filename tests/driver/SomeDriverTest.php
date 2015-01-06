<?php

/**
 * @group Driver
 */

class SomeDriverTest extends CIUnit_TestCase
{
    public function setUp()
    {
        // Set up fixtures to be run before each test
        // Load the tested library so it will be available in all tests
        //$this->CI->load->library('example_lib', '', mylib);
    }
    
    public function testMethod()
    {
        $this->CI = & get_instance();
        $this->CI->load->driver('honey');
        $this->assertEquals('You are forgiven', $this->CI->honey->queenbee->pardon());
        $this->assertEquals('You are forgiven', $this->CI->honey->naughty_bee());
        $this->assertEquals('Here is a pot of honey', $this->CI->honey->workerbee->busybee());
    }
}