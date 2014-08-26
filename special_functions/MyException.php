<?php
/*
 * Created on 30.11.2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 /*
 function exception_error_handler($errno, $errstr, $errfile, $errline ) {
 	echo $errstr;
 	//ErrorLog::LogError($errfile, $errline,"$errno : $errstr" );
    //throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
 }
 
set_error_handler("exception_error_handler");
//set_exception_handler("exception_error_handler");
ob_start('exception_error_handler');
 */
 class MyException extends Exception
{
	public $MyMessage;
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null) {
        // some code
    	$this->MyMessage=$message;
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction() {
        echo "A custom function for this type of exception\n";
    }
}
?>
