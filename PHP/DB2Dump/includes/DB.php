<?php
/**
 * Database wrapper
 */
class DB
{
    /**
     * Connects to the database and returns the result of db2_connect
     * @return resource|bool
     */
    function connect() {
        $database = '*LOCAL';
        $username = 'removed';
        $password = 'removed';
        $db2Connection = db2_connect($database, $username, $password);
        if (!$db2Connection) {
            echo 'db2_conn_error():' . db2_conn_error() . "<br>";
            echo 'db2_conn_errormsg():' . db2_conn_errormsg() . "<br>";
        }
        return $db2Connection;
    }
}
