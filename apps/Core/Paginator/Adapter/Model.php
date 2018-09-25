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
 * Description of Model
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Model extends Adapter {
    /**
     * Almacena la configuracion de la clase.
     * 
     * @var array
     */
    protected $_config;
    /**
     * Almacena el numero de paginas a mostrar en el rango
     * 
     * @var integer
     */
    protected $_showPages = 5;
    
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
        /*
         * Comprobando si existe paginas a mostrar
         */
        if (array_key_exists('showPages', $config)) {
            /*
             * Recuperando valor y convirtiendo a integer
             */
            $showPages = (int) $config['showPages'];
            /*
             * Si es mayor que cero, se asigna a showPages.
             */
            if($showPages > 0){
                $this->_showPages = $showPages;
            }
        }
        /**
         * Comprobando si existr transformador de datos
         */
        if(array_key_exists('transformer', $config)){
            $this->setTransformer($config['transformer']);
        }
        
    }
    /**
     * Retorna los resultados paginados
     * 
     * @return \stdClass
     * @throws Exception
     */
    public function getPaginate() {
        /*
         * Iniciando valores.
         */
        $showPages = $this->_showPages;
        $pages = [];
        $pageItems = [];
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
        $show = (int) $this->_limitRows;
        $items = $config['data'];
        $pageNumber = (int) $this->_page;
        /*
         * Comprobando que items sea un objecto
         */
        if(!is_object($items)){
            throw new Exception('Invalida data for paginator');
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
        $number = $items->count();
        /*
         * Calculando ultima pagina mostrada
         */
        $lastShowPage = $pageNumber - 1;
        /*
         * Calculando registro inicial.
         */
        $start = $show * $lastShowPage;
        /*
         * Registros restantes por mostrar
         */
        $rest = $number % $show;
        /*
         * Si quedan registros por mostrar se incrementa una pagina adicional
         * en caso contrario se calculan las paginas resultantes entre el total 
         * y el numero de registros a mostrar.
         */
        if($rest != 0){
            $totalPages = (int) ($number / $show + 1);            
        }else{
            $totalPages = (int) ($number / $show);
        }
        /*
         * Si el conteo delos registros es mayor a cero
         */
        if($number > 0){
            /*
             * Si el inicio es menor o igual al total
             */
            if($start <= $number){
                /*
                 * Se busca la posicion inicial establecida
                 */
                $items->seek($start);
            }else{
                /*
                 * Se regresa al primer registro y la pagina se vuelve 1.
                 */
                $items->seek(0);
                $pageNumber = 1;
            }
            //iniciando contador
            $i = 1;
            /*
             * Mientras existan items validos
             */
            while($items->valid()){
                /*
                 * Comprobando si existe transformador para los datos
                 */
                $transformer = $this->getTransformer();
                /*
                 * Si es un objeto se transforma los datos en caso contrario
                 * se almacenan sin transformar.
                 */
                if(is_object($transformer)){
                    $pageItems[] = $transformer->transform($items->current());
                }else{
                    /*
                     * Almacena registro actual en contenedor de items
                     */
                    $pageItems[] = $items->current();
                    
                }
                /*
                 * Si el contador es mayor o igual al numero de registros a mostrar
                 * se detiene el ciclo
                 */
                if($i >= $show) break;
                /*
                 * En caso contrario se incrementa el contador y
                 * se pasa al siguient valor.
                 */
                $i++;
                $items->next();
            }
        }else{
            $pageNumber = 1;
        }
        /*
         * Calculando valor de next
         */
        $next = $pageNumber + 1;
        /*
         * Si el siguiente valor es mayor al total de paginas
         */
        if($next > $totalPages){
            /*
             * Next se vuelte el total de paginas
             */
            $next = $totalPages;
        }
        /*
         * Calculando pagina anterior, si el numero de pagina es mayo a 1
         */
        if($pageNumber > 1){
            /*
             * pagina anterior es la rest del numero de pagina menos 1
             */
            $before = $pageNumber - 1;
        }else{
            $before = 1;
        }
        //Rango de Paginacion
        $range = (int) $showPages / 2;
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
            $rightRange = $showPages;
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
        $page->items = $pageItems;
        $page->first = 1;
	$page->before =  $before;
	$page->current = $pageNumber;
	$page->last = $totalPages;
	$page->next = $next;
	$page->totalPages = $totalPages;
	$page->totalItems = $number;
	$page->limit = $this->getLimit();
        $page->pages = $pages;
        
        return $page;
        
    }
}
