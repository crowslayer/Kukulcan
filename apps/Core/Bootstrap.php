<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core;

use Phalcon\Mvc\ModuleDefinitionInterface,
    Phalcon\DiInterface as DI;

/**
 * Description of Bootstrap
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
abstract class Bootstrap implements ModuleDefinitionInterface {
    /**
     *
     * @var DiInterface
     */
    private $_dependencyInjector;
    /**
     *
     * @var EventsManager
     */
    private $_eventsManager;
    /**
     *
     * @var string
     */
    private $_path;
    /**
     *
     * @var string
     */
    private $_root;
    /**
     * Configuracion del boostrap o modulo
     * @var Config
     */
    private $_config;
    /**
     * Nombre del modulo
     * @var String
     */
    protected $_moduleName = "";
    
    public function __construct(DI $di, $eventsManager, $path, $root, $name){
        $this->setDI($di);
        $this->setEventsManager($eventsManager);
        $this->setModuleName($name);
        $this->setRoot($root);
        $this->setModulePath($path);
        
        if(method_exists($this, 'initialize')){
            $this->initialize();
        }
    }
    /**
     * Registra autocargadores de ser necesario
     * @param DI $di
     */
    public function registerAutoloaders(DI $di = null){
        
    }
    /**
     * Registra servicio del modulo
     * @param DI $di
     */
    public function registerServices(DI $di = null){
        
    }
    /**
     * Establece el injector de dependencias
     * @param DI $di
     * @return $this
     */
    public function setDI(DI $di){
        $this->_dependencyInjector = $di;
        return $this;
    }
    /**
     * Devuelve el injector de dependencias
     * @return DiInterface
     */
    public function getDI(){
        return $this->_dependencyInjector;
    }
    /**
     * Establece el directorio principal del modulo
     * @param string $root
     * @return $this
     */
    public function setRoot($root){
        $this->_root = $root;
        return $this;
    }
    /**
     * Retorna el directorio del modulo
     * @return string
     */
    public function getRoot(){
        return $this->_root;
    }
    /**
     * Establece el manejador de eventos
     * @param Event $eventsManager
     */
    public function setEventsManager($eventsManager){
        $this->_eventsManager = $eventsManager;
    }
    /**
     * Retorna el manejador de eventos
     * @return Event
     */
    public function getEventsManager(){
        return $this->_eventsManager;
    }
    /**
     * Establece la configuracion del modulo
     * @param Config $config
     * @return $this
     */
    public function setConfig($config){
        $this->_config = $config;
        return $this;
    }
    /**
     * Retorna la configuracion almcenada.
     * @return mixed
     */
    public function getConfig(){
        return $this->_config;
    }
    /**
     * Establece la ruta del modulo
     * @param string $path
     * @return $this
     */
    public function setModulePath($path){
        $this->_path = $path;
        return $this;
    }
    /**
     * Retorna la ruta del modulo
     * @return string
     */
    public function getModulePath()
    {
        return $this->_path;
    }
    /**
     * Retorna el nombre del modulo
     * @return string
     */
    public function getModuleName(){
        return $this->_moduleName;
    }
    /*
     * Establece el nombre del modulo.
     */
    public function setModuleName($name){
        $this->_moduleName = $name;
        return $this;
    }
    
}
