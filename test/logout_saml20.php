<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once '../cohesion2/Cohesion2.php';
use andreaval\Cohesion2\Cohesion2;
use andreaval\Cohesion2\Cohesion2Exception;
try{
    $cohesion = new Cohesion2;
    $cohesion->useSAML20(true);
    $cohesion->logout();
}
catch(Cohesion2Exception $e){
    die($e->getTraceAsString());
}

echo 'Logout OK';