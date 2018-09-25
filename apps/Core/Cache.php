<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core;

use Phalcon\Config as PhalconConfig;
use Phalcon\Cache\FrontendInterface as FrontendCacheInterface;
use Phalcon\Cache\BackendInterface as BackendCacheInterface;

/**
 * Description of Cache
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
abstract class Cache {
    /*
     * Almacena la configuracion
     */
    protected $_config;
    /*
     * Contenedor de cache de tipo frontend
     */
    protected $_frontendsCache = [];
    /*
     * Contenedor de cache tipo backend
     */
    protected $_backendsCache = [];
    
    /**
     * Establece la configuracion.
     * @param PhalconConfig $config
     * @return $this
     */
    public function setConfig(PhalconConfig $config){
        $this->_config = $config;
        return $this;
    }
    /**
     * Retorna la configuracion almacenada
     * @return mixed
     */
    public function getConfig(){
        return $this->_config;
    }
    /**
     * Retorna el contenedor de la cache de tipo frontend.
     * @return mixed
     */
    public function getFrontendsCache(){
        return $this->_frontendsCache;
    }
    /**
     * Almacena en el contenedor una objeto de tipo cache.
     * 
     * @param FrontendCacheInterface $frontend
     * @param string $identifier
     * @return $this
     * @throws Exception
     */
    public function registerFrontendCache(FrontendCacheInterface $frontend, $identifier){
       if($identifier == false){
            throw new Exception('an identifier is required for the cache');
        }
        
        $this->_frontendsCache[$identifier] = $frontend;
        
        return $this;
    }
    /**
     * Comprueba si existe el objeto en el contenedor de tipo frontend,
     * tomando como parametro el identificador.
     * 
     * @param string $identifier
     * @return boolean
     */
    public function getFrontendCacheByIdentifier($identifier){
        if(!$this->hasFrontendCache($identifier)){
            return false;
        }
        
        $frontends = $this->getFrontendsCache();
        
        return $frontends[$identifier];
        
    }
    /**
     * Comrpueba si existe el objeto en el contenedor.
     * @param string $identifier
     * @return boolean
     */
    public function hasFrontendCache($identifier){
        return array_key_exists($identifier, $this->getFrontendsCache());
    }
    /**
     * Retorna el contenedor de los objetos de tipo backedn cache.
     * @return mixed
     */
    public function getBackendsCache(){
        return $this->_backendsCache;
    }
    /**
     * Almacena en el contenedor, un objeto de tipo backendcache.
     * 
     * @param BackendCacheInterface $backend
     * @param string $identifier
     * @return $this
     * @throws CoreException
     */
    public function registerBackendCache(BackendCacheInterface $backend , $identifier){
        
        if($identifier == false){
            throw new CoreException();
        }
                
        $this->_backendsCache[$identifier] = $backend;

        return $this;
    }
    /**
     * Comprueba si se encuentra registrado el backendCache solicitiado.
     * 
     * @param string $identifier
     * @return boolean
     */
    public function hasBackendCache($identifier){
        return array_key_exists($identifier, $this->getBackendsCache());
    }
    /**
     * Retorna el objeto de tipo backendcache soliticado o false en caso de
     * no existir.
     * 
     * @param string $identifier
     * @return BackendCache
     */
    public function getBackendCache($identifier){
        if($identifier == FALSE){
            return false;
        }
        
        if(!$this->hasBackendCache($identifier)){
            return false;
        }
        
        $backends = $this->getBackendsCache();
        
        return $backends[$identifier];
    }
    
}
