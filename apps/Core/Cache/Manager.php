<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Cache;

use Phalcon\Cache\FrontendInterface as PhalconFrontendCache; 
use Phalcon\Cache\BackendInterface as PhalconBackendCache;

use Kukulcan\Core\Cache as CoreCache;
/**
 * Description of Manager
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Manager extends CoreCache {
    
    public function __construct($config = null) {
        if(!is_null($config)){
            $this->setConfig($config);
        }
        
        $this->initialize();
    }
    
    public function initialize(){
        $this->_initFrontendCache();
        $this->_initBackendCache();
    }
    /**
     * Inicializa el cache de tipo frontend.
     */
    protected function _initFrontendCache(){
        $configs = $this->getConfig();
        
        $frontends = [];
        
        if(is_null($configs)){
            $frontends[] = [
               'adapter' => 'data',
               'identifier' => 'default',
               'options' => [
                        'lifetime' => 86400,
                    ],
               ]; 
        }elseif(!$configs->offsetExists('frontends') || $configs->frontends == false ){
           $frontends[] = [
               'adapter' => 'data',
               'identifier' => 'default',
               'options' => [
                        'lifetime' => 86400,
                    ],
               ]; 
        }else{
            $frontends = $configs->frontends->toArray();
        }
        
        foreach ($frontends as $frontend){
            $adapter = array_key_exists('adapter',$frontend) ? ucfirst(strtolower($frontend['adapter'])) : null;
            $options = array_key_exists('options', $frontend) ? $frontend['options']:null;
            $identifier = array_key_exists('identifier', $frontend) ? $frontend['identifier']:$frontend['adapter'];
            
            $class = __NAMESPACE__. '\\Frontend\\Adapter\\'.$adapter;
            
            if(class_exists($class)){
                $cacheFrontend = new $class($options);
            }else{
                $phalconClassCache = "\\Phalcon\\Cache\\Frontend\\". $adapter;
                
                if(!class_exists($phalconClassCache)){
                    continue;
                }
                
                $cacheFrontend = new $phalconClassCache($options);
            }
            
            if($cacheFrontend instanceof PhalconFrontendCache){
                //store the class in the container to be called according to the needs
                $this->registerFrontendCache($cacheFrontend, $identifier);
            }
            
        }
        
                
    }
    /**
     * Inicializa el cache de tipo backend
     */
    protected function _initBackendCache(){
        $configs = $this->getConfig();
        $backends = [];
        
        if(is_null($configs)){
            $backends[] = [
                'adapter' => 'file',
                'options' => [
                        'prefix' => 'CSRV_',
                        'cacheDir' => '/cache/',
                    ],
                'identifier' => 'default',
                'frontendIdentifier' => 'default',
                
            ];
        }elseif(!$configs->offsetExists('backends') || $configs->backends == false){
            
            $backends[] = [
                'adapter' => 'file',
                'options' => [
                        'prefix' => 'CSRV_',
                        'cacheDir' => '/cache/',
                    ],
                'identifier' => 'default',
                'frontendIdentifier' => 'default',
                
            ];
        }else{
            $backends = $configs->backends->toArray();
        }
        
        foreach ($backends as $backend){
            $adapter = array_key_exists('adapter', $backend) ? ucfirst(strtolower($backend['adapter'])) : null;
            $identifier = array_key_exists('identifier', $backend) ? $backend['identifier'] : $backend['adapter'];
            $options = array_key_exists('options', $backend) ? $backend['options'] : null;
            $frontendIdentifier = array_key_exists('frontendIdentifier', $backend) ? $backend['frontendIdentifier'] : 'default';
            
            if(array_key_exists('cacheDir', $options)){
                $cacheDir = ROOT_PATH . $options['cacheDir'];
                
                if(!is_dir($cacheDir)){
                    @mkdir($cacheDir, 0755, true);
                }
            }
            
            $className = __NAMESPACE__ .'\\Backend\\Adapter\\' . $adapter;
            
            if($this->hasFrontendCache($frontendIdentifier)){
                $frontendCache = $this->getFrontendCacheByIdentifier($frontendIdentifier);
            }else{
                continue;
            }
            
            //priority is given to the class of the application, otherwise the framework is used
            if(class_exists($className)){
                $backendCache = new $className($frontendCache, $options);
            }else{
               $phalconClassCache = "\\Phalcon\\Cache\\Backend\\". $adapter;
               
               if(!class_exists($phalconClassCache)){
                    continue;
                }
                
                $backendCache = new $phalconClassCache($frontendCache, $options);
                
            }
            
            if($backendCache instanceof PhalconBackendCache){
                //store the class in the container to be called according to the needs
                $this->registerBackendCache($backendCache, $identifier);
                
            }
            
        }
        
        
    }
}
