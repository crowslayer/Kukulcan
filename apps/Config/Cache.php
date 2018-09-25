<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return array(
    'cache' => [
        'frontends' => array(
            [
                'adapter' => 'data',
                'identifier' => 'phalconData',
                'options' =>[
                    'lifetime' => 3600,

                ]
                
            ],
            [
                'adapter' => 'base64',
                'identifier' => 'phalconB64',
                'options' =>[
                    'lifetime' => 86400,

                ]
                
            ],
        ),
        'backends' => array(
            [
                'adapter' => 'file',
                'options' => [
                        'prefix' => 'CSRV_',
                        'cacheDir' => '/var/cache/',
                    ],
                'identifier' => 'default',
                'frontendIdentifier' => 'phalconData',
                

            ],
            [
                'adapter' => 'mysql',
                'options' => [
                        'prefix' => 'cdts_',
                        'lifetime' => 3600,
                        'conection' => [
                            'adapter'   => 'mysql',
                            'host'      => '127.0.0.1',
                            'username'  => 'root',
                            'password'  => '',
                            'dbname'    => 'cachedb',
                            'table'     => 'cache',
                        ]
                    ],
                'identifier' => 'mysql',
                'frontendIdentifier' => 'phalconData',
                

            ],
        ),
    ],
);