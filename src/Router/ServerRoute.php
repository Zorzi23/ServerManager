<?php
namespace ServerManager\Router;
use HttpFlow\Headers\HttpHeader;
use ObjectFlow\GenericObject;
use ObjectFlow\Trait\CacheableInstanceTrait;

class ServerRoute {

    /**
     * 
     * @var string[] 
     */
    private $aMethods = [];

    /**
     * 
     * @var string[]
     */
    private $aProtocols = [];

    /**
     * 
     * @var string 
     */
    private $sPath = '/';

    /**
     * 
     * @var string 
     */
    private $sFile = '';
    
    /**
     * 
     * @var HttpHeader[]
     */
    private $aRequestHeaders = [];
    
    /**
     * 
     * @var HttpHeader[]
     */
    private $aResponseHeaders = [];

    /**
     * 
     * @var GenericObject 
     */
    private $oCustomConfig = null;
    
    /**
     * 
     * @var Closure|null 
     */
    private $fnHandle;

    use CacheableInstanceTrait;

    public static function https($xMethods, $sPath, $fnHandle = null) {
        return (new static())
            ->setProtocols(['http', 'https'])
            ->setPath($sPath)
            ->setMethods(is_array($xMethods) ? $xMethods : [ $xMethods ])
            ->setFnHandle($fnHandle);
        }
        
    public static function protocolMethodPathFile($xProtocols, $xMethods, $sPath, $sFile) {
        return static::protocolMethodPath($xProtocols, $xMethods, $sPath)
            ->setFile($sFile);
    }

    public static function protocolMethodPath($xProtocols, $xMethods, $sPath) {
        return (new static())
            ->setProtocols(is_array($xProtocols) ? $xProtocols : [ $xProtocols ])
            ->setMethods(is_array($xMethods) ? $xMethods : [ $xMethods ])
            ->setPath($sPath);
    }


    /**
     * 
     * @return string[]
     */
    public function getMethods() {
        return $this->aMethods;
    }

    /**
     * 
     * @param string $sMethod
     * @return bool
     */
    public function hasMethod($sMethod) {
        return in_array($sMethod, $this->getMethods());
    }

    /**
     * 
     * @param string[] $aMethods
     * @return self
     */
    public function setMethods($aMethods) {
        $this->aMethods = $aMethods;
        return $this;
    }

    /**
     * 
     * @return string[]
     */
    public function getProtocols() {
        return $this->aProtocols;
    }

    /**
     * 
     * @param string[]
     * @return self
     */
    public function setProtocols($aProtocols) {
        $this->aProtocols = $aProtocols;
        return $this;
    }

    /**
     * 
     * @param string $sProtocol
     * @return self
     */
    public function addProtocol($sProtocol) {
        $this->aProtocols[] = $sProtocol;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getPath() {
        return $this->sPath;
    }

    /**
     * 
     * @return string
     */
    public function getFile() {
        return $this->sFile;
    }

    /**
     * 
     * @return array
     */
    public function getPathArray() {
        $aPath = explode('/', $this->getPath()); 
        return array_values(array_filter($aPath));
    }

    /**
     * 
     * @return string
     */
    public function getParentPath() {
        $aTestRouteParentsPath = $this->getPathArray();
        array_pop($aTestRouteParentsPath);
        return implode('', $aTestRouteParentsPath);
    }

    /**
     * 
     * @return self
     */
    public function setPath($sPath) {
        $this->sPath = strtr('{sPath}/', [
            '{sPath}' => rtrim($sPath, '/')
        ]);
        return $this;
    }

    /**
     * 
     * @param string $sFile
     * @return self
     */
    public function setFile($sFile) {
        $this->sFile = $sFile;
        return $this;
    }

    /**
     * 
     * @return HttpHeader[]
     */
    public function getRequestHeaders() {
        return $this->aRequestHeaders;
    }

    /**
     * 
     * @return HttpHeader[]
     */
    public function getResponseHeaders() {
        return $this->aResponseHeaders;
    }

    /**
     * 
     * @return HttpHeader|null
     */
    public function getRequestHeaderByName($sName) {
        foreach($this->aRequestHeaders as $oHeader) {
            if($oHeader->getName() === $sName) {
                return $oHeader;
            }
        }
        return null;
    }

    /**
     * 
     * @return HttpHeader|null
     */
    public function getResponseHeaderByName($sName) {
        foreach($this->aResponseHeaders as $oHeader) {
            if($oHeader->getName() === $sName) {
                return $oHeader;
            }
        }
        return null;
    }

    /**
     * 
     * @param HttpHeader[] $aHeaders
     * @return self
     */
    public function setRequestHeaders($aHeaders) {
        $this->aRequestHeaders = $aHeaders;
        return $this;
    }

    /**
     * 
     * @param HttpHeader[] $aHeaders
     * @return self
     */
    public function setResponseHeaders($aHeaders) {
        $this->aResponseHeaders = $aHeaders;
        return $this;
    }

    /**
     * 
     * @param HttpHeader $oHeader
     * @return self
     */
    public function addRequestHeader($oHeader) {
        $this->aRequestHeaders[] = $oHeader;
        return $this;
    }

    /**
     * 
     * @param HttpHeader $oHeader
     * @return self
     */
    public function addResponseHeader($oHeader) {
        $this->aResponseHeaders[] = $oHeader;
        return $this;
    }

    /**
     * 
     * @return callable|Closure
     */
    public function getFnHandle() {
        return $this->fnHandle;
    }

    /**
     * 
     * @param callable|Closure $fnHandle
     * @return self
     */
    public function setFnHandle($fnHandle){
        $this->fnHandle = $fnHandle;
        return $this;
    }

    /**
     * 
     * @return GenericObject
     */
    public function getCustomConfig() {
        return $this->propCoalesce('oCustomConfig', [GenericObject::newInstance()]);
    }

    /**
     * 
     * @return GenericObject
     */
    public function getCustomConfigAsGenericObjectRecursive() {
        $oCustomConfig = $this->getCustomConfig();
        return GenericObject::fromArrayObjectRecursive($oCustomConfig->data());
    }

    /**
     * 
     * @param GenericObject $oCustomConfig
     * @return self
     */
    public function setCustomConfig($oCustomConfig) {
        $this->oCustomConfig = $oCustomConfig;
        return $this;
    }

    public function onHandle($aParams = []) {
        $fnOnHandle = $this->getFnHandle();
        if(!is_callable($fnOnHandle)) {
            return null;
        }
        foreach($this->getResponseHeaders() as $oHeader) {
            header($oHeader->raw(), true);
        }
        return call_user_func_array($fnOnHandle, $aParams);
    }

}