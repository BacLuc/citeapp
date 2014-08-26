<?php

class RDFDbConn{


function __construct(){
		$db = sparql_connect( __SPARQL_ENDPOINT__ );
		if( !$db ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
		
		
		sparql_ns( "foaf","http://xmlns.com/foaf/0.1/" );
		sparql_ns( "ca",__MYURL__ );
		sparql_ns("dc","http://purl.org/dc/elements/1.1/");
		
		
		
		
	}
	
function getOid(){
		do{
			$sparql="SELECT ?c FROM <".__MYURL__."> WHERE {<".__MYURL__.">  <".__MYURL__."/voc.html#locked>  ?c } ";
		
			$result = sparql_query( $sparql ); 
		
			$locked=sparql_num_rows($result)==0 ? 0 : 1;
		}while($locked==1);
		
		$sparql="INSERT INTO <".__MYURL__."> {<".__MYURL__.">  <".__MYURL__."/voc.html#locked>  '1' } ";
		$result = sparql_query( $sparql ); 
			
		
	
		$sparql="SELECT ?c FROM <".__MYURL__."> WHERE {<".__MYURL__.">  <".__MYURL__."/voc.html#currentOid>  ?c } ";
		$result = sparql_query( $sparql ); 
		if( !$result ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
		 
		$row = sparql_fetch_array( $result );
		$currentOid=$row['c'];
		
		$sparql="
		DELETE FROM GRAPH <".__MYURL__."> {<".__MYURL__.">  <".__MYURL__."/voc.html#currentOid>  '$currentOid' }

		INSERT INTO <".__MYURL__."> {<".__MYURL__.">  <".__MYURL__."/voc.html#currentOid>  '".++$currentOid."' }

		DELETE FROM GRAPH <".__MYURL__."> {<".__MYURL__.">  <".__MYURL__."/voc.html#locked>  '1' }";
			$result = sparql_query( $sparql ); 
		
		return $currentOid;

	}

function getObject($oid, $recLevel=0){
	//echo "bin in getObject<br>";
	$sparql="select ?a ?b ?c FROM <".__MYURL__."> WHERE{ {<".__MYURL__."/instances.php?oid=$oid> ?b ?c} UNION{ ?a ?b <".__MYURL__."/instances.php?oid=$oid>}}";
			
			$result = sparql_query( $sparql );
			$properties=null;
			$relations=null;
			$type=null;
			while( $row = sparql_fetch_array( $result ) )
				{	
					if(!isset($row['c'])){
							$extractedOid=$this->extractOid($row['a']);
							if($recLevel==0){
								$relations[$extractedOid]=array($row['b']=>NULL);
							}
							else{
								$relations[$extractedOid]=array($row['b']=>new RDFObject(NULL, $this, $extractedOid, $recLevel-1));
							}
						}
					else{
						if(!(strpos($row['c'], "oid")===false) && $row['b'] != "ca:hasOid"){
								$relations[$this->extractOid($row['c'])]=array($row['b']=>NULL);
							}
						elseif(!(strpos($row['b'], "instance")===false)){
								$type=$row['c'];
							}
						else{
							$properties[$row['b']]=$row['c'];
							
							}
						
						}
					
				}
				
				return array(
				'relations' => $relations,
				'properties' => $properties,
				'type' => $type
				
				);
	
	
	}
	
function extractOid($urlString){
		
		$firststring=explode("=", $urlString);
			if(!isset($firststring[1])){
				echo $urlString;
				exit;
				
				}
		$secondString=explode(">", $firststring[1]);
		
		return $secondString[0];
	
	}


function writeInstance($object){
		$currentOid=$this->getOid();
		$sparql = "INSERT INTO <".__MYURL__."> 
		{<".__MYURL__."/instances.php?oid=oid$currentOid>  <ca:hasOid>  'oid".$currentOid."' .
		<".__MYURL__."/instances.php?oid=oid$currentOid>  <rdf:instanceOf>  '".$object->getType()."' }";
		
		$result = sparql_query( $sparql ); 
		if( !$result ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
		
		return $currentOid;
	
	}
function addProp($oid, $type,$addPropertie){
		$sparql = "INSERT INTO <".__MYURL__."> 
		{<".__MYURL__."/instances.php?oid=oid$oid>  <$type>  '$addPropertie' }";
		$result = sparql_query( $sparql ); 
		if( !$result ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
	}
function addRel($from_oid, $type, $to_oid ){
		$sparql = "INSERT INTO <".__MYURL__."> 
		{<".__MYURL__."/instances.php?oid=oid$from_oid>  <$type>  <".__MYURL__."/instances.php?oid=oid$to_oid>  }";
		$result = sparql_query( $sparql ); 
		if( !$result ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
	}
	
function searchObjects($searchString, $type=NULL){
	$returnArray = array('objects' => [], 'edges' => []);
	if($type==NULL){
		/*
		
		$sparql="select ?a FROM <http://citeapp.ch> WHERE {?a ?b ?c.FILTER(?c LIKE '%".$searchString."%' && !(?c LIKE '%oid%' || ?b = <rdf:instanceOf>) )}";
		$result = sparql_query( $sparql );
			$properties=null;
			$relations=null;
			$type=null;
			while( $row = sparql_fetch_array( $result ) )
				{	
					$oidArray[]=$this->extractOid($row['a']);
					
				}
		*/
		
		$sparql="select ?a ?g ?f FROm <http://citeapp.ch> WHERE{
				   {?a ?g ?f.
					?a ?b ?c.FILTER(?c LIKE '%$searchString%' && !(?c LIKE '%oid%' || ?b = <rdf:instanceOf>) )}
				   
				   
				} ORDER BY ?a";
		$starttime=microtime(true);
		$result = sparql_query( $sparql );
		$timetosearch=microtime(true) - $starttime;
		echo "<br>query benoetigte: $timetosearch <br>";
		$objects=[];
		$edges=[];
		$currentObjectOid=null;
		$currentExtractedOid=null;
		$currentObj=null;
		while( $row = sparql_fetch_array( $result )){
				if($currentObjectOid==null){
						$currentObjectOid=$row['a'];
						$currentExtractedOid=$this->extractOid($currentObjectOid);
						$currentObj = new RDFObject(null, $this->dbConn);
						$currentObj->setOid($currentExtractedOid);
						$currentObj->levenshtein=100;
					}
				if($currentObjectOid != $row['a']){
						$objects[$currentExtractedOid]=$currentObj;
						$currentObjectOid = $row['a'];
						$currentExtractedOid=$this->extractOid($currentObjectOid);
						$currentObj = new RDFObject(null, $this->dbConn);
						$currentObj->setOid($currentExtractedOid);
						$currentObj->levenshtein=100;
					
					}
				
				if(!(strpos($row['f'], "oid")===false) && $row['g'] != "ca:hasOid"){
						$extractedRelOid=$this->extractOid($row['f']);
						$currentObj->addExistingRelation($extractedRelOid, $row['g'], new RDFObject(null, $this->dbConn));
						$relArray=array('from' => $currentExtractedOid, 'to' =>$extractedRelOid, 'type' => $row['g'] );
						$edges[]=$relArray;
					}
				elseif(!(strpos($row['g'], "instance")===false)){
						$currentObj->setType($row['f']);
					}
				elseif($row['g'] == "ca:hasOid"){
					
					}
				else{
						$lev=levenshtein($searchString, $row['f']);
						if($currentObj->levenshtein > $lev){
								$currentObj->levenshtein=$lev;
								$currentObj->levenprop=$row['g'];
							}
						$currentObj->addExistingProperty($row['g'], $row['f']);
					
					}
						
						
				
			
			}
			
			
		
		
		}
	else{
		
		}
	
	$returnArray['edges'] = $edges;
	
	$returnArray['objects'] = $objects;
	
	return $returnArray;
	
	}	
	
function  deleteProp($oid, $RemovePropertie){}
function deleteRel($oid, $type, $oid ){}

function deleteObject($oid){
	$sparql="
		DELETE FROM <".__MYURL__.">  { ?a ?b ?c } 
		WHERE { ?a ?b ?c.FILTER(?a = <".__MYURL__."/instances.php?oid=oid$oid>) } 
		
		DELETE FROM <".__MYURL__.">  { ?a ?b ?c } 
		WHERE { ?a ?b ?c.FILTER(?c = <".__MYURL__."/instances.php?oid=oid$oid>) } 
		
		";
		
		$result = sparql_query( $sparql ); 
		if( !$result ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
	
	}

}

?>
