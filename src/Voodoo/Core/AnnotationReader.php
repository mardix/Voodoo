<?php
/**
 * -----------------------------------------------------------------------------
 * AnnotationReader
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/VoodOrm
 * @package     VoodooPHP (https://github.com/VoodooPHP/Voodoo/)
 *
 * @copyright   (c) 2012 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * About AnnotationReader
 *
 * AnnotationReader is a  simple class that read annotation of class, method, objects etc.
 * 
 * Learn more: https://github.com/mardix/AnnotationReader
 * 
 * Example:
 * 
 * /**
 *  *@author Mardix
 *  *@param MyParam 
 *  *\/
 * Class MyClass
 * {
 * }
 * 
 * $classRelection = new ReflectionClass("MyClass");
 * $myClassAnno = new AnnotationReader($classRelection->getDocComment());
 * 
 * echo $myClassAnno->get("param"); //-> MyParam
 * 
 */

namespace Voodoo\Core;

class AnnotationReader 
{
    // Regex for the annotaion
    const REGEX_ANNOTATION = '/@(\w+)(?:\s*(?:\s*)?(.*?)(?:\s*\))?)??(?:\n|\*\/)/';
    
    // Regex for K=V pair
    const REGEX_PARAMS = '/(\w+)\s*=\s*(\[[^\]]*\]|"[^"]*"|[^,)\]]*)\]*\s*(?:,*|$)/';
    
    //@type array
    private $annotations = array();

    
    /**
     * The constructor
     * @param string $DocComment - The comment from the doc bloc
     */
    public function __construct($DocComment) {

      if(preg_match_all(self::REGEX_ANNOTATION, $DocComment, $matches)) {
          
        foreach ($matches[1] as $index => $key) {
            $key = strtolower($key);
            $value = $matches[2][$index];
            $val = true;

            if ($value) {
              $hasParams = preg_match_all(self::REGEX_PARAMS, $value, $params, PREG_SET_ORDER);
                if ($hasParams) {
                    $val = array();
                    foreach ($params as $param) {
                        $val[$param[1]] = $this->parseVal($param[2]);
                    }
                } else {
                      $val = trim($value);
                      $val = ($val == "") ? true : $this->parseVal($val);
                }
            }
            if (isset($this->annotations[$key])) {
                if (!is_array($this->annotations[$key])) {
                    $this->annotations[$key] = array($this->annotations[$key]);
                }
                $this->annotations[$key][] = $val;
            } else {
                $this->annotations[$key] = $val;
            }
        }
      }
    }

    /**
     * Parse values and properly format them
     * 
     * @param string $value
     * @return mixed
     */
    private function parseVal($value) {
      $val = trim($value);

        if (substr($val, 0, 1) == '[' && substr($val, -1) == ']') { // Array
            $val = array();
            foreach (explode(',', substr(trim($value), 1, -1)) as $v) {
                $val[] = $this->parseVal($v);
            }
            return $val;
        } else if (substr($val, 0, 1) == '"' && substr($val, -1) == '"') {
            return $this->parseVal(substr($val, 1, -1));
        } else if (strtolower($val) == 'true') { // true
            return true;
        } else if (strtolower($val) == 'false') { // false
            return false;
        } else if (strtolower($val) == 'null') { // null
            return null;
        } else if (is_numeric($val)) {// Numeric value, determine if int or float and then cast
            if ((float) $val == (int) $val) {
                return (int) $val;
            } else {
                return (float) $val;
            }
        } else {
            return $val;
        }
    }

    /**
     * To check if an annotation key exist
     * 
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->__isset($key);
    }

    /**
     * To retrieve an annotation by key
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->__get($key);
    }

    /**
     * Return the annotations as array
     * 
     * @return Array
     */
    public function toArray()
    {
        return $this->annotations;
    }
    
    /**
     * Magic method for $this->get
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        $key = strtolower($key);
        return isset($this->annotations[$key]) ? $this->annotations[$key] : null;
    }

    /**
     * Magic method for isset
     * 
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        $key = strtolower($key);
        return isset($this->annotations[$key]);
    }


}
