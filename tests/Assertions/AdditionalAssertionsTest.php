<?php

namespace Alpa\PhpunitHelpers\Tests\Assertions;

use PHPUnit\Framework\TestCase;
use Alpa\PhpunitHelpers\Assertions\AdditionalAssertionsTrait as Assert;

class AdditionalAssertionsTest extends TestCase
{
    public function testIsError()
    {
        $this->assertTrue(Assert::isError(fn() => trigger_error('ops!'), 'ops', E_USER_NOTICE), 'test equal by errors message ');
        $this->assertTrue(Assert::isError(fn() => trigger_error('ops!',E_USER_ERROR), 'ops', E_USER_ERROR), 'test equal by errors message ');

        $obj = new class {
        };
        $this->assertTrue(Assert::isError(fn() => $obj->no_prop, 'Undefined property:', E_NOTICE | E_WARNING), 'test equality by error message, for an arbitrary error');
        $this->assertTrue(Assert::isError(fn() => $obj->no_prop, function (...$args) {
            return true;
        }, E_NOTICE | E_WARNING), 'test - error analysis');
        $this->assertFalse(Assert::isError(fn() => 'hello', 'ops', E_USER_NOTICE), 'no errors');

        $check = false;
        set_error_handler(function (...$args) use (&$check) {
            $str = 'Undefined property:';
            if (substr($args[1], 0, strlen($str)) === $str) {
                $check = true;
                return true;
            }
            return false;
        },E_NOTICE | E_WARNING);
        $this->assertFalse(Assert::isError(fn() => $obj->no_prop, 'Ops', E_NOTICE | E_WARNING));
        restore_error_handler();
        $this->assertTrue($check, 'Generating an error that was not observed');
        unset($check);
    }

    public function testIsErrorAsException()
    {
       $obj= new class {
           static $prop=1;
       };
        $this->assertTrue(Assert::isError(function() use($obj) {unset($obj::$prop);}, 'Attempt to unset static property', E_ERROR));
        $this->assertTrue(Assert::isError(function() use($obj) {unset($obj::$prop);}, 'Attempt to unset static property', \Error::class));
        $tester=$this;
        $buf_this=null;
        $buf_code=null;
        $buf_message=null;
        $buf_file=null;
        $buf_line=null;
        $this->assertTrue(Assert::isError(
            function() use($obj) { unset($obj::$prop);}, 
            function($code,$message,$file,$line) use (
                $tester,
                &$buf_this,
                &$buf_code,
                &$buf_message,
                &$buf_file,
                &$buf_line
            ){
            $buf_this=isset($this)?$this:null;
            $buf_code=$code;
            $buf_message=$message;
            $buf_file=$file;
            $buf_line=$line;
            return true;
        }, \Error::class));
        $this->assertTrue($buf_this!==null && $buf_this instanceof \Error);
        $this->assertTrue($buf_code===E_ERROR);
        $this->assertTrue($buf_message===$buf_this->getMessage());
        $this->assertTrue($buf_file===$buf_this->getFile());
        $this->assertTrue($buf_line===$buf_this->getLine());
    }
}