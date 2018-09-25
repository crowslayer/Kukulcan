<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Paginator;

/**
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
interface AdapterInterface {
    /**
     * Set the current page number
     */
    public function setCurrentPage($page);

    /**
     * Returns a slice of the resultset to show in the pagination
     */
    public function getPaginate();
    /**
     * Set current rows limit
     * @return StdClass Description
     */
    public function setLimit($limit);

    /**
     * Get current rows limit
     * return integer
     */
    public function getLimit();
}
