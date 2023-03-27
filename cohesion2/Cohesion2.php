<?php
namespace andreaval\Cohesion2;
/**
 * Classe per la gestione del SSO di Cohesion2
 * @version 3.0.0 27/03/2023 22.06
 * @author Andrea Vallorani <andrea.vallorani@gmail.com>
 * @license MIT License <https://github.com/andreaval/Cohesion2PHPLibrary/blob/master/LICENSE>
 * @link http://cohesion.regione.marche.it/cohesioninformativo/
 */
class Cohesion2{
    
    const COHESION2_CHECK = 'https://cohesion2.regione.marche.it/sso/Check.aspx?auth=';
    const COHESION2_LOGIN = 'https://cohesion2.regione.marche.it/SA/AccediCohesion.aspx?auth=';
    const COHESION2_WEB = 'https://cohesion2.regione.marche.it/SSO/webCheckSessionSSO.aspx';
    const COHESION2_SAML20_CHECK = 'https://cohesion2.regione.marche.it/SPManager/WAYF.aspx?auth=';
    const COHESION2_SAML20_WEB = 'https://cohesion2.regione.marche.it/SPManager/webCheckSessionSSO.aspx';
    const SESSION_NAME = 'cohesion2';
    const EIDAS_FLAG = 'eidas=1';
    const PURPOSE_FLAG = 'purpose=';
    
    private $authRestriction = '0,1,2,3';
    private $cert_file = null;
    private $key_file = null;
    private $sso = true;
    private $saml20 = false;
    private $eIDAS = false;
    private $SPIDProPurpose = false;
    
    /**
     * ID sessione SSO
     * @var string
     */
    public $id_sso;
    
    /**
     * ID sessione ASPNET
     * @var string
     */
    public $id_aspnet;
    
    /**
     * Username utente autenticato in Cohesion
     * @var string
     */
    public $username;
    
    /**
     * Profilo dell'utente contenente i dati forniti dal server
     * @var array
     */
    public $profile;
    
    function __construct(){
        //controllo se la sessione è stata avviata
        if(session_status() == PHP_SESSION_NONE) session_start();
        if($this->isAuth()){
            //Utente già autenticato, ripristino sessione
            $obj = unserialize($_SESSION[self::SESSION_NAME]);
            $this->id_sso = $obj->id_sso;
            $this->id_aspnet = $obj->id_aspnet;
            $this->username = $obj->username;
            $this->profile = $obj->profile;
        }
    }
    
    /**
     * Imposta i metodi di autenticazione permessi
     * @param string $authRestriction (separare le varie scelte con una virgola)
     * Valore di default: 0,1,2,3
     * 0 indica di mostrare l’autenticazione con Utente e Password
     * 1 indica di mostrare l’autenticazione con Utente, Password e PIN
     * 2 indica di mostrare l’autenticazione con Smart Card
     * 3 indica di mostrare l’autenticazione di Dominio (valida solo per utenti 
     * interni alla rete regionale)
     * NON TUTTE LE COMBINAZIONI VENGONO ACCETTATE (es. 0,1 vengono comunque 
     * mostrati tutti i metodi)
     * @return Cohesion2
     */
    public function setAuthRestriction($authRestriction){
    	if($authRestriction) $this->authRestriction = $authRestriction;
        return $this;
    }
    
    /**
     * Controlla se l'utente è già stato autenticato
     * @return boolean
     */
    public function isAuth(){
    	return isset($_SESSION[self::SESSION_NAME]);
    }
    
    /**
     * Imposta il ceritificato per invocare il WS del SSO. (Opzionale)
     * @param string $certFilePath File .pem contenente il certificato
     * @param string $keyFilePath File .pem contenente la chiave privata
     * @return Cohesion2
     */
    public function setCertificate($certFilePath,$keyFilePath){
    	$this->cert_file = $certFilePath;
        $this->key_file = $keyFilePath;
        return $this;
    }
    
    /**
     * Attiva o meno l'uso del SingleSignOn. Se disabilitato, l'utente verrà
     * sempre reindirizzato alla pagina di login senza controllare se esso 
     * risulta autenticato o meno tramite SSO
     * @param boolean $on
     * @return Cohesion2
     */
    public function useSSO($on=TRUE){
        $this->sso = $on;
        return $this;
    }
    
    /**
     * Abilita o meno il funzionamento del SSO in modalità SAML2.0. Questa 
     * modalità attiva, se non attivato, il SSO. 
     * @param boolean $on
     * @return Cohesion2
     */
    public function useSAML20($on=TRUE){
        $this->useSSO(true);
        $this->saml20 = $on;
        return $this;
    }
    
    /**
     * Abilita il login eIDAS (e automaticamente la modalità SAML2.0)
     * @return Cohesion2
     */
    public function enableEIDASLogin(){
        $this->useSAML20(true);
        $this->eIDAS = true;
        return $this;
    }
    
    /**
     * Abilita il login con SPID Professionale (PF,PG,LP) e automaticamente la modalità SAML2.0)
     * @param string[] $SPIDProPurposes inserire un array con i purpose richiesti. 
     *                 Default: PF - SPID per Persone Fisiche ad Uso Professionale.
     *                 I possibili valori sono: LP, PG, PF, PX Così come indicato 
     *                 nell'avviso SPID 18 v2: https://www.agid.gov.it/sites/default/files/repository_files/spid-avviso-n18_v.2-_autenticazione_persona_giuridica_o_uso_professionale_per_la_persona_giuridica.pdf
     * @return Cohesion2
     */
    public function enableSPIDProLogin($SPIDProPurposes=['PF']){
        $this->useSAML20(true);
        $this->SPIDProPurpose = implode('|',$SPIDProPurposes);
        return $this;
    }
    
    /**
     * Autentica l'utente nel sistema
     * @return void
     * @throws Exception Invocata eccezione in caso di errore
     */
    public function auth(){
        //file_put_contents('log.txt',__METHOD__."\n",FILE_APPEND);
        if(!$this->isAuth()){
            if(!empty($_REQUEST['auth'])){
                $this->verify($_REQUEST['auth']);
            }
            else{
                $this->check();
            }
        }
    }
    
    /**
     * Chiude la sessione locale e quella del SSO
     * @return void
     */
    public function logout(){
        if($this->isAuth()){
            unset($_SESSION[self::SESSION_NAME]);
            $data = ['Operation'=>'LogoutSito','IdSessioneSSO'=>$this->id_sso,'IdSessioneASPNET'=>$this->id_aspnet];
            $context  = stream_context_create([
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ],
                'ssl' => [
                    'ciphers' => 'DEFAULT:!DH'
                ]
            ]);
            file_get_contents($this->saml20 ? self::COHESION2_SAML20_WEB : self::COHESION2_WEB,false,$context);
        }
    }
    
    /**
     * Chiude la sessione locale lasciando aperta quella del SSO
     * @return void
     */
    public function logoutLocal(){
        if($this->isAuth()){
            unset($_SESSION[self::SESSION_NAME]);
        }
    }
    
    private function check(){
        //file_put_contents('log.txt',__METHOD__."\n",FILE_APPEND);
        $protocol = ($_SERVER["SERVER_PORT"] == 443) ? 'https://' : 'http://';
    	$urlPagina = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $urlPagina .= ($_SERVER['QUERY_STRING']) ? '&' : '?';
        $urlPagina .= 'cohesionCheck=1';
        $xmlAuth = '<dsAuth xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://tempuri.org/Auth.xsd">
            <auth>
                <user />
                <id_sa />
                <id_sito>TEST</id_sito>
                <esito_auth_sa />
                <id_sessione_sa />
                <id_sessione_aspnet_sa />
                <url_validate><![CDATA['.$urlPagina.']]></url_validate>
                <url_richiesta><![CDATA['.$urlPagina.']]></url_richiesta>
                <esito_auth_sso />
                <id_sessione_sso />
                <id_sessione_aspnet_sso />
                <stilesheet>AuthRestriction='.$this->authRestriction.($this->eIDAS? ';'.self::EIDAS_FLAG : '').($this->SPIDProPurpose ? ';'.self::PURPOSE_FLAG.$this->SPIDProPurpose: '').'</stilesheet>
                <AuthRestriction xmlns="">'.$this->authRestriction.'</AuthRestriction>
            </auth>
        </dsAuth>';
        //file_put_contents('log.txt',$xmlAuth."\n",FILE_APPEND);
        $auth = urlencode(base64_encode($xmlAuth));
        if($this->saml20){
            $urlLogin = self::COHESION2_SAML20_CHECK.$auth;
        }
        else{
            $urlLogin = ($this->sso) ? self::COHESION2_CHECK.$auth : self::COHESION2_LOGIN.$auth;
        }
        //file_put_contents('log.txt',$urlLogin."\n",FILE_APPEND);
        header("Location: $urlLogin");
        exit;
    }
    
    private function verify($auth){
        //file_put_contents('log.txt',__METHOD__."\n",FILE_APPEND);
        $xml = trim(base64_decode($auth));
        //file_put_contents('log.txt',$xml."\n",FILE_APPEND);
        $domXML = new \DOMDocument;
        $domXML->loadXML($xml);
        $this->id_sso = $domXML->getElementsByTagName('id_sessione_sso')->item(0)->nodeValue;
        $this->id_aspnet = $domXML->getElementsByTagName('id_sessione_aspnet_sso')->item(0)->nodeValue;
        $this->username = $domXML->getElementsByTagName('user')->item(0)->nodeValue;
        $esito = $domXML->getElementsByTagName('esito_auth_sso')->item(0)->nodeValue;
        if($esito!='OK') return false;
        
        //file_put_contents('log.txt',"Recupero profilo tramite pagina web\n",FILE_APPEND);
        $data = ['Operation'=>'GetCredential','IdSessioneSSO'=>$this->id_sso,'IdSessioneASPNET'=>$this->id_aspnet];
        $context  = stream_context_create([
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
            'ssl' => [
                'ciphers' => 'DEFAULT:!DH'
            ]
        ]);
        
        $result = file_get_contents($this->saml20 ? self::COHESION2_SAML20_WEB : self::COHESION2_WEB,false,$context);
        $domXML->loadXML($result);
        //file_put_contents('log.txt',var_export($result,1)."\n",FILE_APPEND);
        $profilo = simplexml_import_dom($domXML);
        $base = current($profilo->xpath('//base'));
        if($base->login){
            $resp = [];
            foreach($base->children() as $node){
                $resp[$node->getName()] = (string)$node;
            }
            $this->profile = $resp;
            $_SESSION[self::SESSION_NAME] = serialize($this);
            return true;
        }
        else throw new Exception('Impossibile recuperare il profilo utente da Cohesion2');
    }
}

class Cohesion2Exception extends \Exception{}