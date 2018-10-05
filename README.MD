HMVC
----
Phalcon HMVC Proyect Core

Kukulcan
----
Contains the Core to run applications in a modular way with phalcon via HMVC

## Requerimientos
- Phalcon Version > 2.x

### Configuration of modules

You must define the data of the modules to use, each module must contain a bootstrap.php where the options and services necessary to start up will be created.

```php
array(
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

```

## Bootstrap.php

The services in the bootstrap are defined to start the application, register namespace, autoloaders, etc.
Initially, only the view controller must be instantiated in order to use the core and the modules.
You must be defined in the path ../modules/{moduleName}/Bootstrap.php...

```php
/**
 * Description of Bootstrap
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Bootstrap extends CoreBootstrap{

    public function registerServices(\Phalcon\DiInterface $di = null){
        $configDir = __DIR__."/Config";
        $configLocal = CoreConfig::factory($configDir);
        
        $this->setConfig($configLocal);
        
        if(!$configLocal->get('View')){
            throw new Exception('Debe Configurar las vistas');
        }
        
        if(is_null($di)){
            $di = $this->getDI();
        }

        $eventsManager  = new EventsManager();
       
        $uses           = [
            'di'     => $di,
            'em'     => $eventsManager,
            'dir'    => $this->getModulePath(),
            'config' => $configLocal,
            'name'   => $this->getModuleName(),
            'root'  => $this->getRoot(),
        ];
        /*
         * Inicializando servicios
         */
        Services::initializeServices($uses);
        
        //debug application
        $application = $configLocal->get('Application');
        /*
         * Comprobando si esta en modo debug
         */
        if($application->application->debug){
            /*
             * Creando modo depurador
             */
            $debug = new \Phalcon\Debug();
            $debug->listen();
            
        }
        
    
    }
    
}


```