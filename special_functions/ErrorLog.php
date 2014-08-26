<?php
/*
 * Created on 21.11.2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 class ErrorLog{
static function LogError($file, $line,$message){
	$message=utf8_decode_deep($message);
	$dbHandler=new dbHandler();
	$sql="INSERT INTO errorlog (`file`, `line`, `message`) VALUES ('$file', $line, '$message')";
	mysql_query($sql);
	
	
	
} 	
 	
 	
 	
 }
?>
