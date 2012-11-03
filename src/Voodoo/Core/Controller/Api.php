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
 * @name        Controller\Api
 * @desc        Voodoo is already restful. Controller\Api adds the API layer
 *              to serve via an api
 *
 *
 */

namespace Voodoo\Core\Controller;

use Voodoo\Core;

abstract class Api extends Core\Controller
{
    private $restView = null;
    private $renderFormat;

    protected function renderView()
    {
        echo $this->view()->render($this->renderFormat);
    }
    
    /**
     * Set the view response format
     *
     * @return \Voodoo\Core\Controller\Api
     */
    protected function setJsonResponse()
    {
        $this->renderFormat = Core\View\Api::FORMAT_JSON;
        Core\Http\Response::setHeader('Content-type: application/json');
        return $this;
    }


    /**
     * Setup the API view
     * 
     * @return Core\View\Api
     */
    protected function view()
    {
        if ($this->restView == null) {
            $this->restView = new Core\View\Api;
        }
        return $this->restView;
    }

    /**
     * Accept POST
     *
     * @return bool
     */
    protected function acceptPost()
    {
        return $this->acceptMethod("POST");
    }

    /**
     * Accept POST
     *
     * @return bool
     */
    protected function acceptGet()
    {
        return $this->acceptMethod("GET");
    }

    /**
     * Accept POST
     *
     * @return bool
     */
    protected function acceptDelete()
    {
        return $this->acceptMethod("DELETE");
    }

    /**
     * Accept POST
     *
     * @return bool
     */
    protected function acceptPut()
    {
        return $this->acceptMethod("PUT");
    }

    /**
     * Accept a method
     *
     * @return bool
     */
    private function acceptMethod($method)
    {
        if (!Core\Http\Request::is($method)) {
            $this->setHttpCode(405);
            return false;
        }
        return true;
    }

    /**
     * action_404() will be invoked if an action is missing
     */
    public function action_404()
    {
        $this->setHttpCode(404);
        $this->view()->assign("error", array(
            "code" => 404,
            "message" => Core\Http\Response::getHttpCode(404)
        ));
    }    
}
