<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Cache\Backend\Adapter;

use Phalcon\Db,
    Phalcon\Cache\Backend,
    Phalcon\Cache\BackendInterface,
    Phalcon\Cache\FrontendInterface;

use Kukulcan\Core\Cache\Exception as CoreCacheException;

/**
 * Description of Mysql
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Mysql extends Backend implements BackendInterface {
    /**
     * Tiempo de Vida por defecto.
     */
    const LIFETIME = 604800;
    /**
     * Prefijo a ser asignado.
     * @var integer 
     */
    protected $_prefix;
    /**
     * Nombre o campo llave de cache utilizado por ultima ocacion
     * @var string 
     */
    protected $_lastKey;
    /**
     * Tiempo de vida estipulado de la cache
     * @var integer 
     */
    protected $_lastLifetime = null;
    
    protected $_options;
        
    private $_db = null;
    
    private $_table = null;
    
    private $_dataConection = [];
    
    public function __construct(FrontendInterface $frontend,$options = null) {
        if(!is_null($options)){
            $this->setOptions($options);
        }
        
        if(isset($options['conection'])){
            unset($options['conection']);
        }
        
        
        parent::__construct($frontend, $options);
    }

    protected function _getPrefixedIdentifier($id){
        return $this->_prefix . $id;
    }
    
    public function setOptions($options){
        if(!is_array($options)){
            throw new CoreCacheException('Options must be Array');
        }
        if(isset($options['prefix'])){
            $this->setPrefix($options['prefix']);
        }
        
        if(isset($options['lifetime'])){
            $this->_lastLifetime = $options['lifetime'];
        }
        
        if(isset($options['conection'])){
            $this->setConectionData($options['conection']);
            unset($options['conection']);
        }
        
        $this->_options = $options;
        
    }
    /**
     * 
     * @param type $options
     * @return $this
     * @throws CoreCacheException
     */
    public function setConectionData($options){
        if(!is_array($options)){
            throw new CoreCacheException('the conection data mut be array');
        }
        
        $this->_dataConection = $options;
        
        return $this;
    }
    /**
     * 
     * @return array
     */
    public function getConectionData(){
        return $this->_dataConection;
    }
    
    public function getDb(){
        return $this->_db;
    }
    
    public function setDb(AdapterInterface $conection){
        $this->_db = $conection;
        
        return $this;
                
    }
    
    public function getOptions(){
        return $this->_options;
    }
    /**
     * 
     * @param array $options
     * @return boolean
     * @throws CoreCacheException
     */
    private function _connecToDb($options){
        try{
            if(!is_array($options)){
                 throw new CoreCacheException('Options of Conection must be Array');
            }
            
            if(isset($options['adapter'])){
                $adapter = '\\Phalcon\\Db\\Adapter\\Pdo\\'. $options['adapter'];
            }else{
                $adapter = '\\Phalcon\\Db\\Adapter\\Pdo\\Mysql';
            }
            
            if (!isset($options['table']) || empty($options['table']) || !is_string($options['table'])) {
                throw new CoreCacheException("Parameter 'table' is required and it must be a non empty string");
            }
            
            $conection = new $adapter($options);
            
            if(is_object($conection)){
                $this->_table = $conection->escapeIdentifier($options['table']);
                $this->setDb($conection); 
            }
                        
            return $conection;
            
        } catch (\Exception $ex) {
            return false;
        }
    }
    /**
     * 
     * @param string $prefix
     */
    public function setPrefix($prefix) {
        if(!is_null($prefix) || !empty($prefix)){
            $this->_prefix = $prefix;
        }
    }
    /**
     * 
     * @return string
     */
    public function getPrefix() {
        return $this->_prefix;
    }
    /**
     * 
     * @param type $lifetime
     * @return $this
     */
    public function setLifetime($lifetime) {
        $ttl = (int) $lifetime;
        
        if($ttl == 0 || $ttl < 0){
            $ttl = self::LIFETIME;
        }
        
        $this->_lastLifetime = $ttl;
        
        return $this;
    }
    /**
     * 
     * @return integer
     */
    public function getLifetime() {
        return $this->_lastLifetime;
    }
    /**
     * Retorna el contenido almacenado de acuerdo al nombre proporcionado,
     * posterior a la revision del tiempo de vida.
     * @param string $keyName
     * @return boolean
     */
    public function get($keyName, $lifetime = null) {
        if(!isset($keyName) || empty($keyName)){
            return NULL;
        }
        
        $db = $this->getDb();
        
        if(!is_object($db)){
            $db = $this->_connecToDb($this->getConectionData());
            
        }
        
        $prefixedKey = $this->_getPrefixedIdentifier($keyName);
        
        $sql = "SELECT data, lifetime FROM {$this->_table} WHERE key_name = ?";
        $cache = $db->fetchOne($sql, Db::FETCH_ASSOC,[$prefixedKey]);
        $this->_lastKey = $prefixedKey;
        
        if(!$cache){
            return null;
        }
              
        $lifetime = \strtotime($cache['lifetime']);
        
        if( $lifetime < time()){
            $this->delete($keyName);
            return NULL;
        }
        
        $cacheContent = $cache['data'];       
        
        if(\is_numeric($cacheContent)){
            return $cacheContent;
            
        }else{
            return $this->_frontend->afterRetrieve($cacheContent);
        }
        
        return NULL;
    }
    /**
     * Almacena el contenido en la bd, devolviendo TRUE en caso de exito ó false en caso de error.
     * @param string $keyName
     * @param string $content
     * @param integer $lifetime
     * @param boolean $stopBuffer
     * @return boolean
     * @throws Exception
     */
    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true) {
        try{
            
            if($keyName === NULL){
                $prefixedKey = $this->_lastKey;
            }else{
                $prefixedKey = $this->_getPrefixedIdentifier($keyName);
            }

            if(!$prefixedKey || empty($prefixedKey)){
                throw new CoreCacheException('Error, you must provide a name or key to store the cache.');
            }
            
            $frontend = $this->_frontend;
            
            if($content == null){
                $cachedContent = $frontend->getContent();
            }else{
                $cachedContent = $content;
            }
            
            if(!is_numeric($cachedContent)){
               $preparedContent = $frontend->beforeStore($cachedContent); 
            }else{
                $preparedContent = $cachedContent;
            }
            
            if(is_null($lifetime)){
                $lifetime = $this->_lastLifetime;
                
                if(is_null($lifetime) || empty($lifetime)){
                    $lifetime = $frontend->getLifetime();
                }
            }
            
            $db = $this->getDb();
        
            if(!is_object($db)){
                $db = $this->_connecToDb($this->getConectionData());

            }
            
            $ttl = date("Y-m-d H:i:s", time() + (int) $lifetime );
            
            $sql = "SELECT cache_id, data FROM {$this->_table} WHERE key_name = ?";
            $cache = $db->fetchOne($sql,Db::FETCH_ASSOC,[$prefixedKey]);
            
            if(!$cache){
                $status = $db->execute("INSERT INTO {$this->_table} (key_name, data, lifetime) VALUES (?, ?, ?)", [$prefixedKey,
                                                                                                                          $preparedContent,
                                                                                                                          $ttl]
                                            );
            }else{
                $status = $db->execute("UPDATE {$this->_table} SET data = ?, lifetime = ? , updated_at = now() WHERE cache_id = ?",[$preparedContent,
                                                                                                                      $ttl,
                                                                                                                      $cache['cache_id'],
                                                                                                                      ]
                                                );
            }
            
            if($status === false){
                throw new Exception('You can not store cached content');
            }
            
            $isBuffering = $frontend->isBuffering();
            
            if($stopBuffer === true){
                $frontend->stop();
            }
            
            if($isBuffering === true){
                echo $cachedContent;
            }
            
            $this->_started = false;
            
            return true;
            
        } catch (\Exception $ex) {
            
            return false;
        }
        
    }
    /**
     * Elimina el contenido almacenado segun la clave porporcionada.
     * Devuelve TRUE en caso de Exito o False si ocurrio un error.
     * @param string $keyName
     * @return boolean
     */
    public function delete($keyName) {
        $prefixedKey = $this->_getPrefixedIdentifier($keyName);
        
        $db = $this->getDb();
        
        if(!is_object($db)){
            $db = $this->_connecToDb($this->getConectionData());
            
        }
        
        $sql = "SELECT cache_id FROM {$this->_table} WHERE key_name = ?";
        
        $row = $db->fetchOne($sql, Db::FETCH_ASSOC, [$prefixedKey]);
        
        if ($row === FALSE) {
            return false;
        }
        
        return $db->execute("DELETE FROM {$this->_table} WHERE cache_id = ?", [$row['cache_id']]);
    }
    /**
     * Retorna true si existe contenido almacenado y no ha expirado.
     * @param string $keyName
     * @return boolean
     */
    public function exists($keyName = null, $lifetime = NULL) {
        $prefixedKey = $this->_getPrefixedIdentifier($keyName);
        $db = $this->getDb();
        
        if(!is_object($db)){
            $db = $this->_connecToDb($this->getConectionData());
            
        }

        $sql = "SELECT lifetime FROM {$this->_table} WHERE key_name = ?";

        $cache = $db->fetchOne($sql, Db::FETCH_ASSOC, [$prefixedKey]);
        
        if (!$cache) {
            return false;
        }
        
        // Remove the cache if expired
        $lifetime = strtotime($cache['lifetime']);
        
        if ($lifetime < time()) {
            $this->delete($keyName);
            return false;
        }
        
        return true;
    }
    /**
     * 
     * @param string $prefix
     * @return type
     */
    public function queryKeys($prefix = null){
        if (!$prefix) {
            $prefix = $this->_prefix;
        } else {
            $prefix = $this->_getPrefixedIdentifier($prefix);
        }
        
        $db = $this->getDb();
        
        if(!is_object($db)){
            $db = $this->_connecToDb($this->getConectionData());
            
        }
        
        if (!empty($prefix)) {
            $sql = "SELECT key_name FROM {$this->_table} WHERE key_name LIKE ? ORDER BY lifetime";
            $rs  = $db->query($sql, [$prefix . '%']);
        } else {
            $sql = "SELECT key_name FROM {$this->_table} ORDER BY lifetime";
            $rs  = $db->query($sql);
        }
        
        $rs->setFetchMode(Db::FETCH_ASSOC);
        $keys = [];
        
        while ($row = $rs->fetch()) {
            $keys[] = !empty($prefix) ? str_replace($prefix, '', $row['key_name']) : $row['key_name'];
        }
        
        return $keys;
    }
    /**
     * 
     * @param string $keyName
     * @param integer $value
     * @return mixed
     * @throws CoreCacheException
     */
    public function increment($keyName , $value = 1){
        $prefixedKey = $this->_getPrefixedIdentifier($keyName);
        
        $this->_lastKey = $prefixedKey;
        
       $db = $this->getDb();
        
        if(!is_object($db)){
            $db = $this->_connecToDb($this->getConectionData());
            
        }
        
        $table = $this->_table;
        $sql = "SELECT lifetime FROM {$table} WHERE key_name = ?";
        
        $cache = $db->fetchOne($sql, Db::FETCH_ASSOC,[$prefixedKey]);
        
        if(!$cache){
            throw new CoreCacheException('possibly corrupted cache');
        }
        
        $modifiedTime = strtotime($cache['lifetime']);
        
        if(time() < $modifiedTime ){
            $cacheContent = $cache['data'];
            
            if(is_numeric($cacheContent)){
                $incremented = $cacheContent + $value;
                $this->save($prefixedKey, $incremented);
                return $incremented;
            }
            
        }
        
        return null;
        
    }
    /**
     * 
     * @param string $keyName
     * @param integer $value
     * @return mixed
     * @throws CoreCacheException
     */
    public function decrement($keyName, $value = 1){
        $prefixedKey = $this->_getPrefixedIdentifier($keyName);
        
        $this->_lastKey = $prefixedKey;
        
        $db = $this->getDb();
        
        if(!is_object($db)){
            $db = $this->_connecToDb($this->getConectionData());
            
        }
        
        $table = $this->_table;
        
        $sql = "SELECT lifetime FROM {$table} WHERE key_name = ?";
        
        $cache = $db->fetchOne($sql, Db::FETCH_ASSOC,[$prefixedKey]);
        
        if(!$cache){
            throw new CoreCacheException('possibly corrupted cache');
        }
        
        $modifiedTime = strtotime($cache['lifetime']);
        
        if(time() < $modifiedTime ){
            $cacheContent = $cache['data'];
            
            if(is_numeric($cacheContent)){
                $decremented = $cacheContent - $value;
                $this->save($prefixedKey, $decremented);
                return $decremented;
            }
            
        }
        return null;
        
    }

    /**
     * Elimina el contenido de la tabla cache
     * @return bool
     */
    public function flush() {
        $db = $this->getDb();
        
        if(!is_object($db)){
            $db = $this->_connecToDb($this->getConectionData());
            
        }
        return $db->execute("DELETE FROM {$this->_table}");
        
    }
}
