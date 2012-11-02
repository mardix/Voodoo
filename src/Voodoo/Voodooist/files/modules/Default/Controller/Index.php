<?php
/** {{GENERATOR}} (autogen date: {{DATE}})
 ******************************************************************************
 * @desc
 * @package     {{NAMESPACE}}
 * @name        Index
 * @copyright   (c) {{YEAR}}
 ******************************************************************************/


namespace {{NAMESPACE}};

use Voodoo;

class Index extends Voodoo\Core\Controller
{

    /**
     * init() allows you to execute code when the controller is loaded
     */
    protected function init()
    {
        parent::init();
    }

    /**
     * finalize() allows you to execute code before the application exits or renders
     */
    protected function finalize()
    {
        parent::finalize();
    }
//------------------------------------------------------------------------------


    /**
     * Action: Index
     * This is the index action.
     * It is loaded by default or when  action is missing
     */
    public function action_index()
    {
        // Set the page title
        $this->view()->setPageTitle("Welcome");

    }



    /**
     * This is the login action.
     * It allows user to login to your application
     * Add your application's logic in there to make it work
     */
    public function action_login()
    {
        /**
         * Use the blank container
         */
        $this->view()->setContainer("_includes/container-simple");

        // Set the page title
        $this->view()->setPageTitle("Login");

        /**
         * POST
         * Validate and authenticate the login
         */
        if ($this->isPost()) {

            // Email, you can change to Username
            $Email = $_POST["Email"];

            // Password
            $Password = $_POST["Password"];

            // A flag to remember login
            $isRememberMe = $_POST["RememberMe"] ? true : false;

            /**
             * Verify Email and Password
             */
            if(!Core\Helpers::validEmail($Email))
                $this->view()->setError("Invalid Email Address");

            if(!Core\Helpers::validPassword($Password))
                $this->view()->setError("Invalid Password");

            /**
             * No error found
             */
            if (!$this->view()->hasErrors()) {

                // Enter you login logic here

                // If your login is OK, redirect to /account
                if($LoginOK)
                    $this->redirect("/account");

                else
                    $this->view()->setError("Invalid Login");

            }

        }

    }

    /**
     * This is the lostpassword action.
     * It allows user to retrieve their password
     */
    public function action_lostpassword()
    {
        /**
         * Use the blank container
         */
        $this->view()->setContainer("_includes/container-simple");

        // Set the page title
        $this->view()->setPageTitle("Lost Password");

        /**
         * POST
         * Validate and authenticate the login
         */
        if ($this->isPost()) {

            // Email, you can change to Username
            $Email = $_POST["Email"];

            /**
             * Verify Email and Password
             */
            if(!Core\Helpers::validEmail($Email))
                $this->view()->setError("Invalid Email Address");

            /**
             * No error found
             */
            if (!$this->view()->hasErrors()) {

                // Enter you password retrieval logic

                // If password has been retrieve, return to login page
                if($PWFound)
                    $this->redirect("login");

                else
                    $this->view()->setError("Invalid Login");

            }

        }

    }
}
