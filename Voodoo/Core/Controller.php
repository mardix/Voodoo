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
 * @name        Controller
 * @since       Mar 12, 2012
 * @desc        This is the abstract controller which must be extended to read the proper controller name and actions
 *              Controller contains necessary methods for your own controller
 * 
 */
namespace Voodoo\Core;


abstract class Controller{
    
    /**
     * Undefined variables will be assigned in the global vars
     * @var array
     */
    private static $globalVars = array();
    
    /**
     * Paramaters passed when being called
     * @var array
     */
    private $params = array();
    
    /**
     * The full namespace of the controller
     * @var string 
     */
    protected $Namespace = "";
    
    /**
     * The module name
     * @var String 
     */
    protected $ModuleName = "";
    
    /**
     * The controller's name
     * @var String
     */
    protected $ControllerName = "";
    
    /**
     * The action's name being called
     * @var string 
     */
    protected $ActionName = "";
    
    // The action view to use
    protected $ActionView = "";
    
    /**
     * @var Core\View() 
     */
    private $View = null;
    
    /**
     * @var Core\Paginator() 
     */
    private $Paginator  = null;
    
    /**
     * @var Core\INI 
     */
    private $Config = null;
    
    /**
     * On destruct render view
     * @var bool 
     */
    private $disableView = false;
    
    /**
     * A boolean set when view is rendered
     * @var bool 
     */
    private $isRendered = false;
    
    /**
     * This is the index action.
     * It is loaded by default or an action is missing
     * Every controller requires it
     */
     abstract public function action_index();
    
    
    /**
     * __construct is final and can't be overriden by any child class
     * main() lets you put code that could be executed in __construct()
     * @return Controller 
     */
    protected function main(){}
    
/*******************************************************************************/
    
    /**
     * Final construct so no other class can override it 
     * To load something in the constructor, use $this->main()
     * @params array $params - extra segments from that can be accessed with getParams() 
     */
    final public function __construct(Array $params = array()){
        
        $this->Namespace = get_called_class();
        $calledClass = explode("\\",$this->Namespace);
        $this->ModuleName = $calledClass[2];
        $this->ControllerName = $calledClass[4];
        
        $this->setParams($params);        

        /**
         * Load the config 
         */
        $this->loadConfig();
        
        /**
         * Load the view 
         */
        $this->loadView();
        
        /**
         * Load the Paginator 
         */
        $this->loadPaginator();

        /**
         * Execute the main 
         */
        $this->main();

    }

    
    /**
     * It's a wrap
     * By default, when the destructor is called, it will render the views
     * To disable view, in your controller set: $this->disableView(true)
     */
    final public function __destruct(){
        print ($this->renderView());
    }

/*******************************************************************************/
    /**
     * Return the module's name
     * @return string
     */
    public function getModuleName(){
        return $this->ModuleName;
    }
    
    /**
     * Return the controller's name
     * @return string 
     */
    public function getControllerName(){
        return $this->ControllerName;
    }
    
/*******************************************************************************/
// URL - Access url data such as segments and query
    
    /**
     * 
     * Return value of key placed after ?
     * ie. x/y/z/?short=1
     *      $this->getQueryVar(short) = 1
     * @param string $key
     * @return mixed 
     */
    final protected function getQueryVar($key=null){
        return
            HTTP\URI::getUrlQuery($key);
    }
    
    /**
     * Check if a key exists in a query url
     * @param string $key
     * @return bool 
     */
    final protected function queryVarExists($key){
        return
            HTTP\URI::queryUrlHas($key);
    }
    
    /**
     * Set params
     * @param array $params
     * @return Voodoo\Core\Controller
     */
    final protected function setParams(Array $params){
        $this->params = array_merge($this->params,$params);
        return
            $this;
    }
    
    /**
     * Parameters are url segments: ie: /gummy/bear/?q=hello gummy and bear are segments.
     * @param mixed (int | string) - if int, i will pick the index of the . If a string, it will return the k/v pair of the segemnt
     * @return type 
     */
    final protected function getParams($key = 0,$offset=0){
        
        /**
         * key can be a string.
         */
        if(is_string($key)){
            $segments = array_slice($this->params,$offset);

            $i = 0;
            $lastval = '';
            $segs  = array();

            foreach ($segments as $seg)	{
                if ($i % 2){
                    $segs[$lastval] = $seg;
                }
                else {
                    $segs[$seg] = FALSE;
                    $lastval = $seg;
                }
                $i++;
            }
            return
                ($key) ? $segs[$key] : $segs;            
        }
        return
            (is_numeric($key) && $key>0) ? (($this->params[$key - 1]) ? $this->params[$key - 1] : "") : $this->params;
    }

    /**
     * To catch the first numeric value from the URL segment. ie: /music/rap/12573/Where-Have-You-Been. Will return 12573
     * @return mixed (int | null) 
     */
    protected function catchNumericParam(){
        foreach($this->params as $s)
            if(is_numeric($s))
                return
                    $s;
        return
              null;
    }

 
/*******************************************************************************/
    
    /**
     * To get the request uri. It includes everything in the URI
     * @return string 
     */
    protected function getRequestURI(){
        return $_SERVER["REQUEST_URI"];
    }

    /**
     * Return the root dir relative to the Application dir
     * Use it to include files, or get relative path of file
     * @return string 
     */
    public function getRootDir(){
        return VOODOO_APP_ROOT_DIR;
    }
    
    
    /**
     * Return the root url which will properly format the url so it adds or not ? to make the relative link
     * @uses    : to make links
     * @example : http://mysite.com/? -> http://mysite.com/?/ModuleName if htaccess is missing, or http://mysite.com/ModuleName is htaccess is here
     * @return string 
     */    
    public function getRootUrl(){
        return VOODOO_APP_ROOT_URL;
    }
    
    /**
     * Return the site url itself
     * @uses    : To get the site url
     * @example : http://mysite.com/
     * @return string 
     */
    public function getSiteUrl(){
        return VOODOO_APP_SITE_URL;       
    }
    
    /**
     * Return the URL of the module
     * @uses    : Get the module url
     * @return string
     */
    public function getModuleUrl(){
        return  preg_replace("/\/$/","",$this->getRootUrl()."/".(($this->ModuleName == "Main") ? "" : $this->ModuleName));
    }
    
/*******************************************************************************/    
    
// CONTROLLER    
    /**
     * getController allows the current controller to access another controller without rendering the view, specially if you want to access certain methods from another controller
     * @param string $Controller - The controller name. If only the controller name is provided, it will check it in the current Module, if in this format
     *      'Module/Controller' it will access the controller in the called module
     * @return $Module\$Controller
     * @throws Exception 
     */
    final protected function getController($Controller,Array $params = array()){
        
        $Module = $this->ModuleName;
        
        if(preg_match("/\//",$Controller)){
            $xC = explode("/",$Controller);
            $Controller = $xC[1];
            $Module = Helpers::camelize($xC[0],true);
        }    

        if(!is_dir(APPLICATION_MODULES_PATH."/{$Module}"))
            throw new Exception("Can't getController. Module '$Module' doesn't exists");
        
        $Controller = "Application\\Module\\{$Module}\\Controller\\".Helpers::camelize($Controller,true);
        
        if(class_exists($Controller)){
            
            $C =  new $Controller($params);
            
            
           // Also make sure it doesn't render by default;
           return 
               $C->disableView(true); 
        }
            
        else
            throw new Exception("Can't getController. Controller '$Controller' doesn't exists");
    }
    
    
    /**
     * forwardController, unlike getController, forward the current controller to a new controller and allows it to render the view, while it deactivate the current controller view.
     * All the settings and params will be forwarded to the new controller
     * @param type $Controller 
     */
    final protected function forwardController($Controller,Array $params = array()){
        
        $disableView = $this->disableView;
        
        // Disable the current controller before forward
        $this->disableView(true);
        
        return
            $this->getController($Controller,array_merge_recursive($this->params,$params))
                 ->disableView($disableView);
    }

    
/*******************************************************************************/
// ACTION    
    /**
     * Load an action by providing just the name with the Action suffix. 
     * It's purpose is to set the action to be rendered. You still can access the method the normal way $this->action_index
     * i.e $this->getAction("index");
     * @param string $action - The action name without Action as suffix. ie: action_index() =  getAction("index")
     * @param bool $renderAction - Calling getAction() will trigger the view for the action, set to false to disable it
     * @return Controller 
     */
    final public function getAction($action="index",$renderAction = true){
        
        $action = strtolower(Helpers::camelize($action,false));
        
        $actionName = "action_{$action}";

        if(method_exists($this, $actionName)){

            $this->ActionName = strtolower(Helpers::camelize($action,false));
            
            if($renderAction)
                $this->view()->setBody($this->ActionName);
            
            $this->{$actionName}();
                            
        }
        else
            throw new Exception("Action '{$actionName}' doesn't exist in ".get_called_class());
        
        return
            $this;
    } 
    
    /**
     * Set the action name. It will be used to render the view
     * @param type $action
     * @return Controller 
     */
    final protected function setActionName($action){                   
        return $this->setActionView($action);
    }
    /**
     * Return the last action name saved
     * @return string 
     */
    final protected function getActionName(){
        return $this->ActionName;
    }    
    
    /**
     * Set the action view to be displayed
     * @param type $view
     * @return Controller 
     */
    final protected function setActionView($view){
        $this->ActionView = $view;
        return
            $this;
    }  
    
    /**
     * Return the action view
     * @return type 
     */
    final protected function getActionView(){
        return $this->ActionView;
    }  
/*******************************************************************************/

// VIEW    

    /**
     * Return the View instance
     * @return Core\View 
     */
    final protected function view(){
       return $this->View ?: null;
    }    

    /**
     * To enable render view. on __destruct, it will render the view, otherwise it's up to the controller to launch it.
     * @param bool $en
     * @return Controller 
     */
    final public function disableView($bool = true){
        $this->disableView = $bool;
        return
            $this;
    } 
    
    
    /**
     * To load the view
     * @return Voodoo\Core\Controller
     */
    private function loadView(){
        
        $Interface = "\\Voodoo\\Core\\Interfaces\\View";
        
        $ClassInjection = $this->getConfig("VoodooDependencies.Controller.View");
        
        $DI = new \ReflectionClass($ClassInjection);
        
        if(!$DI->implementsInterface($Interface))
            throw new Exception("{$ClassInjection} Must implement the interface: {$Interface}");
            
        $this->View = $DI->newInstanceArgs(array($this->Namespace,$this->getConfig("Views.Container")));
        
        return
            $this;
    }
    
    /**
     * Render the view
     * @return boolean 
     */
    protected function renderView(){
         if(   
              $this->disableView    
           || !$this->view()
           || !$this->view()->exists()  
          )
          return
                false;

            $this->view()->assign(array(
                "App"=>array(
                    "Copyright"=>"Copyright &copy; ".date("Y"), // Copyright (c) 2012   
                    "CurrentYear"=>date("Y"), // The current year
                    // return the full url
                    "Url"=>array(
                        "Root"=>$this->getRootUrl(),
                        "Site"=>$this->getSiteUrl(),
                        "Module"=>$this->getModuleUrl()                        
                    ),
                    // Return path of entities
                    "Path"=>array(
                        "SharedAssets"=>$this->getViewsSharedAssetsDir(),// SharedAssets the global assets
                        "Assets"=>$this->getViewsAssetsDir()// The Assets directory for the current module
                    )
                ),
            ));


            /**
             * LoadTemplates
             * Templates that are set in the config.ini of the module with key/value
             * These templates will be access with their alias in the view page. ie: {{%include @PageAliasName}} 
             */
            $loadTemplates = $this->getConfig("Views.LoadTemplate");
            if(is_array($loadTemplates)){
                foreach($loadTemplates as $pageKey=>$pagePath){
                    $this->view()->addTemplate($pageKey,$pagePath);
                }
            }
         

        /**
         * Create the pagination 
         */
        if($this->paginator()->getTotalItems()){
            $this->view()->assign(array(
               "App"=>array("Pagination"=>$this->paginator()->toArray())
            ));
        }

        /**
         * Render the view in the page 
         */
        return 
            $this->view()->render();

    } 

    
    /**
     * To create the module's assets dir
     * Base on the config file
     * @return string
     */
    private function getViewsAssetsDir(){
        
        $path = $this->getConfig("Views.AssetsDir");
        
            switch(true){
                // Assets in current Module
                case preg_match("/^(_[\w]+)/",$path):
                     $path = "{$this->ModuleName}/Views/{$path}";
                break;
                // Assets anywhere with current page. ie: /ModuleName/Views/_assets
                case preg_match("/^\/\/?/",$path):
                    $path = preg_replace("/^\/\/?/","",$path);
                break;
                // Usually if a URL is provided, and the content will delivered from a different place
                default:
                    return $path;
                break;
            }

            return 
                $this->getSiteUrl()."/".preg_replace("/^\//","",str_replace(BASE_PATH,"",APPLICATION_MODULES_PATH."/$path"));
    }
    
    /**
     * To create the shared assets dir
     * Base on the config file
     * @return string
     */    
    private function getViewsSharedAssetsDir(){
        
        $path = $this->getConfig("Views.SharedAssetsDir");
        
        // Shared assets is from URL
        if(preg_match("/^http(s)?:\/\//",$path))
                return $path;
        
        // Shared assets starts from the root
        return 
            $this->getSiteUrl()."/".preg_replace("/^\//","",str_replace(BASE_PATH,"",$path ?: SHARED_ASSETS_PATH));      
    }
/*******************************************************************************/
// MODEL   

    /**
     * Access the application's global Module at Application/Model
     * @param string $modelNS - The model namespace with the full path
     * @param bool $useModulesModel - by default models will be called from Application\Model\$modelNs, 
     *                                when true it will get the model in current Module Application\$ModuleName\Model\$modelNs
     * @return Model
     * @throws Exception 
     * @example $this->getModel("Accounts/Users");
     */
    final protected function getModel($modelNS,$useModulesModel = false){
        
        $ModelPath = ($useModulesModel) ? "Module\\{$this->ModuleName}\\Model" : "Model";
        
        $Model = "Application\\{$ModelPath}\\".$this->formatNamespace($modelNS);
        
        if(!class_exists($Model))
            throw new Exception("Model doesn't exist: {$Model}");
            
        return
            new $Model();        
    }
    
    
/*******************************************************************************/
// PAGINATOR: Give instant access to the paginator class with loaded data
    
    /**
     * Access the Paginator object
     * @return Core\Paginator 
     */
    public function paginator(){
        return
            $this->Paginator;
    }
    
    
    /**
     * Load the paginator 
     * It will load the class dependency which must implement the Pagination
     * @return Controller
     */
    private function loadPaginator(){
        
        $Interface = "\\Voodoo\\Core\\Interfaces\\Pagination";
        
        $ClassInjection = $this->getConfig("VoodooDependencies.Controller.Pagination");
        
        $DI = new \ReflectionClass($ClassInjection);
        
        if(!$DI->implementsInterface($Interface))
            throw new Exception("{$ClassInjection} Must implement the interface: {$Interface}");
        
            
        $this->Paginator = $DI->newInstanceArgs(array($this->getRequestURI(),$this->getConfig("Views.Pagination.PagePattern")));       
        
        $this->Paginator->setItemsPerPage($this->getConfig("Views.Pagination.ItemsPerPage"))
                        ->setNavigationSize($this->getConfig("Views.Pagination.NavigationSize"));
        return
            $this;
    }
/*******************************************************************************/
// CONFIG
    
    /**
     * Load the config file
     * @return Voodoo\Core\Controller
     */
    private function loadConfig(){
        
        $this->Config = new INI(APPLICATION_MODULES_PATH."/{$this->ModuleName}/Config.ini",false,true);
        
        return
            $this;
    }
    
    /**
     * To access config info
     * @param type $key
     * @return mixed 
     */
    protected function getConfig($key=null){
       return $this->Config->get($key);
    }
    
/*******************************************************************************/

    /**
     * Return a formatted date
     * @param mixed $datetime
     * @param string format ie: M-d-Y - the format to use, or will use the config default
     * @return string
     */
    public function toDate($datetime,$format=null){
        return Helpers::formatDate($datetime,$format ?: $this->getConfig("Views.DateFormat"));
    }
    
    
    /**
     * Return a string to friendly url
     * @param type $url
     * @return string 
     */
    public function toFriendlyUrl($url){
        return Helpers::toFriendlyUrl($url);
    }

    
/*******************************************************************************/

    
    /**
     * Bool if the request method is a POST
     * @return bool
     */
    public function isPost(){
       return HTTP\Request::is("POST"); 
    }
    
    /**
     * Bool if the request method is a GET
     * @return bool
     */    
    public function isGet(){
      return HTTP\Request::is("GET");  
    }    
    
    /**
     * CHeck request if it's an ajax request
     * @return bool
     */
    public function isAjax(){
        return HTTP\Request::isAjax();
    }

    /**
     * Retrieve a cookie that was set. Use normal php setcookie to set a cookie
     * @param type $key
     * @return mix 
     */
    public function getCookie($key){
        return $_COOKIE[$key];
    }
/*******************************************************************************/

    /**
     * To redirect the page to a new page
     * @param type $path 
     */
    public function redirect($path=""){
        
        if(preg_match("/^\/|^http/",$path))
         $url = $path;     
        
        else
            $url = VOODOO_APP_ROOT_URL."/{$this->ModuleName}/{$path}";
        
        Helpers::redirect($url);

    }
    
/*******************************************************************************/
    
    /**
     * To properly format a namespace string to be used
     * @param type $string - the NS string in the format: Namespace/SubNamespace/SubSubNamespce
     * @return string
     */
    private function formatNamespace($string){
        $_l = array(); 
        $_xplod = explode("/",$string);
        foreach($_xplod as $_s)
            if($_s)
                $_l[] = Helpers::camelize($_s,true);
        
        return 
            implode("\\",$_l);        
    }    
    

    
/*******************************************************************************/
// Magic Methods to Set and Unser  
    /**
     * Assign global variables to be used in all controllers. To set a variable that will be used in its own controller, it must be defined prior .ie: public $varName;
     * @param type $name
     * @param type $var 
     */
    final public function __set($name,$var){
        self::$globalVars[$name] = $var;
    }
    
    /**
     * Retrieve a global variable that was set by __set()
     * @param type $name
     * @return type 
     */
    final public function __get($name){
        return (isset(self::$globalVars[$name])) ? self::$globalVars[$name] : null;
    }

    final public function __isset($name){
       return (isset(self::$globalVars[$name])); 
    }
    
    final public function __unset($Name){
        if(self::$globalVars[$name])
            unset(self::$globalVars[$name]);
    }
    
    
    public function __toString(){
       print($this->renderView());
    }
    
    
}

