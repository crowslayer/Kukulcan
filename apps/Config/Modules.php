<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return array(
    'modulesDirectory' => '/Modules',
    'defaultModule' => 'Frontend',
    'modules' => [
        'Frontend' => [
            'moduleDirectory' => '/Frontend',
            'namespace' => 'Tepeu',
            'prefix' => '/',
            'default' => [
                'namespace' => '\\Tepeu\\Controllers',
                'module' => 'Frontend',
                'controller' => 'index',
                'action'    => 'index'
            ],
            
        ],
    ],
);