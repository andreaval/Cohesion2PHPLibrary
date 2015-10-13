<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once '../cohesion2/Cohesion2.php';
try{
    $cohesion=new Cohesion2;
    //$cohesion->setCertificate('cohesion2.crt.pem','cohesion2.key.pem');
    $cohesion->logout();
    //$cohesion->logoutLocal();
}
catch(Exception $e){
    die($e->getMessage());
}

echo 'Logout OK';