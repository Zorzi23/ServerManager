<?php
namespace ServerManager\Router\Validator;
use ObjectFlow\Trait\InstanceTrait;
use ServerManager\Router\ServerRoute;

class RouterProtocolStrategyValidator {

    use InstanceTrait;

    public function validate(ServerRoute $oTestRoute, ServerRoute $oReferenceRoute) {
        foreach($oReferenceRoute->getProtocols() as $sProtocol) {
            if(in_array($sProtocol, $oTestRoute->getProtocols())) {
                return true;
            }
        }
        return false;
    }

}