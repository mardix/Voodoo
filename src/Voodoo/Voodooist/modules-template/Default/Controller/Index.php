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
        $this->view()->setTitle("Site Coming Soon ");
    }


    /**
     * @action newslettersignup
     */
    public function actionNewsletterSignup()
    {
        if ($this->isPost()) {
            $email = $this->getParam("email");
            if (Voodoo\Core\Helpers::validEmail($email)) {

                // ADD YOUR CODE HERE
                
                $this->view()->setFlashMessage("Thank you for subscribing!", "success"); 
            } else {
                $this->view()->setFlashMessage("Invalid email address", "error");
            }
        }
        $this->redirect();
    }
}
