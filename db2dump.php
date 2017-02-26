<?php
/**
 * db2dump.php used for dumping QSYS2 catalogs into SQL and into the browser/file etc...
 * Work in progress.  Need to change procedural code into Object Oriented/Classes.  But this was 
 * developed in spare time so its messy
 */

//::TODO:: change code below to loop through object types and add the other object types specified in https://www.ibm.com/developerworks/community/wikis/home?lang=en#!/wiki/IBM%20i%20Technology%20Updates/page/QSYS2.GENERATE_SQL()%20procedure
array(0 => array('OBJECT_TYPE_COL_NAME'=>'TABLE_NAME','TABLE_TO_PULL_FROM'=>'QSYS2.SYSTABLES' ,'SCHEMA_COL'=>'TABLE_SCHEMA'),
      1 => array('OBJECT_TYPE_COL_NAME'=>'PROCNAME'  ,'TABLE_TO_PULL_FROM'=>'QSYS2.PROCEDURES','SCHEMA_COL'=>'PROCSCHEMA'));

//::TODO:: Modify code below into OO, functions into classes
//$db2Dump = new DB2Dump();
//$db2Dump->SetFileName('/var/data/dump.sql');
//$db2Dump->DumpSchema('DJTGR_D');

//error_reporting(E_ALL);
//ini_set('display_errors', '1');
include 'db.php';
$db2Connection = doconnect();
$objectName    = "";
$schema        = "DJTGR_D"; //AKA library
$objectType    = "";

//Get all table names for this schema
$objectType     = "TABLE";
$sql_query      = "SELECT TABLE_NAME AS OBJECT_NAME FROM QSYS2.SYSTABLES WHERE TABLE_SCHEMA=?";
$parms          = array(
    array(
        "name" => "schema",
        "value" => $schema
    )
);
$sql_statement1 = my_prepare($db2Connection, $sql_query);
for ($i = 0; $i < count($parms); $i++) {
    ${$parms[$i]["name"]} = $parms[$i]["value"];
    db2_bind_param($sql_statement1, $i + 1, $parms[$i]["name"], DB2_PARAM_IN);
}
my_execute($sql_statement1);

$rowsExported = "";
while ($row = db2_fetch_assoc($sql_statement1)) {
    //generateSQL($row['OBJECT_NAME'],$schema,$objectType);
    $objectName = $row['OBJECT_NAME'];
    $sql_query  = "CALL QSYS2.GENERATE_SQL(?,?,?)";
    $parms2     = array(
        array(
            "name" => "object_name",
            "value" => $objectName
        ),
        array(
            "name" => "schema",
            "value" => $schema
        ),
        array(
            "name" => "object_type",
            "value" => $objectType
        )
    );
    global $db2Connection;
    $sql_statement = my_prepare($db2Connection, $sql_query);
    for ($i = 0; $i < count($parms2); $i++) {
        ${$parms2[$i]["name"]} = $parms2[$i]["value"];
        db2_bind_param($sql_statement, $i + 1, $parms2[$i]["name"], DB2_PARAM_IN);
    }
    //my_bind_params($sql_statement,$parms);
    my_execute($sql_statement);
    
    while ($row = db2_fetch_assoc($sql_statement)) {
        $rowsExported .= $row['SRCDTA'] . "<br>";
    }
    
}
echo $rowsExported;
$rowsExported = "";


//Get all pro names for this schema
$objectType     = "PROCEDURE";
$sql_query      = "SELECT PROCNAME AS OBJECT_NAME FROM QSYS2.PROCEDURES WHERE PROCSCHEMA=?";
$parms          = array(
    array(
        "name" => "schema",
        "value" => $schema
    )
);
$sql_statement1 = my_prepare($db2Connection, $sql_query);
for ($i = 0; $i < count($parms); $i++) {
    ${$parms[$i]["name"]} = $parms[$i]["value"];
    db2_bind_param($sql_statement1, $i + 1, $parms[$i]["name"], DB2_PARAM_IN);
}
my_execute($sql_statement1);

$rowsExported = "";
while ($row = db2_fetch_assoc($sql_statement1)) {
    //generateSQL($row['OBJECT_NAME'],$schema,$objectType);
    $objectName = $row['OBJECT_NAME'];
    $sql_query  = "CALL QSYS2.GENERATE_SQL(?,?,?)";
    $parms2     = array(
        array(
            "name" => "object_name",
            "value" => $objectName
        ),
        array(
            "name" => "schema",
            "value" => $schema
        ),
        array(
            "name" => "object_type",
            "value" => $objectType
        )
    );
    global $db2Connection;
    $sql_statement = my_prepare($db2Connection, $sql_query);
    for ($i = 0; $i < count($parms2); $i++) {
        ${$parms2[$i]["name"]} = $parms2[$i]["value"];
        db2_bind_param($sql_statement, $i + 1, $parms2[$i]["name"], DB2_PARAM_IN);
    }
    //my_bind_params($sql_statement,$parms);
    my_execute($sql_statement);
    
    while ($row = db2_fetch_assoc($sql_statement)) {
        $rowsExported .= $row['SRCDTA'] . "<br>";
    }
    
}
echo $rowsExported;





function generateSQL($objectName, $schema, $objectType)
{
    $rowsExported = "";
    $sql_query    = "CALL QSYS2.GENERATE_SQL(?,?,?)";
    $parms2       = array(
        array(
            "name" => "object_name",
            "value" => $objectName
        ),
        array(
            "name" => "schema",
            "value" => $schema
        ),
        array(
            "name" => "object_type",
            "value" => $objectType
        )
    );
    global $db2Connection;
    $sql_statement = my_prepare($db2Connection, $sql_query);
    for ($i = 0; $i < count($parms2); $i++) {
        ${$parms2[$i]["name"]} = $parms2[$i]["value"];
        db2_bind_param($sql_statement, $i + 1, $parms2[$i]["name"], DB2_PARAM_IN);
    }
    //my_bind_params($sql_statement,$parms);
    my_execute($sql_statement);
    
    while ($row = db2_fetch_assoc($sql_statement)) {
        $rowsExported .= $row['SRCDTA'] . "<br>";
    }
    echo $rowsExported;
}

function my_prepare($db2Connection, $sql_query)
{
    $sql_statement = db2_prepare($db2Connection, $sql_query);
    if ($sql_statement === false) {
        echo "<span style=\"color:red;font-size:18px\">Prepare Failed: " . db2_stmt_errormsg() . "</span>";
    }
    return $sql_statement;
}
/*
function my_bind_params(&$sql_statement,&$parms)
{
for($i=0;$i<count($parms);$i++)
{
${$parms[$i]["name"]} = $parms[$i]["value"];
db2_bind_param($sql_statement, $i+1, $parms[$i]["name"], DB2_PARAM_IN);
}
}
*/

function my_execute(&$sql_statement)
{
    $result = db2_execute($sql_statement);
    if ($result === false) {
        echo "<span style=\"color:red;font-size:18px\">db2_execute failed: " . db2_stmt_errormsg() . "</span>";
    }
    return $result;
}
