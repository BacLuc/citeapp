<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

class RDFODBCConn
{
    private $conn;
    private $error;


    function __construct ()
    {

        $conn = odbc_connect('VOS', 'dba', 'dba');

        if ($this->isODBCError($conn)) {
            throw new Exception("ODBC Connection Fehlgeschlagen. Code: " . odbc_error() . " 
				Message: " . odbc_errormsg() . " File: " . __FILE__ . " Line: " . __LINE__, 0);

        }
        $this->conn = $conn;


    }

    function __destruct ()
    {
        odbc_close_all();
    }

    function getOid ()
    {

        $count = 5;
        do {

            $count = $count - 1;
            $sparql = 'SELECT ?c FROM <' . __MYURL__ . '> WHERE {<' . __MYURL__ . '>  <' . __MYURL__
                      . '/voc.html#locked>  ?c } ';

            $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
            $locked = odbc_num_rows($result) == - 1 ? 0 : 1;
        } while ($locked == 1);

        $sparql = 'INSERT INTO <' . __MYURL__ . '> {<' . __MYURL__ . '>  <' . __MYURL__ . '/voc.html#locked>  "1" } ';
        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError($this->conn)) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
				Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }


        $sparql = 'SELECT ?c FROM <' . __MYURL__ . '> WHERE {<' . __MYURL__ . '>  <' . __MYURL__
                  . '/voc.html#currentOid>  ?c } ';
        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError($this->conn)) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
				Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
        $row = odbc_fetch_array($result);
        $currentOid = $row['c'];

        $sparql = '
		DELETE FROM GRAPH <' . __MYURL__ . '> {<' . __MYURL__ . '>  <' . __MYURL__ . '/voc.html#currentOid>  "'
                  . $currentOid . '" }

		INSERT INTO <' . __MYURL__ . '> {<' . __MYURL__ . '>  <' . __MYURL__ . '/voc.html#currentOid>  "'
                  . ++ $currentOid . '" }

		DELETE FROM GRAPH <' . __MYURL__ . '> {<' . __MYURL__ . '>  <' . __MYURL__ . '/voc.html#locked>  "1" }';
        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError($this->conn)) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
				Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }

        return $currentOid;

    }

    function getObject ($oid, $recLevel = 0)
    {

        $sparql = 'select ?a ?b ?c FROM <' . __MYURL__ . '> WHERE{ {<' . __MYURL__ . '/instances.php?oid=' . $oid
                  . '> ?b ?c} UNION{ ?a ?b <' . __MYURL__ . '/instances.php?oid=' . $oid . '>}}';

        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError($this->conn)) {

            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
				Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
        $properties = null;
        $relations = null;
        $type = null;
        if (odbc_num_rows($result) !== 0) {
            while ($row = odbc_fetch_array($result)) {


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

    function extractOid ($urlString)
    {

        $firststring = explode("=", $urlString);

        if (empty($firststring[1])) {
            echo $urlString;

        }


        $secondString = explode(">", $firststring[1]);

        return $secondString[0];

    }


    function writeInstance ($object)
    {
        $currentOid = $this->getOid();
        $sparql = 'INSERT INTO <' . __MYURL__ . '> 
		{<' . __MYURL__ . '/instances.php?oid=' . $currentOid . '>  <ca:hasOid>  "' . $currentOid . '".
		<' . __MYURL__ . '/instances.php?oid=' . $currentOid . '>  <rdf:instanceOf>  "' . $object->getType() . '" }';

        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError($this->conn)) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
				Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }

        return $currentOid;

    }

    function addProp ($oid, $type, $addPropertie)
    {
        $addPropertie = str_replace("'", "\'", $addPropertie);
        $addPropertie = str_replace('"', "\'", $addPropertie);


        $sparql = 'INSERT INTO <' . __MYURL__ . '> 
		{<' . __MYURL__ . '/instances.php?oid=' . $oid . '>  <' . $type . '>  "' . $addPropertie . '" }';

        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError($this->conn)) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
				Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
    }

    function addRel ($from_oid, $type, $to_oid)
    {

        $sparql = 'INSERT INTO <' . __MYURL__ . '> 
		{<' . __MYURL__ . '/instances.php?oid=' . $from_oid . '>  <' . $type . '>  <' . __MYURL__
                  . '/instances.php?oid=' . $to_oid . '>  }';
        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError($this->conn)) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
				Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
    }

    function searchObjects ($searchString, $type = NULL)
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

        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError($this->conn)) {
            echo odbc_errormsg($this->conn);
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
				Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }

        //$timetosearch=microtime(true) - $starttime;

        $objects = [];
        $edges = [];
        $currentObjectOid = null;
        $currentExtractedOid = null;
        $currentObj = null;
        //echo odbc_num_rows($result);
        //if(odbc_num_rows($result) > 0){

        while ($row = odbc_fetch_array($result)) {

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


    function getClasses ($searchString)
    {
        $sparql = 'SELECT DISTINCT ?c FROM <' . __MYURL__ . '> WHERE {?a <rdf:instanceOf> ?c.FILTER(?c LIKE "%'
                  . $searchString . '%")}';
        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');

        if ($this->isODBCError($this->conn)) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
			Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
        $resultArray = array();
        while ($row = odbc_fetch_array($result)) {
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

    function getAllPropertyTypes ($searchString, $type = NULL)
    {
        $sparql = 'SELECT DISTINCT ?c FROM <' . __MYURL__
                  . '> WHERE {?a ?c ?b.FILTER( (?a LIKE "%oid%") && (?c LIKE "http://citeapp.ch/voc.html#%'
                  . $searchString . '%" || ?c LIKE "%' . $searchString . '%") && !(?b LIKE "%oid%")) }';
        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');

        if ($this->isODBCError($this->conn)) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
			Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
        $resultArray = array();
        while ($row = odbc_fetch_array($result)) {
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

    function getAllRelationTypes ($searchString, $type = NULL)
    {

        $sparql = 'SELECT DISTINCT ?c FROM <' . __MYURL__ . '> WHERE {?a ?c ?b.FILTER( (?a LIKE "%oid%") && (?c LIKE "%'
                  . $searchString . '%") && (?b LIKE "%oid%")) }';
        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        //echo $sparql;
        if ($this->isODBCError($this->conn)) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
			Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
        $resultArray = array();
        while ($row = odbc_fetch_array($result)) {
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

    function deleteProp ($oid, $RemovePropertie, $value)
    {
        $sparql =
            'DELETE FROM <' . __MYURL__ . '>  { <' . __MYURL__ . '/instances.php?oid=' . $oid . '> <' . $RemovePropertie
            . '> "' . $value . '" } ';
        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError($this->conn)) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
			Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }

    }

    function deleteRel ($oid, $type, $other_oid)
    {
        $sparql =
            'DELETE FROM <' . __MYURL__ . '>  { <' . __MYURL__ . '/instances.php?oid=' . $oid . '> <' . $type . '> <'
            . __MYURL__ . '/instances.php?oid=' . $other_oid . '> } ';

        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError($this->conn)) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
			Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }
    }

    function deleteObject ($oid)
    {
        $sparql = '
		DELETE FROM <' . __MYURL__ . '>  { ?a ?b ?c } 
		WHERE { ?a ?b ?c.FILTER(?a = <' . __MYURL__ . '/instances.php?oid=' . $oid . '>) } 
		
		DELETE FROM <' . __MYURL__ . '>  { ?a ?b ?c } 
		WHERE { ?a ?b ?c.FILTER(?c = <' . __MYURL__ . '/instances.php?oid=' . $oid . '>) } 
		
		';

        $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
        if ($this->isODBCError($this->conn)) {
            throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
				Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

        }

    }

    /**
     * @param $conn
     * @return bool
     */
    private function isODBCError ($conn)
    {
        return odbc_error($conn) != "";
    }

}

?>
