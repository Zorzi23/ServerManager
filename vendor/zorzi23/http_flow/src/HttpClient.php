<?php
namespace HttpFlow;
use CurlHandle;
use HttpFlow\Headers\HttpHeader;
use ObjectFlow\GenericObject;

/**
 * Class representing an HTTP client
 */
class HttpClient extends GenericObject {

    /**
     * Create a new HttpClient instance with URL and method
     * @param string $sUrl
     * @param string $sMethod
     * @return self
     */
    public static function urlMethod($sUrl, $sMethod) {
        return (new self())
            ->setUrl($sUrl)
            ->setMethod($sMethod);
    }

    /**
     * Set the URL for the HTTP client
     * @throws \Exception
     * @param string $sUrl
     * @return self
     */
    public function setUrl($sUrl) {
        if (!filter_var($sUrl, FILTER_VALIDATE_URL)) {
            throw new \Exception("Malformed URL: $sUrl");
        }
        $this->__set('url', $sUrl);
        return $this;
    }

    /**
     * Extract data from the URL
     * @return GenericObject|null
     */
    public function extractDataFromUrl() {
        $sUrl = $this->getUrl();
        if (!$sUrl) {
            return null;
        }
        $aData = parse_url($sUrl);
        return GenericObject::fromArrayObject($aData);
    }

    /**
     * Get treated headers
     * @return string[]
     */
    public function getTreatedHeaders() {
        return array_map(function($oHeader) {
            return $oHeader->raw();
        }, $this->getHeaders() ?: []);
    }

    /**
     * Add a header to the HTTP client
     * @param HttpHeader $oHeader
     * @return self
     */
    public function addHeader(HttpHeader $oHeader) {
        $this->__add('headers', $oHeader);
        return $this;
    }

    /**
     * Get options for the HTTP client
     * @return array
     */
    private function getOptions() {
        $aOptions = [];
        foreach ($this->configOptionsMethods() as $sMethod) {
            $aCustomOptions = call_user_func([$this, $sMethod]);
            $aOptions += array_filter($aCustomOptions);
        }
        return $aOptions;
    }

    /**
     * Get URL scheme configuration
     * @return array
     */
    private function getUrlSchemeConfig() {
        $aOptions = [
            CURLOPT_URL => $this->getUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $this->getMethod(),
            CURLOPT_FORBID_REUSE => $this->getForbidReuse(),
            CURLOPT_VERBOSE => $this->getVerbose(),
            CURLOPT_FRESH_CONNECT => $this->getForceNewConnection(),
            CURLOPT_TIMEOUT => $this->getTimeout(),
            CURLOPT_HTTPHEADER => $this->getTreatedHeaders(),
        ];
        $this->addPostFields($aOptions);
        $this->addIpResolveOptions($aOptions);
        return $aOptions;
    }

    /**
     * Add POST fields to options if method is POST
     * @param array $aOptions
     */
    private function addPostFields(&$aOptions) {
        if ($this->getMethod() == 'POST') {
            $aOptions[CURLOPT_POSTFIELDS] = http_build_query($this->getData());
        }
    }

    /**
     * Add IP resolve options to options
     * @param array $aOptions
     */
    private function addIpResolveOptions(&$aOptions) {
        if ($this->isUseIpv4()) {
            $aOptions[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }
        if ($this->isUseIpv6()) {
            $aOptions[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V6;
        }
    }

    /**
     * Get proxy configuration
     * @return array
     */
    private function getProxyConfig() {
        $sProxy = $this->getProxy();
        if (!$sProxy) {
            return [CURLOPT_NOPROXY => '127.0.0.1'];
        }
        return $this->buildProxyConfig($sProxy);
    }

    /**
     * Get proxy URL
     * @return string|null
     */
    private function getProxy() {
        $oData = $this->extractDataFromUrl();
        $sCurrentProtocol = strtoupper($oData->getScheme());
        $sEnvVar = strtr('{protocol}_PROXY', ['{protocol}' => $sCurrentProtocol]);
        $sProxy = getenv($sEnvVar) ?: $this->getProxyUrl();
        $bUseProxy = $this->getUseProxy();
        return $bUseProxy === null ? $sProxy : ($bUseProxy ? $sProxy : null);
    }

    /**
     * Build proxy configuration array
     * @param string $sProxy
     * @return array
     */
    private function buildProxyConfig($sProxy) {
        $xProxyType = $this->getProxyAuthType();
        if ($xProxyType && defined($xProxyType)) {
            $xProxyType = constant($xProxyType);
        }
        return [
            CURLOPT_PROXY => $sProxy,
            CURLOPT_PROXYUSERNAME => $this->getProxyUser(),
            CURLOPT_PROXYPASSWORD => $this->getProxyUserPassword(),
            CURLOPT_PROXYAUTH => $xProxyType === null ? CURLAUTH_NTLM : $xProxyType
        ];
    }

    /**
     * Get SSL configuration
     * @return array
     */
    private function getSslConfig() {
        $bUseSsl = $this->isUseSsl();
        if ($bUseSsl === null || $bUseSsl) {
            return [];
        }
        return $this->buildSslConfig();
    }

    /**
     * Build SSL configuration array
     * @return array
     */
    private function buildSslConfig() {
        $bVerifyPeer = $this->getSslVerifyPeer();
        $bVerifyHost = $this->getSslVerifyHost();
        return [
            CURLOPT_SSL_VERIFYPEER => $bVerifyPeer === null ? true : $bVerifyPeer,
            CURLOPT_SSL_VERIFYHOST => $bVerifyHost === null ? 2 : $bVerifyHost
        ];
    }

    /**
     * Get authentication configuration
     * @return array
     */
    private function getAuthConfig() {
        return [CURLOPT_HTTPAUTH => $this->getHttpAuth()];
    }

    /**
     * Configure the cURL handle
     * @param resource $oHandle
     * @return resource|CurlHandle
     */
    private function config($oHandle) {
        foreach ($this->getOptions() as $iOpt => $xValue) {
            curl_setopt($oHandle, $iOpt, $xValue);
        }
        return $oHandle;
    }

    /**
     * Get methods for configuring options
     * @return array
     */
    private function configOptionsMethods() {
        return [
            'getUrlSchemeConfig',
            'getAuthConfig',
            'getSslConfig',
            'getProxyConfig'
        ];
    }

    /**
     * Fetch the response from the URL
     * @throws \Exception
     * @return string|false
     */
    public function fetch() {
        $oHandle = curl_init();
        $oHandle = $this->config($oHandle);
        $xResponse = curl_exec($oHandle);
        if (curl_errno($oHandle)) {
            $sError = curl_error($oHandle);
            curl_close($oHandle);
            throw new \Exception($sError);
        }
        curl_close($oHandle);
        return $xResponse;
    }
}