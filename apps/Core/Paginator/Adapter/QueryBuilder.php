<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Paginator\Adapter;

use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Db;
use Kukulcan\Core\Paginator\Adapter;
use Kukulcan\Core\Paginator\Exception;

/**
 * Description of QueryBuilder
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class QueryBuilder extends Adapter {

    /**
     * Configuration of paginator by model
     */
    protected $_config;

    /**
     * Paginator's data
     */
    protected $_builder;

    /**
     * Columns for count query if builder has having
     */
    protected $_columns;

    /**
     * Almacena el numero de paginas a mostrar en el rango
     * 
     * @var integer
     */
    protected $_showPages = 5;

    public function __construct(array $config) {
        /*
         * Agregando configuracion
         */
        $this->_config = $config;
        /*
         * Comprobando si existe limit
         */
        if (!array_key_exists('limit', $config)) {
            throw new Exception("Parameter 'limit' is required");
        }
        $this->setLimit($config['limit']);
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
            if ($showPages > 0) {
                $this->_showPages = $showPages;
            }
        }
        /*
         * Comprobando data builder
         */
        if (array_key_exist('data', $config)) {
            $builder = $config['data'];
            if (!$builder instanceof Builder) {
                throw new Exception('Parameter Builder is required');
            }
            /*
             * Estableciendo builder
             */
            $this->setBuilder($builder);
        }
        /*
         * Comprobando data builder
         */
        if (array_key_exist('builder', $config)) {
            $builder = $config['builder'];
            if (!$builder instanceof Builder) {
                throw new Exception('Parameter Builder is required');
            }
            /*
             * Estableciendo builder
             */
            $this->setBuilder($builder);
        }
        /*
         * Comprobando si existe columns
         */
        if (array_key_exists('columns', $config)) {

            $this->_columns = $config['columns'];
        }
        /**
         * Comprobando si existr transformador de datos
         */
        if(array_key_exists('transformer', $config)){
            $this->setTransformer($config['transformer']);
        }
    }

    /**
     * Get the current page number
     */
    public function getCurrentPage() {
        return $this->_page;
    }

    /**
     * Establece el constructor o manejador de consultas.
     * 
     * @param Builder $builder
     * @return Kukulcan\Cores\Paginator\QueryBuilder
     */
    public function setBuilder(Builder $builder) {
        $this->_builder = $builder;

        return $this;
    }

    /**
     * Retorna el constructor de consultas almacenados.
     * @return mixed
     */
    public function getBuilder() {
        return $this->_builder;
    }

    /**
     * Devuelve el conjunto de resultados segun la pagina
     * y la cantidad de paginas a mostrar en el objeto paginador.
     * @return \stdClass
     * @throws Exception
     */
    public function getPaginate() {
        /*
         * Iniciando valores.
         */
        $showPages = $this->_showPages;
        $pages = [];
        $pageItems = false;
        $transformer = $this->getTransformer();
        /*
         * Recuperando valores
         */
        $limit = $this->getLimit();
        $pageNumber = (int) $this->getCurrentPage();
        /*
         * Recuperando y clonando builders
         */
        $originalBuilder = $this->getBuilder();
        /**
         * We make a copy of the original builder to leave it as it is
         */
        $builder = clone($originalBuilder);
        /**
         * We make a copy of the original builder to count the total of records
         */
        $totalBuilder = clone($builder);
        $columns = $this->_columns;
        /*
         * Comprobando si el numero de pagina es menor que cero, se iguala a 1
         */
        if ($pageNumber <= 0) {
            $pageNumber = 1;
        }
        /*
         * Calculando numero de registro
         */
        $number = $limit * ($pageNumber - 1);
        /*
         * Estableciendo el limite evitando negativos
         */
        if ($number < $limit) {
            /*
             * establece el limite 
             */
            $builder->limit($limit);
        } else {
            /*
             * Establece limite incio-final
             */
            $builder->limit($limit, $number);
        }
        /*
         * Obteniendo consulta
         */
        $query = $builder->getQuery();
        /*
         * Calculando numero de pagina anterior
         */
        if ($pageNumber == 1) {
            $before = 1;
        } else {
            $before = $pageNumber - 1;
        }
        /*
         * ejecutando la consulta
         */
        $pageItems = $query->execute();
        /*
         * Generando flags para having y groups
         */
        $hasHaving = !empty($totalBuilder->getHaving());
        $groups = $totalBuilder->getGroupBy();
        $hasGroup = !empty($groups);
        /**
         * Change the queried columns by a COUNT(*)
         */
        if ($hasHaving && !$hasGroup) {
            if (empty($columns)) {
                throw new Exception("When having is set there should be columns option provided for which calculate row count");
            }
            $totalBuilder->columns($columns);
        } else {
            $totalBuilder->columns("COUNT(*) [rowcount]");
        }
        /**
         * Change 'COUNT()' parameters, when the query contains 'GROUP BY'
         */
        if ($hasGroup) {
            /*
             * Si groups es un array se crea valor separado por comas
             */
            if (gettype($groups) == "array") {
                $groupColumn = implode(", ", $groups);
            } else {
                $groupColumn = $groups;
            }
            /*
             * Sino existe clausula having
             */
            if (!$hasHaving) {
                $totalBuilder->groupBy(null)->columns(["COUNT(DISTINCT " . $groupColumn . ") AS [rowcount]"]);
            } else {
                $totalBuilder->columns(["DISTINCT " . $groupColumn]);
            }
        }
        /**
         * Remove the 'ORDER BY' clause, PostgreSQL requires this
         */
        $totalBuilder->orderBy(null);

        /**
         * Obtain the PHQL for the total query
         */
        $totalQuery = $totalBuilder->getQuery();

        /**
         * Obtain the result of the total query
         * If we have having perform native count on temp table
         */
        if ($hasHaving) {
            $sql = $totalQuery->getSql();
            $modelClass = $builder->_models;

            if (gettype($modelClass) == "array") {
                $modelClass = array_values($modelClass)[0];
            }

            $model = new $modelClass();
            $dbService = $model->getReadConnectionService();
            $db = $totalBuilder->getDI()->get($dbService);
            $row = $db->fetchOne("SELECT COUNT(*) as \"rowcount\" FROM (" . $sql["sql"] . ") as T1", Db::FETCH_ASSOC, $sql["bind"]);
            $rowcount = $row ? intval($row["rowcount"]) : 0;
            $totalPages = \intval(\ceil($rowcount / $limit));
        } else {
            $result = $totalQuery->execute();
            $row = $result->getFirst();
            $rowcount = $row ? \intval($row->rowcount) : 0;
            $totalPages = \intval(\ceil($rowcount / $limit));
        }

        if ($pageNumber < $totalPages) {
            $next = $pageNumber + 1;
        } else {
            $next = $totalPages;
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
         * Creando clases estandar
         */
        $page = new \stdClass();
        $page->items = $pageItems;
        $page->first = 1;
        $page->before = $before;
        $page->current = $this->getCurrentPage();
        $page->last = $totalPages;
        $page->next = $next;
        $page->totalPages = $totalPages;
        $page->totalItems = $rowcount;
        $page->limit = $this->getLimit();
        
        return $page;
    }

}
