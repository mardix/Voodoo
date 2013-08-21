<?php

/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2013 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * @name        Controller\TAnnotation
 * @desc        Reads the action annotations
 *
 *
 */

namespace Voodoo\Core\Controller;

use Voodoo\Core;

trait TAnnotation
{
    private $annotations = null;
    private $classAnnotations = null;
    
    /**
     * Check if Annotation exists
     * 
     * @param string $key
     * @return bool
     */
    public function actionHasAnnotation($key)
    {
        return $this->actionAnnotationReader()->has($key);
    }

    /**
     * Get an Annotation key for the action
     * 
     * @param string $key
     * @return mixed
     */
    public function getActionAnnotation($key)
    {
        return $this->actionAnnotationReader()->$key;     
    }
    
    /**
     * Check if Annotation exists in the controller base class
     * 
     * @param string $key
     * @return bool
     */
    public function controllerHasAnnotation($key)
    {
        return $this->controllerAnnotationReader()->has($key);
    }

    /**
     * Get an Annotation key for controller
     * 
     * @param string $key
     * @return mixed
     */
    public function getControllerAnnotation($key)
    {
        return $this->controllerAnnotationReader()->$key;     
    }    
    /**
     * Allow us to load it once per action
     * 
     * @return Core\AnnotationReader
     */
    private function actionAnnotationReader()
    {
        $actionName = $this->getActionMethodName();
        if (!$this->annotations[$actionName]) {
            $docComment = $this->reflection->getMethod($actionName)->getDocComment();
            $this->annotations[$actionName] = new Core\AnnotationReader($docComment);
        }  
        return $this->annotations[$actionName];
    }
    
    
    /**
     * Allow us to load it once per controller
     * 
     * @return Core\AnnotationReader
     */
    private function controllerAnnotationReader()
    {
        if (!$this->classAnnotations) {
            $docComment = $this->reflection->getDocComment();
            $this->classAnnotations = new Core\AnnotationReader($docComment);
        }  
        return $this->classAnnotations;
    }    
}



