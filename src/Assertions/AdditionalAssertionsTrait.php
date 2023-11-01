<?php

namespace Alpa\PhpunitHelpers\Assertions;


trait AdditionalAssertionsTrait
{
    /**
     * Will return TRUE if an observed error was thrown, FALSE if there are generated other errors or no errors  , and will throw an error if other errors are observed

     * @param\Closure $call
     * @param string|\Closure $eql
     * @param int $level
     */
    public static function isError($call, $eql,  $level=E_ALL):bool
    {
        if(is_string($eql)){
            $substr=$eql;
            $eql=function(...$args) use ($substr){
                if(substr($args[1],0,strlen($substr))===$substr){
                    return true;
                }
                return false;
            };
        }
        $check=false;
        $bef_hand=null;
        $bef_hand=set_error_handler(function(...$args)use(&$bef_hand,&$check,$eql){
            if($eql(...$args)){
                // Expected error
                $check=true;
                return true;
            } else if($bef_hand!==null){
                //We pass other errors to the previous error handler
                return $bef_hand(...$args);
            }
            return false;
        },$level);
        $call();
        restore_error_handler();
        return $check;
    }
}