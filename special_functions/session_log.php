<?php
/*
 * Created on 25.02.2013
 *
 * Copyright by Lucius Bachmann
 * 
 */
 
 class SessionLog{
 	static function logSession($user_id, $client_ip, $client_browser, $client_op_system, $dbHandler ){
 		$status=$user_id=="null"?0:1;
 		$sql="INSERT INTO session_log(user_id, client_ip, client_browser, client_op_system, status, insert_time, update_time, last_action_time)VALUES" .
 				"($user_id, '$client_ip', '$client_browser', '$client_op_system', $status, ".time().", ".time().", ".time().")";
 		if(!($res=mysql_query($sql))){
 			ErrorLog::LogError("session_log.php", 1, "function LogSession: SQL Error: $sql");
			return false;
 		}else{
 			return mysql_insert_id($dbHandler->dblk);
 		}
 		
 		
 	}
 	
 	static function existsSession($sel_id){
 		$sql="SELECT * FROM session_log WHERE sel_id=".$sel_id." AND status < 2";
 		$res=mysql_query($sql);
 		if(!$res || $res==false){
 			ErrorLog::LogError("session_log.php", 1, "function existsSession: SQL Error: $sql");
			return false;
 		}else{
 			if(mysql_num_rows($res)==1){
 				return true;
 			}
 		}
 	}
 	
 	static function newAction($sel_id){
 		$sql="UPDATE session_log SET last_action_time=".time()." WHERE sel_id=$sel_id";
 		$res=mysql_query($sql);
 		if(!$res || $res==false){
 			ErrorLog::LogError("session_log.php", 1, "function newAction: SQL Error: $sql");
			return false;
 		}else{
 			
 				return true;
 			
 		}
 	}
 	
 	
 	static function logLogin($user_id, $sel_id){
 		$sql="UPDATE session_log SET user_id=$user_id, status=1 WHERE sel_id=$sel_id";
 		if(!($res=mysql_query($sql))){
 			ErrorLog::LogError("session_log.php", 1, "function newAction: SQL Error: $sql");
			return false;
 		}else{
 			
 			
 				return true;
 			
 		}
 	}
 	
 	static function logLogout( $sel_id){
 		$sql="UPDATE session_log SET  status=2 WHERE sel_id=$sel_id";
 		if(!($res=mysql_query($sql))){
 			ErrorLog::LogError("session_log.php", 1, "function newAction: SQL Error: $sql");
			return false;
 		}else{
 			
 				return true;
 			
 		}
 	}
 	
 	static function cleanUpSessionLog(){}
 	
 }
 

?>
