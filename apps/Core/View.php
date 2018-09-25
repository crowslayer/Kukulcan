<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core;

use Phalcon\Mvc\View as PhalconView;
use Phalcon\Mvc\View\Engine\Volt; 

//use Kukulcan\Core\View\Extension;

/**
 * Description of View
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class View extends PhalconView {
    
    public static function factory($di, $config, $moduleDir, $em = null, $module){  
        $options = [];
        
        $view = new View();
        
        $module = strtolower($module);
        $viewConfig = $config->get('view');
        
        if($viewConfig){
            $options = ($viewConfig->get('options')) ? $viewConfig->get('options')->toArray() : [] ;
        }else{
            $options = [
            'compiledPath' => '/var/views/',
            'compiledExtension' => '.php',
            'compiledSeparator' => '_',
            'stat' => TRUE,
            'compileAlways' => false,
            'prefix' => 'dtx_',
            'autoescape' => FALSE,
            ];
        }
        
        $path = ROOT_PATH . DS . $options['compiledPath']. DS . $module . DS; 
        $path = str_replace(['\\\\','\/','/\\','//','\//','\\'], DS, $path);
        
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        /*
         * Recuperando opciones
         */
        $optionsViews = $options;
        /*
         * Recuperando directorio donde se compilan las vistas
         */
        $optionsViews['compiledPath'] = $path;
        /*
         * Recuperando extensiones si existen
         */
        $extensions = ($viewConfig->offsetExists('extensions')) ? $viewConfig->get('extensions')->toArray() : false;
        /*
         * Creando instancia de motor volt y agregando opciones.
         */
        $volt = new Volt($view ,$di);
        $volt->setOptions($optionsViews);
        /*
         * Registrando motores de plantillas
         */
        $view
            ->registerEngines([".volt" => $volt,
                               '.phtml' => '\Phalcon\Mvc\View\Engine\Php'])
            //estableciendo directorio de vistas.
            ->setViewsDir($moduleDir);
        /*
         * Comprobando si hay extensiones establecidas
         */
        if ($extensions && count($extensions > 0)) {
            /*
             * Recorriendo el arreglo debe ser la clase o filtro
             * como campo llave y la configuracion adicional como 
             * el valor en caso de ser necesario.
             * $class => $configClass
             */
            foreach ($extensions as $class => $config){
                /*
                 * Creando clase
                 */
                $class = sprintf("%s\Extension\%s", get_called_class(),$class);
                /*
                 * Comprobando si existe la clase
                 */
                if(!class_exists($class)){
                    continue;
                }
                /*
                 * Obteniendo al compiler
                 */
                $compiler = $volt->getCompiler();
                /*
                 * Agregando clase al compiler
                 */
                $class::addCompiler($compiler, $config);
                
            }
            
        }

        if($em){
            $view->setEventsManager($em);
            
        }
        
        return $view;
    }
}
