<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Paginator\Adapter;

use Kukulcan\Core\Paginator\Adapter;
use Kukulcan\Core\Paginator\Exception;
/**
 * Description of NativeArray
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class NativeArray extends Adapter {

    /**
     * Numero de paginas que se mostraran
     * en el rango de paginacion.
     */
    const DEFAULT_PAGES = 5;

    /**
     * Almacena la configuracion de la clase.
     * 
     * @var array
     */
    protected $_config;

    /**
     * Construye la clase NativeArray
     * @param array $config
     */
    public function __construct(array $config) {
        /*
         * Agregando configuracion
         */
        $this->_config = $config;
        /*
         * Comprobando si existe limit
         */
        if (array_key_exists('limit', $config)) {
            $this->setLimit($config['limit']);
        }
        /*
         * Comprobando si existe pagina
         */
        if (array_key_exists('page', $config)) {

            $this->setCurrentPage($config['page']);
        }
        /**
         * Comprobando si existr transformador de datos
         */
        if(array_key_exists('transformer', $config)){
            $this->setTransformer($config['transformer']);
        }
    }
    /**
     * Retorn la clase estandar con los valores o items a paginar.
     * 
     * @return \stdClass
     * @throws Exception
     */
    public function getPaginate() {
        /*
         * Iniciando valores.
         */
        $pagesRange = self::DEFAULT_PAGES;
        $pages = [];
        $transformer = $this->getTransformer();
        /*
         * Recuperando configuracion
         */
        $config = $this->_config;
        /*
         * Comprobando si existe data
         */
        if (!array_key_exists('data', $config)) {
            throw new Exception("Invalid data for paginator");
        }
        /*
         * Recuperando valores
         */
        $show = (int) $config['limit'];
        $items = $config['data'];
        $pageNumber = (int) $this->_page;
        /*
         * Paginas a mostrar
         */
        if (array_key_exists('showPages', $config)) {
            /*
             * Recuperando paginas a mostrar en la paginacion
             */
            $showPages = (int) $config['showPages'];
            /*
             * Comprobando si es mayor a cero se conserva el valor, en caso contrario
             * se llama al valor por defecto.
             */
            if ($showPages > 0) {
                $pagesRange = $showPages;
            }
        }
        /*
         * Comprobando que items sea un array
         */
        if (!is_array($items)) {
            throw new Exception('Invalid data for paginator');
        }
        /*
         * Comprobando si el numero de pagina es menor que cero, se iguala a 1
         */
        if ($pageNumber <= 0) {
            $pageNumber = 1;
        }
        /*
         * Comprobando que los elementos a mostrar sean mayo que 0
         */
        if ($show <= 0) {
            throw new Exception('Invalid limit for paginator');
        }
        /*
         * Obteniendo el total de items
         */
        $number = count($items);
        /*
         * Dividiendo el numero de items entre los items a mostrars para obtener 
         * el numero de paginas, despues del redondear el valor.
         */
        $roundedTotal = $number / floatval($show);
        $totalPages = (int) $roundedTotal;
        /*
         * Comprobando si el total de paginas es distinto de total redondeado
         * se incrementa el valor en 1
         */
        if ($totalPages != $roundedTotal) {
            $totalPages++;
        }
        /*
         * Obteniendo items a mostrar moviendo el puntero de acuerdo a la pagina.
         */
        $items = array_slice($items, $show * ($pageNumber - 1), $show);
        /*
         * Comprobando si transformer es un objeto
         */
        if(is_object($transformer)){
            /*
             * Comprobando si items es mayor a cero
             */
            if(count($items) > 0){
                /*
                 * Recorriendo arreelo
                 */
                foreach ($items as $item){
                    /*
                     * Transformando items y almacenando
                     */
                    $newItems[] = $transformer->transform($item); 
                }
            }
            /*
             * Sobreescribiendo items
             */
            $items = $newItems;
        }
        /*
         * Obteniendo el numero de pagina siguiente
         */
        if ($pageNumber < $totalPages) {
            $next = $pageNumber + 1;
        } else {
            $next = $totalPages;
        }
        /*
         * Obteniendo numero de pagina anterior
         */
        if ($pageNumber > 1) {
            $before = $pageNumber - 1;
        } else {
            $before = 1;
        }
        //Rango de Paginacion
        $range = (int) $pagesRange / 2;
        /*
         * Obteniendo rango derecho
         */
        $rightRange = $totalPages - $pageNumber;
        $rest = 0;
        /*
         * Si el rango derecho es menos que rango
         */
        if ($rightRange < $range) {
            /*
             * Residuo es la restade range y rangoderecho
             */
            $rest = $range - $rightRange;
        }
        /*
         * Obteniendo rango izquierdo
         */
        $leftRange = $pageNumber - ($range + $rest);
        /*
         * Generado paginas.
         * Iniciando en numero de paginas hasta rangoizquierdo
         */
        for ($i = $pageNumber; $i > $leftRange; $i--) {
            /*
             * Si la pagina es cero se detiene
             */
            if ($i == 0)
                break;
            /*
             * Almacena numero de paginas a la izquierda.
             */
            $pages[] = $i;
        }
        /*
         * Comprobando el numero de pagina es menor al rango para obtener
         * el rango de paginas a la derecha
         */
        if ($pageNumber < $range) {
            $rightRange = $pagesRange;
        } else {
            $rightRange = $pageNumber + $range;
        }
        /*
         * Generando paginas a la derecha.
         * Iniciando en la pagina actual hasta el rango derecho 
         */
        for ($i = $pageNumber + 1; $i <= $rightRange; $i++) {
            /*
             * Si el contador rebasa el total de paginas se detiene
             */
            if ($i > $totalPages)
                break;
            /*
             * Almacenando numero de paginas a la derechas
             */
            $pages[] = $i;
        }
        /*
         * Ordenando paginas
         */
        \sort($pages);
        /*
         * Creando clase standas para devolver resultado
         */
        $page = new \stdClass();
        $page->items = $items;
        $page->first = 1;
	$page->before =  $before;
	$page->current = $pageNumber;
	$page->last = $totalPages;
	$page->next = $next;
	$page->totalPages = $totalPages;
	$page->totalItems = $number;
	$page->limit = $this->_limitRows;
        $page->pages = $pages;
        
        return $page;
    }

}
