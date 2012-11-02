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
 * @name        View\Api
 * @desc        The view layer for API
 *
 */

namespace Voodoo\Core\View;

use Voodoo\Core;

class Api {
    const FORMAT_JSON = 1;
    const FORMAR_TEXT = 2;

    protected $assigned = array();

      /**
     * Assign variable
     * @param  mixed          $key
     * @param  mixed          $val - can be string, numeric, closure, array
     * @return Rest
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
     * @return Rest
     */
    public function unassign($key)
    {
        if(is_string($key) && isset($this->assigned[$key])){
            unset($this->assigned[$key]);
        }

        return $this;
    }

    /**
     * To render the assigned vars
     *
     * @param type $format
     * @return mixed
     */
    public function render($format = self::FORMAT_JSON)
    {
        switch($format) {
            // JSON
            default:
            case self::FORMAT_JSON:
              return json_encode($this->assigned);
            break;
        }

    }
}

