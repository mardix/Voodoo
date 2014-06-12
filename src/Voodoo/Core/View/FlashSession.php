<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2014 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * Flash storage using the $_SESSION storage
 * 
 * @name        Core\View\FlashSession
 * @desc        Flash Session for local Session
 *
 */
namespace Voodoo\Core\View;

use Voodoo\Core;

class FlashSession implements IFlashStorage
{
    private $key = "__flashsession__";
    
    public function __construct() 
    {
        Core\Env::sessionStart();
        if (!isset($_SESSION[$this->key])){
            $_SESSION[$this->key] = [];
        }         
    }
    
    /**
     * Set session data
     * 
     * @param array $data
     * @return \Voodoo\Core\View\FlashSession
     */
    public function set(Array $data)
    {
        $_SESSION[$this->key][] = [
            "message" => $message,
            "type" => $type,
            "data" => $data
        ];  
        return $this;
    }
    
    /**
     * Get the session
     * @return Array
     */
    public function get()
    {
        return $_SESSION[$this->key];        
    }
    
    /**
     * Clear session
     */
    public function clear()
    {
        unset($_SESSION[$this->key]);        
    }
}
