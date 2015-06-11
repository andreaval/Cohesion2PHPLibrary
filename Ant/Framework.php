<?php
namespace Ant;

/**
 * Ant PHP Framework
 * Compatible PHP 5.4, 5.5, 5.6
 * 
 * @package Ant
 * @version 4.10
 * @license MIT License <http://opensource.org/licenses/MIT>
 * @author Andrea Vallorani <andrea.vallorani@gmail.com>
 * @copyright 2006-2015 Andrea Vallorani
 * @todo PSR-1, PSR-2, PSR-3
 */
class Framework{
    
    /**
     * Version number
     */ 
    const VERSION = 4;
    
    /**
     * Revision number
     */
    const REVISION = 10;
    
    /**
     * Lingua di riferimento del framework
     * @var string
     */
    static private $lang = 'en';
    
    /**
     * Codifica usata dal framework
     * @var string
     */
    static private $charset = 'utf-8';
    
    /**
     * Imposta o legge la lingua del framework
     * @param string $lang Codice ISO a 2 lettere
     * @return string Lingua
     */
    static public function lang($lang=null){
        if($lang) self::$lang = $lang;
        return self::$lang;
    }
    
    /**
     * Imposta o legge la codifica del framework
     * @param string $charset Codifica (utf-8, iso-8859-1, ecc.)
     * @return string Codifica
     */
    static public function charset($charset=null){
        if($charset) self::$charset = $charset;
        return self::$charset;
    }
    
    /**
     * Attivando la modalità 'migrate', i nomi delle classi di AV3 vengono 
     * riconoscitui e tradotti nelle nuove classi. Tuttavia alcuni metodi 
     * potrebbe non essere più presenti nelle classi tradotte
     */
    static function migrate(){
        spl_autoload_register(function ($class_name){
            $prefix=substr($class_name,0,3);
            if($prefix=='AV_'){
                $filename = __DIR__. '/_migrate/'.$class_name.'.php';
                if(file_exists($filename)) require $filename;
            }
        });
    }
    
    /**
     * Imposta se PHP deve mostrare o meno gli errori
     * @param int $error Se mostrare o meno gli errori
     * @param bool $warning Se mostrare o meno i warning
     * @param bool $notice Se mostrare o meno le notice
     */
    static function errors($error=true,$warning=true,$notice=true){
        if($error){
            if($notice) error_reporting(E_ALL);
            elseif($warning) error_reporting(E_ALL ^ E_NOTICE);
            else error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
            ini_set('display_errors',1);
        }
        else{
            error_reporting(E_ERROR);
            ini_set('display_errors',0);
        }
    }
}