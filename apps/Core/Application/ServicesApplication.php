<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Application;

use Phalcon\Di\FactoryDefault as PhalconDI;
use Phalcon\DiInterface;
//use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Group as RouterGroup;
use Kukulcan\Core\Router;
use Kukulcan\Core\Config as CoreConfig;
use Kukulcan\Core\Constant\Services as CoreServices;
use Kukulcan\Core\Logger\Manager as LoggerManager;
use Kukulcan\Core\Cache\Manager as CacheManager;

/**
 * Description of ServicesApplication
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class ServicesApplication {
    const MODULES_PATH = "/Modules";
    /**
     *
     * @var Kukulcan\Core\Config.
     */
    protected $_config;
    /**
     *
     * @var Phalcon\DiInterface
     */
    protected $_dependencyInjector;
    /**
     * 
     * @param PhalconDI $di
     * @param Kukulcan\Core\Config $config
     */
    public function __construct($di = null , $config) {
        if(!$di instanceof DiInterface){
            $di = new PhalconDI();
            
        }
        /*
         * Estableciendo injector de dependencias
         * y configuraciones.
         */
        $this->setConfig($config);
        $this->setDI($di);
        /*
         * Comprobando si existe el metodo initialize se ejecuta.
         */
        if(method_exists($this, "initialize")){
            $this->initialize();
        }
    }
    /**
     * Establece el Injector de Dependencias
     * @param DiInterface $di
     * @return $this
     */
    public function setDI(DiInterface $di){
        $this->_dependencyInjector = $di;
        
        return $this;
    }
    /**
     * Retorna el injector de dependencias
     * @return DiInterface
     */
    public function getDI(){
        return $this->_dependencyInjector;
    }
    /**
     * Retorna la configuraciones almacenadas
     * @return Config
     */
    public function getConfig(){
        return $this->_config;
    }
    /**
     * Establece las configuraciones.
     * 
     * @param Config $config
     */
    public function setConfig($config){
        $this->_config = $config;
    }
    /**
     * Inicializa las configuraciones y las establce.
     */
    protected function _initConfig(){
        /*
         * Crea una instancia de las configuraciones
         * leyendo el contenido de la carpeta Config.
         */
        $config = CoreConfig::factory();
        /*
         * Establece las configuraciones
         */
        $this->setConfig($config);
    }
    /**
     * Iniciaiza el manejador de logs del sistema.
     */
    protected function _initLogger(){
        /*
         * recuperando configuraciones
         */
        $config = $this->getConfig();
        /*
         * Recuperando configuracion del logger
         */
        $config = $config->get('Logger');
        /*
         * Recuperando injector de dependencias
         */
        $di = $this->getDI();   
        /*
         * Creando instancia del manajedarod de logs con las configuraciones establecidas.
         */
        $manager = new LoggerManager($config);
        /*
         * Agregando el sevicio al injector de dependencias.
         */
        $di->setShared(CoreServices::LOGGER_MANAGER, $manager);
        
    }
    /**
     * Iniciando el manejador de cache.
     */
    protected function _initCache(){
        $cacheConfig = null;
        /*
         * Recuperando configuraciones
         */
        $config = $this->getConfig();
        $config = $config->get('Cache');
        /*
         * Recuperando injector de dependencias
         */
        $di = $this->getDI();
        /*
         * Comprobando que existe la configuracion
         */        
        if($config){
            if($config->offsetExists('cache')){
                $cacheConfig = $config->cache;
            }
        }
        /*
         * Registrando el servicio en el injector de dependencias.
         */
        $di->setShared(CoreServices::CACHE_MANAGER, function() use ($cacheConfig) {
            
            $cacheManager = new CacheManager($cacheConfig);
            
            return $cacheManager;
            
        });
        
        
    }
    /**
     * Creando el componente router
     * @return boolean
     */
    protected function _initRouter(){
        $di = $this->getDI();
        /*
         * Recuperando configuracion
         */
        $config = $this->getConfig();
        /*
         * Recuperando configuracion general de los modulos
         */
        $modulesConfig = $config->get('Modules');
        /*
         * Recuperando campo modulos
         */
        $modules = $modulesConfig->get('modules');
        /*
         * Sino existem modulos en la configuracio retorna false.
         */
        if(!$modules){
            return false;
        }
        /*
         * Recuperando modulo por defecto
         */
        $defaultRouting = $modules[$modulesConfig->defaultModule]->default;
        /*
         * Creando instancia del router,
         * Removiendo slashes extra y
         * estableciendo el modulo por defecto
         */
        $router = new Router();
        $router->removeExtraSlashes(true);
        //$router->setDefaults((array)$defaultRouting);
        /*
         * Recuperando directorio de modulos.
         */
        $modulesDirectory = $modulesConfig->offsetExists('modulesDirectory') ? APPS_PATH . $modulesConfig->get('modulesDirectory') : APPS_PATH . self::MODULES_PATH ;
        /*
         * Eliminando slaches extra
         */
        $modulesDirectory = str_replace(array('/','\/','\\','////','\\\\') , DS, $modulesDirectory);
        /*
         * Recorriendo modulos
         */
        foreach ($modules as $module) {
            /*
             * Obteniendo directorio del modulos
             */
            $path = $modulesDirectory . $module->moduleDirectory;
            
            $route = $module;
            
            /*
             * Recorriendo path del modulo
             */
            $find = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path) , \RecursiveIteratorIterator::SELF_FIRST);
            /*
             * Recorriendo directorios
             */
            foreach ($find as $key => $obj) {
                /*
                 * Obteniendo nombre del archivo leido
                 */
                $file = $obj->getFilename();
                /*
                 * Si es bootstrap.php
                 */
                if ($file == 'Bootstrap.php') {
                    /*
                     * Llamando al namespace
                     */
                    $namespace = $module->namespace . '\\Controllers';
                    /*
                     * Obteniendo nombre del modulo
                     */
                    $modname = \str_replace('\\Controllers', '', $namespace);
                    /*
                     * Recuperando prefijo del grupo o modulo
                     */
                    $prefix = strtolower(preg_replace('/\/$/', '', $route->prefix)  );
                    /*
                     * Creando nuevo grupo
                     */
                    $group = new RouterGroup(array(
                        'module' => $route->default->module,
                        'namespace' => $namespace,
                        
                    ));
                    /*
                     * Agregando el prejido del modulo
                     */
                    $group->setPrefix($prefix);
                    $router->setPrefixModule($prefix,$route->default->module);
                    /*
                     * Agregando ruta por defecto
                     */
                    $group->add('', array(
                        'controller' => 'index',
                        'action' => 'index'
                    ));
                    $group->add('/', array(
                        'controller' => 'index',
                        'action' => 'index'
                    ));
                    /*
                     * Agregando rutas
                     */
                    $group->add('/:controller', array(
                        'controller' => 1,
                        'action' => 'index'
                    ));
                    $group->add('/:controller/:action', array(
                        'controller' => 1,
                        'action' => 2
                    ));
                    $group->add('/:controller/:action/:params', array(
                        'controller' => 1,
                        'action' => 2,
                        'params' => 3
                    ));
                    /*
                     * Montando groups
                     */
                    $router->mount($group);
                    
                }
            } 
        }
        /*
         * Estableciendo servicio en el inyector de dependencias
         */        
        $di->set(CoreServices::ROUTER, $router);
        
    }
    /**
     * Inicializa los servicios de la applicacion.
     * 
     * @param DiInterface $di
     * @param Config $config
     * @return \self
     */
    public static function initializeServices($di = null, $config){
        if(!$di instanceof DiInterface){
            $di = new PhalconDI();
        }
        $services = new self($di , $config);
        
        return $services;
    }
    /**
     * Inicializ los servicios estipulados.
     */
    public function initialize(){
        $this->_initLogger();
        $this->_initCache();
        $this->_initRouter();
    }
}
