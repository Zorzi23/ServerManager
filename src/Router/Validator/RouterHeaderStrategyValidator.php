<?php
namespace ServerManager\Router\Validator;
use ObjectFlow\Trait\InstanceTrait;
use ServerManager\Router\ServerRoute;

class RouterHeaderStrategyValidator {

    use InstanceTrait;

    public function validate(ServerRoute $oTestRoute, ServerRoute $oReferenceRoute) {
        $bValidHeaders = true;
        foreach($oReferenceRoute->getRequestHeaders() as $oHeader) {
            $oTestHeader = $oTestRoute->getRequestHeaderByName($oHeader->getName()); 
            if(!$oTestHeader) {
                continue;
            }
            $bValidHeaders = $bValidHeaders && str_contains( $oTestHeader->raw(), $oHeader->raw()); 
        }
        return $bValidHeaders;
    }
    
    /**
     * 
     * @param \ServerManager\Router\ServerRoute $oRoute
     * @return mixed
     */
    public function isEqual(ServerRoute $oTestRoute, $oReferenceRoute) {
        return $oTestRoute->getPath() === $oReferenceRoute->getPath();
    }

    /**
     * 
     * @param \ServerManager\Router\ServerRoute $oTestRoute
     * @param \ServerManager\Router\ServerRoute $oReferenceRoute
     * @return bool
     */
    public function isPathSubPathOf(ServerRoute $oTestRoute, ServerRoute $oReferenceRoute) {
        $aTestRouteParentsPath = $oReferenceRoute->getPathArray();
        array_pop($aTestRouteParentsPath);
        $sParentsPath = implode('', $aTestRouteParentsPath);
        $oReferenceRouteParente = (new ServerRoute())->setPath($sParentsPath);
        return $oReferenceRouteParente->getPath() === $oTestRoute->getPath();
    }

    /**
     * 
     * @param \ServerManager\Router\ServerRoute $oRoute
     * @return mixed
     */
    public function isRouteEnableSubPaths(ServerRoute $oRoute) {
        return $oRoute->getCustomConfig()->isEnableSubPaths();
    }

}