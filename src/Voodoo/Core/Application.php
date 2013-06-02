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
 * @name        Application
 * @desc        Application is the bootstrap class of the framework.
 *              Its role is to make sure the proper module/controller and action are loaded, based on the URI provided
 *              It also get the routes to reroute a URI to a new based on the path
 *
 */

namespace Voodoo\Core;

use DirectoryIterator,
    ReflectionClass,
    ReflectionMethod,
    ReflectionException;

class Application
{
    CONST NAME = "VoodooPHP";
    CONST VERSION = "1.0";
    CONST AUTHOR = "Mardix < https://github.com/mardix >";
    CONST LICENSE = "MIT";
    CONST REPO_LINK = "https://github.com/VoodooPHP/Voodoo";
/*******************************************************************************/

    private $routingSegments = [];
    private $moduleName = "";
    private $controllerName = "";
    private $action = "";
    private $config = null;
    private $appDir = "";
    private $baseNamespace = "";
    private $routes = [];
    private $uri = "/";
    private $defaultAppName = "Www";
    private $defaultModule = "Main";
    private $defaultController = "Index";
    private $reservedNames = ["Conf"];


    /**
     * The constructor
     *
     * @param string $appBaseDir - The dir containing /App directory
     * @param string $appName
     * @param string $uri
     * @throws Exception
     */
    public function __construct($appBaseDir, $appName = "Www", $uri = null)
    {
        if (!$uri) {
            $uri = implode("/", Http\Request::getUrlSegments());
        }
        Env::setAppRootDir($appBaseDir);
        $appName = self::formatName($appName);

        if(in_array($appName, $this->reservedNames)) {
            throw new Exception("'{$appName}' is a Voodoo reserved name, and it can't be assigned as an application name ");
        }

        if (! is_dir(Env::getAppRootDir())) {
            throw new Exception("The application root: 'App' directory doesn't exist at: ". Env::getAppRootDir());
        } else {

            Autoloader::register(dirname(Env::getAppRootDir()));
            $this->appDir = Env::getAppRootDir()."/{$appName}";
            
            if(! is_dir($this->appDir)) {
                throw new Exception("The application name: '{$appName}' doesn't exist at: ". $this->appDir);
            }
            
            $this->baseNamespace = "App\\{$appName}";
            $this->config = (new Config("VoodooApp"))->loadFile($this->appDir."/Config.ini");
            $this->setUri($uri);
            $this->setRouting($this->config->get("routes.path") ?: []);

            if ($this->config->get("application.defaultModule")) {
                $this->defaultModule = $this->config->get("application.defaultModule");
            }
            if ($this->config->get("application.defaultController")) {
                $this->defaultController = $this->config->get("application.defaultController");
            }
        }
    }

    /**
     * Set the default module
     *
     * @param string $moduleName
     * @return \Voodoo\Core\Application
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
     * @return \Voodoo\Core\Application
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
        return $this;
    }


    /**
     * Config is restricted directory that contains the app config
     * By default it reside in the App/Conf
     * @param type $path
     * @return \Voodoo\Core\Application
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
     * @return \Voodoo\Core\Application
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * doVoodoo
     * Abracadabra!
     * Voila, Magic!
     * This is how it works, if a segment exist as Module,Controller,Action,
     * it will shift it and the segment will become the current
     *
     * @return \Voodoo\Core\Application
     * @throws Exception
     */
    public function doVoodoo()
    {
        $this->parseRoutes();

         /**
          * Set Module
          * Modules are the top level of the views and controllers. It's a like a directory for your appliction... well it is
          * By default, if no module is found, the 'Main' module will be accessed
          * If a module is not specified, it will fall in the main
          */
        $currentSegment = isset($this->routingSegments[0]) ? $this->routingSegments[0] : "";
        $this->moduleName = self::formatName($currentSegment);
        if (! $this->moduleName || ! is_dir($this->getModulesPath($this->moduleName))) {
            $this->moduleName = "";
            /**
             * Module Discovery
             * We'll evaluate each module name to see if any exactly match the requested module name
             */
            $currentSegment = isset($this->routingSegments[0]) ? $this->routingSegments[0] : "";
            $s0  = strtolower(self::formatName($currentSegment));
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
               $this->moduleName = self::formatName($this->defaultModule);
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
             $currentSegment = isset($this->routingSegments[0]) ? $this->routingSegments[0] : "";
             $this->controllerName = self::formatName($currentSegment);
             if (! class_exists($this->getControllerNS())) {

                /**
                * Controller Discovery
                * When we can't find the controller based on the name provided, we'll try to discover it before we go to Index
                * We'll evaluate each controller name to see if it exactly matches the requested controller name
                */
                $s0  = strtolower(self::formatName($this->routingSegments[0]));
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
                 $this->controllerName = self::formatName($this->defaultController);
             } catch (ReflectionException $e2) {
                 throw new Exception("Controller :'$this->controllerName' is not found in Module: '{$this->moduleName}'","",$e2->getPrevious());
             }
         }

         /**
          * Set the Action: action$NameOfMethod()
          * default: actionIndex()
          */
        $currentSegment = isset($this->routingSegments[0]) ? $this->routingSegments[0] : "";
        $tmpAction = self::formatName($currentSegment);
        if ($tmpAction) {
           $publicActions = $this->callControllerReflection()->getMethods(ReflectionMethod::IS_PUBLIC);
           foreach($publicActions as $action) {
               if(preg_match("/^action{$tmpAction}$/i", $action->name)) {
                   $this->action = preg_replace("/^action/", "", $action->name);
                   break;
               }
           }
        }
        if (! $this->action) {
            if (count(array_filter($this->routingSegments))
                && $this->callControllerReflection()->hasMethod("action404")) {
                $this->action = "404";
            } else {
               $this->action = "Index";
               if(! $this->callControllerReflection()->hasMethod("action{$this->action}")) {
                   throw new Exception("Action: 'action{$this->action}' is missing in: '".$this->callControllerReflection()->getName()."'");
               }
            }
        } else {
            array_shift($this->routingSegments);
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
     * Get the modules path
     *
     * @param  string $module - The module name
     * @return string
     */
    private function getModulesPath($module = "")
    {
        $module = self::formatName($module,true);
        return $this->appDir.($module ? ("/".$module): "");
    }

    /**
     * Parse the routes and create the segment for: Module/Controller/Action
     *
     * @return \Voodoo\Core\Application
     */
    private function parseRoutes()
    {
        $uri = (new Router($this->routes))->parse($this->uri);
        $this->routingSegments = explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $uri));
        return $this;
    }

    /**
     * Return a CameLized string
     *
     * @return string
     */
    public static function formatName($name)
    {
        return Helpers::camelize($name, true);
    }
}
