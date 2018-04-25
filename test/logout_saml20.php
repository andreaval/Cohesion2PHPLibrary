<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once '../cohesion2/Cohesion2.php';
try{
    $cohesion=new Cohesion2;
    $cohesion->useSAML20(true);
    $cohesion->logout();
}
catch(Exception $e){
    die($e->getMessage());
}

echo 'Logout OK';