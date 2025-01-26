<?php
namespace HttpFlow;
use ObjectFlow\GenericObject;
use ServerManager\ServerInfo;

class HttpRequestHandler {

    /**
     * 
     * @param int $iCode
     * @return int
     */
    public static function responseCode($iCode) {
        http_response_code($iCode);
        return $iCode;
    }

    /**
     * 
     * @return GenericObject
     */
    public static function post() {
        return GenericObject::fromArrayObject($_POST);
    }
    
    /**
     * 
     * @return GenericObject
     */
    public static function get() {
        return GenericObject::fromArrayObject($_GET);
    }

    /**
     * 
     * @return string
     */
    public static function getRequestedMethod() {
        return ServerInfo::read('REQUEST_METHOD');
    }

    /**
     * 
     * @return string
     */
    public static function getRequestedProtocol() {
        return ServerInfo::read('REQUEST_SCHEME');
    }

    /**
     * 
     * @return string
     */
    public static function getRequestedFullPath() {
        return ServerInfo::read('REQUEST_URI');
    }

    /**
     * 
     * @return \SplFileInfo
     */
    public static function getRequestedFileInfoPath() {
        return new \SplFileInfo(static::getRequestedFullPath());
    }

    /**
     * 
     * @return string
     */
    public static function getRequestedPath() {
        $oFileInfo = static::getRequestedFileInfoPath();
        if($oFileInfo->getExtension()) {
            return $oFileInfo->getPath();
        }
        return $oFileInfo->getPathName();
    }

    /**
     * 
     * @return string
     */
    public static function getRequestedFile() {
        $oFileInfo = static::getRequestedFileInfoPath();
        if(!$oFileInfo->getExtension()) {
            return null;
        }
        return $oFileInfo->getFilename();
    }

    /**
     * 
     * @return bool
     */
    public static function isHeadersSent() {
        return headers_sent();
    }

}