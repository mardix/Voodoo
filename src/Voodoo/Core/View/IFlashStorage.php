<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2014 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * Iterface for the flash storage
 * 
 * @name        Core\View\IFlashStorage
 *
 */
namespace Voodoo\Core\View;

namespace Voodoo\Core\View;

interface IFlashStorage
{
    /**
     * Set the data
     * 
     * @param array $data
     */
    public function set(Array $data);
    
    /**
     * Get the data
     * 
     * @return Array
     */
    public function get();
    
    /**
     * Clear the data
     */
    public function clear();
}
