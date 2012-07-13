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
    DirectoryIterator;

class Voodoo {
    
    private $segments = array();

    private $ModuleName = "";
    
    private $ControllerName = "";
    
    private $Action = "";
    
    /**
     * To statically load soup
     * @param type $URI
     * @param array $Routes
     * @return Voodoo\Core\self 
     */
    public static function Magic($URI="/",Array $Routes = array()){
        
        $Voodoo = new self($URI,$Routes);
        
        $Voodoo->doMagic();
    }
    
    
    /**
     * Use a URI string in this format: /Module/Controller/Action. ie: /Store/Items/Delete/1
     * @param string $URI :  /Module/Controller/Action. ie: /Store/Items/Delete/1
     * @param array $Routes: to override the default route
     */
    public function __construct($URI="/",Array $Routes = array()){

        // Make sure we add the trailing slash
        $URI .= (!preg_match("/\/$/",$URI)) ? "/" : "";
        
        // Get the Routes
        $Routes = count($Routes) ? $Routes : (Core\INI::Routes()->get("Routes") ? : array());
        
        // Reroute the URI based on Routes
        $URI = Router::Create($Routes)->parse($URI);
             
        /**
         * Build the URI segments that will be used to redirect to wherever in the application 
         * /Module/Controller/Action
         */
        $this->segments = explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $URI));

    }
    
        
    /**
     * doMagic
     * Abracadabra!
     * Voila, Magic!
     * This is how it works, if a segment exist as Module,Controller,Action, 
     * it will shift it and the segment will become the current
     * @return Voodoo
     * @throws Core\Exception 
     */
    public function doMagic(){

        
         /**
          * Set Module 
          * Modules are the top level of the views and controllers. It's a like a directory for your appliction... well it is
          * By default, if no module is found, the 'Main' module will be accessed
          * If a module is not specified, it will fall in the main
          */
         $this->ModuleName = $this->formatName($this->segments[0],true);
   
         if(!$this->ModuleName || !is_dir(APPLICATION_MODULES_PATH."/".$this->ModuleName)){
             $this->ModuleName = "";

            /**
             * Module Discovery 
             * We'll evaluate each module name to see if any exactly match the requested module name
             */
            $s0  = strtolower($this->formatName($this->segments[0],true));
            foreach (new DirectoryIterator(APPLICATION_MODULES_PATH) as $fileInfo) {
                if (!$fileInfo->isDot() && $fileInfo->isDir()) {
                    if( $s0 == strtolower($fileInfo->getFilename())){
                        $this->ModuleName = $fileInfo->getFilename();
                        array_shift($this->segments);
                        break;
                    }
                }
            } 
             
            
             /**
              * Fall back to the Main module 
              */
             if(!$this->ModuleName)
                $this->ModuleName = $this->formatName("Main",true);
           
                if(!is_dir(APPLICATION_MODULES_PATH."/".$this->ModuleName))
                        throw new Core\Exception("Module: '{$this->ModuleName}' doesn't exist!");
         }   
         else
            array_shift($this->segments);
         

         /**
          * Set the Controller 
          * Controllers are classes in a module. They are responsible for the logic of your application.
          * The default controller is Index. And it's loaded by default if the controller doesn't exist or was not specified
          */
         try{
             
             $this->ControllerName = $this->formatName($this->segments[0],true);
             
             /**
              * Controller discovery in the module 
              */
             if(!class_exists($this->getControllerNS())){

                /**
                * Controller Discovery 
                * When we can't find the controller based on the name provided, we'll try to discover it before we go to Index
                * We'll evaluate each controller name to see if it exactly matches the requested controller name
                */
                $s0  = strtolower($this->formatName($this->segments[0],true));
                foreach (new DirectoryIterator(APPLICATION_MODULES_PATH."/{$this->ModuleName}") as $fileInfo) {
                    if ($fileInfo->isFile()) {
                        if( $s0 == strtolower($fileInfo->getFilename())){
                            
                            $this->ControllerName = $fileInfo->getFilename();
                            
                            if(class_exists($this->getControllerNS())){
                                array_shift($this->segments);
                                break;                                
                            }
                        }
                    }
                }                 
             }
             
             
             $this->callControllerReflection();
             
             array_shift($this->segments);
             
         }catch(\ReflectionException $e){
             
             try{
                 // Fall back to Index
                 $this->ControllerName = $this->formatName("index",true);
                 
             }catch(\ReflectionException $e2){
                 throw new Core\Exception("Controller :'$this->ControllerName' is not found in Module: '{$this->ModuleName}'","",$e2->getPrevious());
             }
         }
         
         
         /**
          * Set the Action 
          * Actions are methods in the controllers in the format myactionnameAction(). 
          * Each action is associate to a view file, and it is what is called to execute your application.
          * The default action is indexAction(). If an action doesn't exist it will fall in the default one
          */
        
         $this->Action = strtolower($this->formatName($this->segments[0],false));
        
         if(!$this->callControllerReflection()->hasMethod("action_{$this->Action}")){
             
             $this->Action = strtolower($this->formatName("index",false));
             
             if(!$this->callControllerReflection()->hasMethod("action_{$this->Action}"))
                 throw new Core\Exception("Action: 'action_{$this->Action}' is missing in: '".$this->callControllerReflection()->getName()."'");     
         }
         else 
             array_shift($this->segments); 
         
         
         /**
          * Cooking time... 
          */
         
         $ControllerN = $this->getControllerNS();
         
         if(class_exists($ControllerN)){
             
             $Controller = new $ControllerN($this->segments);
             
             $Controller->getAction($this->Action);
                    
         }
         else
             throw new Core\Exception("Controller: '{$ControllerN}' doesn't exist!");

        return
            $this;
    }
    
    
    /**
     * Call the controller reflection
     * @return \ReflectionClass 
     */
    private function callControllerReflection(){

        return 
            new \ReflectionClass($this->getControllerNS());
        
    }
    
    /**
     * return the controller's namespace
     * @return type 
     */
    private function getControllerNS(){
        
        return
            "Application\\Module\\{$this->ModuleName}\\Controller\\{$this->ControllerName}";
    }


    /**
     * Format the name properly. Only accept letters, numbers, dash and underscore
     * @param type $name
     * @param type $pascalCase 
     */
    private function formatName($name,$pascalCase = false){
    
        $name = preg_replace("/[^a-z09_\-]/i","",$name);
        
        return
            Core\Helpers::camelize($name,$pascalCase);
            
    }
    
    
}

