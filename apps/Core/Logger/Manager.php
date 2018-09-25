<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Logger;

use Phalcon\Logger\AdapterInterface as LoggerInterface;

use Kukulcan\Core\Logger as CoreLogger;

/**
 * Description of Manager
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Manager extends CoreLogger {
    /*
     * Adaptador por defecto a usar
     */
    const DEFAULT_ADAPTER = 'Files';
    /**
     * Construya el manager
     * @param mixed $config
     */
    public function __construct($config = null) {
        if(!is_null($config)){
            $this->setConfig($config);
        }
        
        $this->_prepare();
    }
    /**
     * Registra los adaptadores en el contenedor
     * @return boolean
     */
    protected function _prepare(){
        /*
         * Recupera la configuracion
         */
        $config = $this->getConfig();
        /*
         * Sino existe devuelve false
         */
        if(!$config){
            return false;
        }
        /*
         * Comprobando que existe el campo loggers
         */
        if($config->offsetExists('loggers')){
            /*
             * Convirtiendo a array.
             */
            $loggers = $config->loggers->toArray();
        }
        /*
         * Recorriendo loggers
         */
        foreach($loggers as $logger){
            /*
             * Recuperando valores
             */
            $adapter = ucfirst(strtolower(array_key_exists('adapter', $logger) ? $logger['adapter'] : self::DEFAULT_ADAPTER));
            $options = array_key_exists('options', $logger) ? $logger['options'] : null;
            $identifier = array_key_exists('identifier', $logger) ? $logger['identifier'] : 'default';
            /*
             * Creando el nombre de la clase
             */
            $className = sprintf("%s\\Adapter\\%s",__NAMESPACE__ , $adapter);
            /*
             * Comprobando que existe la clase
             */
            if(!class_exists($className)){
                /*
                 * Sino existe se llama al framework phalcon
                 */
                $phalconClass = "\\Phalcon\\Logger\\Adapter\\".$adapter;
                /*
                 * Comprobando que exista en el phalcon el adaptador,
                 * en caso contrario continua
                 */
                if(class_exists($phalconClass)){
                    $className = $phalconClass;

                }else{
                    continue;
                }
            }
            /*
             * Creando una instancia del adaptador
             */
            $logAdapter = new $className($options);
            /*
             * Comprobando que sea una instancia de loggerInterface
             */
            if(!$logAdapter instanceof LoggerInterface){
                continue;
            }
            /*
             * Registrando logger.
             */
            $this->registerLogger($logAdapter, $identifier);
            
        }
        
    }
}
