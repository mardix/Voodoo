<?php
/** {{GENERATOR}} (autogen date: {{DATE}})
 ******************************************************************************
 * @desc        The default controller. It shows a coming soon page
 * @package     {{MODULENAMESPACE}}
 * @name        Index
 * @copyright   (c) {{YEAR}}
 ******************************************************************************/

namespace {{MODULENAMESPACE}}\Controller;

use Voodoo,
    {{MODULENAMESPACE}}\Model,
    {{MODULENAMESPACE}}\Exception;

class Index extends BaseController
{
    /**
     * @action index
     * @desc This is the index action. Loaded by default or when action is missing
     */
    public function actionIndex()
    {
        // Set the page title
        $this->view()->setPageTitle("Site Coming Soon ");
    }


    /**
     * @action newslettersignup
     */
    public function actionNewsletterSignup()
    {
        if ($this->isPost()) {
            $email = $this->getParam("email");

            // Add newsletter signup below
            if ($email) {

            }
        }
        $this->view()->setFlash("Thank you for subscribing!", "success");
        $this->redirect();
    }
}
