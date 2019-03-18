<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

class RDFODBCConn implements GraphDBProtocol
{
    private $conn;
    private $error;


    function __construct ()
    {
        $conn = new PDO('odbc:VOS', 'dba', 'dba');
        $this->conn = $conn;


    }

    private function getOid ()
    {

        $count = 5;
        do {

            $count = $count - 1;
            $sparql = 'SELECT ?c FROM <' . __MYURL__ . '> WHERE {<' . __MYURL__ . '>  <' . __MYURL__
                      . '/voc.html#locked>  ?c } ';

            $result = $this->$this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
            $locked = $result->rowCount() == - 1 ? 0 : 1;
        } while ($locked == 1);

        $sparql = 'INSERT INTO <' . __MYURL__ . '> {<' . __MYURL__ . '>  <' . __MYURL__ . '/voc.html#locked>  "1" } ';
        $result = $this->$this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError()) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
				Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }


        $sparql = 'SELECT ?c FROM <' . __MYURL__ . '> WHERE {<' . __MYURL__ . '>  <' . __MYURL__
                  . '/voc.html#currentOid>  ?c } ';
        $result = $this->$this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError()) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
				Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
        $row = $result->fetch();
        $currentOid = $row['c'];

        $sparql = '
		DELETE FROM GRAPH <' . __MYURL__ . '> {<' . __MYURL__ . '>  <' . __MYURL__ . '/voc.html#currentOid>  "'
                  . $currentOid . '" }

		INSERT INTO <' . __MYURL__ . '> {<' . __MYURL__ . '>  <' . __MYURL__ . '/voc.html#currentOid>  "'
                  . ++ $currentOid . '" }

		DELETE FROM GRAPH <' . __MYURL__ . '> {<' . __MYURL__ . '>  <' . __MYURL__ . '/voc.html#locked>  "1" }';
        $result = $this->$this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError()) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
				Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }

        return $currentOid;

    }

    public function getObject ($oid, $recLevel = 0)
    {

        $sparql = 'select ?a ?b ?c FROM <' . __MYURL__ . '> WHERE{ {<' . __MYURL__ . '/instances.php?oid=' . $oid
                  . '> ?b ?c} UNION{ ?a ?b <' . __MYURL__ . '/instances.php?oid=' . $oid . '>}}';

        $result = $this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError()) {

            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
				Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
        $properties = null;
        $relations = null;
        $type = null;
        if ($result->rowCount() !== 0) {
            while ($row = $result->fetch()) {


                if (!isset($row['c'])) {

                    //echo "relation from Object with oid $oid  to object with ".$row['c']." wird untersucht<br>";
                    $extractedOid = $this->extractOid($row['a']);
                    if ($recLevel == 0) {
                        $relations[$extractedOid][$row['b']][0] = NULL;
                        $relations[$extractedOid][$row['b']][1] = - 1;
                    }
                    else {
                        $relations[$extractedOid][$row['b']][0] =
                            new RDFObject(NULL, $this, $extractedOid, $recLevel - 1);
                        $relations[$extractedOid][$row['b']][1] = - 1;

                    }

                }
                else {

                    if (!(strpos($row['c'], "oid") === false) && $row['b'] != "ca:hasOid") {
                        if ($recLevel == 0) {
                            $relations[$this->extractOid($row['c'])][$row['b']][0] = NULL;
                            $relations[$this->extractOid($row['c'])][$row['b']][1] = 1;
                        }
                        else {
                            $relations[$this->extractOid($row['c'])][$row['b']][0] =
                                new RDFObject(NULL, $this, $this->extractOid($row['c']), $recLevel - 1);
                            $relations[$this->extractOid($row['c'])][$row['b']][1] = 1;

                        }

                    }
                    elseif (!(strpos($row['b'], "instance") === false)) {
                        $type = $row['c'];
                    }
                    else {
                        $properties[$row['b']] = $row['c'];

                    }

                }


            }
        }
        //echo "reclevel ist : $recLevel <br>";
        //my_print_r($relations);
        return array(
            'relations'  => $relations,
            'properties' => $properties,
            'type'       => $type,

        );


    }

    private function extractOid ($urlString)
    {

        $firststring = explode("=", $urlString);

        if (empty($firststring[1])) {
            echo $urlString;

        }


        $secondString = explode(">", $firststring[1]);

        return $secondString[0];

    }


    public function writeInstance ($object)
    {
        $currentOid = $this->getOid();
        $sparql = 'INSERT INTO <' . __MYURL__ . '> 
		{<' . __MYURL__ . '/instances.php?oid=' . $currentOid . '>  <ca:hasOid>  "' . $currentOid . '".
		<' . __MYURL__ . '/instances.php?oid=' . $currentOid . '>  <rdf:instanceOf>  "' . $object->getType() . '" }';

        $result = $this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError()) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
				Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }

        return $currentOid;

    }

    public function addProp ($oid, $type, $addPropertie)
    {
        $addPropertie = str_replace("'", "\'", $addPropertie);
        $addPropertie = str_replace('"', "\'", $addPropertie);


        $sparql = 'INSERT INTO <' . __MYURL__ . '> 
		{<' . __MYURL__ . '/instances.php?oid=' . $oid . '>  <' . $type . '>  "' . $addPropertie . '" }';

        $result = $this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError()) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
				Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
    }

    public function addRel ($from_oid, $type, $to_oid)
    {

        $sparql = 'INSERT INTO <' . __MYURL__ . '> 
		{<' . __MYURL__ . '/instances.php?oid=' . $from_oid . '>  <' . $type . '>  <' . __MYURL__
                  . '/instances.php?oid=' . $to_oid . '>  }';
        $result = $this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError()) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
				Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
    }

    public function searchObjects ($searchString, $type = NULL)
    {

        $returnArray = array( 'objects' => [], 'edges' => [] );
        if ($type == NULL) {


            $sparql = 'select ?a ?g ?f FROm <http://citeapp.ch> WHERE{
				   {?a ?g ?f.
					?a ?b ?c.FILTER(?c LIKE "%' . $searchString . '%" && !(?c LIKE "%oid%" || ?b = <rdf:instanceOf>) && !(?b LIKE "%currentOid%"))}
				} ORDER BY ?a';


        }
        else {
            $sparql = 'select ?a ?g ?f FROm <http://citeapp.ch> WHERE{
				   {?a ?g ?f.
					?a ?b ?c.FILTER(?c LIKE "%' . $searchString . '%" && !(?c LIKE "%oid%" || ?b = <rdf:instanceOf>) && !(?b LIKE "%currentOid%") ).
					?a <rdf:instanceOf> ' . $type . '}
				} ORDER BY ?a';


        }

        $result = $this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError()) {
            echo $this->conn->errorInfo();
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
				Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }

        //$timetosearch=microtime(true) - $starttime;

        $objects = [];
        $edges = [];
        $currentObjectOid = null;
        $currentExtractedOid = null;
        $currentObj = null;
        //echo odbc_num_rows($result);
        //if(odbc_num_rows($result) > 0){

        while ($row = $result->fetch()) {

            if ($currentObjectOid == null) {
                $currentObjectOid = $row['a'];
                $currentExtractedOid = $this->extractOid($currentObjectOid);
                $currentObj = new RDFObject(null, $this->dbConn);
                $currentObj->setOid($currentExtractedOid);
                $currentObj->levenshtein = 100;
            }
            if ($currentObjectOid != $row['a']) {


                $objects[$currentExtractedOid] = $currentObj;
                $currentObjectOid = $row['a'];

                $currentExtractedOid = $this->extractOid($currentObjectOid);
                $currentObj = new RDFObject(null, $this->dbConn);
                $currentObj->setOid($currentExtractedOid);
                $currentObj->levenshtein = 100;

            }

            if (!(strpos($row['f'], "oid") === false) && $row['g'] != "ca:hasOid") {
                //echo "bin in beziehungsdetection<br>";
                $extractedRelOid = $this->extractOid($row['f']);
                $newObject = new RDFObject(null, $this->dbConn);
                $currentObj->addExistingRelation($extractedRelOid, $row['g'], $newObject);
                $relArray = array( 'from' => $currentExtractedOid, 'to' => $extractedRelOid, 'type' => $row['g'] );
                $edges[] = $relArray;
            }
            elseif (!(strpos($row['g'], "instance") === false)) {
                $currentObj->setType($row['f']);
            }
            elseif ($row['g'] == "ca:hasOid") {

            }
            else {
                if (strlen($row['f']) < 1) {
                    continue;
                }
                $lev = levenshtein($searchString, substr($row['f'], 0, 100)) / strlen(substr($row['f'], 0, 100));
                if ($currentObj->levenshtein > $lev) {
                    $currentObj->levenshtein = $lev;
                    $currentObj->levenprop = $row['g'];
                }
                $currentObj->addExistingProperty($row['g'], $row['f']);

            }


        }
        $objects[$currentExtractedOid] = $currentObj;


        //}


        $returnArray['edges'] = $edges;

        $returnArray['objects'] = $objects;

        return $returnArray;


    }


    public function getClasses ($searchString)
    {
        $sparql = 'SELECT DISTINCT ?c FROM <' . __MYURL__ . '> WHERE {?a <rdf:instanceOf> ?c.FILTER(?c LIKE "%'
                  . $searchString . '%")}';
        $result = $this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');

        if ($this->isODBCError()) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
			Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
        $resultArray = array();
        while ($row = $result->fetch()) {
            $explode = explode('#', $row['c']);
            $label = $row['c'];
            if (count($explode) > 1 && count($explode) <= 2) {
                $label = $explode[1];
            }
            $pushArray =
                array( 'class' => $row['c'], 'label' => $label, 'levenshtein' => levenshtein($searchString, $label) );
            $resultArray[] = $pushArray;
        }
        function cmp ($a, $b)
        {


            if ($a['levenshtein'] == $b['levenshtein']) {
                return 0;
            }
            return ($a['levenshtein'] > $b['levenshtein']) ? 1 : - 1;
        }

        uasort($resultArray, 'cmp');

        utf8_encode_deep($resultArray);
        $returnArray = array( "check" => "suc", "result" => $resultArray );
        echo json_encode($returnArray);


    }

    public function getAllPropertyTypes ($searchString, $type = NULL)
    {
        $sparql = 'SELECT DISTINCT ?c FROM <' . __MYURL__
                  . '> WHERE {?a ?c ?b.FILTER( (?a LIKE "%oid%") && (?c LIKE "http://citeapp.ch/voc.html#%'
                  . $searchString . '%" || ?c LIKE "%' . $searchString . '%") && !(?b LIKE "%oid%")) }';
        $result = $this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');

        if ($this->isODBCError()) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
			Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
        $resultArray = array();
        while ($row = $result->fetch()) {
            if ($row['c'] == "ca:hasOid" || !(strpos($row['c'], "instance") === false)) {
                continue;
            }


            $explode = explode('#', $row['c']);
            $label = $row['c'];
            if (count($explode) > 1 && count($explode) <= 2) {
                $label = $explode[1];
            }

            $pushArray =
                array( 'class' => $row['c'], 'label' => $label, 'levenshtein' => levenshtein($searchString, $label) );
            $resultArray[] = $pushArray;
        }
        function cmp ($a, $b)
        {


            if ($a['levenshtein'] == $b['levenshtein']) {
                return 0;
            }
            return ($a['levenshtein'] > $b['levenshtein']) ? 1 : - 1;
        }

        uasort($resultArray, 'cmp');

        utf8_encode_deep($resultArray);
        $returnArray = array( "check" => "suc", "result" => $resultArray );
        echo json_encode($returnArray);

    }

    public function getAllRelationTypes ($searchString, $type = NULL)
    {

        $sparql = 'SELECT DISTINCT ?c FROM <' . __MYURL__ . '> WHERE {?a ?c ?b.FILTER( (?a LIKE "%oid%") && (?c LIKE "%'
                  . $searchString . '%") && (?b LIKE "%oid%")) }';
        $result = $this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        //echo $sparql;
        if ($this->isODBCError()) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
			Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
        $resultArray = array();
        while ($row = $result->fetch()) {
            $explode = explode('#', $row['c']);
            $label = $row['c'];
            if (count($explode) > 1 && count($explode) <= 2) {
                $label = $explode[1];
            }

            $pushArray =
                array( 'class' => $row['c'], 'label' => $label, 'levenshtein' => levenshtein($searchString, $label) );
            $resultArray[] = $pushArray;
        }
        function cmp ($a, $b)
        {


            if ($a['levenshtein'] == $b['levenshtein']) {
                return 0;
            }
            return ($a['levenshtein'] > $b['levenshtein']) ? 1 : - 1;
        }

        uasort($resultArray, 'cmp');

        utf8_encode_deep($resultArray);
        $returnArray = array( "check" => "suc", "result" => $resultArray );
        echo json_encode($returnArray);

    }

    public function deleteProp ($oid, $RemovePropertie, $value)
    {
        $sparql =
            'DELETE FROM <' . __MYURL__ . '>  { <' . __MYURL__ . '/instances.php?oid=' . $oid . '> <' . $RemovePropertie
            . '> "' . $value . '" } ';
        $result = $this->$this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError()) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
			Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }

    }

    public function deleteRel ($oid, $type, $other_oid)
    {
        $sparql =
            'DELETE FROM <' . __MYURL__ . '>  { <' . __MYURL__ . '/instances.php?oid=' . $oid . '> <' . $type . '> <'
            . __MYURL__ . '/instances.php?oid=' . $other_oid . '> } ';

        $result = $this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError()) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
			Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
    }

    public function deleteObject ($oid)
    {
        $sparql = '
		DELETE FROM <' . __MYURL__ . '>  { ?a ?b ?c } 
		WHERE { ?a ?b ?c.FILTER(?a = <' . __MYURL__ . '/instances.php?oid=' . $oid . '>) } 
		
		DELETE FROM <' . __MYURL__ . '>  { ?a ?b ?c } 
		WHERE { ?a ?b ?c.FILTER(?c = <' . __MYURL__ . '/instances.php?oid=' . $oid . '>) } 
		
		';

        $result = $this->conn->query('CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError()) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . $this->conn->errorCode() . " 
				Message: " . $this->conn->errorInfo() . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }

    }

    /**
     * @return bool
     */
    private function isODBCError ()
    {
        return $this->conn->errorCode() != "00000";
    }

}

?>
