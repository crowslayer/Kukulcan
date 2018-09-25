<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core;

use Phalcon\Mvc\Controller as PhalconController;
use Kukulcan\Core\Constant\Services as ApplicationServices;
/**
 * Description of Controller
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
abstract class Controller extends PhalconController{
    /**
     * Recupera los datos enviados por el metodo _POST.
     * 
     * @param string $name
     * @param mixed $filters
     * @param mixed $default
     * @return mixed
     */
    public function getPost($name = null, $filters = null, $default = null) {
        return $this->{ApplicationServices::REQUEST}->getPost($name, $filters, $default);
    }
    /**
     * Recupera los datos enviados por el metodo _GET.
     * 
     * @param string $name
     * @param mixed $filters
     * @param mixed $default
     * @return mixed
     */
    public function get($name = null, $filters = null, $default = null) {
        return $this->{ApplicationServices::REQUEST}->get($name, $filters, $default);
    }
    /**
     * Recupera los datos enviados por el metodo _GET.
     * 
     * @param string $name
     * @param mixed $filters
     * @param mixed $default
     * @return mixed
     */
    public function getQuery($name = null, $filters = null, $default = null){
         return $this->{ApplicationServices::REQUEST}->getQuery($name, $filters, $default);
    }
    /**
     * Recupera los datos enviados por el metodo _PUT.
     * 
     * @param string $name
     * @param mixed $filters
     * @param mixed $default
     * @return mixed
     */
    public function getPut($name = null, $filters = null, $default = null){
         return $this->{ApplicationServices::REQUEST}->getPut($name, $filters, $default);
    }
    /**
     * Retorna el componente vista activo.
     * @return Phalcon\Mvc\View
     */
    public function getView(){
        return $this->{ApplicationServices::VIEW};
    }
    /**
     * Retorna la configuracion almacenada
     * @return Phalcon\Config
     */
    public function getConfig(){
        return $this->getDI()->get(ApplicationServices::CONFIG);
    }
    /**
     * Comprueba si el metodo recibido es via post.
     * @return boolean
     */
    public function isPost(){
        return $this->{ApplicationServices::REQUEST}->isPost();
    }
    /**
     * Comprueba si el metodo recibido es via get.
     * @return boolean
     */
    public function isGet(){
        return $this->{ApplicationServices::REQUEST}->isGet();
    }
    /**
     * Comprueba si el metodo recibido es via put.
     * @return boolean
     */
    public function isPut(){
        return $this->{ApplicationServices::REQUEST}->isPut();
    }
    /**
     * Comprueba si el metodo recibido es via ajax.
     * @return boolean
     */
    public function isAjax(){
        return $this->{ApplicationServices::REQUEST}->isAjax();
    }
    /**
     * Comprueba el token de seguridad para evitar XSCS
     * @return mixed
     */
    public function checkToken(){
        return $this->{ApplicationServices::SECURITY}->checkToken();
    }
    /**
     * Retorna el servicio solicitado.
     * 
     * @param string $serviceName
     * @return mixed
     */
    public function getDIService($serviceName ,$shared = false){
        if($shared){
            $service = $this->getDI()->getShared($serviceName);
        }else{
            
            $service = $this->getDI()->get($serviceName);
        }
        
        return $service;
    }
    /**
     * Realiza la redireccion de acuerdo a los parametros establecidos.
     * 
     * @param string $location
     * @param boolean $externalRedirect
     * @param string $statusCode
     * @return mixed
     */
    protected function redirect($location = null, $externalRedirect = false, $statusCode = 302){
        return $this->{ApplicationServices::RESPONSE}->redirect($location, $externalRedirect, $statusCode);
        
    }
    /**
     * Redirige el flujo al controlador y accion especificados.
     * 
     * @param string $controller
     * @param string $action
     * @return void
     */
    protected function forward($controller , $action){
        $parameters = [
            'controller' => $controller,
            'action' => $action
        ];
        
        return $this->{ApplicationServices::DISPATCHER}->forward($parameters);
    }
}
