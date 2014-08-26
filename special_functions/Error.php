<?php
/*
 * Created on 30.11.2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 class Error
{
    // CATCHABLE ERRORS
    public static function captureNormal( $number, $message, $file, $line )
    {
       
        ErrorLog::LogError($file, $line, "$number : $message");
    }
   
    // EXTENSIONS
    public static function captureException($exception )
    {
      ErrorLog::LogError($exception->getFile(),$exception->getLine(),  $exception->getCode()." : ".$exception->getMessage);
    }
   
    // UNCATCHABLE ERRORS
    public static function captureShutdown( )
    {
        $error = error_get_last( );
        if( $error ) {
            ## IF YOU WANT TO CLEAR ALL BUFFER, UNCOMMENT NEXT LINE:
            # ob_end_clean( );
           
            // Display content $error variable
            echo '<pre>';
            print_r( $error );
            echo '</pre>';
        } else { return true; }
    }
}

ini_set( 'display_errors', 1 );
error_reporting( -1 );
set_error_handler( array( 'Error', 'captureNormal' ) );
set_exception_handler( array( 'Error', 'captureException' ) );
register_shutdown_function( array( 'Error', 'captureShutdown' ) );

// PHP set_error_handler TEST
//IMAGINE_CONSTANT;

// PHP set_exception_handler TEST
//throw new Exception( 'Imagine Exception' );
 
?>
