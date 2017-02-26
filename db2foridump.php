<?php

/**
 * Dumps DB2 for i data objects into SQL
 */
class db2foridump {
    private $_db2connection;
    
    /**
     * Pass in the db2connection resource from db2_connect()
     */
    public function __construct($db2connection) {
        $this->_db2connection = $db2connection;
    }
    
    public function DumpSchema(string $schema){
    }
}
