<?php
/**
 * Dumps DB2 for i data objects into SQL
 */
class DB2Dump {
    private $_db2Connection;
    private $_schema;
    private $_objectNames;
    private $_outputArray;
    private $_filename="/tmp";
    private $_outputType;

    /**
     * Pass in the db2connection resource from db2_connect()
     */
    public function __construct($db2connection) {
        $this->_db2Connection = $db2connection;
        $this->_outputType = OutputTypes::_BROWSER;
    }
    
    public function SetFilename($filename) {
        $this->_filename = $filename;   
    }
    
    public function SetOutputType($outputType) {
        $this->_outputType = $outputType;   
    }
    
    public function CreateDumpsBySchema(string $schema){
        $this->_schema = $schema;
        $this->createTableDump();
        $this->createTableDataDump();
        $this->createProcedureDump();
        $this->createViewDump();
    }
    
    private function createTableDump(){
        $sql = "SELECT t1.*,t1.TABLE_NAME AS OBJECT_NAME "
                . "FROM QSYS2.SYSTABLES t1 "
                . "WHERE TABLE_SCHEMA=? AND "
                . "      TABLE_TYPE='T'";
        $stmt = db2_prepare($this->_db2Connection, $sql);
        db2_execute($stmt,[$this->_schema]);
        while ($row = db2_fetch_assoc($stmt)) {
            $this->_objectNames[ObjectTypes::_TABLE][] = $row['OBJECT_NAME'];
        }
        $this->generateSQL($this->_objectNames[ObjectTypes::_TABLE], ObjectTypes::_TABLE);
    }
    private function createTableDataDump(){
        foreach($this->_objectNames[ObjectTypes::_TABLE] as $tableName)
        {
            //Get Column data to determine if string
            $arrayIsColumnString=array();
            $sql="SELECT * FROM QSYS2.COLUMNS WHERE TABLE_SCHEMA=? AND \"TABLE_NAME\"=?";
            $stmt = db2_prepare($this->_db2Connection, $sql);
            db2_execute($stmt,[$this->_schema,$tableName]);
            while ($row = db2_fetch_assoc($stmt)) {
                $arrayIsColumnString[$row['COLUMN_NAME']]=$row['CHARACTER_MAXIMUM_LENGTH']>0?true:false;
            }
            
            //Create Insert
            $sql="SELECT * FROM ".$this->_schema.".".$tableName;
            $stmt = db2_prepare($this->_db2Connection, $sql);
            db2_execute($stmt,[$this->_schema]);
            while ($row = db2_fetch_assoc($stmt)) {
                $columns = implode(",",array_keys($row));
                $valuesArray = array();
                foreach(array_keys($row) as $column)
                {
                    $valuesArray[] = $arrayIsColumnString[$column]?"'$row[$column]'":$row[$column];
                }
                $insertSQL = "INSERT INTO ".$this->_schema.".".$tableName." (".$columns.") VALUES (".implode(",",$valuesArray).");";
                $this->_outputArray[] = $insertSQL;
            }
        }
    }
    
    private function createProcedureDump(){
        $sql = "SELECT PROCNAME AS OBJECT_NAME FROM QSYS2.PROCEDURES WHERE PROCSCHEMA=?";
        $stmt = db2_prepare($this->_db2Connection, $sql);
        db2_execute($stmt,[$this->_schema]);
        while ($row = db2_fetch_assoc($stmt)) {
            $this->_objectNames[ObjectTypes::_PROCEDURE][] = $row['OBJECT_NAME'];
        }
        $this->generateSQL($this->_objectNames[ObjectTypes::_PROCEDURE], ObjectTypes::_PROCEDURE);
    }
    
    private function createViewDump(){
        $sql = "SELECT SYSTEM_VIEW_NAME AS OBJECT_NAME FROM QSYS2.SYSVIEWS WHERE TABLE_SCHEMA=?";
        $stmt = db2_prepare($this->_db2Connection, $sql);
        db2_execute($stmt,[$this->_schema]);
        while ($row = db2_fetch_assoc($stmt)) {
            $this->_objectNames[ObjectTypes::_VIEW][] = $row['OBJECT_NAME'];
        }
        $this->generateSQL($this->_objectNames[ObjectTypes::_VIEW], ObjectTypes::_VIEW);
    }
    
    private function generateSQL(array $objectNames,string $objectType)
    {
        foreach($objectNames as $objectName)
        {
            $sql = "CALL QSYS2.GENERATE_SQL(?,?,?)";
            $stmt = db2_prepare($this->_db2Connection, $sql);
            db2_execute($stmt,[$objectName,$this->_schema,$objectType]);
            while ($row = db2_fetch_assoc($stmt)) {
                $this->_outputArray[] = $row['SRCDTA'];
            }
        }
    }
    
    public function Output() {
        $finalOutput="";
        foreach ($this->_outputArray as $output) {
            if($this->_outputType== OutputTypes::_BROWSER)
            {
                $finalOutput .= "{$output}<br>";
            }
            else if($this->_outputType== OutputTypes::_FILE)
            {
                $finalOutput .= $output;
            }
        }
        if($this->_outputType== OutputTypes::_BROWSER)
        {
            echo $finalOutput;
        }
        else if($this->_outputType== OutputTypes::_FILE)
        {
            file_put_contents($this->_filename, $finalOutput);
        }
    }
}

//::HACK:: don't want to have a bunch of files yet 
class ObjectTypes {
    const __default = self::_TABLE;
    const _ALIAS = 'ALIAS';
    const _CONSTRAINT = 'CONSTRAINT';
    const _FUNCTION = 'FUNCTION';
    const _INDEX = 'INDEX';
    const _PROCEDURE = 'PROCEDURE';
    const _SCHEMA = 'SCHEMA';
    const _SEQUENCE = 'SEQUENCE';
    const _TABLE = 'TABLE';
    const _TABLEDATA = 'TABLEDATA';
    const _TRIGGER = 'TRIGGER';
    const _TYPE = 'TYPE';
    const _VARIABLE = 'VARIABLE';
    const _VIEW = 'VIEW';
    const _XSR = 'XSR';
}
//::HACK:: don't want to have a bunch of files yet 
class OutputTypes { 
    const __default = self::_BROWSER;
    const _BROWSER = 'browser';
    const _FILE = 'file';
}