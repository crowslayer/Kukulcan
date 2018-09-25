HMVC
----

Kukulcan
----
Contien el Core para correr aplicacion

## Requerimientos
- Phalcon Version > 2.x

### Configuracion de modulos

Se debe definir los datos de los modulos emplear, cada modulo debera contener un bootstrap.php donde se crearan las opciones y servicios necesarios para arrancar.

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