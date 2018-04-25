<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once '../cohesion2/Cohesion2.php';
try{
    $cohesion=new Cohesion2;
    $cohesion->useSAML20(true);
    $cohesion->auth();
}
catch(Exception $e){
    die($e->getMessage());
}

if($cohesion->isAuth()){
    echo 'Utente autenticato: '.$cohesion->username.'<br>';
    echo 'Id SSO: '.$cohesion->id_sso.'<br>';
    echo 'Id Aspnet: '.$cohesion->id_aspnet.'<br>';
    echo 'Profilo: <pre>'.var_export($cohesion->profile,1).'</pre>';
}