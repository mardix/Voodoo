<?php
/** {{GENERATOR}} (autogen date: {{DATE}})
 ******************************************************************************
 * @desc        The default controller. It shows a coming soon page
 * @package     {{NAMESPACE}}
 * @name        Index
 * @copyright   (c) {{YEAR}}
 ******************************************************************************/

namespace {{NAMESPACE}};

use Voodoo;

class Index extends Voodoo\Core\Controller
{

    /**
     * Action: Index
     * This is the index action.
     * It is loaded by default or when  action is missing
     */
    public function action_index()
    {
        // Set the page title
        $this->view()->setPageTitle("Site Coming Soon ");
    }


    /**
     * @action newslettersignup
     */
    public function action_newslettersignup()
    {
        if ($this->isPost()) {
            $email = $this->getParam("email");
            
            // Add newsletter signup below
            if ($email) {
                
            }
        }
        $this->view()->setFlash("Thank you for subscribing!");
        $this->redirect($this->getModuleUrl());
    }
}
