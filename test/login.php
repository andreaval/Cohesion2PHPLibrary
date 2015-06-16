<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once 'cohesion2/Cohesion2.php';
try{
    $cohesion=new Cohesion2;
    $cohesion->setAuthRestriction('0,1');
    //$cohesion->setCertificate('cohesion2.crt.pem','cohesion2.key.pem');
    $cohesion->auth();
}
catch(Exception $e){
    die($e->getMessage());
}

if($cohesion->isAuth()){
    echo 'Utente autenticato: '.$cohesion->username.'<br>';
    echo 'Id SSO: '.$cohesion->id_sso.'<br>';
    echo 'Id Aspnet: '.$cohesion->id_aspnet.'<br>';
    echo 'Profilo: '.var_export($cohesion->profile,1).'<br>';
}
