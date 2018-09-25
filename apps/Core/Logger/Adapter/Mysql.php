<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Logger\Adapter;

use Phalcon\Db\Column;
use Phalcon\Logger\Exception;
use Phalcon\Logger\Formatter\Line as LineFormatter;
use Phalcon\Logger\Adapter as LoggerAdapter;
use Phalcon\Logger\AdapterInterface;
use Phalcon\Db\AdapterInterface as DbAdapterInterface;

/**
 * Description of Mysql
 * Adapter to store logs in a database table
 * 
 * @author Alexander Herrera <crowslayer@gmail.com>
 * @package Phalcon\Logger\Adapter
 * @ elementName
 */
class Mysql extends LoggerAdapter implements AdapterInterface{
    const FORMAT_MESSAGE = "%message%";
    
    /**
     * Name
     * @var string
     */
    protected $_name = 'HidraLogs';
    /**
     * Adapter options
     * @var array
     */
    protected $_options = [];
    /**
     * @var \Phalcon\Db\AdapterInterface
     */
    protected $_db;
    
    private $_connectionData = [];
    
    private $_table;

    /**
     * Class constructor.
     *
     * @param  string $name
     * @param  array  $options
     * @throws \Phalcon\Logger\Exception
     * 
     */
    
    public function __construct(array $options = []){
        
        $this->setOptions($options);
        
    }
    
    private function _connectDatabase(){
        try{
            $options = $this->getConectionData();
            
            if(!is_array($options)){
                 throw new Exception('Options of Conection must be Array');
            }
            
            if(isset($options['adapter'])){
                $adapter = '\\Phalcon\\Db\\Adapter\\Pdo\\'. $options['adapter'];
            }else{
                $adapter = '\\Phalcon\\Db\\Adapter\\Pdo\\Mysql';
            }
            
            $conection = new $adapter($options);
            
            if(is_object($conection)){
                $this->setDb($conection); 
            }
                        
            return $conection;
            
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function setName($name){
        $this->_name = $name;
        
        return $this;
    }
    
    public function getName(){
        return $this->_name;
    }
    
    public function setOptions($options, $merge = false){
        if(!is_array($options)){
            throw new Exception('Options must be Array');
        }
                
        if(array_key_exists('conection', $options)){
            $this->setConectionData($options['conection']);
            
            unset($options['conection']);
        }
        
        if(array_key_exists('name', $options)){
            $this->setName($options['name']);
        }
        
        $this->_options = $options;
        
        return $this;
    }
    
    public function getOptions(){
        return $this->_options;
    }
    
    public function setConectionData($options){
        if(!is_array($options)){
            throw new Exception('the conection data mut be array');
        }
        
        if(array_key_exists('table', $options)){
            $this->_table = $options['table'];
            unset($options['table']);
        }
        
        $this->_connectionData = $options;
        
        return $this;
    }
    
    /**
     * Sets database connection
     *
     * @param AdapterInterface $db
     * @return $this
     */
    public function setDb(DbAdapterInterface $db){
        $this->_db = $db;
        
        return $this;
    }
    
    public function getDb(){
        
        if(!$this->_db instanceof DbAdapterInterface ){
            $this->_connectDatabase();
        }
        
        return $this->_db;
        
    }
    
    public function getConectionData(){
        return $this->_connectionData;
    }
    
    public function getFormatter(){
        
        if (!is_object($this->_formatter)) {
            
            $options = $this->getOptions();
            
            $message = array_key_exists('formatter', $options) ? $options['formatter'] : self::FORMAT_MESSAGE;
                        
            $this->_formatter = new LineFormatter($message);
        }
        
        return $this->_formatter;
    }
    
    /**
     * Writes the log to the file itself
     *
     * @param string  $message
     * @param integer $type
     * @param integer $time
     * @param array   $context
     * @return bool
     */
    public function logInternal($message, $type, $time, $context = []){
        
        $db = $this->getDb();
        
        if(!$db instanceof DbAdapterInterface){
            throw new Exception('no data connection was found');
        }
        
        $table = $this->_table;
        $sql = sprintf("INSERT INTO %s VALUES (null, ?, ?, ?, ?)", $table);
        
        $db->execute(
                $sql,
                [
                    $this->_name, 
                    $type, 
                    $this->getFormatter()->format($message, $type, $time, $context), 
                    $time
                ],
                [
                    Column::BIND_PARAM_STR, 
                    Column::BIND_PARAM_INT,
                    Column::BIND_PARAM_STR,
                    Column::BIND_PARAM_INT
                ]
        );
        
    }
    
    public function close(){
        $db = $this->getDb();
        
        if ($db->isUnderTransaction()) {
            $db->commit();
        }
        
        $db->close();
        
        return true;
    }
    
    public function begin(){
        $db = $this->getDb();
        
        $db->begin();
        
        return $this;
    }
    
    public function commit(){
        
        $db = $this->getDb();
        
        $db->commit();
        
        return $this;
    }
    
    public function rollback(){
        
        $db = $this->getDb();
        $db->rollback();
        
        return $this;
    }

}
