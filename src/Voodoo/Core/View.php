<?php

/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/VoodooPHP/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2012-2013 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * @name        Core\View
 * @desc        Core View. 
 * 
 * Mustache Extension
 * == New Markups
 *      === LAYOUT ===
 *      - Layout: To change the layout to another one from Views/_layout directory
 *              {{!use_layout default}} will include _layout/default.html
 * 
 *      === INCLUDE ===
 *      {{!include $path}} is added to include file using $this->addTemplate during parsing
 *      It's best to use {{!include}} when the included file will contains other customs included
 *      
 *      $path:
 *          {{!include @action_view}}. Include a file by @name, when it was included
 *                                  directly with $this->addTemplate($name, $src)
 *          {{!include filename}}. Without ../ or / it will include file from the current
 *                                  view directory of the controller
 *          {{!include ../filanem}}. With the ../ it will include file from the
 *                                  /Views of the current module
 *          {{!include /AppName/ModuleName/Views/filename}}. Include a file from
 *                                  a different Appname, with a full path
 *   
 */

namespace Voodoo\Core;

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;

class View 
{
    use View\TView;
    
    const TITLE_CONCAT_PREPEND = 1;
    const TITLE_CONCAT_APPEND = 2;
    
    public $isDisabled = false;
    public $isRendered = false;

    // View file extension
    protected $ext = ".html";
    protected $appRootDir;
    protected $actionView;
    protected $isActionViewAbsolute = false;
    protected $layout;
    protected $isLayoutAbsolute = false;
    protected $config;
    protected $renderedContent;
    protected $controllersViewPath;
    
    protected $templates =  [];
    protected $templateDir = "";
    protected $parsed = false;
    protected $definedRaws = []; 

    private $controller = null;
    private $renderJSON = false;
 
    private $meta = [];
    
    private $templateKeys = [
        "view"      => "action_view",
        "layout"    => "page_layout"
    ];
    
    private $path = [
        "components" => "_components",
        "layouts"  => "_layouts"
    ];
    
    private $engine = null;
    private $flashMessage = null;
//------------------------------------------------------------------------------
    /**
     * The constructor
     *
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->templateDir = $this->controller->getModuleDir() . "/Views";
        $this->controllersViewPath = $this->templateDir . "/";
        $this->controllersViewPath .= $this->controller->getControllerName();
        $this->appRootDir = Env::getAppRootDir();

        /**
         * Handlebars
         */
        $hbOptions = ["extension" => $this->ext];
        $partialsLoader = new FilesystemLoader($this->templateDir, $hbOptions);
        $this->engine = new Handlebars(["partials_loader" => $partialsLoader]);
                    
        /**
         * FlashMessage
         */
        $this->flashMessage = new View\FlashMessage;
    }

    /**
     * To set the title
     * 
     * @param string $title
     * @param bool $concat
     * @param int $position
     * @return \Voodoo\Core\View
     */
    public function setTitle($title, $concat = false, $position = self::TITLE_CONCAT_APPEND)
    {
        if ($concat) {
            if ($position == self::TITLE_CONCAT_APPEND) {
                $title = $this->meta["title"] . " " . $title;
            } else if ($position == self::TITLE_CONCAT_PREPEND) {
                $title = $title . " " . $this->meta["title"];
            } 
        } 
        return $this->setMeta("title", $title);
    }
    
    /**
     * Set the description
     * 
     * @param string $description
     * @return \Voodoo\Core\View
     */
    public function setDescription($description)
    {
        $this->meta["description"] = $description;
        return $this;
    }

    /**
     * Set the keywords
     * 
     * @param array $keywords
     * @return \Voodoo\Core\View
     */
    public function setKeywords(Array $keywords)
    {
        return $this->setMeta("keywords", implode(",", array_unique($keywords)));
    }
    
    /**
     * Set the pagination model
     * 
     * @param array $data
     * @return View
     */
    public function setPagination(Array $data) 
    {
        $this->assign("_app.pagination", $data);
        return $this;
    }
        
    /**
     * Set multiple meta data
     * @param type $key
     * @param type $value
     * @return \Voodoo\Core\View
     */
    public function setMeta($key, $value)
    {
        $this->meta[$key] = $value;
        return $this;
    }

    /**
     * Set a flash message
     * 
     * @param type $message
     * @param type $type
     * @param array $data
     * @return \Voodoo\Core\View
     */
    public function setFlashMessage($message, $type = View\FlashMessage::TYPE_NOTICE, Array $data = [])
    {
        $this->flashMessage->set($message, $type, $data);
        return $this;
    }
    
    /**
     * Get flash message
     * 
     * @param string $type - Return flash messages of a type
     * @return Array
     */
    public function getFlashMessage($type = null)
    {
        return $this->flashMessage->get($type);
    }
    
    /**
     * Clear flash message
     * 
     * @return \Voodoo\Core\View
     */
    public function clearFlashMessage()
    {
        $this->flashMessage->clear();
        return $this;
    }
    
/*******************************************************************************/

    /**
     * Check if the views directory exists
     * 
     * @return bool
     */
    public function exists()
    {
        return is_dir($this->controllersViewPath);
    }

    /**
     * Set the view layout to use. By default it will user the contain set in config.
     * 
     * @param  string $filename - The name of the layout under _layouts/
     * @return Voodoo\Core\View
     */
    public function useLayout($filename, $isAbsolute = false)
    {
        $this->isLayoutAbsolute = $isAbsolute;
        $this->layout = $filename;
        if (! $this->isLayoutAbsolute) {
            $this->layout = $this->path["layouts"] . "/" . $filename;
        }
        return $this;
    }

    /**
     * Return if the layout is set
     * 
     * @return bool
     */
    public function issetLayout()
    {
        return $this->layout ? true : false;
    }
    
    
    /**
     * Set the view body  to use. By default it will use the the action view => $action.html
     * 
     * @param  string $filename
     * @param  bool $absolute - true, it will use the full path of filename, or it will look in the current Views
     * @return Voodoo\Core\View
     */
    public function setActionView($filename, $absolute = false)
    {
        $this->actionView = $filename;
        $this->isActionViewAbsolute = $absolute;
        return $this;
    }

    /**
     * Return if the view is set
     * 
     * @return bool
     */
    public function issetActionView()
    {
        return $this->actionView ? true : false;
    }
    
    
    /**
     * Retun bool if the action view file exists
     * 
     * @return bool
     */
    public function actionViewExists()
    {
        $file = ($this->isActionViewAbsolute == true) 
                ? $this->actionView 
                : $this->templateDir.$this->actionView;
        return file_exists($file);
    }
    
    /**
     * To set the error page
     *  
     * @param string $errorMessage 
     * @param int $errorCode
     * @return Voodoo\Core\View
     */
    public function setActionError($errorMessage = "", $errorCode = 500 )
    {
        if($errorMessage) {
            $this->setError($errorMessage);
        }
        $this->setActionView($this->path["components"]."/error_".$errorCode);
        return $this;
    }
    
    /**
     * To render as JSON
     * 
     * @param type $bool
     * @return \Voodoo\Core\View
     */
    public function renderToJson($bool = true)
    {
        $this->renderJSON = $bool;
        return $this;
    }
    
    /**
     * Check if the view is to be returned as JSON
     * 
     * @return bool
     */
    public function isRenderToJson()
    {
        return $this->renderJSON;
    }

    /**
     * Render the template
     * 
     * @return String
     */
    public function render()
    {
        if($this->renderJSON) {// RENDER AS JSON
            $assigned = $this->getAssigned();
            unset($assigned["_app"]);
            return json_encode($assigned);
        } else {// RENDER AS HTML
            // Content already rendered
            if ($this->renderedContent && $this->isRendered) {
                return $this->renderedContent;
            }

            // _app.title
            if (isset($this->meta["title"])) {
                $this->assign("_app.title", $this->meta["title"]);
            }

             // _app.flashMessage
            $flashMessage = $this->getFlashMessage();
            if ($flashMessage) {
                $this->assign("_app.flashMessage", $flashMessage);
                $this->clearFlashMessage();
            }
            
            // _app.error
            if ($this->hasError()) {
                $this->assign("_app.error", $this->getMessage("error"));
            }

            $this->assign("_app", [
                    "year"              => date("Y"),
                    "site_url"          => $this->controller->getSiteUrl(),
                    "base_url"          => $this->controller->getBaseUrl(),
                    "meta"              => $this->meta,
                    "shared_assets"     => $this->getPublicAssetsDir(),
                    "assets"            => $this->getModuleAssetsDir(),
                    "module_name"       => $this->controller->getModuleName(),
                    "module_url"        => $this->controller->getModuleUrl(),
                    "controller_name"   => $this->controller->getControllerName(),
                    "controller_url"    => $this->controller->getControllerUrl(),
                    "action_name"       => $this->controller->getActionName(),
                    "action_url"        => $this->controller->getActionUrl(),
            ]);

            $renderName = $this->templateKeys["view"];
            $this->addTemplate(
                        $this->templateKeys["view"], 
                        $this->actionView, 
                        $this->isActionViewAbsolute
                    );

            if ($this->layout) {
               $renderName = $this->templateKeys["layout"];
               $this->addTemplate(
                        $this->templateKeys["layout"], 
                        $this->layout,
                        $this->isLayoutAbsolute
                    );
            }

            $this->isRendered = true;

            $this->parse();

            if (isset($this->templates[$renderName])) {
                $this->renderedContent = $this->engine->render(
                                                $this->templates[$renderName], 
                                                $this->getAssigned()
                                        );
                // Strip HTML Comments
                if ($this->controller->getConfig("views.stripHtmlComments")) {
                   $this->renderedContent = 
                           Helpers::stripHtmlComments($this->renderedContent);
                }
            }
            return $this->renderedContent;            
        }
    }

   
    /**
     * Return the complete file path of the template in the current view.
     * It can be used to access another views from another module
     * 
     * @param  strin   $path
     * @return string
     */
    public function getFilePath($path = "index")
    {
        return $this->controllersViewPath . "/" . strtolower($path) . $this->ext;
    }
    
    /**
     * To add a template file
     * 
     * @param  type $name - the name of the template. Can be used to call it: {{!include @name}}
     * @param  type  $src - the filepath relative to the working dir
     * @return Voodoo\Core\View
     */
    public function addTemplate($name, $src, $absolutePath = false)
    {
        if (! $absolutePath) {
            $controllerName = $this->controller->getControllerName();
            $src = (preg_match("/^({$controllerName}\/|_[\w]+)/", $src)) 
                                ? $src : "{$controllerName}/{$src}";
            $src = $this->addExt($this->templateDir . "/" . $src);
        } 
        $content = $this->loadFile($src, true);

        /**
         * {{!use_layout layout_name}}
         * Only @action_view checks for the layout
         * It parses the template first to make sure it doesn't contain any raw tags
         */
        if ($name == $this->templateKeys["view"]) {
            $content = $this->parseTemplate($content);
            if(preg_match("/{{!use_layout\s+(.*?)\s*}}/i", $content, $matches)){
               $content = str_replace($matches[0], "", $content);
               $this->useLayout($matches[1]);
           }           
        }
        $this->addTemplateString($name, $content);
        return $this;
    }
  
    /**
     * Add file extension if ommitted
     * @param  string $file
     * @return string
     */
    private function addExt($file)
    {
        if (! pathinfo($file, PATHINFO_EXTENSION)){
            return $file . $this->ext;
        }
        return $file;
    }
    /**
     * To add a template string
     * @param  string           $name
     * @param  string           $content
     * @return Voodoo\Core\View
     */
    public function addTemplateString($name, $content)
    {
        $this->templates[$name] = $this->parseTemplate($content);
        return $this;
    }

    /**
     * To remove a template name
     * @param  type           $name
     * @return Voodoo\Core\View
     */
    public function removeTemplate($name)
    {
        if (isset($this->templates[$name])) {
            unset($this->templates[$name]);
        }
        return $this;
    }    
    
    
    /**
     * To reset the parsing
     * @return Voodoo\Core\View
     */
    public function reparse()
    {
        $this->parsed = false;
        return $this;
    }

    /**
     * Load the template file
     * @param  type   $src
     * @param  type   $absolute
     * @return string
     * @throws Voodoo\Core\View\Exception
     */
    protected function loadFile($src)
    {
         if (! file_exists($src)) {
             throw new Exception\View("File '{$src}' doesn't exist");
         } else {
             return file_get_contents($src);
         }
    }

    /**
     * Parse the template and catch any {{!include }} expression
     * 
     * @param string $template
     * @return string
     */
    private function parseTemplate($template)
    {
        if (preg_match_all("/{{!include\s+(.*?)\s*}}/i", $template, $matches)) {
            foreach ($matches[1] as $k => $src) {
                if (! preg_match("/^@/",$src)) {
                    if (preg_match("/^\//", $src)) { // {{!include /outOfScopePath}}
                        $src = $this->appRootDir . $this->addExt($src);
                    } else if (preg_match("/^\.\.\//", $src)) { // {{!include ../pathFromViewsRoot}}
                        $src = str_replace("..", "", $src);
                        $src = $this->templateDir . $this->addExt($src);  
                    } else { // {{!include pathOfCurrentControllersViewPath}}
                        $src = $this->controllersViewPath. "/" . $this->addExt($src);
                    } 
                    $tkey = md5($src);
                    if(!isset($this->templates[$tkey])) {
                        $this->addTemplate($tkey, $src, true);
                    }
                    $template = $this->parseTemplate(
                                    str_replace(
                                            $matches[0][$k],
                                            $this->templates[$tkey],
                                            $template
                                    )
                                );
                }
            }
        }
        return $template;
    }

    /**
     * Parse the template
     * 
     * @return $this
     */
    private function parse()
    {
        if (! $this->parsed) {
            $this->parsed = true;
            foreach ($this->templates as $kk => $template) {
                if (preg_match_all("/{{!include\s+(.*?)\s*}}/i",$template,$matches)) {
                    foreach ($matches[1] as $k=>$src) {
                        // Anything with @Reference
                        if (preg_match("/^@/",$src)) {
                            $tplRef = preg_replace("/^@/","",$matches[1][$k]);
                            if (isset($this->templates[$tplRef])) {
                                $this->templates[$kk] = str_replace(
                                                        $matches[0][$k],
                                                        $this->templates[$tplRef],
                                                        $this->templates[$kk]
                                                    );
                            }
                        }
                    }
                }
            }            
        }
        return $this;
    }    
 
    /**
     * To create the module's assets dir
     * Base on the config file
     * @return string
     */
    private function getModuleAssetsDir()
    {
        $path = $this->controller->getConfig("views.moduleAssetsDir");
        switch (true) {
            // Assets in current Module
            case preg_match("/^(_[\w]+)/", $path):
                $moduleNamespace = $this->controller->getModuleNamespace();
                $path = "{$moduleNamespace}/Views/{$path}";
                break;
            // Assets anywhere with current page. ie: /ModuleName/Views/_assets
            case preg_match("/^\/\/?/", $path):
                $path = preg_replace("/^\/\/?/", "", $path);
                break;
            // Usually if a URL is provided, and the content will delivered from a different place
            default:
                return $path;
                break;
        }
        $url = preg_replace("/\/$/","",$this->controller->getSiteUrl());
        $path = str_replace(["\\"],["/"], $path);
        return "{$url}/{$path}";
    }

    /**
     * To create the shared assets dir
     * Base on the config file
     * @return string
     */
    private function getPublicAssetsDir()
    {
        $path = $this->controller->getConfig("views.publicAssetsDir");

        // Shared assets is from URL
        if (preg_match("/^http(s)?:\/\//", $path)){
            return $path;
        }

        // Shared assets starts from the root
        return
            $this->controller->getSiteUrl() . "/" . preg_replace("/^\//", "",
                    str_replace(Env::getFrontControllerPath(), 
                                "", 
                                $path ? : Env::getPublicAssetsPath()));
    }
    
    public function __string()
    {
        return $this->render();
    }
  
}
