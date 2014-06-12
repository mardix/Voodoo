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
 * Flash are temporary data retained in a session from one request to another
 * Data should be deleted in the next request
 * 
 * @name        Core\View\FlashMessage
 * @desc        Flash Message
 *
 */

namespace Voodoo\Core\View;

use Voodoo\Core;

class FlashMessage 
{
    const TYPE_ERROR    = "error";
    const TYPE_SUCCESS  = "success";
    const TYPE_WARNING  = "warning";
    const TYPE_NOTICE   = "info";
    const TYPE_HELP     = "help";
    
    private $storage    = null;

    /**
     * 
     * @param \Voodoo\Core\View\IFlashStorage $storage
     */
    public function __construct(IFlashStorage $storage) 
    {
        $this->storage = $storage;
    }
    
    /**
     * Set the flash
     * 
     * @param string $message
     * @param string $type
     * @param array $data
     * 
     * @return FlashMessage
     */
    public function set($message, $type = self::TYPE_NOTICE, Array $data = [])
    {
        $this->storage->set([
            "message" => $message,
            "type" => $type,
            "data" => $data
        ]);
        return $this;
    }

    /**
     * Clear flash
     */
    public function clear()
    {
        $this->storage->clear();
    }
    
    /**
     * Get the flash
     * 
     * @return type
     */
    public function get($type = null)
    {
        $flash = $this->storage->get();
        if ($flash) {
            if (! $type) {
                return $flash;
            } else {
                $newFlash = [];
                foreach ($flash as $stack) {
                    if ($stack["type"] == $type) {
                        $newFlash[] = $stack;
                    }
                }
                return $newFlash;
            }
        } else {
            return null;
        }
    }
}

