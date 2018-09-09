<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("include.php");

if (isset($_POST)) {

    if (isset($_POST['action'])) {

        try {

            $dbConn = new RDFODBCConn();
            $RDFMed = new RDFMediator($dbConn);
            if ($_POST['action'] == 'searchObjectsForGraph' && isset($_POST['searchstring'])) {

                $RDFMed->searchObjects($_POST['searchstring']);
                $RDFMed->printToJsonForGraph();


            }

            elseif ($_POST['action'] == 'searchObjectsForSearch' && isset($_POST['searchstring'])) {
                $RDFMed->searchObjects($_POST['searchstring']);
                $RDFMed->printToJsonForSearch();


            }
            elseif ($_POST['action'] == 'getObjectById') {
                if (isset($_POST['recLevel'])) {
                    $RDFMed->getObject($_POST['oid'], $_POST['recLevel']);
                }
                else {
                    $RDFMed->getObject($_POST['oid']);
                }

                $RDFMed->printToJsonForGraph();


            }

            elseif ($_POST['action'] == 'newObject' && isset($_POST['type'])) {
                if (isset($_POST['properties']) && isset($_POST['values'])) {

                    $properties = array();
                    foreach ($_POST['properties'] as $number => $property) {
                        $properties[$property] = $_POST['values'][$number];

                    }
                    $oid = $RDFMed->addObject($_POST['type'], $properties);
                }
                else {
                    $oid = $RDFMed->addObject($_POST['type'], array());
                }
                echo utf8_encode(json_encode(array( 'check' => 'suc', 'oid' => $oid )));


            }

            elseif ($_POST['action'] == 'deleteObject' && isset($_POST['oid'])) {
                $RDFMed->deleteObject($_POST['oid']);
                echo utf8_encode(json_encode(array( 'check' => 'suc', 'oid' => $_POST['oid'] )));


            }


            elseif ($_POST['action'] == 'addProperty' && isset($_POST['oid']) && isset($_POST['property'])
                    && isset($_POST['value'])) {

                $RDFMed->addProperty($_POST['oid'], $_POST['property'], $_POST['value']);
                echo utf8_encode(json_encode(array( 'check' => 'suc' )));


            }
            elseif ($_POST['action'] == 'removeProperty' && isset($_POST['oid']) && isset($_POST['property'])
                    && isset($_POST['value'])) {
                $RDFMed->removeProperty($_POST['oid'], $_POST['property'], $_POST['value']);
                echo utf8_encode(json_encode(array( 'check' => 'suc' )));


            }

            elseif ($_POST['action'] == 'addRelation' && isset($_POST['oid']) && isset($_POST['relation'])
                    && isset($_POST['other_oid'])) {
                $RDFMed->addRelation($_POST['oid'], $_POST['relation'], $_POST['other_oid']);
                echo utf8_encode(json_encode(array( 'check' => 'suc' )));


            }
            elseif ($_POST['action'] == 'removeRelation' && isset($_POST['oid']) && isset($_POST['relation'])
                    && isset($_POST['other_oid'])) {
                $RDFMed->removeRelation($_POST['oid'], $_POST['relation'], $_POST['other_oid']);
                echo utf8_encode(json_encode(array( 'check' => 'suc' )));


            }
            elseif ($_POST['action'] == 'getClasses' && isset($_POST['searchstring'])) {
                $dbConn->getClasses($_POST['searchstring']);


            }

            elseif ($_POST['action'] == 'getAllPropertyTypes' && isset($_POST['searchstring'])) {
                $dbConn->getAllPropertyTypes($_POST['searchstring']);


            }

            elseif ($_POST['action'] == 'getAllRelationTypes' && isset($_POST['searchstring'])) {

                $dbConn->getAllRelationTypes($_POST['searchstring']);


            }


        }
        catch (Exception $e) {
            my_print_r($e);

        }

    }


}

?>
