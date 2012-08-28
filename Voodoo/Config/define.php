<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/VoodooPHP/Voodoo
 * 
 * @copyright   (c) 2012 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 * @desc    Define all the constants in this file
 */

/**
 * Name 
 */
define("VOODOO_NAME","VoodooPHP");

/**
 * Version  
 */
define("VOODOO_VERSION","1.3.1");

/**
 * Author 
 */
define("VOODOO_AUTHOR_NAME","Mardix");

/**
 * Repo 
 */
define("VOODOO_REPO_LINK","https://github.com/VoodooPHP/Voodoo");

/**
 * PHP Requirements 
 */
define("VOODOO_PHP_VERSION","5.3");

/**
 * License 
 */
define("VOODOO_LICENSE","MIT");


/**
 * Generator. For scripts that will create file 
 */
define("VOODOO_GENERATOR",VOODOO_NAME." ".VOODOO_VERSION);

/*******************************************************************************/

if(!isset($BASE_PATH))
    $BASE_PATH = ".";

// BASE PATH
define("BASE_PATH",$BASE_PATH);

/*******************************************************************************/
# SOUP
// The main sysytem path
define("VOODOO_PATH",BASE_PATH."/Voodoo");
    /**
     * Hold config
     */
    define("VOODOO_CONFIG_PATH",VOODOO_PATH."/Config");
    /**
     * Hold the system's kitchen... where we cook stuff
     */
    define("VOODOO_MAGICIAN_PATH",VOODOO_PATH."/Magician"); 
    /**
     * Contains the template to auto create Module. It can contain models controllers and views
     */
    define("VOODOO_MAGICIAN_MODULES_TEMPLATES_PATH",VOODOO_MAGICIAN_PATH."/Includes/Modules-Templates");

    
/*******************************************************************************/
/**
 * AddOn
 * Third party app/class under the namespace \AddOn, that extends Soup
 */
define("ADDON_PATH",BASE_PATH."/AddOn");


/*******************************************************************************/
# Application

// APPLICATION PATH DEFINE
define("APPLICATION_PATH",BASE_PATH."/Application");

    /**
     * Holds user defined class 
     */
    define("APPLICATION_LIBS_PATH",APPLICATION_PATH."/Lib");
    
    
    /**
     * Holds your application's Model 
     */
    define("APPLICATION_MODELS_PATH",APPLICATION_PATH."/Model");


    /**
     * Holds your application Module 
     */
    define("APPLICATION_MODULES_PATH",APPLICATION_PATH."/Module");
              

    /**
     * Configuration for the application
     */
    define("APPLICATION_CONFIG_PATH",APPLICATION_PATH."/Config");     
               
    /**
     * Holds all the files to be included
     */
    define("APPLICATION_INCLUDES_PATH",APPLICATION_PATH."/Includes");     
    
         
    /**
     * Holds all the variables data, such as upload temporary files, cache etc
     */
    define("APPLICATION_VAR_PATH",APPLICATION_PATH."/Var");   
        /**
         * DB files for SQLite
         */
        define("APPLICATION_DB_PATH",APPLICATION_VAR_PATH."/db");    
        /**
         * Temporary files
         */
        define("APPLICATION_TMP_PATH",APPLICATION_VAR_PATH."/tmp");       
        /**
         * Cache files
         */
        define("APPLICATION_CACHE_PATH",APPLICATION_VAR_PATH."/cache");  
        
/*******************************************************************************/    
/**
* SharedAssets 
* Holds shared assets (js,css etc..) that can be used by all Modules
*/
define("SHARED_ASSETS_PATH",BASE_PATH."/SharedAssets"); 
  

    