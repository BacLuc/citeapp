<?php
/**
 * Created by PhpStorm.
 * User: lucius
 * Date: 18.11.18
 * Time: 23:13
 */

interface GraphDBProtocol
{
    public function getObject ($oid, $recLevel = 0);

    public function writeInstance ($object);

    public function addProp ($oid, $type, $addPropertie);

    public function addRel ($from_oid, $type, $to_oid);

    public function searchObjects ($searchString, $type = NULL);

    public function getClasses ($searchString);

    public function getAllPropertyTypes ($searchString, $type = NULL);

    public function getAllRelationTypes ($searchString, $type = NULL);

    public function deleteProp ($oid, $RemovePropertie, $value);

    public function deleteRel ($oid, $type, $other_oid);

    public function deleteObject ($oid);
}