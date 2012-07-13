<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/VoodooPHP/Voodoo
 * @package     VoodooPHP
 * 
 * @copyright   (c) 2012 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 * 
 * @name        Core\Exception
 * @since       Apr 23, 2011
 * @desc        Exception
 * 
 */

namespace Voodoo\Core;

class Exception extends \Exception{
    
    /**
     * To notify admin of an exception 
     */
    public function notifyAdmin($email){
        
    }
    
    public function __toString(){
        return "Message: ".$this->getMessage()." - Code:".$this->getCode();
    }
}



