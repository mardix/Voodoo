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
 * @name        Core\View\TView
 * @desc        Trait for Views
 *
 */

namespace Voodoo\Core\View;

use Voodoo\Core;

trait TView {

    protected $assigned = array();
    protected $messages = array();


    /**
     * Assign variable
     * @param  mixed          $key - can be string, dot notation k/v, or array to set data as bulk
     * @param  mixed          $val - can be string, numeric, array
     * @return Api
     */
    public function assign($key, $val="")
    {
        if (is_string($key) || is_array($key)) {
            $data = array();

            if (is_string($key)) {
                if (preg_match("/\./",$key)) { // dot notation keys
                    Core\Helpers::setArrayToDotNotation($data, $key, $val);
                } else {
                  $data[$key] = $val;
                }
            } else {
                $data = $key;
            }

            $this->assigned = array_merge_recursive($data,$this->assigned);

            return $this;

        } else {
            throw new Core\Exception("Can't assign() $key. Invalid key type. Must be string or array");
        }
    }

    /**
     * To unassign variable by key name
     * @param  string         $key, the key name associated when it was created
     * @return Api
     */
    public function unassign($key)
    {
        if(is_string($key) && isset($this->assigned[$key])){
            unset($this->assigned[$key]);
        }
        return $this;
    }

    /**
     * Get all the assigned vars
     *
     * @return Array
     */
    public function getAssigned()
    {
        return $this->assigned;
    }

    /**
     * Set an error message
     *
     * @param string $message
     * @return TView
     */
    public function setError($message)
    {
       return $this->setMessage($message, "error");
    }

    /**
     * Check if error exist
     * @return bool
     */
    public function hasError()
    {
        return $this->getMessage("error") ? true : false;
    }

    /**
     * To set a message
     * 
     * @param string $message - the message
     * @param string $type - The type of message: error, success
     * @return TView
     */
    public function setMessage($message, $type)
    {
        $this->messages[$type] = $message;
        return $this;
    }

    /**
     * To return the messages saved
     *
     * @param string $type - message type: error, success
     * @return Array | null
     */
    public function getMessage($type)
    {
        
        return isset($this->getMessages()[$type]) ? $this->getMessages()[$type] : null;
    }

    /**
     * Return all the messages
     * 
     * @return Array
     */
    public function getMessages()
    {
        return $this->messages;
    }


}

