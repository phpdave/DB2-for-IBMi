<?php
function doconnect()
{
    $database      = '*LOCAL';
    $username      = 'removed';
    $password      = 'removed';
    $db2Connection = db2_connect($database, $username, $password);
    //echo 'Connection Result for '.$database.':';
    //var_dump($db2Connection);
    //echo '<br>';
    if (!$db2Connection) {
        echo 'db2_conn_error():' . db2_conn_error() . "<br>";
        echo 'db2_conn_errormsg():' . db2_conn_errormsg() . "<br>";
    }
    return $db2Connection;
}
