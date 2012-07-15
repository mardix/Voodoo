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
 * @name        Interfaces\View
 * @desc        To keep consistency in Voodoo controller, the view class must implement this interface
 * 
 */
namespace Voodoo\Core\Interfaces;

interface View{
    
    public function setPageTitle($title="");
    
    public function setError($err);
    
    public function hasErrors();
    
    public function setSuccess($succ);
    
    public function render();
    
    public function setContainer($filename,$absolute="");
    
    public function assign($key,$val);
    
    
    
}