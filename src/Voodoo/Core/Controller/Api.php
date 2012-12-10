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

use Voodoo\Core,
    Closure;

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
     * Finalize
     *
     * Do some assignment and finalize the process
     */
    protected function finalize()
    {
        parent::finalize();

        if($this->view()->hasError()) {
            $this->view()->assign("error", $this->view()->getMessage("error"));
        }
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
     * Check if a request method is the one provided.
     * If failed it will set 405 error
     *
     * @param string $method - (GET | POST | PUT | DELETE)
     * @param string $errorMessage - The error message if failed
     * @return bool
     */
    private function requestMethod($method, $errorMessage="")
    {
        if (!Core\Http\Request::is($method)) {
            $this->setHttpCode(405);

            if($errorMessage) {
                $this->view()->setError($errorMessage);
            }
            return false;
        }
        return true;
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


    /**
     * To execute an action. 
     * But unlike the Core\Controller::getAction()
     * Api::getAction() checks for the '@request' annotation in the action.
     * The @request annotation is array containing the keys: method, response
     * - Annotation
     *      @request Array
     * - arguments
     *      method (POST|GET|PUT|DELETE) - The request method to accept
     *      response - a message to display if the request method fails
     * - example: 
     *      @request [method=POST, response="This is an error message"]
     * 
     * @param string $action
     * @return Core\Controller
     */
    public function getAction($action = "index")
    {
        // Set the action to get the annotation
        $this->setActionName($action);

        $request = $this->getActionAnnotation("request");

        if(is_array($request) && $request["method"]) {
            $response = $request["response"] ?: "";
            if (!$this->requestMethod($request["method"], $response)) {
                return null;
            } else {
                return parent::getAction($action);
            }
        } else {
            return parent::getAction($action);
        }
    }
}
