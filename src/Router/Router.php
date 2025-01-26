<?php
namespace ServerManager\Router;
use HttpFlow\Headers\HttpHeader;
use HttpFlow\HttpRequestHandler;
use ServerManager\Router\ServerRoute;
use ServerManager\Router\Validator\RouterHeaderStrategyValidator;
use ServerManager\Router\Validator\RouterMethodStrategyValidator;
use ServerManager\Router\Validator\RouterPathStrategyValidator;
use ServerManager\Router\Validator\RouterProtocolStrategyValidator;

class Router {

    /**
     * 
     * @var ServerRoute[]
     */
    private $aRoutes = [];

    /**
     * 
     * @var ServerRoute 
     */
    private $oHandleRoute = null;

    /**
     * 
     * @var callable 
     */
    private $fnOnErrorHandle = null;

    /**
     * 
     * @return static
     */
    public static function newInstance() {
        return new static();
    }

    /**
     * 
     * @param mixed $aRoutes
     * @return Router
     */
    public static function routeCurrentRequest($aRoutes) {
        return static::route($aRoutes, self::createRequestedServerRoute());
    }

    public static function route($aRoutes, $oReferenceRoute) {
        $oRouter = static::newInstance();
        $oRouter->setRoutes($aRoutes);
        $oRouter->handle($oReferenceRoute);
        return $oRouter;
    }

    /**
     * 
     * @return ServerRoute[]
     */
    public function getRoutes() {
        return $this->aRoutes;
    }

    /**
     * 
     * @return ServerRoute
     */
    public function getRequestedRoute() {
        return static::createRequestedServerRoute();
    }

      /**
     * 
     * @return ServerRoute
     */
    public function getHandleRoute() {
        return $this->oHandleRoute;
    }

    /**
     * 
     * @param ServerRoute[] $aRoutes
     * @return self
     */
    public function setRoutes($aRoutes) {
        $this->aRoutes = $aRoutes;
        return $this;
    }

    /**
     * 
     * @param ServerRoute $oRoutes
     * @return self
     */
    public function addRoute(ServerRoute $oRoute) {
        $this->aRoutes[] = $oRoute;
        return $this;
    }

    /**
     * 
     * @return self
     */
    public function setHandleRoute($oHandleRoute) {
        $this->oHandleRoute = $oHandleRoute;
        return $this;
    }

    public function handle($oTestRoute) {
        foreach($this->getRoutes() as $oReferenceRoute) {
            if(!$this->isValidRoute($oTestRoute, $oReferenceRoute)) {
                continue;
            }
            $this->setHandleRoute($oReferenceRoute);
            $aRoutes = [
                'handle' => $oTestRoute,
                'handleReference' => $oReferenceRoute,
            ];
            $oReferenceRoute->onHandle([$aRoutes]);
            return $this;
        }
        $this->onErrorHandle([$oTestRoute]);
        return $this;
    }

    public function isValidRoute(ServerRoute $oTestRoute, ServerRoute $oReferenceRoute) {
        foreach($this->validationStrategys() as $sValidationClass) {
            $oValidator = call_user_func([$sValidationClass, 'newInstance']);
            if(!$oValidator->validate($oTestRoute, $oReferenceRoute)) {
                return false;
            }
        }
        return true;
    }

    protected function validationStrategys() {
        return [
            RouterProtocolStrategyValidator::class,
            RouterMethodStrategyValidator::class,
            RouterPathStrategyValidator::class,
            RouterHeaderStrategyValidator::class
        ];
    }

    /**
     * 
     * @return ServerRoute
     */
    public static function createRequestedServerRoute() {
        $oRoute = ServerRoute::protocolMethodPathFile(
            HttpRequestHandler::getRequestedProtocol(),
            HttpRequestHandler::getRequestedMethod(),
            HttpRequestHandler::getRequestedPath(),
            HttpRequestHandler::getRequestedFile(),
        );
        foreach(getallheaders() as $sHeader => $xValue) {
            $oRoute->addRequestHeader(HttpHeader::nameValue($sHeader, $xValue));
        }
        return $oRoute;
    }


    /**
     * 
     * @return callable
     */
    public function getFnOnErrorHandle() {
        return $this->fnOnErrorHandle;
    }

    /**
     * 
     * @param callable $fnOnErrorHandle
     * @return self
     */
    public function setFnOnErrorHandle($fnOnErrorHandle) {
        $this->fnOnErrorHandle = $fnOnErrorHandle;
        return $this;
    }

    public function onErrorHandle($aParams = []) {
        $fnOnErrorHandle = $this->getFnOnErrorHandle();
        if(!is_callable($fnOnErrorHandle)) {
            return $this->onErrorHandleDefault();
        }
        return call_user_func_array($fnOnErrorHandle, $aParams);
    }

    protected function onErrorHandleDefault() {
        http_response_code(404);
        die;
    }

    public static function redirectToPath($sPath) {
        header("Location: {$sPath}");
        return $sPath;
    }

    public static function redirectToServerFile($sFile) {
        require_once($sFile);
        return $sFile;
    }
    
}