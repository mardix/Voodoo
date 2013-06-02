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
    private static $baseDir = "";

    /**
     * Creates your Voodoo App
     * @param string $defaultBaseDir - The default base dir where App will be created
     * @param array $options
     *      BaseDir
     *      App
     *      FrontController
     *      Config
     *      PublicAssets
     * @param bool - to display or not the process on the screen
     */
    
    /**
     * Create your Voodoo  application
     * @param string $rootDir - The root dir where /App will be installed
     * @param type $appConfigDirName - All conf are at the root of /App/Conf, to add the config under another dir, set the name of the dir here.
     * @param bool $echo - To show the details of the setup
     * @return type
     */
    public static function create($rootDir, $appConfigDirName = "", $echo = true)
    {
        self::$echoCreator = $echo;
        self::$baseDir = $rootDir;

        Core\Autoloader::register($rootDir);

        // Set up the environment
        Core\Env::setAppRootDir($rootDir);
        Core\Env::setConfigPath($appConfigDirName);
        Core\Env::setFrontControllerPath($rootDir);
        Core\Env::setPublicAssetsPath($rootDir);

        $jsonFile = Core\Env::getConfigPath()."/".self::$appJson;
        $Voodooist = new self;

        self::e(Core\Application::NAME." ".Core\Application::VERSION." : The Voodooist!");
        self::e("-----------------------------------------------------------------------");

        // /VoodooApp
         if (! file_exists(Core\Env::getConfigPath()."/System.ini")) {
            self::e("> creating Dir: ".Core\Env::getAppRootDir());
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
                                $path = isset($model["path"]) ? $model["path"] : "";
                                $namespace = isset($model["namespace"]) ? $model["namespace"] : "";
                                $Voodooist->createModel($model["name"], $model["dbAlias"], $model["table"],
                                                        $model["primaryKey"], $model["foreignKey"]);
                                self::e(self::t(3)."|");
                                self::e(self::t(3)."|_{$model["name"]}");
                            }
                        }
                    }
                }
            }
            // Create detached models. Models that are placed outside of modules
            if ($schema["models"]) {
                self::e("|");
                self::e("| Creating models..");
                foreach ($schema["models"] as $model) {
                    $path = $model["path"];

                    if (!$path) {
                        self::e("Detached models must have a path. \$path is empty for {$model["name"]}", true);
                    }
                    $namespace = isset($model["namespace"]) ? $model["namespace"] : "";
                    $Voodooist->createModel($model["name"], $model["dbAlias"], $model["table"],
                                            $model["primaryKey"], $model["foreignKey"],
                                            $path, $namespace);
                    self::e(self::t(1)."|");
                    self::e(self::t(1)."|_{$model["name"]}");
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
           "GENERATOR" => Core\Application::NAME." ".Core\Application::VERSION,
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

/*******************************************************************************/

    public function setApplication($name)
    {
        $this->applicationName = Core\Application::formatName($name);
        $this->applicationPath = Core\Env::getAppRootDir()."/{$this->applicationName}";
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
        $module = $this->moduleName = Core\Application::formatName($module);
        $appControllerDir = $this->applicationPath."/{$module}/Controller";
        $appModelDir = $this->applicationPath."/{$module}/Model";
        $this->moduleNamespace = $this->applicationNS."\\{$this->moduleName}";

        $newModelDir = is_dir($appModelDir);
        $countModels = 0;

        $this->mkdir($appControllerDir);


        $exception =  $this->applicationPath."/{$this->moduleName}"."/Exception.php";
        $this->saveTpl("exception", $exception,["MODULENAMESPACE" => $this->moduleNamespace]);

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
                    $this->mkdir($appModelDir);
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
        $this->controllerName = Core\Application::formatName($controllerName);
        $file = $this->applicationPath."/{$this->moduleName}"."/Controller/{$this->controllerName}.php";
        $baseControllerFile = $this->applicationPath."/{$this->moduleName}"."/Controller/BaseController.php";

        if(file_exists($file)) {
            return false;
        }

        $this->mkdir($file);

        $api_tpl = ($isApi) ? "_api" : "";
        $this->saveTpl("base_controller".$api_tpl, $baseControllerFile , ["MODULENAMESPACE" => $this->moduleNamespace]);
        $this->saveTpl("controller".$api_tpl, $file, ["CONTROLLER"=>$this->controllerName, "MODULENAMESPACE" => $this->moduleNamespace]);

        $this->addAction("index");
        $this->addView("index");


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
        $this->actionName = Core\Application::formatName($action);
        $clsControllerName = $this->applicationNS."\\{$this->moduleName}\\Controller\\{$this->controllerName}";
        $controller = $this->applicationPath."/{$this->moduleName}/Controller/{$this->controllerName}.php";

        try {
            $Reflection = new ReflectionClass($clsControllerName);

            if (!$Reflection->hasMethod("action{$this->actionName}")) {

                $inlineCode = "";
                if (!$Reflection->isSubclassOf("\Voodoo\Core\Controller\Api")) {
                    $inlineCode = "\$this->view()->setPageTitle(\"\");";
                }

                $tpl  = $this->parseTpl("controller_action",array("METHODNAME"=>$this->actionName,"INLINECODE"=>$inlineCode));

                $content = preg_replace("/}\s*$/", $tpl, file_get_contents($controller));

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
        $viewDir = $this->applicationPath."/{$this->moduleName}/Views";

        // It's not a bare module
        if (is_dir($viewDir)) {
            $action = Core\Application::formatName($action ?: $this->actionName);
            $viewFile = "{$viewDir}/{$this->controllerName}/{$action}.html";
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
    public function createModel($modelName, $alias, $tableName, $primaryKey="", $foreignKey="", $customModelPath = "", $customModelNamespace = "")
    {
        $nsModel = "";
        foreach (explode("/",$modelName) as $model) {
            if($nsModel) {
                $nsModel .= "/";
            }
            $nsModel .= Core\Application::formatName($model);
        }

        $modelName = basename($nsModel);

        if($customModelPath && $customModelNamespace) {
            $modelNameSpace = $customModelNamespace;
            $file = str_replace("{{BASEDIR}}", self::$baseDir, $customModelPath);
        } else {
            $modelNameSpace = $this->moduleNamespace."\\Model";
            $file = $this->applicationPath."/{$this->moduleName}/Model";
        }
        $this->mkdir($file);
        $this->saveTpl("exception", $file."/Exception.php", ["MODULENAMESPACE" => $modelNameSpace]);
        $file .= "/{$nsModel}.php";
        $moduleNameSpace = $modelNameSpace;

        if (!in_array($nsModelN2 = dirname($nsModel), array(".",""))) {
            $modelNameSpace .= "\\".str_replace("/","\\",$nsModelN2);
        }
        $this->mkdir($file);

        $settings = array(
            "MODELNAME" => $modelName,
            "MODELNAMESPACE" => $modelNameSpace,
            "MODULENAMESPACE" => $moduleNameSpace,
            "TABLENAME" => $tableName,
            "PRIMARYKEY" => $primaryKey,
            "FOREIGNKEY" => $foreignKey,
            "DBALIAS" => $alias
        );

        $type = Core\Config::DB()->get("{$alias}.type");
        $rdbms = ["mysql", "pgsql", "sqlite"];
        $this->saveTpl(in_array($type, $rdbms) ? "model_rdbms" : "model_simple", $file, $settings);
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
      $this->mkdir(Core\Env::getAppRootDir());
      Core\Helpers::recursiveCopy(Core\Env::getVoodooistPath()."/files/setup/App", Core\Env::getAppRootDir());
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
