<?php

$sparql = 'select ?a ?b ?c FROm <http://citeapp.ch> WHERE{ ?a ?b ?c}';
$oldurl = 'http://citeapp.ch';
$newurl = '';


$result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
if (!odbc_error($this->conn)) {
    echo odbc_errormsg($this->conn);
    throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
				Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

}

//$timetosearch=microtime(true) - $starttime;


while ($row = odbc_fetch_array($result)) {
    $new_a = str_replace($oldurl, $newurl, $row['a']);
    $new_b = str_replace($oldurl, $newurl, $row['b']);
    $new_c = str_replace($oldurl, $newurl, $row['c']);
    if (!(strpos($row['c'], "instances.php?") === false)) {
        $sparql_del = 'DELETE FROM <' . $oldurl . '>  { <' . $row['a'] . '> <' . $row['b'] . '> <' . $row['c'] . '> } ';
        $sparql_insert = 'INSERT INTO <' . $newurl . '> {<' . $row_a . '> <' . $row_b . '> <' . $row_c . '> }';
    }
    else {
        $sparql_del = 'DELETE FROM <' . $oldurl . '>  { <' . $row['a'] . '> <' . $row['b'] . '> "' . $row['c'] . '" } ';
        $sparql_insert = 'INSERT INTO <' . $newurl . '> {<' . $row_a . '> <' . $row_b . '> "' . $row_c . '" }';


    }

    $result = odbc_exec($this->conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparql . '\', NULL, 0)');
    if (!odbc_error($this->conn)) {
        echo odbc_errormsg($this->conn);
        throw new Exception("ODBC Operation Fehlgeschlagen. Code: " . odbc_error($this->conn) . " 
							Message: " . odbc_errormsg($this->conn) . " File: " . __FILE__ . " Line: " . __LINE__, 1);

    }


}

?>
