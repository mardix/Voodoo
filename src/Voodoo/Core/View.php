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
 * @name        Core\View
 * @desc        Core View. 
 * 
 * Mustache Extension
 * == New Markups
 *      - Include
 *              {{%include filename.html}} : include the file from the working dir
 *              {{%include !/my/other/path/file.html}} : include fiel outside of the working dir
 *              {{%include @TemplateName}} : include a file reference name, which was loaded with ThickMustache::addTemplate($name,$src)
 *
 *      - Raw: Mustache tags between {{%raw}}{{/raw}} will not be parsed
 *              {{%raw}}
 *                  {{}}
 *              {{/raw}}
 * 
 *      - Layout
 *              {{%layout _layouts/default}}
 *              
 */

namespace Voodoo\Core;

use Voodoo\Paginator;

class View 
{
    use View\TView;
    
    public $isDisabled = false;
    public $isRendered = false;

    // View file extension
    protected $ext = ".html";

    protected $moduleName;
    protected $controllerName;
    protected $applicationPath;
    protected $viewsPath;
    protected $controllerPath;
    protected $body;
    protected $layout;
    protected $config;
    protected $renderedContent;
    protected $controllersViewPath;
    
    protected $templates =  [];
    protected $templateDir = "";
    protected $parsed = false;
    protected $definedRaws = []; 
    
    private $pageTitle;
    private $pageDescription;
    private $controller = null;
    private $paginator = null;
    private $form = null;
    private $renderJSON = false;
 
    private $templateKeys = [
        "view"      => "pageView",
        "layout"    => "pageLayout"
    ];
    
    private $path = [
        "includes" => "_includes",
        "layouts"   => "_layouts",
        "error"    => "_includes/error"
    ];
//------------------------------------------------------------------------------
    /**
     * The constructor
     *
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->moduleName = $this->controller->getModuleName();
        $this->controllerName = $this->controller->getControllerName();
        $this->applicationPath = $this->controller->getApplicationDir();
        $this->viewsPath = $this->controller->getModuleDir() . "/Views";
        $this->controllersViewPath = $this->viewsPath . "/{$this->controllerName}";
        $this->setDir($this->controllersViewPath);
    }
    
    /**
     * Ser the working dir. By default files will be loaded from there
     * @param string $dir
     * @return \Voodoo\Core\View
     */
    public function setDir($dir)
    {
        $this->templateDir =   preg_match("!/$!",$dir) ? $dir : "{$dir}/";
        return $this;
    } 
    
    /**
     * Set the extension to use
     * 
     * @param  string $extension
     * @return Voodoo\Core\View
     */
    final public function setExtension($extension = ".html")
    {
        $this->ext = $extension;
        return $this;
    }
    
    /**
     * Set the page title
     * 
     * @param string $title
     * @return ViewController
     */
    final public function setPageTitle($title = "")
    {
        $this->pageTitle = $title;
        return $this;
    }

    /**
     * Set the page description
     * 
     * @param string $desc
     * @return ViewController
     */
    final public function setPageDescription($desc = "")
    {
        $this->pageDescription = $desc;
        return $this;
    }
    
    
    
    /**
     * Return the module full path
     * 
     * @return string
     */
    public function getModulePath()
    {
        return $this->modulePath;
    }

    /**
     * Return the controller full path
     * 
     * @return string
     */
    public function getControllerPath()
    {
        return $this->controllerPath;
    }

    /**
     * Check if the views directory exists
     * 
     * @return bool
     */
    final public function exists()
    {
        return is_dir($this->controllersViewPath);
    }



    /**
     * Set the view layout to use. By default it will user the contain set in config.
     * 
     * @param  string $filename
     * @param  bool  $absolute - true, it will use the full path of filename, or it will look in the current Views
     * @return Voodoo\Core\View
     */
    public function setLayout($filename, $absolute = false)
    {
        $this->layout = $filename;
        $this->isLayoutAbsolute = $absolute;
        return $this;
    }

    /**
     * Set the view body  to use. By default it will use the the action view => $action.html
     * 
     * @param  string $filename
     * @param  bool $absolute - true, it will use the full path of filename, or it will look in the current Views
     * @return Voodoo\Core\View
     */
    public function setView($filename, $absolute = false)
    {
        $this->body = $filename;
        $this->isBodyAbsolute = $absolute;
        return $this;
    }

    /**
     * To set the error page
     *  
     * @param int $errorCode
     * @param string $errorMessage 
     * @return Voodoo\Core\View
     */
    public function setViewError($errorCode, $errorMessage = "")
    {
        if($errorMessage) {
            $this->setError($errorMessage);
        }
        $this->setView($this->path["error"]."/".$errorCode);
        
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
        // RENDER AS JSON
        if($this->renderJSON) {
            $assigned = $this->getAssigned();
            unset($assigned["this"]);
            return json_encode($assigned);
        } else {
            // RENDER AS HTML
            
            // Content already rendered
            if ($this->renderedContent && $this->isRendered) {
                return $this->renderedContent;
            }
            
            /**
             * LoadTemplates
             * Templates that are set in the config.ini of the module with key/value
             * These templates will be access with their alias in the view page. ie: {{%include @PageAliasName}}
             */
            $loadTemplates = $this->controller->getConfig("views.loadTemplate");
            if (is_array($loadTemplates)) {
                foreach ($loadTemplates as $pageKey => $pagePath) {
                    $this->addTemplate($pageKey, $pagePath);
                }
            }

            // this.title
            if ($this->pageTitle) {
                $this->assign("this.title", $this->pageTitle);
                $this->setMetaTag("TITLE", $this->pageTitle);
            }
            // this.description
            if ($this->pageDescription) {
                $this->assign("this.description", $this->pageDescription);
                $this->setMetaTag("Description", $this->pageDescription);
            }
            // this.pagination
            if ($this->paginator && $this->paginator->getTotalItems()) {
                $this->assign("this.pagination", $this->paginator()->toArray());
            }
             // this.flashMessage
            $flashMessage = $this->getFlashMessage();
            if ($flashMessage) {
                $this->assign("this.flashMessage", $flashMessage);
                $this->clearFlash();
            }
            // this.error
            if ($this->hasError()) {
                $this->assign("this.error", $this->getMessage("error"));
            }

            $this->assign("this", [
                    "module"    => [
                        "name"      => $this->moduleName,
                        "url"       => $this->controller->getModuleUrl(),
                        "assets"    => $this->getModuleAssetsDir()
                    ],

                    "controller" => [
                        "name"      => $this->controller->getControllerName(),
                        "url"       => $this->controller->getControllerUrl()
                    ],

                    "action" => [
                        "name"      => $this->controller->getActionName(),
                        "url"       => $this->controller->getControllerUrl()."/".$this->controller->getActionName()
                    ],

                    "global"       => [
                        "url"   => $this->controller->getBaseUrl(),
                        "assets"    =>  $this->getPublicAssetsDir()
                    ],

                    "year"      => date("Y"),
                    "siteUrl"   => $this->controller->getSiteUrl(),
            ]);

            $renderName = $this->templateKeys["view"];
            $this->addTemplate($this->templateKeys["view"], $this->body, $this->isBodyAbsolute);

            if ($this->layout) {
               $renderName = $this->templateKeys["layout"];
               $this->addTemplate($this->templateKeys["layout"], $this->layout, $this->isLayoutAbsolute);
            }

            $this->isRendered = true;

            $this->parse();

            if (isset($this->templates[$renderName])) {
                $template = (new View\Mustache($this->templates[$renderName], $this->getAssigned()))->render();

                // replace the raws and return
                $content = str_replace(array_keys($this->definedRaws),array_values($this->definedRaws),$template);

                // Strip HTML Comments
                if ($this->controller->getConfig("views.stripHtmlComments")) {
                   $content = Helpers::stripHtmlComments($content);
                }
                $this->renderedContent = $content;
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
    public function getPath($path = "index")
    {
        return $this->controllersViewPath . "/" . strtolower($path) . $this->ext;
    }



    /**
     * Create meta tags
     * 
     * @param  string $tag     - the tag name
     * @param  string $content - content
     * @return \Core\View
     *
     * @example
     * {{#app.metaTags}}
     *     {{{.}}}
     * {{/app.metaTags}}
     */
    public function setMetaTag($tag, $content = "")
    {
        switch (strtolower($tag)) {
            case "keywords":
                $tagName = "keywords";
                $content = implode(",", array_unique(array_map("trim", explode(",", $content))));
                break;
            case "lang":
                $metaTag = "<META HTTP-EQUIV=\"CONTENT-LANGUAGE\" CONTENT=\"{$content}\">";
                break;
            case "noindex":
                $tagName = "robots";
                $content = "NOINDEX,NOFOLLOW";
                break;
            case "canonical":
                $metaTag = "<link rel=\"canonical\" href=\"{$content}\">";
                break;
            default:
                $tagName = $tag;
                break;
        }

        if ($tagName){
            $metaTag = "<META NAME=\"$tagName\" CONTENT=\"$content\">";
        }
        if ($metaTag) {
            $this->assign("this.metaTags",array($metaTag));
        }

        return $this;
    }

    /**
     * OPENGRAPH
     * To create FB opengraph properties
     *
     * @example
     * {{#app.openGraphTags}}
     *     {{{.}}}
     * {{/app.openGraphTags}}
     */
    public function setOpenGraphTag($Prop, $content = "")
    {
        if (is_array($Prop)) {
            foreach ($Prop as $property => $content) {
                if (is_array($content)) {
                    foreach ($content as $cv) {
                        $this->setOpenGraphTag($property, $cv);
                    }
                } else {
                    $this->setOpenGraphTag($property, $content);
                }
            }
        } elseif (is_string($Prop) && $content) {
            $this->assign("this.openGraphTags", 
                    array("<meta property=\"$property\" content=\"$content\"/>"));
        }
    }



    /**
     * Access the Paginator object
     * 
     * @param int $totalItems - Set the total items 
     * @param int $itemsPerPage - Total items per page
     * @param string $uri - By default it will create a url from the URI, change it to set it to another url
     * 
     * @return Voodoo\Paginator
     */
    public function paginator($totalItems = null, $itemsPerPage = null, $uri = null)
    {
        if (! $this->paginator) {
            if(! $uri) {
                $uri = $this->controller->getBaseUrl();
                $uri .= $this->controller->getRequestURI();
            }
            $pattern = $this->controller->getConfig("views.pagination.pagePattern");
            $itemsPerPage = $itemsPerPage ?: $this->controller->getConfig("views.pagination.itemsPerPage");
            $navigationSize = $this->controller->getConfig("views.pagination.navigationSize");
            
            $this->paginator = new Paginator($uri, $pattern);
            $this->paginator->setItemsPerPage($itemsPerPage)
                            ->setNavigationSize($navigationSize);
        }
        if (is_numeric($totalItems)) {
            $this->paginator->setTotalItems($totalItems);
        }
        return $this->paginator;
    }

    /**
     * Return the Forms object
     * 
     * @return Core\View\Forms
     */
    public function form(){
        if(!$this->form){
            $this->form = new View\Forms;
        }
        return $this->form;
    }

    
    /**
     * To add a template file
     * 
     * @param  type $name - the name of the template. Can be used to call it: {{%include @name}}
     * @param  type  $src - the filepath relative to the working dir
     * @return Voodoo\Core\View
     */
    public function addTemplate($name, $src, $absolutePath = false)
    {
        $src = $this->getRealPath($src, $absolutePath);
        $content = $this->loadFile($src, true);

        /**
         * {{%layout path}}
         * Only @pageView checks for the layout
         * It parses the template first to make sure it doesn't contain any raw tags
         */
        if ($name == $this->templateKeys["view"]) {
            $content = $this->parseTemplate($content);
            if(preg_match("/{{%layout\s+(.*?)\s*}}/i", $content, $matches)){
               $content = str_replace($matches[0], "", $content);
               $this->setLayout($matches[1]);
           }           
        }
        
        $this->addTemplateString($name, $content);
        return $this;
    }
  
    /**
     * Get the real path of the file to include
     * 
     * @param string $src
     * @param bool $absolutePath
     * @return string
     */
    private function getRealPath($src, $absolutePath = false)
    {
        /**
         * To add a template file
         * To make it easy, you can load views of other modules in the current template
         * To do so, there are are two rules:
         *
         * 1. Single leading slash / mean to access another controller in the current module. ie: /Controller/view-file
         * 2. Double leading slash // means to access another module. ie: //ModuleName/Controller/view-file
         * 3. Triple leading slash !/ means to access another app module. ie: !/AppName/ModuleName/Controller/view-file
         * 3. If there are no slash, it will just call it from the current controller
         *
         * Access to absolute dir:
         * Absolute directory start with _WHATEVERNAME. These name will always be access from the root of the template.
         * They are but not limited to: _includes, _layouts ...
         *
         * Access _includes from other modules, to do so:
         *     //ModuleName/_includes/file.html
         */
        if (preg_match("/^\//", $src)) {

            // Current Module
            if (preg_match("/^\/([a-z0-9]+)/i", $src)) {
                $src = $this->moduleName . $src;
            } else if (preg_match("/^\/\/([a-z0-9]+)/i", $src)) {// Outter module
                $src = preg_replace("/^\/\//", "", $src);
            }

            $segments = explode("/", $src, 3);

            $Module = Helpers::camelize($segments[0], true);

            // Dont't convert absolute dir. Dir starts with _
            $Controller = preg_match("/^_[\w]+$/i", $segments[1]) ? $segments[1] : Helpers::camelize($segments[1], true);
            $viewAction = $segments[2];
            $src = ($absolutePath || preg_match("/^({$this->controllerName}|_[\w]+)/", $src)) ? $src : "{$this->controllerName}/{$src}";
            if ($Controller) {
                $src = $this->addFileExtension($this->applicationPath . "/{$Module}/Views/{$Controller}/$viewAction");
                $absolutePath = true;
            }
        }

        /**
         * Properly format the filename
         * If absolute path or is in _includes or the controller dir, leave as is
         * Second cond: to add the extension if it's missing
         */
        $src = ($absolutePath || preg_match("/^({$this->controllerName}\/|_[\w]+)/", $src)) ? $src : "{$this->controllerName}/{$src}";
        $src = $this->addFileExtension($src);
        $src = ($absolutePath) ? $src : ($this->viewsPath . "/{$src}");
        
        return $src;
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
     */
    protected function loadFile($src, $absolute=false)
    {
         $src = ($absolute == true) ? $src : $this->templateDir.$src;
         return (file_exists($src)) ? file_get_contents($src) : "";
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
                    str_replace(Env::getFrontControllerPath(), "", $path ? : Env::getPublicAssetsPath()));
    }

    /**
     * Add file extension if ommitted
     * @param  string $file
     * @return string
     */
    private function addFileExtension($file)
    {
        if (!pathinfo($file, PATHINFO_EXTENSION)){
            return $file . $this->ext;
        }
        return $file;
    }

    /**
     * parseTemplate
     * Once we receive template we'll want to parse it and get it ready
     * @param  string $template
     * @return string
     */
    private function parseTemplate($template)
    {
        /**
         * Raw
         * {{%raw}}{{/raw}}
         * extract everything between raw, and replace them on render
         */
        if (preg_match_all("/{{%raw}}(.*?){{\/raw}}/si",$template,$matches)) {
            $rawCount = count($this->definedRaws);
            foreach ($matches as $k=>$v) {
                if (isset($matches[1][$k])) {
                    ++$rawCount;
                    $name = "__TM::RAW{$rawCount}__";
                    $this->definedRaws[$name] = $matches[1][$k];
                    $template = str_replace($matches[0][$k],$name,$template);
                }
            }
        }

        /**
         * Include
         * {{%include file.html}}
         * To include other template file into the current one
         * {{%include file.html}} will load file in the working directory of the system
         * {{%include !/my/outside/dir/file.html}} will load file from the absolute path
         */
        if (preg_match_all("/{{%include\s+(.*?)\s*}}/i",$template,$matches)) {

            foreach ($matches[1] as $k => $src) {
                if (!preg_match("/^@/",$src)) {
                    $absolute = preg_match("/^!/",$src) ? true : false;

                    $src = preg_replace("/^!/","",$src);

                    $tkey = md5($src);

                    if(!isset($this->templates[$tkey])) {
                        $this->addTemplate($tkey, $src, $absolute);
                    }
                    $template = $this->parseTemplate(str_replace($matches[0][$k],$this->templates[$tkey],$template));
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
        if ($this->parsed){
            return false;
        }

        $this->parsed = true;
        foreach ($this->templates as $kk => $template) {
            if (preg_match_all("/{{%include\s+(.*?)\s*}}/i",$template,$matches)) {
                foreach ($matches[1] as $k=>$src) {
                    // Anything with @Reference
                    if (preg_match("/^@/",$src)) {
                        $tplRef = preg_replace("/^@/","",$matches[1][$k]);
                        if (isset($this->templates[$tplRef])) {
                            $this->templates[$kk] = str_replace($matches[0][$k],$this->templates[$tplRef],$this->templates[$kk]);
                        }
                    }
                }
            }
        }
        
        return $this;
    }    
    
    public function __string()
    {
        return $this->render();
    }
}
