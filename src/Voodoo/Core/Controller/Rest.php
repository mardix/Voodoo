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
 * @name        Controller\Rest
 * @desc        Voodoo is already restful. Controller\Api adds the API layer
 *              to serve via an api
 *
 *
 */

namespace Voodoo\Core\Controller;

use Voodoo\Core;

abstract class Rest extends Core\Controller
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
     * @return \Voodoo\Core\Controller\Rest
     */
    protected function setJsonResponse()
    {
        $this->renderFormat = Core\View\Rest::FORMAT_JSON;
        Core\Http\Response::setHeader('Content-type: application/json');
        return $this;
    }


    /**
     * Setup the API view
     *
     * @return Core\View\Rest
     */
    protected function view()
    {
        if ($this->restView == null) {
            $this->restView = new Core\View\Rest;
        }
        return $this->restView;
    }

}
