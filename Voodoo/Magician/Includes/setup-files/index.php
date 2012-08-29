<?php
/**
 * -----------------------------------------------------------------------------
 *                      VoodooPHP Front Controller
 * 
 * The main entry point of your application
 * 
 * Every request is rerouted through here and we'll do the magic... Abracadabra...
 * 
 * -----------------------------------------------------------------------------
 * 
 * @name        index.php
 *
 */
/******************************************************************************/


use Voodoo\Core;

/**
 * The base path or the root of the application, where the directories: Voodoo & Application reside. 
 */
$BASE_PATH = ".";

/**
 * Init 
 */
require($BASE_PATH."/Voodoo/init.php");
    
    try{
        /**
        * Let's do the magic
        * That's all you need to run the application.
        * Everything else is in you Controller...
        * 
        * Voila, Magic!
        */
        Core\Voodoo::Magic(Core\HTTP\URI::getPathSegments());
                 
    }catch(Core\Exception $e){


        /**
        * Notify admin of this exception
        * @param String - Email address
        */
        if(Core\Config::get("System.SendExceptionToEmail")){
           $e->sendToEmail(Core\Config::get("System.AdminEmail")); 
        }
        


        /**
        * Something really bad happened. We'll show it and exit the application 
        */
        print(VOODOO_NAME." Fatal Exception: ".$e->getMessage());


        exit;
    }


