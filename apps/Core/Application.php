<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core;

//Phalcon
use Phalcon\DiInterface;
use Phalcon\Config as PhalconConfig;
use Phalcon\Events\Manager as EventsManager;

use Kukulcan\Core\Logger\Adapter\Files as LoggerFiles;
use Kukulcan\Core\Application\MvcApplication;
use Kukulcan\Core\Application\ServicesApplication;
/**
 * Description of Application
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Application {
    /*
     * Ruta para los modulos
     */
    const MODULES_PATH = "/Modules";
    /**
     * Almacena la configuraciones
     * @var Phalcon\Config
     */
    protected $_config;
    /**
     * Almacena la aplicacion
     * @var Kukulcan\Core\Application\MvcApplication
     */
    protected $_application;
    /**
     * Almacena el inyector de dependencias.
     * @var Phalcon\DiInterface
     */
    protected $_dependencyInjector;
    /*
     * almacena el loader
     */
    protected $_loader;
    
    public function __construct() {
        
        if(method_exists($this, "initialize")){
            $this->{"initialize"}();
        }
    }
    /**
     * Establece el inyecto de dependencias.
     * @param DiInterface $di
     * @return $this
     */
    public function setDI(DiInterface $di){
        $this->_dependencyInjector = $di;
        
        return $this;
    }
    /**
     * Retorna el inyector de dependencias.
     * @return DiInterface
     */
    public function getDI(){
        return $this->_dependencyInjector;
    }
    /**
     * Establece las configuraciones en el contenedor.
     * 
     * @param PhalconConfig $config
     * @return $this
     */
    public function setConfig(PhalconConfig $config){
        $this->_config = $config;
               
        return $this;
    }
    /**
     * retorna las configuraciones almacenadas.
     * @return Phalcon\Config
     */
    public function getConfig(){
        return $this->_config;
    }
    /**
     * Establece la aplicacion a utilizar
     * @param MvcApplication $application
     * @return $this
     */
    public function setApplication(MvcApplication $application){
        $this->_application = $application;
        
        return $this;
    }
    /**
     * Retornar la application almacenada.
     * 
     * @return Mvc\Application
     */
    public function getApplication(){
        return $this->_application;
    }
    
    /**
     * Inicializa el autocargador
     */
    protected function _initLoader(){
        $loader = new Loader();
        /*
         * Estableciendo espacio de nombres
         */
        $namespaces = [
            "Kukulcan" => APPS_PATH,
            
        ];
        
        $loader->registerNamespaces($namespaces);
        $loader->register();
        
        $this->_loader = $loader;
    }
    /**
     * Inicializa las configuraciones
     */
    protected function _initConfig(){
        $config = Config::factory();
        $this->setConfig($config);
    }
    /**
     * Inicializa los valores para la applicacion.
     */
    protected function _initEnviroment(){
        //records any warnings/errors
        set_error_handler(function($errorCode, $errorMessage, $errorFile, $errorLine){
            $date = date('Y-m-d'); 
            $filename = "log-system" . $date;
            $id = bin2hex(openssl_random_pseudo_bytes(5));
            
            $templateError = "[%s][Error] Message: %s.  [ ErrorFile: %s  inLine: %s]";
            $errorString = sprintf($templateError, $id, $errorMessage, $errorFile, $errorLine) . PHP_EOL;
                        
            $logger = new LoggerFiles($filename);
            $logger->error($errorString);
            
        });
        
        //records Exception
        set_exception_handler(function($e){
            $date = date('Y-m-d'); 
            $filename = "log-system" . $date;
            $logger = new LoggerFiles($filename);
            
            $id = bin2hex(openssl_random_pseudo_bytes(5));
            
            $templateError = "[%s][Exception]  Message: %s.  [ ErrorFile: %s  inLine: %s]";
            $errorString = sprintf($templateError, $id,$e->getMessage(), $e->getFile(), $e->getLine());
            
            if($e->getTraceAsString()){
                $errorString .= $e->getTraceAsString() . PHP_EOL;
            }else{
                $errorString . PHP_EOL;
            }
            
            $logger->error($errorString);
            
        });
        
    }
    /**
     * Inicializa los servicios de la aplicacion
     * para su uso.
     */
    protected function _initServices(){
        $services = ServicesApplication::initializeServices(null, $this->getConfig());
        $this->setDI($services->getDI());
    }
    /**
     * Inicializa la applicacion MVC
     */
    protected function _initApplication(){
        $application = new MvcApplication($this->getDI());
        $eventsManager = new EventsManager();
        $application->setEventsManager($eventsManager);
        
        $this->setApplication($application);
    }
    /**
     * Inicializa los modulos de la aplicacion.
     * 
     * @throws Exception
     */
    protected function _initModules(){
        $config = $this->getConfig();
        
        $bootstraps = [];
        $namespaces = [];
        
        $modules = $config->get('Modules');
        
        if(!$modules){
            throw new Exception('Not found');
        }
        
        $modulesDirectory = $modules->get('modulesDirectory') ? APPS_PATH . $modules->get('modulesDirectory') : APPS_PATH . self::MODULES_PATH ;
        $modulesDirectory = str_replace(array('/','\/','\\','////','\\\\') , DS, $modulesDirectory);
        
        $modulesConfig = $modules->get('modules');
        
        if(!$modulesConfig){
            throw new Exception('Modules not found');
        }
        
        foreach ($modulesConfig as $name => $module){
            $path = $modulesDirectory . $module->moduleDirectory;
            
            $find = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path) , \RecursiveIteratorIterator::SELF_FIRST);
            
            foreach ($find as $fullPath => $obj){
                if ($obj->getFilename() == 'Bootstrap.php') {
                    
                    $objectPath = $obj->getPath();
                    $objectPath = str_replace(array('\\','/','\/', "\\\\",'////') , DS, $objectPath);
                    
                    $namespaces[$module->namespace] =  $objectPath . DS;
                    $bootstraps[$name] = [
                                            'root' => $name,
                                            'path' => $objectPath,
                                            'moduleName' => $name,
                                            'class' => $module->namespace . "\Bootstrap",
                                          
                                        ];
                }
            }
        }
        
        $this->_loader->registerNamespaces($namespaces, true);
        $this->_loader->register();
        
        $this->_registerModules($bootstraps);
        
    }
    /**
     * Registra los modulos en la aplicacion.
     * @param array $bootstraps
     * @param boolean $merge
     */
    protected function _registerModules(array $bootstraps, $merge = false){
     
        $application = $this->getApplication();
        $di = $this->getDI();
        
        $modules = $application->getModules();
        
        foreach ($bootstraps as $name => $module){
            /*
             * If the module exists in the application, continue the loop
             */
            if (isset($modules[$name])) continue;
            
            $root = $module['root'];
            $className = $module['class'];
            $moduleName = $module['moduleName'];
            $directory = $module['path'];
            
            $bootstrap = new $className($di, $di->getEventsManager() , $directory, $root, $moduleName);
         
            $newModules[$name] = function () use ($bootstrap, $di) {
               
                $bootstrap->registerAutoloaders($di);
                $bootstrap->registerServices($di);
                
                return $bootstrap;
            };
            
        }
        /*
         * Registrando modulos
         */
        $application->registerModules($newModules,true);
       
    }
    /**
     * Inicializa las funcines especificadas.
     */
    public function initialize(){
        
        $this->_initConfig();
        $this->_initLoader();
        $this->_initEnviroment();
        $this->_initServices();
        $this->_initApplication();
        $this->_initModules();
        
    }
    /**
     * Maneja las peticiones a la aplicacion.
     */
    public function handle(){
        $application = $this->getApplication();
       
        echo $application->handle()->getContent();
    }
    /**
     * Corre la aplicacion maneja los eventos.
     */
    public function run(){
        $this->handle();
    }
}
