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
 * @desc        Exception
 * 
 */

namespace Voodoo\Core;

class Exception extends \Exception{
    
    public function __toString(){
        return "Message: ".$this->getMessage()." - Code:".$this->getCode();
    }
    
    
    
    /**
     * Allow the exception to be sent to an email address
     * 
     * @param string $email 
     */
    public function sendToEmail($email){

    $_post = print_r($_POST,true);
    $_get = print_r($_GET,true);
    $_cookie = print_r($_COOKIE,true);
    $_request = print_r($_REQUEST,true);
    $_server = print_r($_SERVER,true);
    $_e = print_r($this,true);

$message = "
VoodooPHP is reporting an exception error in your application

Server: {$_SERVER["HTTP_HOST"]}

Date: ".date("Y-m-d H:i:s")."


--------------------------------------------------------------------------------

>> EXCEPTION MESSAGE
".$this->__toString()."

--------------------------------------------------------------------------------
        
>> EXCEPTION: 
$_e

--------------------------------------------------------------------------------
        
>> SERVER: 
{$_server}

--------------------------------------------------------------------------------

>> REQUEST: 
{$_request}

--------------------------------------------------------------------------------

>> POST : 
{$_post}

--------------------------------------------------------------------------------

>> GET:
{$_get}

--------------------------------------------------------------------------------

>> COOKIE: 
{$_cookie}

"; 
     // Send
     @mail($email, "{$_SERVER["HTTP_HOST"]} : EXCEPTION ERROR CAUGHT ", $message);

    }
    

}



