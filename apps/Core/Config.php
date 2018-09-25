<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core;

use Phalcon\Config as PhalconConfig;

/**
 * Description of Config
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Config extends PhalconConfig{
    const
        /**
         * Archivos de Configuracion
         */
        CONFIG_PATH = 'Config';

        
    public function __construct($config = []) {
        parent::__construct($config);
    }
    /**
     * 
     * @param type $configDir
     * @return type
     */
    public static function factory($configDir = null) {
        $config = self::_populate($configDir);
        
        return $config;
    }
    
    /**
     * Recorre el directorio de configuraciones almacenando todos los archivos
     * en el mismo.
     * 
     * @param string $configDir
     * @return Config
     */
    protected static function _populate($configDir) {
        if(!is_null($configDir)){ 
            $path = $configDir;
            
        }else{
            $path = APPS_PATH . self::CONFIG_PATH;
            
        }
        
        $config = new Config([]);
        
        foreach (scandir($path) as $file) {
            if ($file == '.' || $file == '..') continue;
            
            $data = include_once ($path . '/' . $file);
            
            if(is_array($data) && !empty($data)){
                $data = new PhalconConfig($data);
                
            }
            
            if($data instanceof PhalconConfig){
                $config->offsetSet(basename($file, '.php') , $data);

            }
        }
              
        return $config;
    }
}
