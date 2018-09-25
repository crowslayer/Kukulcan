<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Cache\Backend\Adapter;

use Phalcon\Cache\Backend\File as CacheFiles;

/**
 * Description of Files
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class File extends CacheFiles{
    const CACHE_DIR = "/var/cache/";
    
    public function __construct($frontend , $options) {
        $cacheDir = array_key_exists('cacheDir', $options) ? $options['cacheDir'] : self::CACHE_DIR;
        
        $fullPathCache = ROOT_PATH . $cacheDir;
        $options['cacheDir'] = $fullPathCache;
        
        if(!is_dir($fullPathCache)){
            @mkdir($fullPathCache, 0755, true);
        }
        
        parent::__construct($frontend,$options);
        
    }
}
