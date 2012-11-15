<?php
/**
 * -----------------------------------------------------------------------------
 * ThickMustache
 * -----------------------------------------------------------------------------
 * @author      Mardix http://github.com/mardix
 *
 * @desc        ThickMustache extends Mustache, a logic-less  template engine
 *              (To learn more about Mustache: http://mustache.github.php)
 *
 *              ThickMustache extends Mustache.php. It adds the ability to:
 *                  work with template files
 *                  include other template files
 *                  assign variables
 *                  defined raw blocks, which will not be converted upon render
 * == New Markups
 *
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

namespace Voodoo\Core\View;

class ThickMustache
{
    use TView;
    
    protected $templates =  array();

    protected $templateDir = "";

    protected $parsed = false;

    protected $definedRaws = array();

    /**
     * Constructor
     * @param string $templateDir
     */
    public function __construct($templateDir="")
    {
       $this->setDir($templateDir);
    }

    /**
     * Set the working directory. By default files will be loaded from there
     * @param  string         $dir
     * @return \ThickMustache
     */
    public function setDir($dir)
    {
      $this->templateDir =   preg_match("!/$!",$dir) ? $dir : "{$dir}/";
      return $this;
    }


    /**
     * To add a template file
     * @param  type           $name     - the name of the template. Can be used to call it: {{%include @name}}
     * @param  type           $src      - the filepath relative to the working dir
     * @param  bool           $absolute - If true, $src will be the absolute
     * @return \ThickMustache
     */
    public function addTemplate($name,$src,$absolute = false)
    {
        return $this->addTemplateString($name,$this->loadFile($src,$absolute));
    }

    /**
     * To add a template string
     * @param  type           $name
     * @param  type           $content
     * @return \ThickMustache
     */
    public function addTemplateString($name,$content)
    {
        $this->templates[$name] = $this->parseTemplate($content);
        return $this;
    }

    /**
     * To remove a template name
     * @param  type           $name
     * @return \ThickMustache
     */
    public function removeTemplate($name)
    {
        if (isset($this->templates[$name])) {
            unset($this->templates[$name]);
        }
        return $this;
    }

    /**
     * Render the template
     * @param  string $name - name of the template
     * @param  array  $data - data for this context
     * @return string if success, or false
     */
    public function renderMustache($name, Array $data = array())
    {
        $this->parse();
        if (isset($this->templates[$name])) {
            $data = array_merge($this->assigned,$data);
            $template = (new Mustache($this->templates[$name], $data))->render();
            // replace the raws and return
            return str_replace(array_keys($this->definedRaws),array_values($this->definedRaws),$template);
        }
        return false;
    }

    /**
     * To reset the parsing
     * @return \ThickMustache
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
    protected function loadFile($src,$absolute=false)
    {
         $src = ($absolute == true) ? $src : $this->templateDir.$src;
         return (file_exists($src)) ? file_get_contents($src) : "";
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
    }

}
