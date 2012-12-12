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

    /**
     * Init
     * We'll set the api for  JSON response
     */
    protected function init()
    {
        parent::init();
        $this->setJsonResponse();
    }

    /**
     *
     * @param string $echoView
     *
     * @return string
     */
    protected function renderView($echoView = true)
    {
        $view = $this->view()->render($this->renderFormat);

        if($echoView) {
            echo $view;
        } else {
            return $view;
        }
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
     * action_404() will be invoked if an action is missing
     */
    public function action_404()
    {
        $errorCode = 404;
        $message = Core\Http\Response::getHttpCode($errorCode);

        $this->setHttpCode($errorCode);
        $this->view()->setError($message, $errorCode);
    }
}
