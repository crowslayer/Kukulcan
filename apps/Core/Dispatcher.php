<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core;

use Phalcon\Mvc\Dispatcher as PhalconDispatcher;
 
/**
 * Description of Dispatcher
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Dispatcher extends PhalconDispatcher {
    private $_rootName;
    
    public function setRootName($name){
        $this->_rootName = $name;
    }
 
    public function getRootName(){
        return $this->_rootName;
    }
}
