<?php
namespace HttpFlow;
use HttpFlow\Headers\HttpHeader;
use ObjectFlow\Trait\InstanceTrait;
use HttpFlow\HttpRequestHandler;

class HttpRequestResponse {

    use InstanceTrait;

    public function buffFlush($xContent) {
        $this->buff($xContent);
        ob_flush();
        return $this;
    }

    public function buff($xContent) {
        echo is_string($xContent) ? $xContent : '';
        return $this;
    } 

    public function json($xValue, $iFlags = JSON_PRETTY_PRINT) {
        if(!HttpRequestHandler::isHeadersSent()) {
            $this->throwHeader(HttpHeader::nameValue('Content-Type', 'text/json'));
        }
        echo json_encode($xValue, $iFlags);
        return $this;
    }

    public function throwHeader(HttpHeader $oHeader) {
        header($oHeader->raw());
        return $this;
    }

    /**
     * 
     * @param int $iCode
     * @return static
     */
    public function statusCode($iCode) {
        HttpRequestHandler::responseCode($iCode);
        return $this;
    }

}