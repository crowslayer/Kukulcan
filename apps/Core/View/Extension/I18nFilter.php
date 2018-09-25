<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\View\Extension;


/**
 * Description of I18nFilter
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class I18nFilter{
    const DEFAULT_NAME = 'i18n';
    /**
     * Agrega el filtro para internacinalizacion de los idiomas.
     * 
     * @param Volt\Compiler $compiler
     * @param array $config
     */
    public static function addCompiler($compiler , $config){
        /*
         * Comprobando si no es un array
         */
        if(!is_array($config) || count($config) == 0){
            $nameExtension = self::DEFAULT_NAME;
        }
        /*
         * Obteniendo el nombre de la funcion/filtro
         */
        $nameExtension = (array_key_exists('objectName', $config)) ? $config['objectName'] : self::DEFAULT_NAME;
        
        $compiler->addFilter($nameExtension,'$this->i18n->query');
        
    }
}
