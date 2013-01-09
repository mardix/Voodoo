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

use DirectoryIterator,
    ReflectionClass,
    ReflectionException;

class Voodoo
{
    CONST NAME = "VoodooPHP";
    
    CONST VERSION = "0.13";
    
    CONST AUTHOR = "Mardix < https://github.com/mardix >";
    
    CONST LICENSE = "MIT";

    CONST REPO_LINK = "https://github.com/VoodooPHP/Voodoo";
    
    
/*******************************************************************************/

    private $routingSegments = [];

    private $moduleName = "";

    private $controllerName = "";

    private $action = "";
    
    private $config = null;
    
    private $appPath = "";

    private $baseNamespace = "";

    private $appRootDir;
    
    private $routes = [];
    
    private $uri = "/";
    
    private $defaultAppName = "Www";
    
    // default Module
    private $defaultModule = "Main";
    
    // default controller
    private $defaultController = "Index";


    /**
     * The constructor 
     * 
     * @param string $appRootDir
     * @param string $appName
     * @param string $uri
     * @throws Exception
     */
    public function __construct($appRootDir, $appName = "Www", $uri = "/") 
    {
        $this->appRootDir = $appRootDir;
        $this->setAppPath($appName);
        
        if (! is_dir($this->appPath)) {
            throw new Exception("The App directory doesn't exist at: {$this->appPath}");
        }
        // Register the autoload for App\
        Autoloader::register(dirname($this->appRootDir));
        
        // Application's config file
        $configFile = $this->appPath."/Config.ini";
        $this->config = (new Config("VoodooApp"))->loadFile($configFile);        
        
        Env::setAppPath($this->appRootDir);
        $this->setConfigPath(Env::getAppPath()."/_config");
        $this->setUri($uri); 
        $this->setRouting($this->config->get("routes.path") ?: []);
        
        /**
         * Set default Module and Controller
         */
        if ($this->config->get("application.defaultModule")) {
            $this->defaultModule = $this->config->get("application.defaultModule");
        }
        if ($this->config->get("application.defaultController")) {
            $this->defaultController = $this->config->get("application.defaultController");
        }        
    }

    /**
     * Set the default module
     * 
     * @param string $moduleName
     * @return \Voodoo\Voodoo
     */
    public function setDefaultModule($moduleName)
    {
        $this->defaultModule = $moduleName;
        return $this;
    }
    
    /**
     * Set the default Controller
     * 
     * @param string $controllerName
     * @return \Voodoo\Voodoo
     */
    public function setDefaultController($controllerName)
    {
        $this->defaultController = $controllerName;
        return $this;
    }
    
    /**
     * Set the routes
     * By default the routes are loaded from the app config file.
     * Stting it here will override the default one
     * @param type $routes
     */
    public function setRouting(Array $routes)
    {
        $this->routes = $routes;
    }
    
  
    /**
     * Config is restricted directory that contains the app config
     * By default it reside in the App/_config
     * @param type $path
     * @return \Voodoo\Voodoo
     */
    public function setConfigPath($path)
    {
        Env::setConfigPath($path);
        return $this;
    }
    
    
    public function setPublicAssetsPath($path)
    {
        Env::setPublicAssetsPath($path);
        return $this;
    }
    /**
     * Set the URI to match the pattern : Module/Controller/Action
     * 
     * @param string $uri
     * @return \Voodoo\Voodoo
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }    
    
    /**
     * doMagic
     * Abracadabra!
     * Voila, Magic!
     * This is how it works, if a segment exist as Module,Controller,Action,
     * it will shift it and the segment will become the current
     * 
     * @return Voodoo
     * @throws Exception
     */
    public function doMagic()
    {
        $this->parseRoutes();
        
         /**
          * Set Module
          * Modules are the top level of the views and controllers. It's a like a directory for your appliction... well it is
          * By default, if no module is found, the 'Main' module will be accessed
          * If a module is not specified, it will fall in the main
          */
         $this->moduleName = $this->formatName($this->routingSegments[0],true);
         if (! $this->moduleName || ! is_dir($this->getModulesPath($this->moduleName))) {
             $this->moduleName = "";
            /**
             * Module Discovery
             * We'll evaluate each module name to see if any exactly match the requested module name
             */
            $s0  = strtolower($this->formatName($this->routingSegments[0],true));
            foreach (new DirectoryIterator($this->getModulesPath()) as $fileInfo) {
                if (! $fileInfo->isDot() && $fileInfo->isDir()) {
                    if ( $s0 == strtolower($fileInfo->getFilename())) {
                        $this->moduleName = $fileInfo->getFilename();
                        array_shift($this->routingSegments);
                        break;
                    }
                }
            }
            if (! $this->moduleName){
               $this->moduleName = $this->formatName($this->defaultModule, true);
            }
            if (! is_dir($this->getModulesPath($this->moduleName))){
               throw new Exception("Module: '{$this->moduleName}' doesn't exist!");
            }
         } else {
            array_shift($this->routingSegments);
         }

         /**
          * Set the Controller
          * Controllers are classes in a module. They are responsible for the logic of your application.
          * The default controller is Index. And it's loaded by default if the controller doesn't exist or was not specified
          */
         try {
             $this->controllerName = $this->formatName($this->routingSegments[0], true);
             if (! class_exists($this->getControllerNS())) {
                /**
                * Controller Discovery
                * When we can't find the controller based on the name provided, we'll try to discover it before we go to Index
                * We'll evaluate each controller name to see if it exactly matches the requested controller name
                */
                $s0  = strtolower($this->formatName($this->routingSegments[0],true));
                foreach (new DirectoryIterator($this->getModulesPath($this->moduleName)) as $fileInfo) {
                    if ($fileInfo->isFile()) {
                        if ( $s0 == strtolower($fileInfo->getFilename())) {
                            $this->controllerName = $fileInfo->getFilename();
                            
                            if (class_exists($this->getControllerNS())) {
                                array_shift($this->routingSegments);
                                break;
                            }
                        }
                    }
                }
             }
             $this->callControllerReflection();
             array_shift($this->routingSegments);
         } catch (ReflectionException $e) {
             try {
                 // Fall back to Index
                 $this->controllerName = $this->formatName($this->defaultController, true);
             } catch (ReflectionException $e2) {
                 throw new Exception("Controller :'$this->controllerName' is not found in Module: '{$this->moduleName}'","",$e2->getPrevious());
             }
         }


         /**
          * Set the Action
          * Actions are methods in the controllers in the format action_$name().
          * Each action is associate to a view file, and it is what is called to execute your application.
          * The default action is action_index(). If an action doesn't exist it will fall in the default one
          */
         $this->action = strtolower($this->formatName($this->routingSegments[0],false));
         if ($this->action) {
             if (! $this->callControllerReflection()->hasMethod("action_{$this->action}")) {
                 if ($this->callControllerReflection()->hasMethod("action_404")) {
                     $this->action = "404";
                 } else {
                    $this->action = strtolower($this->formatName("index",false));
                    if(! $this->callControllerReflection()->hasMethod("action_{$this->action}")) {
                        throw new Exception("Action: 'action_{$this->action}' is missing in: '".$this->callControllerReflection()->getName()."'");    
                    }
                 }
             } else {
                array_shift($this->routingSegments);
             }
         } else {
             $this->action = "index";
         }
         
         $ControllerN = $this->getControllerNS();
         if (class_exists($ControllerN)) {
             $Controller = new $ControllerN($this->routingSegments);
             $Controller->getAction($this->action);
         } else {
             throw new Exception("Controller: '{$ControllerN}' doesn't exist!");
         }
        return $this;
    }

    /**
     * Call the controller reflection
     * 
     * @return \ReflectionClass
     */
    private function callControllerReflection()
    {
        return  new ReflectionClass($this->getControllerNS());
    }

    /**
     * return the controller's namespace
     * 
     * @return string
     */
    private function getControllerNS()
    {
        return
            "{$this->baseNamespace}\\{$this->moduleName}\\Controller\\{$this->controllerName}";
    }

    /**
     * Format the name properly. Only accept letters, numbers, dash and underscore
     * 
     * @param type $name
     * @param type $pascalCase
     */
    private function formatName($name, $pascalCase = false)
    {
        $name = preg_replace("/[^a-z09_\-]/i","",$name);
        return Helpers::camelize($name, $pascalCase);
    }

    
    /**
     * Set the modules path
     *
     * @param  string  $path
     * @return Voodoo
     */
    private function setAppPath($appName)
    {
        $appName = $this->formatName($appName, true);
        Env::setAppPath($this->appRootDir);
        $this->appPath = Env::getAppPath()."/{$appName}";
        $this->baseNamespace = "App\\{$appName}";
        return $this;
    }  
    
    /**
     * Get the modules path
     *
     * @param  string $module - The module name
     * @return string
     */
    private function getModulesPath($module = "")
    {
        $module = $this->formatName($module,true);
        return $this->appPath.($module ? ("/".$module): "");
    }  
    
    /**
     * Parse the routes and create the segment for: Module/Controller/Action
     * 
     * @return \Voodoo\Voodoo
     */
    private function parseRoutes()
    {
        $uri = (new Router($this->routes))->parse($this->uri);
        $this->routingSegments = explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $uri));       
        return $this;
    }      
}
