<?php

namespace Alpa\PhpunitHelpers\Assertions;


trait AdditionalAssertionsTrait
{
    /**
     * Will return TRUE if an observed error was thrown, FALSE if there are generated other errors or no errors  , and will throw an error if other errors are observed
     * @param\Closure $call Runs a closure with a code that needs to be checked for an error. If the error is an exception, then the variable $this will be defined in the closure. Checking for $this - isset($this)
     * @param string|\Closure $eql If a line, then checks whether this substring is present at the beginning of the error message.
     * If there is a closure, then the error checking code must be specified in the closure. If the test is successful, the closure should return true.
     * @param int|\Error $level
     */
    public static function isError($call, $eql, $level = E_ALL): bool
    {
        $err_level=is_int($level)?$level:E_ALL;
        $exc_level=is_string($level)?$level:\Error::class;
        if (is_string($eql)) {
            $substr = $eql;
            $eql = function (...$args) use ($substr) {
                if (substr($args[1], 0, strlen($substr)) === $substr) {
                    return true;
                }
                return false;
            };
        }
        $check = false;
        $bef_hand = null;
        $is_caught_error=false;
        $bef_hand = set_error_handler(function (...$args) use (&$bef_hand, &$check, $eql,&$is_caught_error) {
            $is_caught_error=true;
            if (true===$eql(...$args)) {
                // Expected error
                $check = true;
                return true;
            } else if ($bef_hand !== null) {
                //We pass other errors to the previous error handler
                return $bef_hand(...$args);
            }
            return false;
        }, $err_level);
        try{
            $call();
        }catch(\Error $e){
            if(!($e instanceof $exc_level)){
                throw $e;
            }
            $eql=$eql->bindTo($e);
            $code=$e->getCode();
            if(empty($code)){
                $code=E_ERROR;
            }
            if(!$is_caught_error && true===$eql($code,$e->getMessage(),$e->getFile(),$e->getLine())){
                $check=true;
            }
        } finally {
            restore_error_handler();
            return $check;
        }
    }
}