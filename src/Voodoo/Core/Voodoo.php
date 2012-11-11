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
 * @name        Voodoo
 * @desc        Voodoo is the bootstrap class of the framework.
 *              Its role is to make sure the proper module/controller and action are loaded, based on the URI provided
 *              It also get the routes to reroute a URI to a new based on the path
 *
 */

namespace Voodoo\Core;

use Voodoo\Core,
    DirectoryIterator,
    ReflectionClass,
    ReflectionException;

class Voodoo
{
    CONST NAME = "VoodooPHP";
    
    CONST VERSION = "0.4.1";
    
    CONST AUTHOR = "Mardix < https://github.com/mardix >";
    
    CONST LICENSE = "MIT";
    
    CONST PHP_VERSION = "5.3";
    
    CONST REPO_LINK = "https://github.com/VoodooPHP/Voodoo";
    
    
/*******************************************************************************/
    
    
    private $segments = array();

    private $moduleName = "";

    private $controllerName = "";

    private $action = "";

    private $modulesPath = "";

    private $baseNamespace = "";

    // default Module
    private $defaultModule = "Main";
    
    // default controller
    private $defaultController = "Index";
    
    /**
     * The constructor
     *
     * @param string $modulesPath - The full path
     * @param string $URI         : Use a URI string in this format: /Module/Controller/Action. ie: /Store/Items/Delete/1
     * @param array  $Routes      : to override the default route
     */
    public function __construct($appName = "www", $URI = "/", Array $Routes = array())
    {
        if (version_compare(PHP_VERSION, self::PHP_VERSION, '<') ) {
            throw new Core\Exception (self::NAME." requires PHP ".self::PHP_VERSION." or greater");
        }
        
        $appName = $this->formatName($appName, true);
        $application = Path::VoodooApp()."/{$appName}";
        
        $this->setModulesPath($application);

        // Make sure we add the trailing slash
        $URI .= (!preg_match("/\/$/",$URI)) ? "/" : "";

        // Reroute the URI based on Routes
        $URI = Core\Router::Create($Routes)->parse($URI);

        /**
         * Build the URI segments that will be used to redirect to wherever in the application
         * /Module/Controller/Action
         */
        $this->segments = explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $URI));

    }

    /**
     * Root of Voodoo directory
     *
     * @return string
     */
    public function getRootDir()
    {
        return dirname(dirname(__DIR__));
    }

    /**
     * Get the modules path
     *
     * @param  string $module - The module name
     * @return string
     */
    public function getModulesPath($module = "")
    {
        $module = $this->formatName($module,true);

        return $this->modulesPath.($module ? ("/".$module): "");
    }

    /**
     * Set the modules path
     *
     * @param  string      $path
     * @return Core\Voodoo
     */
    public function setModulesPath($path)
    {
        $this->modulesPath = $path;
        $basename = str_replace(array($this->getRootDir(), "/","."), array("", "\\",""), $this->getModulesPath());
        $this->baseNamespace = preg_replace("/^\\\/", "", $basename);

        return $this;
    }
    /**
     * doMagic
     * Abracadabra!
     * Voila, Magic!
     * This is how it works, if a segment exist as Module,Controller,Action,
     * it will shift it and the segment will become the current
     * 
     * @param   string $module  - To change the default module
     * @param   string $controller - To change the default controller
     * @return Voodoo
     * @throws Core\Exception
     */
    public function doMagic($module = "", $controller = "")
    {
        if ($module){
            $this->defaultModule = $module;
        }
        if ($controller) {
            $this->defaultController = $controller;
        }
        
        
         /**
          * Set Module
          * Modules are the top level of the views and controllers. It's a like a directory for your appliction... well it is
          * By default, if no module is found, the 'Main' module will be accessed
          * If a module is not specified, it will fall in the main
          */
         $this->moduleName = $this->formatName($this->segments[0],true);

         if (!$this->moduleName || !is_dir($this->getModulesPath($this->moduleName))) {
             $this->moduleName = "";

            /**
             * Module Discovery
             * We'll evaluate each module name to see if any exactly match the requested module name
             */
            $s0  = strtolower($this->formatName($this->segments[0],true));
            foreach (new DirectoryIterator($this->getModulesPath()) as $fileInfo) {
                if (!$fileInfo->isDot() && $fileInfo->isDir()) {
                    if ( $s0 == strtolower($fileInfo->getFilename())) {
                        $this->moduleName = $fileInfo->getFilename();
                        array_shift($this->segments);
                        break;
                    }
                }
            }

             /**
              * Fall back to the Main module
              */
            if (!$this->moduleName){
               $this->moduleName = $this->formatName($this->defaultModule, true);
            }

            if (!is_dir($this->getModulesPath($this->moduleName))){
               throw new Core\Exception("Module: '{$this->moduleName}' doesn't exist!");
            }
         } else {
            array_shift($this->segments);
         }

         /**
          * Set the Controller
          * Controllers are classes in a module. They are responsible for the logic of your application.
          * The default controller is Index. And it's loaded by default if the controller doesn't exist or was not specified
          */
         try {

             $this->controllerName = $this->formatName($this->segments[0], true);

             if (!class_exists($this->getControllerNS())) {

                /**
                * Controller Discovery
                * When we can't find the controller based on the name provided, we'll try to discover it before we go to Index
                * We'll evaluate each controller name to see if it exactly matches the requested controller name
                */
                $s0  = strtolower($this->formatName($this->segments[0],true));
                foreach (new DirectoryIterator($this->getModulesPath($this->moduleName)) as $fileInfo) {
                    if ($fileInfo->isFile()) {
                        if ( $s0 == strtolower($fileInfo->getFilename())) {
                            $this->controllerName = $fileInfo->getFilename();
                            
                            if (class_exists($this->getControllerNS())) {
                                array_shift($this->segments);
                                break;
                            }
                        }
                    }
                }
             }

             $this->callControllerReflection();

             array_shift($this->segments);

         } catch (ReflectionException $e) {

             try {
                 // Fall back to Index
                 $this->controllerName = $this->formatName($this->defaultController, true);

             } catch (ReflectionException $e2) {
                 throw new Core\Exception("Controller :'$this->controllerName' is not found in Module: '{$this->moduleName}'","",$e2->getPrevious());
             }
         }


         /**
          * Set the Action
          * Actions are methods in the controllers in the format action_$name().
          * Each action is associate to a view file, and it is what is called to execute your application.
          * The default action is action_index(). If an action doesn't exist it will fall in the default one
          */

         $this->action = strtolower($this->formatName($this->segments[0],false));

         if ($this->action) {
             if (!$this->callControllerReflection()->hasMethod("action_{$this->action}")) {
                 if ($this->callControllerReflection()->hasMethod("action_404")) {
                     $this->action = "404";
                 } else {
                    $this->action = strtolower($this->formatName("index",false));
                    if(!$this->callControllerReflection()->hasMethod("action_{$this->action}")) {
                        throw new Core\Exception("Action: 'action_{$this->action}' is missing in: '".$this->callControllerReflection()->getName()."'");    
                    }
                 }
             
             } else {
                array_shift($this->segments);
             }
         } else {
             $this->action = "index";
         }



         $ControllerN = $this->getControllerNS();

         if (class_exists($ControllerN)) {

             $Controller = new $ControllerN($this->segments);

             $Controller->getAction($this->action);

         } else {
             throw new Core\Exception("Controller: '{$ControllerN}' doesn't exist!");
         }

        return $this;
    }

    /**
     * Call the controller reflection
     * @return \ReflectionClass
     */
    private function callControllerReflection()
    {
        return  new ReflectionClass($this->getControllerNS());
    }

    /**
     * return the controller's namespace
     * @return type
     */
    private function getControllerNS()
    {
        return
            "{$this->baseNamespace}\\{$this->moduleName}\\Controller\\{$this->controllerName}";
    }

    /**
     * Format the name properly. Only accept letters, numbers, dash and underscore
     * @param type $name
     * @param type $pascalCase
     */
    private function formatName($name,$pascalCase = false)
    {
        $name = preg_replace("/[^a-z09_\-]/i","",$name);
        return Core\Helpers::camelize($name,$pascalCase);
    }

}
