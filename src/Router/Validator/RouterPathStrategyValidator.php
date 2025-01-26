<?php
namespace ServerManager\Router\Validator;
use ObjectFlow\Trait\InstanceTrait;
use ServerManager\Router\ServerRoute;

class RouterPathStrategyValidator {

    use InstanceTrait;

    public function validate(ServerRoute $oTestRoute, ServerRoute $oReferenceRoute) {
        $bEqual = $this->isPathEqual($oTestRoute, $oReferenceRoute);
        if($bEqual) { return true; }
        return $this->isRouteEnableSubPaths($oReferenceRoute) ?
            $this->isPathSubPathOf($oTestRoute, $oReferenceRoute)
            : false;
    }
    
    /**
     * 
     * @param \ServerManager\Router\ServerRoute $oRoute
     * @return mixed
     */
    public function isPathEqual(ServerRoute $oTestRoute, $oReferenceRoute) {
        return $oTestRoute->getPath() === $oReferenceRoute->getPath()
            && $oTestRoute->getFile() === $oReferenceRoute->getFile();
    }

    /**
     * 
     * @param \ServerManager\Router\ServerRoute $oTestRoute
     * @param \ServerManager\Router\ServerRoute $oReferenceRoute
     * @return bool
     */
    public function isPathSubPathOf(ServerRoute $oTestRoute, ServerRoute $oReferenceRoute) {
        return strncasecmp($oTestRoute->getPath(), $oReferenceRoute->getPath(), strlen($oReferenceRoute->getPath())) === 0;
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