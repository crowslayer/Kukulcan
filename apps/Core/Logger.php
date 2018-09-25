<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core;

use Phalcon\Config as PhalconConfig;
use Phalcon\Logger\AdapterInterface as LoggerInterface;

/**
 * Description of Logger
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
abstract class Logger {
    /*
     * Directorio por defecto de los logs
     */
    const LOGGER_DIRECTORY = '/var/logs/';
    /**
     * Almacena la configuracion
     * @var Config
     */
    protected $_config;
    /**
     * Contenedor de logs clases
     * @var array
     */
    protected $_container = [];
    /**
     * Establece la configuracion
     * @param PhalconConfig $config
     * @return $this
     */
    public function setConfig(PhalconConfig $config){
        $this->_config = $config;
        return $this;
       
    }
    /**
     * Devuelve la configuracion
     * @return mixed
     */
    public function getConfig(){
        return $this->_config;
    }
    /**
     * Devuelve los loggers registrados
     * @return array
     */
    public function getLoggers(){
        return $this->_container;
    }
    /**
     * Comprueba si esta registrado el contenedor.
     * 
     * @param string $identifier
     * @return boolean
     */
    public function hasLogger($identifier){
        $loggers = $this->getLoggers();
        
        return array_key_exists($identifier, $loggers);
        
    }
    /**
     * Devuelve el logger solicitado, si existe en el contenedor, o 
     * false en caso de error.
     * 
     * @param string $identifier
     * @return mixed
     */
    public function getLogger($identifier){
        if($this->hasLogger($identifier)){
            $loggers = $this->getLoggers();
            
            return $loggers[$identifier];
            
        }
        
        return false;
    }
    /**
     * Registra el logger.
     * 
     * @param LoggerInterface $logger
     * @param string $identifier
     * @return $this
     */
    public function registerLogger(LoggerInterface $logger , $identifier = 'default'){
        /*
         * Sino se proporciona identificador se toma el valor por defecto
         */
        if(!$identifier){
            $identifier = 'default';
        }
        /*
         * Registrando en contenedor.
         */
        $this->_container[$identifier] = $logger;
        
        return $this;
    }
    
    
}
