<?php
namespace ServerManager\Router\Validator;
use ObjectFlow\Trait\InstanceTrait;
use ServerManager\Router\ServerRoute;

class RouterMethodStrategyValidator {

    use InstanceTrait;

    public function validate(ServerRoute $oTestRoute, ServerRoute $oReferenceRoute) {
        foreach($oReferenceRoute->getMethods() as $sMethods) {
            if($sMethods === '*') { return true; }
            if(in_array($sMethods, $oTestRoute->getMethods())) {
                return true;
            }
        }
        return false;
    }

}