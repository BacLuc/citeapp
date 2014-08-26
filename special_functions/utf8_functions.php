<?php
/*
 * Created on 20.03.2013
 *
 * Author:  Lucius Bachmann
 * 
 */
 
 function utf8_encode_deep($array){
	if(!is_array($array)){
		return utf8_encode($array);
	}
	foreach($array as $key=> &$value)
	{
		$value=utf8_encode_deep($value);
		
		
	}
	
	return $array;
	
}

function utf8_decode_deep($array){
	if(!is_array($array)){
		$array=preg_replace("/'/", "&#39;", $array);

		$array=preg_replace("/’/", "&#39;", $array);
		
		$array = str_replace('´', "&#180;", $array);
		
		$array = str_replace('`', "&#96;", $array);
		return utf8_decode($array);
	}
	foreach($array as $key=> &$value)
	{
		$value=utf8_decode_deep($value);
		
		
	}
	
	return $array;
	
}

function my_print_r(&$object, $recstep=0){
	if(!is_array($object) && !is_object($object)){
			echo "<p style='padding-left: ".$recstep."em;'>$object</p>";
		}
	else{
		if(is_object($object)){
				if(isset($object->isPrinted)){
					if($object->isPrinted){
							echo "<p style='padding-left: ".$recstep."em;'>pointer to oid ".$object->getOid()."</p>";
						}
					else{
						$object->isPrinted=true;
						foreach($object as $key => &$value){
							echo "<p style='padding-left: ".$recstep."em;'>$key =>";
							my_print_r($value, $recstep+1);
							echo  "</p>";
						}
						
					}
				}
			}
		else{
			foreach($object as $key => &$value){
					echo "<p style='padding-left: ".$recstep."em;'>$key =>";
					my_print_r($value, $recstep+1);
					echo  "</p>";
				}
			
			}
		}
	
	}

?>
