<?php
namespace ServerManager;
use ObjectFlow\Trait\InstanceTrait;

class ServerInfo {

    use InstanceTrait;

    public static function getServerPort() {
        return self::read('SERVER_PORT');
    }

    public static function getServerProtocol() {
        return self::read('SERVER_PROTOCOL');
    }

    public static function getServerProtocolFormatedForHttp() {
        preg_match('@HTTP@', self::getServerProtocol(), $aMatches);
        list($sHttpProtocol) = $aMatches;
        return strtolower($sHttpProtocol);
    }

    public static function getHttpHost() {
        return self::read('HTTP_HOST');
    }

    public static function getServerDocumentRoot() {
        return self::read('HTTP_HOST');
    }

    public static function getServerName() {
        return self::read('SERVER_NAME');
    }

    public static function read($sVariable) {
        return isset($_SERVER[$sVariable])
            ? $_SERVER[$sVariable]
            : null;
    }

}