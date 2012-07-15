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
 * @name        Core\Controller
 * @since       Jun 24, 2011
 * @desc        A View/Controller class 
 * 
 */

namespace Voodoo\Core;


class View extends ThickMustache implements Interfaces\View{
    
    /**
     * The default file extension if no extension is in the filename. Otherwise it will whatever extension the file has
     * @var type 
     */
    protected $ext = ".html"; 

    protected $ModuleName,
              $ControllerName,
              $modulePath,
              $controllerPath,
              $Body,
              $Container;
    
    public $isDisabled = false;
    
    public $isRendered = false;


//------------------------------------------------------------------------------
/* Constructor */


    /**
     * The constructor
     * @param string $Namespace - the namespace of the controller
     * @param string $defaultExtension - the extension to be used if no extension is provided
     */
    
    
    /**
     *
     * @param Voodoo\Core\Controller $Controller
     * @param type $defaultExtension 
     */
    function __construct($Namespace,$defaultContainer="",$defaultExtension=".html"){

        $calledClass = explode("\\",$Namespace);

        $this->ModuleName = $calledClass[2];
        
        $this->ControllerName = $calledClass[4];
        
        $this->modulePath = APPLICATION_MODULES_PATH."/{$this->ModuleName}/Views";

        $this->controllerPath = $this->modulePath."/{$this->ControllerName}";

        $this->setExtension($defaultExtension);        
        
        $this->setContainer($defaultContainer);
        
        parent::__construct($this->controllerPath);

    }

    /**
     * Return the module full path
     * @return string 
     */
    public function getModulePath(){
       return
            $this->modulePath;
    }
    
    /**
     * Return the controller full path
     * @return string 
     */
    public function getControllerPath(){
       return
            $this->ControllerPath;
    }
    
    /**
     * Check if the views directory exists
     * @return bool
     */
    final public function exists(){

        return
            is_dir($this->modulePath);
    }

    
    /**
     * Set the extension to use 
     * @param type $extension
     * @return Voodoo\Core\View 
     */
    final public function setExtension($extension=".html"){
        
        $this->ext = $extension;
        
        return 
            $this;
    }
    
    
    /**
     * Set the view container to use. By default it will user the contain set in config.
     * @param string $filename
     * @param bool $absolute - true, it will use the full path of filename, or it will look in the current Views
     * @return Voodoo\Core\View
     */
    public function setContainer($filename,$absolute=false){
        
        $this->Container = $filename;
        
        $this->isContainerAbsolute = $absolute;
        
        return
            $this;
    }
    
    /**
     * Set the view body  to use. By default it will use the the action view => $action.html
     * @param string $filename
     * @param bool $absolute - true, it will use the full path of filename, or it will look in the current Views
     * @return Voodoo\Core\View
     */    
    public function setBody($filename,$absolute=false){
        
        $this->Body = $filename;
        
        $this->isBodyAbsolute = $absolute;
 
        
        return
            $this;
    }    

    
    /**
     * Render the template
     * @return String 
     */
    public function render(){

        if($this->renderedContent && $this->isRendered)
            return $this->renderedContent;

        
        /**
         * Set the body 
         */
        $renderName = "PageBody";

        $this->addTemplate("PageBody",$this->Body,$this->isBodyAbsolute);
     
        /**
         * $viewContainer can be disabled by $this->setViewContainer(false) 
         */
        if($this->Container !== false && $this->Container){
            
            $renderName = "PageContainer";

            $this->addTemplate("PageContainer",$this->Container,$this->isContainerAbsolute);
        }

        
        $this->isRendered = true;

        $this->renderedContent = parent::render($renderName);
        
        return
            $this->renderedContent;

    }
    

    /**
    * To render partial content
    * @param string $Content - TEXT or filepath
    * @param array $assignments
    * @param type $isFile - if true, $Content must be a path
    * @return string
    */
    public function renderPartial($Content,Array $assignments = array(),$isFile=false){

        if($isFile){
            $tplName = "TEMP_".md5($Content);
            $this->addTemplate($tplName,$Content);
        }
        
        else{
          $tplName = "TEMP_".time();
          $this->addTemplateString($tplName, $Content);
        }
        
        if(count($assignments))
            $this->assign($assignments);
        
        $content = $this->getContent($tplName);
        
        if(count($assignments))
            $this->unassign($assignments);
        
        parent::removeTemplate($tplName)
              ->reparse();
        
        return
            $content;
    }   
    
     /**
     * Return the complete file path of the template in the current view.
     * It can be used to access another views from another module
     * @param type $path
     * @return string 
     */
    public function getPath($path = "index"){
        return
            $this->controllerPath."/".strtolower($path).$this->ext;
    }   
    
/*******************************************************************************/

// Mehod affecting the header and meta data
    /**
     * Set the page title
     * @param type $title
     * @access Page::Title
     * @return ViewController
     */
    final public function setPageTitle($title=""){
        
        $this->assign(array(
            "App"=>array(
                "Page"=>array(
                    "Title"=>$title 
                )                
            )
        ));            
        $this->setMetaTag("TITLE",$title);

        return $this;
    }

    /**
     * Set the page description
     * @param type $desc
     * @access Page::Description
     * @return ViewController
     * 
     */
    final public function setPageDescription($desc=""){

            $this->assign(array(
                "App"=>array(
                    "Page"=>array(
                        "Description"=>$desc
                    )                
                )
            ));
  
        $this->setMetaTag("DESCRIPTION",$desc);

        return $this;
    }


    /**
     * Create meta tags
     * @param string $tag - the tag name
     * @param string $content - content
     * @return \Core\View 
     * 
     * @example
     * {{#Page.MetaTags}}
     *     {{{.}}}
     * {{/Page.MetaTags}}
     */
    public function setMetaTag($tag,$content=""){
        
        switch(strtolower($tag)){

            case "keywords":
                $tagName = "keywords";
                $content = implode(",",array_unique(array_map("trim",explode(",",$content))));
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

        if($tagName)
            $metaTag = "<META NAME=\"$tagName\" CONTENT=\"$content\">";

        if($metaTag){
            $this->assign(array(
                "App"=>array(
                    "Page"=>array(
                        "MetaTags"=>array($metaTag) 
                    )                
                )
            ));            
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
    public function setOpenGraphTag($Prop,$content=""){
        
        if(is_array($Prop)){
            
            foreach($Prop as $property=>$content){

                if(is_array($content)){
                    foreach($content as $cv)
                        $this->setOpenGraphTag($property,$cv);
                }
                
                else
                    $this->setOpenGraphTag($property,$content);
            }  
        }
        
        else if(is_string($Prop) && $content){
            $this->assign(array(
                "App"=>array(
                    "Page"=>array(
                        "OpenGraphTags"=>array("<meta property=\"$property\" content=\"$content\"/>") 
                    )                
                )
            ));
        }
        
    }
    


    /**
     * Set the canonical url
     * @param type $path - if it's a string, it will just add it to the url, if a number it will look thru the path and build it up
     * @return ViewController 
     */
    public function setCanonUrl($path = null){

        if(is_string($path))
            $url = $path;
        
        else if(is_numeric($path)){
            
            $url = $this->getPageName()."/";
            
            // Include the action automatically
            $p = implode("/",array_slice($this->URIArgs,0,1+$path));
            
            $url .= "{$p}/";
        }

        $url = preg_replace("/(\/{2,})/","",$url);
        
        $url = SOUP_APP_ROOT_URL."/{$url}";

        $this->setMetaTag("canonical",$url);
        
        
        return $this;
    }
          
//------------------------------------------------------------------------------
// ERROR HANDLING
//------------------------------------------------------------------------------

    /**
     * To set error message that can be displayed by System.Errors.Message
     * @param string | bool  $err - if false, it will delete the error message
     * @return Voodoo\Core\View 
     * 
     * @example
     * 
     * {{#App.Errors}}
     *     {{#Messages}}
     *          {{.}}
     *     {{/Messages}}
     * {{/App.Errors}}
     */
    public function setError($err){
        if($err === false)
            unset($this->assigned["App"]["Errors"]);
        else
            $this->assign(array(
                "App"=>array(
                    "Errors"=>array(
                        "Messages"=>array($err)
                    )                
                )
            ));

        return $this;
    }

    
    /**
     * To set success message that can be display by System.Success.Message
     * @param string | bool  $succ - if false, it will delete the success message
     * @return Voodoo\Core\View 
     * @example
     * 
     * {{#App.Success}}
     *     {{#Messages}}
     *          {{.}}
     *     {{/Messages}}
     * {{/App.Success}}
     * 
     */
    public function setSuccess($succ){
        if($succ === false)
            unset($this->assigned["App"]["Success"]);
        else
            $this->assign(array(
                "App"=>array(
                    "Success"=>array(
                        "Messages"=>array($succ)
                    )                
                )
            ));
        return $this;
    }

    /**
     * Check if there is an error
     * @return bool 
     */
    public function hasErrors(){
        return (count($this->assigned["App"]["Errors"])) ? true : false;
    }

    
/*******************************************************************************/
    
    /**
     * To add a template file
     * @param type $name - the name of the template. Can be used to call it: {{%include @name}}
     * @param type $src - the filepath relative to the working dir
     * @param bool $absolute - If true, $src will be the absolute 
     * @return Voodoo\Core\View 
     */
    public function addTemplate ($name,$src,$absolute = false){

            /**
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
            if(preg_match("/^\//",$src)){

                // Current Module
                if(preg_match("/^\/([a-z0-9]+)/i",$src))
                    $src = $this->ModuleName.$src;

                // Outter module
                else if(preg_match("/^\/\/([a-z0-9]+)/i",$src))
                    $src = preg_replace("/^\/\//","",$src);


                    $segments = explode("/",$src,3);

                    $Module = Helpers::camelize($segments[0],true);

                    // Dont't convert absolute dir. Dir starts with _
                    $Controller = preg_match("/^_[\w]+$/i",$segments[1]) ? $segments[1] : Helpers::camelize($segments[1],true);

                    $viewAction = $segments[2];

                    $src = ($absolutePath || preg_match("/^({$this->ControllerName}|_[\w]+)/",$src)) 
                                    ? $src 
                                    : "{$this->ControllerName}/{$src}";

                if($Controller){
                    $src = $this->addFileExtension(APPLICATION_MODULES_PATH."/{$Module}/Views/{$Controller}/$viewAction");
                    $absolutePath = true;
                }
            }

            /**
            * Properly format the filename
            * If absolute path or is in _includes or the controller dir, leave as is
            * Second cond: to add the extension if it's missing 
            */
            $src = ($absolutePath || preg_match("/^({$this->ControllerName}|_[\w]+)/",$src)) 
                            ? $src 
                            : "{$this->ControllerName}/{$src}";

            $src =  $this->addFileExtension($src);

            $src =($absolutePath) ? $src : ($this->modulePath."/{$src}");

            parent::addTemplate($name,$src,true); 

            return
                $this;

    }  

    
    /**
     * Add file extension if ommitted
     * @param string $file
     * @return string 
     */
    private function addFileExtension($file){
        if(!pathinfo($file,PATHINFO_EXTENSION))
            return
                $file.$this->ext;
        return
            $file;
    }

    public function __string(){
        return $this->render();
    }
}
