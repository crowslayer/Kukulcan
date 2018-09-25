<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core;

use Phalcon\Loader as PhalconLoader;

/**
 * Description of Loader
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Loader extends PhalconLoader {
    
   public function autoLoad($className){
        $parts = explode('\\', $className);
        $classFile = array_pop($parts);
        $classPath = implode('\\', $parts);
         
        $rootPath = false;
        
        if (array_key_exists($classPath, $this->_namespaces)) { 
            $rootPath = $this->_namespaces[$classPath];
            
            // phalcon 3.0
            if(is_array($rootPath)){
                $rootPath = $rootPath[0];
            }
            
        } else if (array_key_exists($parts[0], $this->_namespaces)) {
            
            $rootPath = $this->_namespaces[$parts[0]];
            
            //phalcon 3.0
            if(is_array($rootPath)){
                $rootPath = $rootPath[0];
            }
            
            $rootPath = $rootPath . implode(DS, array_slice($parts, 1)) . DS;
            
        }
        
        if (false !== $rootPath) {
                       
            $filePath = $rootPath . $classFile;
            
            foreach ($this->_extensions as $ext) {
                
                if (file_exists($filePath . '.' . $ext)) {
                    
                    require ($filePath . '.' . $ext);
                    
                    return true;
                }
            }
        }
        
        parent::autoLoad($className);
    }
}
