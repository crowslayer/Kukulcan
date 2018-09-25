<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core;

use Phalcon\Mvc\Router as PhalconRouter;

/**
 * Description of Router
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Router extends PhalconRouter {
    /**
     * Almacena los prefijos de los modulos.
     * @var array
     */
    protected $_prefixModule = [];
    
    /**
     * Agrega el prefixo del modulo al contenedos, especificando
     * el nombre del modulo.
     * 
     * @param string $prefix
     * @param string $moduleName
     * @return $this
     */
    public function setPrefixModule($prefix,$moduleName){
        
        $this->_prefixModule[$moduleName] = $prefix;
        
        return $this;
    }
    /**
     * Recupera el prefix del modulo, tomand como parametro el 
     * nombre del modulo.
     * 
     * @param string $moduleName
     * @return string
     */
    public function getPrefixModule($moduleName){
        if($this->hasPrefix($moduleName)){
            $prefixes = $this->getPrefixes();
            
            return $prefixes[$moduleName];
        }
        return '';
    }
    /**
     * Comprueba si existe el prefixo de acuerdo al nombre del modulo.
     * 
     * @param type $moduleName
     * @return boolean
     */
    public function hasPrefix($moduleName){
        $prefix = $this->getPrefixes();
        
        return array_key_exists($moduleName, $prefix);
    }
    /**
     * Retorna los modulos con sus prefix.
     * 
     * @return array
     */
    public function getPrefixes(){
        return $this->_prefixModule;
    }
    
}
