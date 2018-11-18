<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

class RDFObject
{
    public $oid;//unique object identifier
    public $new;//if it is a new object or not
    public $type;//cite, text, personbag, person
    public $properties;// type => value
    public $newProperties;
    public $removeProperties;
    public $relations;// [[oid => [type =>&object]],[]...]
    public $newRelations;
    public $removeRelations;
    /**
     * @var GraphDBProtocol
     */
    public $dbConn;
    public $isPrinted;
    public $levenshtein;
    public $levenprop;

    function __construct ($type, GraphDBProtocol &$dbConn, $oid = null, $recLevel = 0)
    {

        if ($oid == null) {
            $this->type = $type;
            $this->dbConn = $dbConn;
            $this->new = true;

        }
        else {

            $this->getByOid($type, $dbConn, $oid, $recLevel);

        }
        $this->isPrinted = false;
        $this->levenshtein = 100;
    }

    function getByOid ($type, GraphDBProtocol &$dbConn, $oid, $recLevel)
    {

        $this->dbConn = $dbConn;
        $values = $this->dbConn->getObject($oid, $recLevel);

        $this->properties = $values['properties'];
        $this->relations = $values['relations'];
        $this->oid = $oid;
        $this->type = $values['type'];
        $this->new = false;


    }

    function addExistingRelation ($oid, $type, &$object)
    {

        if ($oid == NULL) {

            $this->relations[] = array( $type => $object );
        }
        else {

            $this->relations[$oid][$type] = $object;
        }

    }

    function updateRelation ($oid_old, $type_old, $oid, $type, &$object)
    {
        $this->removeRelation($oid_old, $type_old);
        $this->addRelation($oid, $type, $object);
    }

    function removeRelation ($oid, $type)
    {
        if (isset($this->relations[$oid][$type])) {

            unset($this->relations[$oid][$type]);
        }
        $this->removeRelations[$oid][$type] = NULL;

    }

    function addRelation ($oid, $type, &$object)
    {

        if ($oid == NULL) {

            $this->newRelations[] = array( $type => $object );
        }
        else {

            $this->newRelations[$oid][$type] = $object;
        }

    }

    function addProperty ($type, $value)
    {
        $this->newProperties[$type] = $value;
    }

    function addExistingProperty ($type, $value)
    {
        $this->properties[$type] = $value;
    }

    function updateProperty ($type_old, $type, $oldvalue, $value)
    {
        $this->removeProperty($type_old, $oldvalue);
        $this->newPropertys[$type] = $value;
    }

    function removeProperty ($type, $value)
    {
        if (isset($this->properties[$type])) {
            unset($this->properties[$type]);
        }
        $this->removePropertys[$type] = $value;

    }

    function getType ()
    {
        return $this->type;
    }

    function setType ($type)
    {
        $this->type = $type;
    }

    function getOid ()
    {
        return $this->oid;
    }

    function setOid ($oid)
    {
        $this->oid = $oid;
    }

    function isNew ()
    {
        return $this->new;
    }

    function write_to_db ()
    {
        if ($this->new) {
            $this->new = false;

            $this->oid = $this->dbConn->writeInstance($this);


            if (count($this->properties) > 0) {
                if (isset($this->properties['http://citeapp.ch/voc.html#prename'])
                    || isset($this->properties['http://citeapp.ch/voc.html#surname'])) {
                    $this->newProperties['http://citeapp.ch/voc.html#fullname'] = '';
                }
                if (isset($this->properties['http://citeapp.ch/voc.html#prename'])) {
                    $this->properties['http://citeapp.ch/voc.html#fullname'] .= $this->properties['http://citeapp.ch/voc.html#prename'];
                }
                if (isset($this->properties['http://citeapp.ch/voc.html#surname'])) {
                    $this->properties['http://citeapp.ch/voc.html#fullname'] .= " "
                                                                                . $this->properties['http://citeapp.ch/voc.html#surname'];
                }

                foreach ($this->properties as $type => $addPropertie) {
                    $this->dbConn->addProp($this->oid, $type, $addPropertie);
                }

            }

            if (count($this->relations) > 0) {
                foreach ($this->relations as $oid => $addRelation) {
                    foreach ($addRelation as $type => $object) {
                        /**
                         * @var $object RDFObject
                         */
                        if ($object->isNew()) {
                            $object->write_to_db();
                            $oid = $object->getOid();
                        }
                        $this->dbConn->addRel($this->oid, $type, $oid);
                    }
                }
            }

            if (count($this->newProperties) > 0) {
                if (isset($this->newProperties['http://citeapp.ch/voc.html#prename'])
                    || isset($this->newPproperties['http://citeapp.ch/voc.html#surname'])) {
                    $this->newProperties['http://citeapp.ch/voc.html#fullname'] = '';
                }
                if (isset($this->newProperties['http://citeapp.ch/voc.html#prename'])) {
                    $this->newProperties['http://citeapp.ch/voc.html#fullname'] .= $this->properties['http://citeapp.ch/voc.html#prename'];
                }
                if (isset($this->newProperties['http://citeapp.ch/voc.html#surname'])) {
                    $this->newProperties['http://citeapp.ch/voc.html#fullname'] .= " "
                                                                                   . $this->properties['http://citeapp.ch/voc.html#surname'];
                }

                foreach ($this->newProperties as $type => $addPropertie) {
                    $this->dbConn->addProp($this->oid, $type, $addPropertie);
                }
                $this->properties = $this->newProperties;
                $this->newProperties = array();
            }

            if (count($this->newRelations) > 0) {
                foreach ($this->newRelations as $oid => $addRelation) {
                    foreach ($addRelation as $type => $object) {
                        /**
                         * @var $object RDFObject
                         */
                        if ($object->isNew()) {
                            $object->write_to_db();
                            $oid = $object->getOid();
                        }
                        $this->dbConn->addRel($this->oid, $type, $oid);
                    }
                    $this->relations = $this->newRelations;
                    $this->newRelations = array();
                }
            }


            if (count($this->removeProperties) > 0) {

                foreach ($this->removeProperties as $type => $RemovePropertie) {
                    $this->dbConn->deleteProp($this->oid, $type, $RemovePropertie);
                }
            }
            if (count($this->removeRelations) > 0) {
                foreach ($this->removeRelations as $oid => $RemoveRelation) {
                    foreach ($RemoveRelation as $type => $value) {
                        $this->dbConn->deleteRel($this->oid, $type, $oid);
                    }
                }
            }


        }
        else {


            if (count($this->newProperties) > 0) {

                if (isset($this->newProperties['http://citeapp.ch/voc.html#prename'])
                    || isset($this->newPproperties['http://citeapp.ch/voc.html#surname'])) {
                    $this->newProperties['http://citeapp.ch/voc.html#fullname'] = '';
                }
                if (isset($this->properties['http://citeapp.ch/voc.html#prename'])) {
                    $this->properties['http://citeapp.ch/voc.html#fullname'] =
                        $this->properties['http://citeapp.ch/voc.html#surname'];
                }
                if (isset($this->properties['http://citeapp.ch/voc.html#surname'])) {
                    $this->properties['http://citeapp.ch/voc.html#fullname'] .= " "
                                                                                . $this->properties['http://citeapp.ch/voc.html#prename'];
                }

                foreach ($this->newProperties as $type => $addPropertie) {

                    $this->dbConn->addProp($this->oid, $type, $addPropertie);
                    $this->properties[$type] = $addPropertie;
                }
                //$this->properties=$this->newProperties;
                $this->newProperties = array();
            }

            if (count($this->newRelations) > 0) {
                foreach ($this->newRelations as $oid => $addRelation) {
                    foreach ($addRelation as $type => $object) {
                        /**
                         * @var $object RDFObject
                         */
                        if ($object->isNew()) {
                            $object->write_to_db();
                            $oid = $object->getOid();
                        }
                        $this->dbConn->addRel($this->oid, $type, $oid);
                        $this->relations[$oid][$type] = $object;
                    }
                    //$this->relations=$this->newRelations;
                    $this->newRelations = array();
                }
            }


            if (count($this->removeProperties) > 0) {

                foreach ($this->removeProperties as $type => $RemovePropertie) {
                    $this->dbConn->deleteProp($this->oid, $type, $RemovePropertie);
                }
            }
            if (count($this->removeRelations) > 0) {
                foreach ($this->removeRelations as $oid => $RemoveRelation) {
                    foreach ($RemoveRelation as $type => $value) {
                        $this->dbConn->deleteRel($this->oid, $type, $oid);
                    }
                }
            }


        }

    }

    function delete ()
    {

        $this->dbConn->deleteObject($this->oid);
    }


}

?>
