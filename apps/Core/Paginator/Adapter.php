<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Paginator;

/**
 * Description of Adapter
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
abstract class Adapter implements AdapterInterface {
    /**
     * Number of rows to show in the paginator. By default is null
     */
    protected $_limitRows = null;

    /**
     * Current page in paginate
     */
    protected $_page = null;
    /**
     * Almacena el adaptador para transdormar los datos.
     * 
     * @var Tranformer
     */
    protected $_transformer = null;


    /**
     * Set the current page number
     * @param int $page
     * @return $this
     */
    public function setCurrentPage($page){
        /*
         * Agregando numero de pagina
         */
        $this->_page = (int)$page;
        
        return $this;
    }

    /**
     * Set current rows limit
     * 
     */
    public function setLimit($limit){
        /*
         * Agregando limite
         */
        $this->_limitRows = (int) $limit;
        
        return $this;
    }
    
    /**
     * Get current rows limit
     * return integer
     */
    public function getLimit(){
        return $this->_limitRows;
    }
    /**
     * Establece el transformer para los datos.
     * 
     * @param mixed $transformer
     * @return $this
     */
    public function setTransformer($transformer){
        $class = null;
        /*
         * Comprobando si es un string
         */
        if(is_string($transformer)){
            if(class_exists($transformer)){
                $class = new $transformer;
            }
        }
        /*
         * Comprobando si es una instancia de transformer
         */
        if(is_object($transformer)){
            $class = $transformer;
        }
        /*
         * Estableciendo parametro
         */
        $this->_transformer = $class;
        
        return $this;
    }
    /**
     * Retorna el transformador para los datos.
     * @return mixed
     */
    public function getTransformer(){
        return $this->_transformer;
    }
}
