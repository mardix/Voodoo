<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2012 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * @name        Core\HTTP\Response
 * @desc        Set the HTTP response
 */

namespace Voodoo\Core\Http;

class Response
{
    private static $httpCodes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content Found', // use 204 instead of 404 when server response ok, but not the content requested
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        420 => 'Rate Limit Reached',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    ]; 
    
    /**
     * Get the HTTP Code
     * @param int $code
     * @return string
     */
    public static function getHttpCode($code = 0)
    {
        return isset(self::$httpCodes[$code]) ? self::$httpCodes[$code] : null;
    }
    
    /**
     * Set headers
     * 
     * @param string $data
     * @param int $code
     */
    public static function setHeader($data = "", $code = 0)
    {
        header($data, true, $code);
    }
    
    /**
     * Set the status
     * @param <type> $status
     */
    public static function setStatus($code = 200)
    {
        $status_header = 'HTTP/1.1 ' . $code . ' ' . self::getHttpCode($code);
        self::setHeader($status_header, $code);
    }   
    
    public static function redirect($url, $httpCode = 302)
    {
        self::setHeader("Location: {$url}", $httpCode);
        exit;
    }
}


