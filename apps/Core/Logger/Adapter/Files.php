<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Logger\Adapter;

use Phalcon\Logger\Adapter\File as PhFile;
use Phalcon\Logger\Formatter\Line as FormatterLine;

/**
 * Description of Files
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Files extends PhFile {
   const DEFAULT_PATH = "/var/logs/";
   const DEFAULT_FILENAME = "main-log";
   const DEFAULT_FORMAT = '[%date%][%type%] %message%';
   const DEFAULT_EXTENSION = 'log';
   
   public function __construct($config) {
       
       $logOptions = null;
       
       if(is_array($config)){
           $filename = array_key_exists('filename', $config) ? $config['filename'] : self::DEFAULT_FILENAME;
           $extension = array_key_exists('extension', $config) ? $config['extension'] : self::DEFAULT_EXTENSION;
           $logsDir = array_key_exists('logsDir', $config) ? $config['logsDir'] : self::DEFAULT_PATH;
           $logFormat = array_key_exists('formatter', $config) ? $config['formatter'] : self::DEFAULT_FORMAT;
                   
       }
       
       if(is_string($config)){
           $logsDir = self::DEFAULT_PATH;
           $filename = $config;
           $extension = self::DEFAULT_EXTENSION;
           $logFormat = self::DEFAULT_FORMAT;
           
       }
       
       if(!$config){
           $logsDir = self::DEFAULT_PATH;
           $filename = self::DEFAULT_FILENAME;
           $extension = self::DEFAULT_EXTENSION;
           $logFormat = self::DEFAULT_FORMAT;
       }
       
       $partsName = explode(".", $filename);

        if (count($partsName) > 1) {

            $extName = array_pop($partsName);

            if ($extension == $extName) {
                $logName = $filename;
            } else {
                $logName = $filename . '.' . $extension;
            }
        } else {
            $logName = $filename . '.' . $extension;
        }

        $logPath = ROOT_PATH . $logsDir;
       
       if(!is_dir($logPath)){
           mkdir($logPath, 0755, true);
       }
       
       $fullname = $logPath . $logName;
       
       $formater = new FormatterLine($logFormat);
       
       $this->setFormatter($formater);
       
       parent::__construct($fullname,$logOptions);
       
       
   }
}