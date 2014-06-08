<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2013 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * Flash are temporary data passed from on request to another
 * Data should be deleted in the next request
 * 
 * @name        Core\View\FlashMessage
 * @desc        Trait for Views
 *
 */

namespace Voodoo\Core\View;

use Voodoo\Core;

class FlashMessage 
{
    const TYPE_ERROR = "error";
    const TYPE_DANGER = "danger";
    const TYPE_SUCCESS = "success";
    const TYPE_WARNING = "warning";
    const TYPE_NOTICE = "notice";
    const TYPE_HELP = "help";
       
    private $key = "flashMessage";
    
    public function __construct() {
        Core\Env::sessionStart();
        if (!isset($_SESSION[$this->key])){
            $_SESSION[$this->key] = [];
        }
    }
    /**
     * Set the flash
     * 
     * @param string $message
     * @param string $type
     * @param array $data
     * @return TView
     */
    public function set($message, $type = self::TYPE_NOTICE, Array $data = [])
    {
        $_SESSION[$this->key][] = [
            "message" => $message,
            "type" => $type,
            "data" => $data
        ];      
        return $this;
    }

    /**
     * Clear flash
     * 
     * @return TView
     */
    public function clear()
    {
        unset($_SESSION[$this->key]);
        return $this;
    }
    
    /**
     * Get the flash
     * 
     * @return type
     */
    public function get($type = null)
    {
        $flash = $_SESSION[$this->key];
        if ($flash) {
            if (! $type) {
                return $flash;
            } else {
                $newFlash = [];
                foreach ($flash as $stack) {
                    if ($stack["type"] == $type) {
                        $newFlash[] = $stack;
                    }
                }
                return $newFlash;
            }
        } else {
            return null;
        }
    }
}
