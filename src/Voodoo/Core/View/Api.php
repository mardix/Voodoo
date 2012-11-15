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
    use TView;

    const FORMAT_JSON = 1;
    const FORMAT_TEXT = 2;


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
            case self::FORMAT_JSON:
              return json_encode($this->getAssigned());
            break;
        }

    }
    

}

