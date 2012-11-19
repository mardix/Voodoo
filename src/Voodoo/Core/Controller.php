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
 * @desc        This is the abstract controller which must be extended to read the proper controller name and actions
 *              Controller contains necessary methods for your own controller
 *
 */

namespace Voodoo\Core;

use ReflectionClass,
    Closure;

abstract class Controller
{
    /**
     * Segments passed
     * @var array
     */
    private $segments = array();

    /**
     * The full namespace of the controller
     * @var string
     */
    protected $namespace = "";

    /**
     * The module name
     * @var String
     */
    protected $moduleName = "";

    /**
     * The controller's name
     * @var String
     */
    protected $controllerName = "";

    /**
     * The action's name being called
     * @var string
     */
    protected $actionName = "";
    // The action view to use
    protected $actionView = "";

    /**
     * @var Core\View()
     */
    private $view = null;

    /**
     * @var Core\INI
     */
    private $config = null;

    /**
     * On destruct render view
     * @var bool
     */
    private $disableView = false;
    private $moduleDir = "";
    private $applicationDir = "";
    private $moduleNamespace = "";
    private $controllerNamespace = "";
    private $modelNamespace = "";

    protected $httpStatusCode = 200;
    
    protected $exit = false;
    
    protected $reflection = null;
//------------------------------------------------------------------------------
    /**
     * This is the index action.
     * It is loaded by default or an action is missing
     * Every controller requires it
     */
    abstract public function action_index();
//------------------------------------------------------------------------------

    /**
     * construct so no other class can override it
     * To load something in the constructor, use $this->main()
     * @params array $segments - extra segments from that can be accessed with getParams()
     */
    final public function __construct(Array $segments = array())
    {
        /**
         * Built variables based on the controller
         */
        $this->reflection = new ReflectionClass(get_called_class());

        $namespace = $this->reflection->getNamespaceName();

        $nsArr = explode("\\", $namespace);
        $this->moduleName = current(array_splice($nsArr, -2));
        $this->namespace = $this->reflection->getName();
        $this->controllerName = $this->reflection->getShortName();
        $this->controllerNamespace = $namespace;
        $this->moduleDir = dirname(dirname($this->reflection->getFileName()));
        $this->applicationDir = dirname($this->moduleDir);
        $this->moduleNamespace = $this->getParentNamespace($namespace);
        $this->modelNamespace = $this->moduleNamespace."\\Model";

        $this->segments = $segments;

        $this->init();
    }

    /**
     * init()
     * __construct is and can't be overriden by any child class
     * init() lets you put code that could be executed in __construct()
     * @return Controller
     */
    protected function init()
    {}

    /**
     * finalize()
     * Code to excute before rendering
     */
    protected function finalize()
    {
        Http\Response::setStatus($this->httStatusCode);
        return $this;
    }


    /**
     * It's a wrap
     * By default, when the destructor is called, it will render the views
     * To disable view, in your controller set: $this->disableView(true)
     */
    final public function __destruct()
    {
        if (! $this->exit) {
            $this->finalize();
            $this->renderView();            
        }
    }

    /**
     * Using exit() anywhere, the __destruct() will still be executed
     * _exit() will force the destructor to not execute the finalize or render
     * @param bool $exit
     * @return boolean
     */
    final protected function _exit($exit = true)
    {
        $this->exit = $exit;
        return true;
    }
//------------------------------------------------------------------------------

    /**
     * Get POST ot GET params
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParam($key = null, $default = null)
    {
        return Http\Request::getParam($key, $default);
    }

    /**
     * Segements are part of the URL separated by /
     * ie: /gummy/bear/?q=hello 'gummy' and 'bear' are segments.
     * @param mixed (int | string) - if int, i will pick the index of the . If a string, it will return the k/v pair of the segemnt
     * @param int Where to start the segment
     * @return mixed
     */
    public function getSegment($key = null, $offset = 0)
    {
        if (is_numeric($key)) {
           return $this->segments[$key - 1];
        } else if (is_string($key)) {
            $segments = array_slice($this->segments, $offset);
            $i = 0;
            $lastval = '';
            $segs = array();

            foreach ($segments as $seg) {
                if ($i % 2) {
                    $segs[$lastval] = $seg;
                } else {
                    $segs[$seg] = FALSE;
                    $lastval = $seg;
                }
                $i++;
            }
            return ($key) ? $segs[$key] : $segs;
        } else {
            return $this->segments;
        }
    }

    /**
     * To catch the first numeric value from the URL segment. ie: /music/rap/12573/Where-Have-You-Been. Will return 12573
     * @return mixed (int | null)
     */
    public function catchNumericSegment()
    {
        foreach ($this->segments as $s) {
            if (is_numeric($s)) {
                return $s;
            }
        }
        return null;
    }

//------------------------------------------------------------------------------

    /**
     * Return the module's name
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Return the controller's name
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * Get the module directory
     * 
     * @return string
     */
    public function getModuleDir()
    {
        return $this->moduleDir;
    }

    /**
     * Get the application
     * 
     * @return string
     */
    public function getApplicationDir()
    {
        return $this->applicationDir;
    }

    /**
     * To get the request uri. It includes everything in the URI
     * 
     * @return string
     */
    public function getRequestURI()
    {
        return $_SERVER["REQUEST_URI"];
    }

    /**
     * Return the root dir relative to the Application dir
     * Use it to include files, or get relative path of file
     * @return string
     */
    public function getBaseDir()
    {
        return Env::getApplicationBaseDir();
    }

    /**
     * Return the root url which will properly format the url so it adds or not ? to make the relative link
     * @uses    : to make links
     * @example : http://mysite.com/? -> http://mysite.com/?/ModuleName if htaccess is missing, or http://mysite.com/ModuleName is htaccess is here
     * @return string
     */
    public function getBaseUrl()
    {
        $questionMark = Config::System()->get("useUrlQuestionMark");
        return $this->getSiteUrl().($questionMark ? "/?" : "");
    }

    /**
     * Return the site url itself
     * 
     * @uses    : To get the site url
     * @example : http://mysite.com/
     * @return string
     */
    public function getSiteUrl()
    {
        return Env::getUrl();
    }

    /**
     * Return the URL of the module
     * 
     * @uses    : Get the module url
     * @return string
     */
    public function getModuleUrl()
    {
        return preg_replace("/\/$/", "", $this->getBaseUrl() . "/" . (($this->moduleName == "Main") ? "" : $this->moduleName));
    }

    /*     * **************************************************************************** */

// CONTROLLER
    /**
     * To access another controller without rendering it
     *
     * @param string $controllerName. If it starts with \ (backslash) 
     * it will load it from the absolute path, otherwise it loads from the current namespace
     * @param array $params
     * @return \Voodoo\Core\controller
     * @throws Exception
     */
    protected function getController($controllerName, Array $params = array())
    {
        $controller = (strpos('\\',$controllerName) === 0)
                        ? $controller
                        : $this->controllerNamespace."\\".Helpers::camelize($controllerName, true);

        $clsRef = new ReflectionClass($controller);

        if ($clsRef->isSubclassOf(__CLASS__)) {
            return (new $controller($params))->disableView();
        } else {
            throw new Exception("Can't getController(). Controller '$controller' doesn't exists or not an instance of Voodoo\Core\Controller");
        }
    }

    /**
     * forward, unlike getController, forward the current controller to a new controller 
     * and allows it to render the view, while it deactivate the current controller view.
     * All the settings and params will be forwarded to the new controller
     * @param type $Controller
     */
    protected function forward($Controller, Array $params = array())
    {
        $this->_exit();
        
        $params = array_merge_recursive($this->segments, $params);
        return $this->getController($Controller, $params)
                        ->disableView($this->disableView);
    }

    /*     * **************************************************************************** */

// ACTION
    /**
     * Load an action by providing just the name without the Action suffix.
     * Its purpose is to set the action to be rendered. You still can access the method the normal way $this->action_index
     * i.e $this->getAction("index");
     * @param  string     $action       - The action name without Action as suffix. ie: action_index() =  getAction("index")
     * @return Controller
     */
    public function getAction($action = "index")
    {
        $action = strtolower(Helpers::camelize($action, false));

        $actionName = "action_{$action}";

        if (method_exists($this, $actionName)) {
            $this->actionName = $action;
            $this->setActionView($this->actionName);
            $this->{$actionName}();
        } else {
            throw new Exception("Action '{$actionName}' doesn't exist in " . get_called_class());
        }
        return $this;
    }

    /**
     * Return the last action name saved
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * Set the action view to be displayed
     * @param  type       $view
     * @return Controller
     */
    protected function setActionView($view)
    {
        $this->actionView = $view;
        return $this;
    }

    /**
     * Return the action view
     * @return type
     */
    protected function getActionView()
    {
        return $this->actionView;
    }

// VIEW

    /**
     * Return the View instance
     * @return Core\View
     */
    protected function view(Closure $vendorCallback = null)
    {
        if (!$this->view){
            if (is_callable($vendorCallback)) {
                $this->view = $vendorCallback();
            } else {
                $this->view = new View($this);
                $this->view->setContainer($this->getConfig("views.container"));
            }
        }
        return $this->view;
    }

    /**
     * To render the controller's view
     * @param  bool    $echoView - to print the view or just return it
     * @return boolean
     */
    protected function renderView($echoView = true)
    {
        if ($this->disableView || !$this->viewExists()) {
            return false;
        } else {
            $this->view()->setBody($this->actionView);
            $render = $this->view()->render();
            return ($echoView) ? print($render) : $render;
        }
    }


    /**
     * Verify if the view directory exists
     * @return bool
     */
    protected function viewExists()
    {
        return is_dir($this->moduleDir."/Views");
    }


    /**
     * To enable render view. on __destruct, it will render the view, otherwise it's up to the controller to launch it.
     * @param  bool       $en
     * @return Controller
     */
    public function disableView($bool = true)
    {
        $this->disableView = $bool;
        return $this;
    }

    /*     * **************************************************************************** */

// MODEL
    /**
     * Access the module's model, or load it from another module
     * @param string $modelName  - The name of the model to use. 
     * If it starts with a \ (blackslah), it will load the model from there
     * Otherwise it loads the model from the current NS
     * @return Model
     * @throws Exception
     * @example $this->getModel("Users");
     *          $this->getModel("Bands");
     */
    protected function getModel($modelName)
    {
        $model = (strpos('\\',$modelName) === 0)
                        ? $model
                        : $this->modelNamespace."\\"
                            .Helpers::camelize($modelName, true);

        if (class_exists($model)) {
            return new $model;
        } else {
            throw new Exception("Model doesn't exist: {$model}"); 
        }
    }

    /*     * **************************************************************************** */
// CONFIG

    /**
     * To access config info
     * @param  type  $key
     * @return mixed
     */
    public function getConfig($key = null)
    {
        if (!$this->config) {
            $configFile = "{$this->applicationDir}/Config.ini";
            $this->config = (new Config($this->controllerNamespace))
                                ->loadFile($configFile);
        }
        return $this->config->get($key);
    }

    /*     * **************************************************************************** */

    /**
     * Return a formatted date
     * @param mixed $datetime
     * @param string format ie: M-d-Y - the format to use, or will use the config default
     * @return string
     */
    public function toDate($datetime, $format = null)
    {
        return Helpers::formatDate($datetime, $format ? : $this->getConfig("views.dateFormat"));
    }

    /**
     * Return a string to friendly url
     * @param  type   $url
     * @return string
     */
    public function toFriendlyUrl($url)
    {
        return Helpers::toFriendlyUrl($url);
    }

    /*     * **************************************************************************** */

    /**
     * Bool if the request method is a POST
     * @return bool
     */
    public function isPost()
    {
        return Http\Request::is("POST");
    }

    /**
     * Bool if the request method is a GET
     * @return bool
     */
    public function isGet()
    {
        return Http\Request::is("GET");
    }

    /**
     * CHeck request if it's an ajax request
     * @return bool
     */
    public function isAjax()
    {
        return Http\Request::isAjax();
    }

    /**
     * Retrieve a cookie that was set. Use normal php setcookie to set a cookie
     * @param  type $key
     * @return mix
     */
    public function getCookie($key)
    {
        return $_COOKIE[$key];
    }

    /**
     * Set the http status code
     *
     * @param int $code
     * @return Voodoo\Core\Controller
     */
    public function setHttpCode($code = 200)
    {
        $this->httpStatusCode = $code;
        return $this;
    }
    /*     * **************************************************************************** */

    /**
     * To redirect the page to a new page
     * @param string $path
     * @param int $httpCode 
     */
    public function redirect($path = "", $httpCode = 302)
    {
        if (preg_match("/^http/", $path)) { // http://xyz
            $url = $path;
        } else if(preg_match("/^\//", $path)) { // we'll add the ? if possible
            $url = $this->getBaseUrl().$path; 
        } else { // go to the current module
            $url = $this->getModuleUrl()."/{$path}";
        }
       
        $this->_exit();
        return Http\Response::redirect($url, $httpCode);
    }
    
    /**
     * Return the parent namespace
     * @param type $namespace
     * @return string
     */
    private function getParentNamespace($namespace)
    {
        $nsArr = explode("\\", $namespace);
        return implode("\\",array_splice($nsArr,0,-1));
    }
    
    /**
     * Return the full name of the class
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->controllerNamespace;
    }    
}
