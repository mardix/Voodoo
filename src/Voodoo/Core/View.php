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
 * @name        Core\View
 * @desc        The controller's view class
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
 */

namespace Voodoo\Core;

class View 
{
    use View\TView;
    
    public $isDisabled = false;
    public $isRendered = false;

    // View file extension
    protected $ext = ".html";

    protected $moduleName,
                $controllerName,
                $applicationPath,
                $viewsPath,
                $controllerPath,
                $body,
                $container,
                $config,
                $renderedContent,
                $controllersViewPath;
    
    protected $templates =  [];
    protected $templateDir = "";
    protected $parsed = false;
    protected $definedRaws = [];  
    
    private $pageTitle,
            $pageDescription;
    private $controller = null;
    private $paginator = null;
    private $form = null;

  
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

        $this->assign([
            "App" => [
                "Copyright" => "Copyright &copy; " . date("Y"), // Copyright (c) 2012
                "CurrentYear" => date("Y"), // The current year

                "SiteUrl" => $this->controller->getSiteUrl(),
                "Url" => $this->controller->getBaseUrl(),

                "Module" => [
                    "Name"      => $this->moduleName,
                    "Url"       => $this->controller->getModuleUrl(),
                    "Assets"    => $this->getModuleAssetsDir()
                ],

                "Assets"    => $this->getPublicAssetsDir()
            ],
        ]);

    }

    /**
     * Set the working directory. By default files will be loaded from there
     * @param  string         $dir
     * @return Voodoo\Core\View
     */
    public function setDir($dir)
    {
        $this->templateDir =   preg_match("!/$!",$dir) ? $dir : "{$dir}/";
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
     * Set the extension to use
     * 
     * @param  type             $extension
     * @return Voodoo\Core\View
     */
    final public function setExtension($extension = ".html")
    {
        $this->ext = $extension;
        return $this;
    }

    /**
     * Set the view container to use. By default it will user the contain set in config.
     * 
     * @param  string           $filename
     * @param  bool             $absolute - true, it will use the full path of filename, or it will look in the current Views
     * @return Voodoo\Core\View
     */
    public function setContainer($filename, $absolute = false)
    {
        $this->container = $filename;
        $this->isContainerAbsolute = $absolute;
        return $this;
    }

    /**
     * Set the view body  to use. By default it will use the the action view => $action.html
     * 
     * @param  string           $filename
     * @param  bool             $absolute - true, it will use the full path of filename, or it will look in the current Views
     * @return Voodoo\Core\View
     */
    public function setBody($filename, $absolute = false)
    {
        $this->body = $filename;
        $this->isBodyAbsolute = $absolute;
        return $this;
    }

    /**
     * Render the template
     * 
     * @return String
     */
    public function render()
    {
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

        if ($this->renderedContent && $this->isRendered)
            return $this->renderedContent;

        // App.Page.Title
        if ($this->pageTitle) {
            $this->assign("App.Page.Title", $this->pageTitle);
            $this->setMetaTag("TITLE", $this->pageTitle);
        }

        // App.Page.Description
        if ($this->pageDescription) {
            $this->assign("App.Page.Description", $this->pageDescription);
            $this->setMetaTag("Description", $this->pageDescription);
        }

        // App.Pagination
        if ($this->paginator && $this->paginator->getTotalItems()) {
            $this->assign("App.Pagination", $this->paginator()->toArray());
        }
        
         // App.FlashMessage
        $flashMessage = $this->getFlashMessage();
        if ($flashMessage) {
            $this->assign("App.FlashMessage", $flashMessage);
            $this->clearFlash();
        }

        $renderName = "PageBody";

        $this->addTemplate("PageBody", $this->body, $this->isBodyAbsolute);

        if ($this->container !== false && $this->container) {
            $renderName = "PageContainer";
            $this->addTemplate("PageContainer", $this->container, $this->isContainerAbsolute);
        }
        $this->isRendered = true;

        $this->parse();
        if (isset($this->templates[$renderName])) {
            $template = (new View\Mustache($this->templates[$renderName], $this->assigned))->render();
            // replace the raws and return
            $this->renderedContent =
                    str_replace(array_keys($this->definedRaws),array_values($this->definedRaws),$template);
        }
        return $this->renderedContent;
    }

   
    /**
     * Return the complete file path of the template in the current view.
     * It can be used to access another views from another module
     * @param  type   $path
     * @return string
     */
    public function getPath($path = "index")
    {
        return $this->controllersViewPath . "/" . strtolower($path) . $this->ext;
    }

    /**
     * Set the page title
     * @param type $title
     * @access Page::Title
     * @return ViewController
     */
    final public function setPageTitle($title = "")
    {
        $this->pageTitle = $title;
        return $this;
    }

    /**
     * Set the page description
     * @param type $desc
     * @access Page::Description
     * @return ViewController
     *
     */
    final public function setPageDescription($desc = "")
    {
        $this->pageDescription = $desc;
        return $this;
    }

    /**
     * Create meta tags
     * @param  string     $tag     - the tag name
     * @param  string     $content - content
     * @return \Core\View
     *
     * @example
     * {{#Page.MetaTags}}
     *     {{{.}}}
     * {{/Page.MetaTags}}
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
            $this->assign("App.Page.MetaTags",array($metaTag));
        }

        return $this;
    }

    /**
     * OPENGRAPH
     * To create FB opengraph properties
     *
     * @example
     * {{#Page.OpenGraphTags}}
     *     {{{.}}}
     * {{/Page.OpenGraphTags}}
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
            $this->assign("App.Page.MetaTags", 
                    array("<meta property=\"$property\" content=\"$content\"/>"));
        }
    }



    /**
     * Access the Paginator object
     * @return Core\View\Paginator
     */
    public function paginator()
    {
        if (!$this->paginator) {
            $uri = $this->controller->getRequestURI();
            $pattern = $this->controller->getConfig("views.pagination.pagePattern");
            $itemsPerPage = $this->controller->getConfig("views.pagination.itemsPerPage");
            $navigationSize = $this->controller->getConfig("views.pagination.navigationSize");

            $this->paginator = new View\Paginator($uri, $pattern);
            $this->paginator->setItemsPerPage($itemsPerPage)
                            ->setNavigationSize($navigationSize);
        }
        return $this->paginator;
    }

    /**
     * Return the Forms object
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
     * @param  type             $name     - the name of the template. Can be used to call it: {{%include @name}}
     * @param  type             $src      - the filepath relative to the working dir
     * @return Voodoo\Core\View
     */
    public function addTemplate($name, $src, $absolutePath = false)
    {
        /**
         * To add a template file
         * To make it easy, you can load views of other modules in the current template
         * To do so, there are are two rules:
         *
         * 1. Double leading slash // means to access another module. ie: //ModuleName/Controller/view-file
         * 2. Single leading slash / mean to access another controller in the current module. ie: /Controller/view-file
         * 3. If there are no slash, it will just call it from the current controller
         *
         * Access to absolute dir:
         * Absolute directory start with _WHATEVERNAME. These name will always be access from the root of the template.
         * They are but not limited to: _includes
         *
         * Access _includes from other modules, to do so:
         *
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
        $src = ($absolutePath || preg_match("/^({$this->controllerName}|_[\w]+)/", $src)) ? $src : "{$this->controllerName}/{$src}";
        $src = $this->addFileExtension($src);
        $src = ($absolutePath) ? $src : ($this->viewsPath . "/{$src}");
        $this->addTemplateString($name, $this->loadFile($src, true));
        return $this;
    }
  
    /**
     * To add a template string
     * @param  type           $name
     * @param  type           $content
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
                $path = "{$this->moduleName}/Views/{$path}";
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

        $root = Env::getRootDir();
        $baseDir = Config::Application()->get("VoodooApp.BaseDir") == "/" ? "" : Config::Application()->get("VoodooApp.BaseDir");
        $modulePath = str_replace(array($root, "\\", $baseDir),  array("", "/",""),$this->applicationPath);
        $url = preg_replace("/\/$/","",$this->controller->getSiteUrl());
        return $url.$modulePath."/$path";

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
                    str_replace(Path::Base(), "", $path ? : Path::Assets()));
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
