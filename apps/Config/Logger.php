<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return array(
    'loggers' => array(
            [
                'adapter' => 'files',
                'options' => [
                    'filename' => 'system-log',
                    'extension' => 'log',
                    'logsDir' => '/var/logs/system/',
                    'formatter' => '[%date%][%type%] %message%',
                ],
                'identifier' => 'system'
            ],
            [
                'adapter' => 'mysql',
                'options' => [
                    'name' => 'HidraLogs_Gateway',
                    'conection' => [
                            'adapter'   => 'mysql',
                            'host'      => '127.0.0.1',
                            'username'  => 'root',
                            'password'  => '',
                            'dbname'    => 'logsdb',
                            'table'     => 'logs',
                        ],
                    'formatter' => '%message%',
                ],
                'identifier' => 'database'
            ],
        
        ),
    );