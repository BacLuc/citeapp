<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
class RDFMediator{
	public $resultList;
	public $edges;
	public $dbConn;
	public $oidlist;
	public $shownlist;
	public $edgehashlist;
	
	
	
function __construct(&$dbConn){
		session_start();
		$this->dbConn=$dbConn;
		//my_print_r($_SESSION['oid_list']);
		if(isset($_SESSION['oid_list'])){
				$this->oidlist=$_SESSION['oid_list'];
			}
			
		
		if(isset($_SESSION['edges'])){
				$this->edges=$_SESSION['edges'];
			}
			
		if(isset($_SESSION['shownlist'])){
				$this->shownlist=$_SESSION['shownlist'];
			}
		if(isset($_SESSION['edgehashlist'])){
				$this->shownlist=$_SESSION['edgehashlist'];
			}
			
		
		
	}
	
	
function __destruct(){
		$_SESSION['oid_list'] = $this->oidlist;
		$_SESSION['edges']= $this->edges;
		$_SESSION['shownlist']= $this->shownlist;
		$_SESSION['edgehashlist']= $this->edgehashlist;
	}
	
function resetSession(){
		$_SESSION['oid_list'] = "";
		$_SESSION['edges']= "";
		$_SESSION['shownlist']= "";
		$_SESSION['edgehashlist']= "";
	
	}
	
	
function searchObjects($searchString, $type=NULL){
		
		$starttime=microtime(true);
		$searchResult=$this->dbConn->searchObjects($searchString, $type);
		$this->resultList=$searchResult['objects'];
		$this->edges = $searchResult['edges'];
		$timetosearch=microtime(true) - $starttime;
		//echo "<br>objektsuche benoetigte: $timetosearch <br>";
		$starttime=microtime(true);
		
			
		function cmp($a, $b) {
			
		
			if ($a->levenshtein == $b->levenshtein) {
				return 0;
			}
		return ($a->levenshtein > $b->levenshtein) ? 1 : -1;
		}
	
	uasort($this->resultList, 'cmp');
	
	$timetosearch=microtime(true) - $starttime;
		//echo "<br>rest benötigte: $timetosearch <br>";
	}
	
function getObject($oid, $recLevel = 0){
		
		$startObject = new RDFObject(NULL, $this->dbConn, $oid, $recLevel);
		
		
		$this->resultList[$startObject->getOid()] = $startObject;
		$this->fillObjectArrayRecursive($startObject);
		
		
	
	}

function fillObjectArrayRecursive(&$object){
	
	//echo "printing object with oid".$object->getOid()."<br>";
	//my_print_r($object->relations);
	if(is_array($object->relations)){ //check array shit
		if(count($object->relations)>0){
				//echo "checking relations of object ".$object->getOid()."<br>";
				foreach($object->relations as $oid => $prop){ //go through every relation
						//echo "checking relation to ".$oid."<br>";
						
						if(!isset($this->resultList[$oid])){ //if target is not already listed
								//echo "".$oid." is not in the list <br>";
								$first = true;
								foreach($prop as $label => $relObject){ //make an edge for each relation
										if($first && $relObject[0] != null){
											//echo "first time ".$oid." is checked, so put it in resultlist<br>";
											$this->resultList[$oid]=$relObject[0];
											$first=false;
											}
										if($relObject[1] == -1){
										
											$relArray=array('from' => $oid ,'to' =>$object->getOid(), 'type' => $label );
										}else{	
											$relArray=array('from' => $object->getOid(), 'to' =>$oid, 'type' => $label );
										}
										//var_dump($relArray);
										//echo "<br>";
										$this->edges[]=$relArray;
										//my_print_r($this->edges);
										if($relObject[0] != null){
												$this->fillObjectArrayRecursive($relObject[0]);
											}
										
									}
								
							}
					
					}
				}
			}
	}
	
function addObject($type,$properties){
	$type=utf8_encode($type);
	$type = str_replace("\n", "", $type);
	$newObject = new RDFObject($type, $this->dbConn);
	
	if(is_array($properties)){
		if(count($properties) > 0){
			foreach($properties as $key => $value){
					$newObject->addProperty($key, $value);
				
				}
		}
	}
	$newObject->write_to_db();
	
	return $newObject->getOid();
	
	}


function addProperty($oid, $type, $property){

		$Object = new RDFObject( NULL, $this->dbConn, $oid, 0);


		$Object->addProperty($type, $property);


		$Object->write_to_db();
		
	
	}

function removeProperty($oid, $type, $property){
	$Object = new RDFObject( NULL, $this->dbConn, $oid, 0);
	$Object->removeProperty($type, $property);
	$Object->write_to_db();
	
	}

function addRelation($oid, $type, $other_oid){
	$Object = new RDFObject( NULL, $this->dbConn, $oid, 0);
	$Object->addRelation($other_oid, $type, new RDFObject(NULL, $this->dbConn, $other_oid,0));
	$Object->write_to_db();
	
	}

function removeRelation($oid, $type, $other_oid){
	$Object = new RDFObject( NULL, $this->dbConn, $oid, 0);
	$Object->removeRelation($other_oid, $type);
	$Object->write_to_db();
	}
	
function deleteObject($oid){
		$Object = new RDFObject( NULL, $this->dbConn, $oid, 0);
		$Object->delete();
	
	
	}

function printToJsonForSearch(){
		$resultArray=array();
		foreach($this->resultList as $oid => $object){
				$properties = array();
				if(is_array($object->properties)){
						if(count($object->properties)>0){
				
							foreach($object->properties as $type => $value){
									$explode = explode('#', $type);
									$label=$type;
									if(count($explode)>1 && count($explode)<=2){
												$label=$explode[1];
											}
									$property = array('type' => $type, 'label' => $label, 'value' => $value );
									$properties[]=$property;
								
								}
						}
					}
					
					$explode= explode('#', $object->getType());
					$label = $object->getType();
					if(count($explode)>1 && count($explode)<=2){
						$label=$explode[1];
					}
					$pushObject = array('type' => $object->getType(), 'oid' => $oid, 'properties' => $properties, 'levenprop' => $object->properties[$object->levenprop]);
					$this->shownlist[$oid]=1;
				
				$resultArray[$oid]=$pushObject;	
			
			}
			
		utf8_encode_deep($resultArray);
		$printArray = array('check'=> 'suc', 'result' => $resultArray);
		echo json_encode($printArray);
	
	}


function printToJsonForGraph(){
		$resultArray=array();
		//my_print_r($this->resultList);
		//my_print_r($this->edges);
		foreach($this->resultList as $oid => $object){
				
				if(!isset($this->oidlist[$oid]) || true){
					
					$properties = array();
					if(is_array($object->properties)){
							if(count($object->properties)>0){
								foreach($object->properties as $type => $value){
										if(!(strpos(strtolower($type), "oid")===false)){}
										else{
											$explode = explode('#', $type);
											$label = $type;
											if(count($explode)>1 && count($explode)<=2){
												$label=$explode[1];
											}
												$property = array('type' => $type, 'label' => $label, 'value' => $value );
											
											$properties[]=$property;
										}
									}
								}
							}
					$explode= explode('#', $object->getType());
					$label = $object->getType();
					if(count($explode)>1 && count($explode)<=2){
						$label=$explode[1];
					}
					//my_print_r($explode);
					
					
					
					$pushObject = array('type' => $object->getType(), 'label' => $label, 'oid' => $oid, 'properties' => $properties );
					
					
						$resultArray[$oid]=$pushObject;
						$this->shownlist[$oid]=1;
					
					
				}
						
			
			}
			
		$printEdges= array();
		if(is_array($this->edges)){
			if(count($this->edges)>0){
				foreach($this->edges as $edge){
						if(!isset($this->oidlist[$edge['from']]) || true){
							$explode = explode('#', $edge['type']);
							$label = $edge['type'];
							if(count($explode)>1 && count($explode)<=2){
								$label=$explode[1];
							}
							$edge['label']=$label;
							if(empty($this->edgehashlist[$edge['from'].$label.$edge['to']])){
								$printEdges[]=$edge;
								$this->edgehashlist[$edge['from'].$label.$edge['to']]=1;
								
							}
						}
					
					}
				}
			}
		$printArray = array('check'=> 'suc', 'nodes' => $resultArray, 'edges' => $printEdges);
			
		utf8_encode_deep($printArray);
		echo json_encode($printArray);
	
	
	
	
	}

}
?>
