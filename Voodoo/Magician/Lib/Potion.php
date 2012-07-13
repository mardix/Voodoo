<?php

namespace Voodoo\Magician\Lib;

use Voodoo\Core;


define("GEN_SINCE",date("M j,Y H:i"));


Class Potion{
    
    public static $AGENT = VOODOO_GENERATOR;
    
    public static $SINCE = GEN_SINCE;
    
    protected  $tplContent = array();

    
    public function __construct(){

       $this->parseData = array(
           "NAME"=>"",
           "PROJECT_NAME"=>Core\Config::get("Project.Name"),
           "PROJECT_LICENSE"=>Core\Config::get("Project.License"),
           "PROJECT_COPYRIGHT"=>Core\Config::get("Project.Copyright"),
           "YEAR"=>date("Y"),
           "DATE"=>GEN_SINCE,
           "GENERATOR"=>VOODOO_GENERATOR,
       );   
       
       $this->DBINI =  Core\INI::DB(false);
    }
    


    /**
     * Save the template to a real file
     * @param type $templateName - name of template
     * @param type $filePath - filename to save the template to
     * @param array $Data - data to pass to template
     * @param boolean $forceSave - if file exists it will forcely saved it
     * @return \Cook 
     */
    private function saveTpl($templateName,$filePath,Array $Data = array(),$forceSave = false){

        if(!file_exists($filePath) || $forceSave == true)
            file_put_contents($filePath,$this->parseTpl($templateName,$Data));
        
        return $this;
    }
    
    /**
     * Parse the template, by adding the data into the file in the  format {{DATANAME}}
     * @param type $templateName
     * @param array $Data
     * @return type 
     */
    private function parseTpl($templateName,Array $Data = array()){
        
        $tpl = strtolower($templateName);
        
        if(!isset($this->tplContent[$tpl]))
            $this->tplContent[$tpl] = file_get_contents(VOODOO_MAGICIAN_PATH."/Includes/templates/".strtolower($templateName));

        $Data = array_merge($this->parseData,$Data);
        $nData = array();
        
        foreach($Data as $k=>$v){
            if(is_array($v)){
                foreach($v as $vk=>$vv){
                    $nData["{{{$k}.{$vk}}}"] = $vv;
                }
            }
            else
                $nData["{{{$k}}}"] = $v;
        }
        
        return
            str_replace(array_keys($nData),array_values($nData),$this->tplContent[$tpl]);        
    }
    
    
    /**
     * Parse the content of a file instead
     * @param type $filePath
     * @param array $Data
     * @return type 
     */
    private function parseFile($filePath,Array $Data = array()){

        $content = file_get_contents($filePath);

        $Data = array_merge($this->parseData,$Data);
        $nData = array();
        
        foreach($Data as $k=>$v){
            if(is_array($v)){
                foreach($v as $vk=>$vv){
                    $nData["{{{$k}.{$vk}}}"] = $vv;
                }
            }
            else
                $nData["{{{$k}}}"] = $v;
        }
        
        return
            file_put_contents($filePath,str_replace(array_keys($nData),array_values($nData),$content)); 
        
    }
    
    /**
     * Create a dir
     * @param type $filePath 
     */
    private function mkdir($filePath){
        
        $dirName = pathinfo($filePath,PATHINFO_DIRNAME);
        
        if(!is_dir($dirName))
             mkdir($dirName,0775,true); 
             
    }

    /**
     * To create a new application dir. usually on fresh install
     * @param type $basePath 
     */
    public function newApplication($basePath,Array $Data = array()){
        
        $application = $basePath."/Application";
        
        $this->createFileSystem($basePath,"Application");  
        
        /**
         * Copy pre-made files 
         */
        $recursiveCopy = array(
            ".",
        );
        foreach($recursiveCopy as $src){
          Core\Helpers::recursiveCopy(VOODOO_MAGICIAN_PATH."/Includes/setup-files/{$src}", $basePath."/{$src}");  
        } 

        $this->saveTpl("application_config","{$application}/Config/Config.ini",$Data);

        $this->buildRoutes(array("Routes[\"(:any)\"]"=>"$1"));
        
    }
    
    /**
     * Create a module name
     * @param type $module
     * @return type
     * @throws Core\Exception 
     */
    public function createModuleName($module){
        return
            $this->prepareName($module,true);
    }
    
    /**
     * Check if a module exists
     * @param type $module
     * @return type 
     */
    public function moduleExists($module){
        
        $dir = APPLICATION_MODULES_PATH."/".$this->createModuleName($module);
        
        return
            is_dir($dir);
    }
    
    
    /**
     * To create the application Module, which contains model/view/controller
     * @param type $basePath - The base path
     * @param type $name - The name of the ssub application, including sub path, ie: default or social-apps/facebook/canvas
     * @param bool $bare - A bare module doesn't have views. Only controllers
     * @return string - the module name 
     */
    public function createModule($module,$templateDir="Default",$bare = false){
        
        $module = $this->createModuleName($module);

            /**
            * Build views 
            * A bare module is a module without Views but contains controller.
            */
            if(!$bare){

                $dir = APPLICATION_MODULES_PATH."/".$module."/Views";

                @mkdir($dir,0775,true);

                //Copy pre-made files 
                $views = VOODOO_MAGICIAN_MODULES_TEMPLATES_PATH."/{$templateDir}/Views";
                if(is_dir($views)){
                    Core\Helpers::recursiveCopy($views, $dir);   
                }
            }

            /**
            * Build controllers
            */
            $controlTpl = VOODOO_MAGICIAN_MODULES_TEMPLATES_PATH."/{$templateDir}/Controller";
            $controll = APPLICATION_MODULES_PATH."/{$module}/Controller";
            @mkdir(APPLICATION_MODULES_PATH."/{$module}/Controller",0775,true);
            if(is_dir($controlTpl)){
                
                Core\Helpers::recursiveCopy($controlTpl,$controll); 

                $controllerNameSpace = "Application\\Module\\{$module}\\Controller";
                
                //Let's go in each file and update some VARIABLE 
                $DirIt = new \DirectoryIterator($controll);
                foreach($DirIt as $Dir){
                    if(!$Dir->isDot() && $Dir->isFile()){
                        $this->parseFile($Dir->getPathname(),array("MODULENAME"=>$module,"TEMPLATENAME"=>$templateDir,"NAMESPACE"=>$controllerNameSpace));
                    }
                }               
            }
            
            /**
             * Build Models
             */
            $modelTpl = VOODOO_MAGICIAN_MODULES_TEMPLATES_PATH."/{$templateDir}/Model";
            $modell = APPLICATION_MODULES_PATH."/{$module}/Model";
            @mkdir(APPLICATION_MODULES_PATH."/{$module}/Model",0775,true);
            if(is_dir($modelTpl)){
                
                Core\Helpers::recursiveCopy($modelTpl,$modell); 

                $modelNameSpace = "Application\\Module\\{$module}\\Model";
                
                //Let's go in each file and update some VARIABLE 
                $DirIt = new \DirectoryIterator($modell);
                foreach($DirIt as $Dir){
                    if(!$Dir->isDot() && $Dir->isFile()){
                        $this->parseFile($Dir->getPathname(),array("MODULENAME"=>$module,"TEMPLATENAME"=>$templateDir,"NAMESPACE"=>$modelNameSpace));
                    }
                }               
            }
            

        // Or create new controller
        $this->createController($module,"Index");
        
        $file = APPLICATION_MODULES_PATH."/{$module}/Config.ini";
        
        $this->saveTpl("module_config",$file,array("MODULENAME"=>$module,"TEMPLATENAME"=>$templateDir));
        
        return
            $module;
    }
    
    
   /**
    *Create a controller name
    * @param type $controllerName
    * @return type 
    */
    public function createControllerName($controllerName){
        return
            $this->prepareName($controllerName,true);
    }
    /**
     * Check if a controller exists
     * @param type $module
     * @param type $controllerName
     * @return type 
     */
    public function controllerExists($module,$controllerName){
        
        $controllerName = $this->createControllerName($controllerName);

        $module = $this->createModuleName($module);

        return
            is_file(APPLICATION_MODULES_PATH."/{$module}"."/Controller/{$controllerName}.php");
    }
    
    /**
     * To create a controller
     * @param type $module - The name of the module
     * @param type $controllerName - The controller name
     * @return string - controller name 
     */
    public function createController($module,$controllerName){

        $controllerName = $this->createControllerName($controllerName);

        $module = $this->createModuleName($module);

        $controllerNameSpace = "Application\\Module\\{$module}\\Controller";

        $file = APPLICATION_MODULES_PATH."/{$module}"."/Controller/{$controllerName}.php";

        $this->mkdir($file);
        
        $this->createView($module,$controllerName,"index");
                
        $this->saveTpl("controller",$file,array("CONTROLLER"=>$controllerName,"NAMESPACE"=>$controllerNameSpace));
        
        return
            $controllerName;

    }
    
    /**
     * Create an action name
     * @param type $action
     * @return type 
     */
    public function createActionName($action){
        return
            strtolower($this->prepareName($action,false,true));
    }
    /**
     * To create a controller
     * @param type $module - The name of the module
     * @param type $controllerName - The controller name
     * @return bool 
     */
    public function createAction($module,$controllerName,$action,$description=""){

        $controllerName = $this->createControllerName($controllerName);

        $module = $this->createModuleName($module);

        $action = $this->createActionName($action);
        
        $clsControllerName = "Application\\Module\\{$module}\\Controller\\{$controllerName}";
        
        $controller = APPLICATION_MODULES_PATH."/{$module}/Controller/{$controllerName}.php";
        
 
        try{
            
            $Reflection = new \ReflectionClass($clsControllerName);

            if(!$Reflection->hasMethod("action_{$action}")){
               
                $tpl  = $this->parseTpl("controller_action",array("METHODNAME"=>$action,"METHODDESCRIPTION"=>stripslashes($description)));
                
                $content = preg_replace("/}\s*$/",$tpl,file_get_contents($controller));
                
                file_put_contents($controller, $content);
                
                
                $this->createView($module,$controllerName,$action);
              
                
            }

        }catch(\Exception $e){
            
            
        }

        return
           $action;

    }
    
    
    
    /**
     * Create a controller file
     * @param type $fileName
     * @param type $returnFilePathOnly
     * @return string|boolean 
     */
    public function createView($module,$controllerName,$action){
        
        $controllerName = $this->createControllerName($controllerName);

        $module = $this->createModuleName($module);

        $action = $this->createActionName($action);
        
        /**
         * Create the view file 
         */
        $viewDir = APPLICATION_MODULES_PATH."/{$module}"."/Views";
        
        // It's not a bare module
        if(is_dir($viewDir)){
            $viewFile = "{$viewDir}/{$controllerName}/{$action}.html";

            $this->mkdir($viewFile);

            $this->saveTpl("view",$viewFile,array("NAME"=>$action));         
        }
                
        return
            $this;

    }    
    
    /**
     * Create a DB alias name
     * @param type $alias
     * @return type 
     */
    public function createAliasName($alias){
        return
            $this->prepareName($alias,true);
    }
    
    /**
     * Return the aliases name
     * @return type 
     */
    public function getModelAliases(){
        return
            $this->DBINI->get();
    }
    
    /**
     * Return the aliases name
     * @return type 
     */
    public function getModelAliasesName(){
        return
            array_keys($this->DBINI->get());
    }
    
    
    /**
     * Return the model alias data
     * @param type $alias
     * @return Array
     */
    public function getModelAlias($alias=""){
        return
            $this->DBINI->get($alias);
    }
    
    
    /**
     * Check if Alias exists
     * @param type $alias
     * @return bool
     */
    public function modelAliasExists($alias){
       
       $alias = $this->createAliasName($alias);
       
       return
            is_array($this->DBINI->get($alias));
    }
    
    
    /**
     * Add/Update model config file DB.ini
     * @param type $alias
     * @param array $Config
     * @return \Cook 
     */
    public function addModelConfig($alias,Array $Config){
        
        $this->DBINI->set($Config,$alias);
        
        $INI = Core\INI::arrayToINI($this->DBINI->toArray());
        
        $file = APPLICATION_CONFIG_PATH."/DB.ini";
        
        $this->saveTpl("app_config_db",$file,array("DBALIAS"=>$INI),true);        
        
        return
            $this;
    }

    /**
     * Create a model name
     * @param type $modelName
     * @return type 
     */
    public function createModelName($modelName){
        return
            Core\Helpers::camelize($modelName,true);
    }
    
    
    /**
     * Create a model
     * @param type $alias
     * @param type $modelName
     * @param type $tableName
     * @param type $primaryKey 
     */
    public function createModel($alias,$modelName,$tableName,$primaryKey=""){
        
        $modelName = $this->createModelName($modelName);

        $alias = $this->createAliasName($alias);

        $modelNameSpace = "Application\\Model\\{$alias};";

        $file = APPLICATION_MODELS_PATH."/{$alias}"."/{$modelName}.php";

        $this->mkdir($file);

        
        switch(strtolower($this->DBINI->get("{$alias}.Type"))){
            
            // MySQL & SQLite
            case "mysql":
            case "sqlite":
                $this->saveTpl("model_table",$file,array("MODELNAME"=>$modelName,"NAMESPACE"=>$modelNameSpace,"TABLENAME"=>$tableName,"PRIMARYKEY"=>$primaryKey));               
            break;
            
            case "mongodb":
                $this->saveTpl("model_mongodb",$file,array("MODELNAME"=>$modelName,"NAMESPACE"=>$modelNameSpace,"COLLECTIONNAME"=>$tableName,"PRIMARYKEY"=>$primaryKey));               
            break;            
        
            // MySQL & SQLite
            case "nodb":
                $this->saveTpl("model_nodb",$file,array("MODELNAME"=>$modelName,"NAMESPACE"=>$modelNameSpace));               
            break;
        
        }
      
    }

    
    
    /**
     * Build and save the routes
     * @param array $Routes 
     */
    public function buildRoutes(Array $Routes){

        $INI = Core\INI::arrayToINI($Routes);
        
        if(!$INI)
            $INI = 'Routes["(:any)"] = "$1"';
        
        $file = APPLICATION_CONFIG_PATH."/Routes.ini";
        
        $this->saveTpl("app_config_routes",$file,array("ROUTES"=>$INI),true);        
        
    }
    
    /**
     * Get the routes and return the array
     * @return type 
     */
    public function getRoutes(){
      
      return
            Core\Router::Create(Core\INI::Routes(false)->get("Routes"))->getRoutes();
    }
    
    
    /**
     * To create a filesystem. The framework is aware of all the path
     * @param type $path - The path to follow
     * @param type $fileSystem, the type structure to use. If its an array it will make it the structire itself
     */
    public function createFileSystem($path,$fileSystem){

       $FS = array(
           "application"=>array(
                    "AddOn",
                    "Application"=>array(
                        "Config",
                        "Model",
                        "Module",
                        "Lib",
                        "Var"=>array(
                           "cache",
                           "tmp",
                           "db"
                        )
                    ),
                    "SharedAssets"=>array(
                        "css",
                        "js",
                        "images",
                        "scripts",
                    ),
                ),
       );
       
       $sFS = (is_array($fileSystem)) ? $fileSystem : $FS[strtolower($fileSystem)];
       
       foreach($sFS as $dk=>$dv){

           if(is_string($dk) && !is_dir("{$path}/$dk}"))
               @mkdir("{$path}/$dk",0775);
           
           if(is_array($dv))
               $this->createFileSystem("{$path}/$dk",$dv);

           else if(!is_dir("{$path}/$dv"))
               @mkdir("{$path}/$dv",0775);
          
       }

    }    
    
    
    public function listModuleTemplates(){

        $tpl = array();
             // DISPLAY ALL MODULES
            $DirIt = new \DirectoryIterator(VOODOO_MAGICIAN_MODULES_TEMPLATES_PATH);
            foreach($DirIt as $Dir){

                if(!$Dir->isDot() && $Dir->isDir()){
                    
                    $templateNameDir = $Dir->getBasename();
                    $description = "";
                    $_Config = array();
                    $_configF = $Dir->getPath()."/{$templateNameDir}/info.ini";
                    
                    if(file_exists($_configF)){
                       $_Info = parse_ini_file($_configF);
                      
                       $templateName = $_Info["Name"] ?: $templateNameDir;
                       $description = $_Info["Description"];
                    }
                    
                    $tpl[] = array(
                      "Name"=>$templateName,
                      "Description"=>$description,
                      "Path"=>$templateNameDir
                    );
                }
            }        
        
            
            $tpl[] = array(
                "Name"=>"Bare Template",
                "Description"=>"The module will contain only Controllers. Usually good for writing API application, or apps that don't need a normal views",
                "Path"=>"__BARE__TPL__"
            );
            
       return
            $tpl;
    }
    
    
    /**
     * To prepare some name for the
     * @param string $str
     * @param bool $camelCase - To use UpperCamelCase or nor
     * @param bool $canStartWithNum - If the string can start with a numeric value
     * @return type 
     */
    protected function prepareName($str,$camelCase = true,$canStartWithNum = false){
        
        $str = preg_replace("/\W/","",trim($str));
        
        if(!$canStartWithNum)
            $str = preg_replace("/^([0-9]+)/","",$str);
            

        return
            Core\Helpers::camelize($str,$camelCase);
    }
    
    
}