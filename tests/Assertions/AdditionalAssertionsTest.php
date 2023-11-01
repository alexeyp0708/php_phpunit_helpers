<?php

namespace Alpa\PhpunitHelpers\Tests\Assertions;

use PHPUnit\Framework\TestCase;
use Alpa\PhpunitHelpers\Assertions\AdditionalAssertionsTrait as Assert;

class AdditionalAssertionsTest extends TestCase
{
    public function testIsError()
    {
        $this->assertTrue(Assert::isError(fn()=>trigger_error('ops!'),'ops',E_USER_NOTICE),'test equal by errors message ');
        $obj=new class {};
        $this->assertTrue(Assert::isError(fn()=>$obj->no_prop,'Undefined property:',E_NOTICE|E_WARNING), 'test equality by error message, for an arbitrary error');
        $this->assertTrue(Assert::isError(fn()=>$obj->no_prop,function(...$args){return true;},E_NOTICE|E_WARNING),'test - error analysis');
        $this->assertFalse(Assert::isError(fn()=>'hello','ops',E_USER_NOTICE),'no errors');
        
        $check=false;
        set_error_handler(function(...$args)use(&$check){
            $str='Undefined property:';
            if(substr($args[1],0,strlen($str))===$str){
                $check=true;
            }
            return false;
        });
        $this->assertFalse(Assert::isError(fn()=>$obj->no_prop,'Ops',E_NOTICE|E_WARNING));
        restore_error_handler();
        $this->assertTrue($check, 'Generating an error that was not observed');
        unset($check);
    }
}