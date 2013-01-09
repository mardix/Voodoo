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
 * @name        Voodooist\Voodooist
 * @desc        This file automatically setup files properly for Voodoo
 *
 */

namespace Voodoo\Voodooist;

use Voodoo\Core,
    ReflectionClass;

define("GEN_SINCE",date("M j,Y H:i"));

class Voodooist
{
    public static $SINCE = GEN_SINCE;
    private static $echoCreator = false;
    protected $tplContent = array();
    
    private static $appJson = "app.json";
    

    /**
     * Creates your Voodoo App
     * @param array $options
     *      FrontController
     *      App
     *      Config
     *      PublicAssets
     * @param bool - to display or not the process on the screen
     */
    public static function create(Array $options = [], $echo = false)
    {
        self::$echoCreator = $echo;
        $defaultBaseDir = dirname(dirname(__DIR__));
        $default = [
            "FrontController" => $defaultBaseDir,
            "App" => $defaultBaseDir,
            "Config" => $defaultBaseDir."/App/_config",
            "PublicAssets" => $defaultBaseDir
        ];

        $options = array_merge($default, $options);

        // Set up the environment
        Core\Env::setAppPath($options["App"]);
        Core\Env::setConfigPath($options["Config"]);
        Core\Env::setFrontControllerPath($options["FrontController"]);
        Core\Env::setPublicAssetsPath($options["PublicAssets"]);

        $jsonFile = Core\Env::getConfigPath()."/".self::$appJson;
        $Voodooist = new self;

        self::e(Core\Voodoo::NAME." ".Core\Voodoo::VERSION." : The Voodooist!");
        self::e("-----------------------------------------------------------------------");

        // /VoodooApp
         if (! file_exists(Core\Env::getConfigPath()."/System.ini")) {
            self::e("> creating Dir: ".Core\Env::getAppPath());
            $Voodooist->createVoodooApp();
        }

        if (! file_exists($jsonFile)) {
            self::e("Error");
            self::e("'{$jsonFile}' doesn't exist!", true);
            return;
        }

        $json = file_get_contents($jsonFile);
        $schema = json_decode($json, true);

        if ($error = Core\Helpers::getJsonLastError()) {
            self::e("Error");
            self::e("'app-schema' contains a JSON error : ({$error["code"]}) {$error["message"]}", true);
            return;
        }

        self::e("> checking front controller... ");
        $Voodooist->createFrontController();


        // /assets
        if ($schema["createPublicAssets"] === true && !is_dir(Core\Env::getPublicAssetsPath())) {
            self::e("> creating Assets dir: ".Core\Env::getPublicAssetsPath());
            $Voodooist->createPublicAssets();
        }

        self::e("> building application from schema...\n");

            $created = " [CREATED] ";
            foreach ($schema["applications"] as $app) {

                $Voodooist->setApplication($app["name"]);
                self::e("| {$app["name"]}");

                if (isset($app["modules"])) {
                    foreach($app["modules"] as $module){
                        $moduleAction = "";
                        $isApi = (isset($module["isApi"]) && $module["isApi"] === true) ? true : false;
                        $omitViews = (isset($module["omitViews"]) && $module["omitViews"] === true) ? true : false;
                        if ($Voodooist->createModule($module["name"], $module["template"], $isApi, $omitViews) ){
                            $moduleAction = $created;
                        }
                        self::e(self::t()."|");
                        self::e(self::t()."|_{$module["name"]}");


                        // Create controllers
                        if (isset($module["controllers"])) {

                            self::e(self::t(2)."|_ Controller");

                            foreach ($module["controllers"] as $controller) {

                                $controllerAction = "";
                                $cIsApi = $isApi;
                                if (isset($controller["isApi"])) {
                                    $cIsApi = $controller["isApi"];
                                }

                                if ($Voodooist->createController($controller["name"], $cIsApi)) {
                                    $controllerAction = $created;
                                }

                                self::e(self::t(3)."|");
                                self::e(self::t(3)."|_{$controller["name"]} {$controllerAction}");

                                // actions
                                if (isset($controller["actions"])) {
                                    foreach ($controller["actions"] as $action) {
                                        $actionAction = "";
                                        if($Voodooist->addAction($action)) {
                                           $actionAction = $created;
                                        }
                                        self::e(self::t(4)."|");
                                        self::e(self::t(4)."|_{$action} {$actionAction}");
                                    }
                                }
                            }
                        }

                        // Create Models
                        if (isset($module["models"])) {
                            self::e(self::t(2)."|");
                            self::e(self::t(2)."|_ Model");
                            foreach ($module["models"] as $model) {
                                $Voodooist->createModel($model["name"], $model["dbAlias"], $model["table"], $model["primaryKey"], $model["foreignKey"]);
                                self::e(self::t(3)."|");
                                self::e(self::t(3)."|_{$model["name"]}");
                            }
                        }
                    }
                }
            }
        self::e("Done!");
    }    
  
/*******************************************************************************/
/*******************************************************************************/
/*******************************************************************************/
    
    public function __construct()
    {
       $this->parseData = array(
           "NAME" => "",
           "YEAR" => date("Y"),
           "DATE" => GEN_SINCE,
           "GENERATOR" => Core\Voodoo::NAME." ".Core\Voodoo::VERSION,
       );
    }

    /**
     * Save the template to a real file
     * @param  type    $templateName - name of template
     * @param  type    $filePath     - filename to save the template to
     * @param  array   $Data         - data to pass to template
     * @param  boolean $forceSave    - if file exists it will forcely saved it
     */
    private function saveTpl($templateName,$filePath,Array $Data = array(),$forceSave = false)
    {
        if (! file_exists($filePath) || $forceSave == true){
            file_put_contents($filePath,$this->parseTpl($templateName,$Data));
        }
        return $this;
    }

    /**
     * Parse the template, by adding the data into the file in the  format {{DATANAME}}
     * @param  type  $templateName
     * @param  array $Data
     * @return type
     */
    private function parseTpl($templateName,Array $Data = array())
    {
        $tpl = strtolower($templateName);

        if (! isset($this->tplContent[$tpl])) {
            $this->tplContent[$tpl] = file_get_contents(Core\Env::getVoodooistPath()."/files/templates/".strtolower($templateName));
        }

        $Data = array_merge($this->parseData,$Data);
        $nData = array();

        foreach ($Data as $k=>$v) {
            if (is_array($v)) {
                foreach ($v as $vk=>$vv) {
                    $nData["{{{$k}.{$vk}}}"] = $vv;
                }
            } else {
                $nData["{{{$k}}}"] = $v;
            }
        }
        return str_replace(array_keys($nData),array_values($nData),$this->tplContent[$tpl]);
    }

    /**
     * Parse the content of a file instead
     * @param  type  $filePath
     * @param  array $Data
     * @return type
     */
    private function parseFile($filePath,Array $Data = array())
    {
        $content = file_get_contents($filePath);

        $Data = array_merge($this->parseData,$Data);
        $nData = array();

        foreach ($Data as $k=>$v) {
            if (is_array($v)) {
                foreach ($v as $vk=>$vv) {
                    $nData["{{{$k}.{$vk}}}"] = $vv;
                }
            } else {
                $nData["{{{$k}}}"] = $v;
            }
        }
        return file_put_contents($filePath,str_replace(array_keys($nData),array_values($nData),$content));

    }

    /**
     * Create a dir
     * @param type $filePath
     */
    private function mkdir($file)
    {
        $dir = $file;

        if(preg_match("/\.[a-z09]{1,4}$/i", $dir)) {
            $dir = dirname($dir);
        }
        if(!is_dir($dir)) {
             mkdir($dir,0775,true);
        }
        return $this;
    }


    /**
     * To prepare some name for the
     * @param  string $str
     * @param  bool   $camelCase       - To use UpperCamelCase or nor
     * @param  bool   $lowercase - To lower case
     * @return type
     */
    protected function formatName($str, $camelCase = true, $lowercase = false)
    {
        $str = preg_replace("/\W/","",trim($str));

        $str = preg_replace("/^([0-9]+)/","",$str);

        $str = Core\Helpers::camelize($str,$camelCase);

        return $lowercase ? strtolower($str) : $str;
    }
/*******************************************************************************/

    public function setApplication($name)
    {
        $this->applicationName = $this->formatName($name);
        $this->applicationPath = Core\Env::getAppPath()."/{$this->applicationName}";
        $this->applicationNS = "App\\{$this->applicationName}";

        $this->mkdir($this->applicationPath);

        $file = $this->applicationPath."/Config.ini";

        $this->saveTpl("application_config",$file,["APPLICATIONNAME"=>$this->applicationName]);
    }


    /**
     * To create the application Module, which contains model/view/controller
     * @param  type   $basePath - The base path
     * @param  type   $name     - The name of the ssub application, including sub path, ie: default or social-apps/facebook/canvas
     * @param  bool   $isApi     -
     * @return string - the module name
     */
    public function createModule($module, $templateDir = "Default", $isApi = false, $omitViews = false)
    {
        $module = $this->moduleName = $this->formatName($module);
        $appControllerDir = $this->applicationPath."/{$module}/Controller";
        $appModelDir = $this->applicationPath."/{$module}/Model";
        $this->moduleNamespace = $this->applicationNS."\\{$this->moduleName}";

        $newModelDir = is_dir($appModelDir);
        $countModels = 0;

        $this->mkdir($appControllerDir);
        $this->mkdir($appModelDir);

        $exception =  $this->applicationPath."/{$this->moduleName}"."/Exception.php";
        $this->saveTpl("module_exception", $exception,["MODULENAMESPACE" => $this->moduleNamespace]);

        if (! $isApi) {

            if (! $omitViews) {
                $viewsDir = $this->applicationPath."/".$module."/Views";
                $this->mkdir($viewsDir);
                $this->mkdir($viewsDir."/_assets");
                $this->mkdir($viewsDir."/_includes");
                $this->mkdir($viewsDir."/_layouts");
            }

            if ($templateDir) {

                $modulesTemplate = Core\Env::getVoodooistPath()."/modules-template/{$templateDir}";
                $viewsTpl = "{$modulesTemplate}/Views";
                if (is_dir($viewsTpl) && is_dir($viewsDir)) {
                    Core\Helpers::recursiveCopy($viewsTpl, $viewsDir);
                }

                $controlTpl = "{$modulesTemplate}/Controller";
                if (is_dir($controlTpl)) {
                    Core\Helpers::recursiveCopy($controlTpl,$appControllerDir);
                    //Let's go in each file and update some VARIABLE
                    $DirIt = new \DirectoryIterator($appControllerDir);
                    foreach ($DirIt as $Dir) {
                        if (!$Dir->isDot() && $Dir->isFile()) {
                            $this->parseFile($Dir->getPathname(),array("MODULENAMESPACE" => $this->moduleNamespace,"TEMPLATENAME"=>$templateDir));
                        }
                    }
                }

                $modelTpl = "{$modulesTemplate}/Model";
                if (is_dir($modelTpl)) {
                    Core\Helpers::recursiveCopy($modelTpl,$appModelDir);
                    //Let's go in each file and update some VARIABLE
                    $DirIt = new \DirectoryIterator($appModelDir);
                    foreach ($DirIt as $Dir) {
                        if (!$Dir->isDot() && $Dir->isFile()) {
                            $countModels++;
                            $this->parseFile($Dir->getPathname(),array("MODULENAMESPACE" => $this->moduleNamespace,"TEMPLATENAME"=>$templateDir));
                        }
                    }
                }
            }
        }

        // Create a SampleModel
        if (! $newModelDir && $countModels == 0) {
            //$this->createModel("SampleModel", "MyDB", "sample_table", "id", "%s_id");
        }

        $this->createController("Index", $isApi);

        return $this;
    }

    /**
     * To create a controller
     * @param  type   $controllerName - The controller name
     * @return bool
     */
    public function createController($controllerName, $isApi = false)
    {
        $this->controllerName = $this->formatName($controllerName);

        $file = $this->applicationPath."/{$this->moduleName}"."/Controller/{$this->controllerName}.php";

        if(file_exists($file)) {
            return false;
        }

        $this->mkdir($file);

        $this->addView("index");

        $tpl = ($isApi) ? "controller_api" : "controller";

        $this->saveTpl($tpl, $file,array("CONTROLLER"=>$this->controllerName, "MODULENAMESPACE" => $this->moduleNamespace));

        return true;
    }

    /**
     * To create a controller
     * @param  type $module         - The name of the module
     * @param  type $controllerName - The controller name
     * @return bool
     */
    public function addAction($action)
    {
        $this->actionName = $this->formatName($action,false, true);

        $clsControllerName = $this->applicationNS."\\{$this->moduleName}\\Controller\\{$this->controllerName}";

        $controller = $this->applicationPath."/{$this->moduleName}/Controller/{$this->controllerName}.php";

        try {
            $Reflection = new ReflectionClass($clsControllerName);
            if (!$Reflection->hasMethod("action_{$this->actionName}")) {

                $inlineCode = "";
                if (!$Reflection->isSubclassOf("\Voodoo\Core\Controller\Api")) {
                    $inlineCode = "\$this->view()->setPageTitle(\"\");";
                }

                $tpl  = $this->parseTpl("controller_action",array("METHODNAME"=>$this->actionName,"INLINECODE"=>$inlineCode));

                $content = preg_replace("/}\s*$/",$tpl,file_get_contents($controller));

                file_put_contents($controller, $content);

                $this->addView();

                return true;
            }

        } catch (Exception $e) {

        }

        return false;
    }



    /**
     * Create a controller file
     * @param  type           $fileName
     * @param  type           $returnFilePathOnly
     * @return string|boolean
     */
    public function addView($action = "")
    {
        $controllerName = $this->controllerName;

        $module = $this->moduleName;

        $action = $action ? : $this->actionName;

        /**
         * Create the view file
         */
        $viewDir = $this->applicationPath."/{$this->moduleName}/Views/{$nsModel}";

        // It's not a bare module
        if (is_dir($viewDir)) {
            $viewFile = "{$viewDir}/{$controllerName}/{$action}.html";
            $this->mkdir($viewFile);
            $this->saveTpl("view",$viewFile,array("NAME"=>$action));
        }
        return $this;
    }

    /**
     * Create a model
     * @param string $moduleName
     * @param type   $alias
     * @param type   $modelName
     * @param type   $tableName
     * @param type   $primaryKey
     *
     * @TODO Add model template for MongoDb, Redis
     */
    public function createModel($modelName, $alias, $tableName, $primaryKey="", $foreignKey="")
    {
        $nsModel = "";
        foreach (explode("/",$modelName) as $model) {
            if($nsModel) {
                $nsModel .= "/";
            }
            $nsModel .= $this->formatName($model);
        }

        $modelName = basename($nsModel);

        $modelNameSpace = $this->applicationNS."\\{$this->moduleName}\\Model";
        if (!in_array($nsModelN2 = dirname($nsModel), array(".",""))) {
            $modelNameSpace .= "\\".str_replace("/","\\",$nsModelN2);
        }

        $file = $this->applicationPath."/{$this->moduleName}/Model/{$nsModel}.php";

        $this->mkdir($file);

        $settings = array(
           "MODELNAME" => $modelName,
           "MODULENAMESPACE" => $this->moduleNamespace,
           "TABLENAME" => $tableName,
           "PRIMARYKEY" => $primaryKey,
           "FOREIGNKEY" => $foreignKey,
           "DBALIAS" => $alias
        );

        $modelType = array(
            "mysql" => "model",
            "pgsql" => "model",
            "sqlite" => "model",
            "mongodb" => "model_mongodb",
            "redis" => "model_redis"
        );

        $this->saveTpl("model",$file,$settings);

        return $this;
    }


/*******************************************************************************/

    public function createFrontController(){
        Core\Helpers::recursiveCopy(Core\Env::getVoodooistPath()."/files/setup/front-controller", Core\Env::getFrontControllerPath());
    }

    /**
     * Create the public assets
     */
    public function createPublicAssets()
    {
      $this->mkdir(Core\Env::getPublicAssetsPath());
      Core\Helpers::recursiveCopy(Core\Env::getVoodooistPath()."/files/setup/assets", Core\Env::getPublicAssetsPath());
    }

    /**
     * Create the voodooApp dir
     */
    public function createVoodooApp()
    {
      $this->mkdir(Core\Env::getAppPath());
      Core\Helpers::recursiveCopy(Core\Env::getVoodooistPath()."/files/setup/App", Core\Env::getAppPath());
    }

    /**
     *  Setup everything
     */
    public function setup()
    {
        Core\Helpers::recursiveCopy(Core\Env::getVoodooistPath()."/files/setup", Core\Env::getFrontControllerPath());
    }
/*******************************************************************************/
/*******************************************************************************/
/*******************************************************************************/
    // echo message
    public static function e($msg, $error = false) {
        if (self::$echoCreator) {
            echo "$msg \n";
        }
        if ($error) {
            throw new \Exception($error);
        }
    }

    // create tab
    public static function t($multiplier = 1){
        return str_repeat("\t",$multiplier ? : 1);
    }

}
