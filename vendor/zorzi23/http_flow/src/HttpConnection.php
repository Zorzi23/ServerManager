<?php
namespace HttpFlow;

class HttpConnection {
    
    /**
     * 
     * @return void
     */
    public static function closeConnection() {
        header('Content-Enconding: none');
        header('Connection: close');
        header(sprintf('Content-Length: %s', ob_get_length()));
        if(function_exists('fastcgi_finish_request')) {
            \fastcgi_finish_request();
        }
        self::flush();
    }

    /**
     * 
     * @return void
     */
    public static function flush() {
        ob_end_flush();
        flush();
    }

}