<?php
/*
 * Created on 11.09.2013
 *
 * Author:  Lucius Bachmann
 * 
 */
 
 function time_is_before($time1, $time2){
 
 	$Zeitarray1=explode(":", $time1);
 	
 	$Zeitarray1[0]=intval($Zeitarray1[0]);
 	$Zeitarray1[1]=intval($Zeitarray1[1]);
 	$time1=$Zeitarray1[0]*60 + $Zeitarray1[1];
 	
 	
 	$Zeitarray2=explode(":", $time2);
 	$Zeitarray2[0]=intval($Zeitarray2[0]);
 	$Zeitarray2[1]=intval($Zeitarray2[1]);
 	
 	$time2=$Zeitarray2[0]*60 + $Zeitarray2[1];
 	
 
 	return $time1<$time2?true: false;;
 	
 }
 
 function time_diff($time1, $time2){
 	$Zeitarray1=explode(":", $time1);
 	$Zeitarray1[0]=intval($Zeitarray1[0]);
 	$Zeitarray1[1]=intval($Zeitarray1[1]);
 	$time1=$Zeitarray1[0]*60 + $Zeitarray1[1];
 	
 	
 	$Zeitarray2=explode(":", $time2);
 	$Zeitarray2[0]=intval($Zeitarray2[0]);
 	$Zeitarray2[1]=intval($Zeitarray2[1]);
 	
 	$time2=$Zeitarray2[0]*60 + $Zeitarray2[1];
 	
 
 	return $time1-$time2;
 }
 
  function date_is_before($date1, $date2){
 	$Zeitarray1=explode(".", $date1);
 	$Zeitarray1[0]=intval($Zeitarray1[0]);
 	$Zeitarray1[1]=intval($Zeitarray1[1]);
 	$Zeitarray1[2]=intval($Zeitarray1[2]);
 	
 	
 	$Zeitarray2=explode(".", $date2);
 	$Zeitarray2[0]=intval($Zeitarray2[0]);
 	$Zeitarray2[1]=intval($Zeitarray2[1]);
 	$Zeitarray2[2]=intval($Zeitarray2[2]);
 	
 	if($Zeitarray2[2]>$Zeitarray1[2]){
 		return true;
 	}else{
 		if($Zeitarray2[1]>$Zeitarray1[1]){
 			return true;
 		}else{
 			if($Zeitarray2[0]>$Zeitarray1[0]){
 				return true;
 			}else{
 				return false;
 			}
 		}
 	}
 	
 }
 
 function Str_to_Min($String){
 		$Zeitarray1=explode(":", $String);
 	$Zeitarray1[0]=intval($Zeitarray1[0]);
 	$Zeitarray1[1]=intval($Zeitarray1[1]);
 	$minutes=$Zeitarray1[0]*60 + $Zeitarray1[1];
 	return $minutes;
 }
 
 	function cmp($a, $b) {
		if(!is_array($a)){
			return -1;
			
		}	
		if(!is_array($b)){
			return 1;
			
		}
	
	    if (date_is_before($b['app_date'], $a['app_date'])) {
	    	return 1;
	        
	    }
	    if($a['app_date']==$b['app_date']){
	    	if(time_is_before($b['app_time_start'],$a['app_time_start'] )){
	    		return 1;
	    	}else{
	    		return -1;
	    	}
	    }
	    return -1;
	}
?>
