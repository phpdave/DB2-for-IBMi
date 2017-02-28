<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include './includes/autoloader.php';
$db = new DB();
$dumper = new DB2Dump($db->connect());
$dumper->CreateDumpsBySchema($schema = 'DJTGR_D');
$dumper->Output();