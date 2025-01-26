<?php
namespace ServerManager\Session;
use ObjectFlow\GenericObject;
use ObjectFlow\Trait\InstanceTrait;

class SessionHandler {

    use InstanceTrait;

    /**
     * 
     * @return GenericObject
     */
    public function data() {
        return GenericObject::fromArrayObject($_SESSION);
    } 

}