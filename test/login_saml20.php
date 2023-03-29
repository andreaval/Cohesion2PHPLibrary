<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once '../cohesion2/Cohesion2.php';
use andreaval\Cohesion2\Cohesion2;
use andreaval\Cohesion2\Cohesion2Exception;
try{
    $cohesion=new Cohesion2;
    $cohesion->useSAML20(true);    
    //$cohesion->enableEIDASLogin();
    //$cohesion->enableSPIDProLogin(["PF", "PG", "LP"]);
    $cohesion->auth();
}
catch(Cohesion2Exception $e){
    die($e->getMessage().'<br>'.$e->getTraceAsString());
}

if($cohesion->isAuth()){
    echo 'Utente autenticato: '.$cohesion->username.'<br>';
    echo 'Id SSO: '.$cohesion->id_sso.'<br>';
    echo 'Id Aspnet: '.$cohesion->id_aspnet.'<br>';
    echo 'Profilo: <pre>'.var_export($cohesion->profile,1).'</pre>';
}