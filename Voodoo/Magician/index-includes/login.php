<?php
/**
 * The login page. If a session doesn't exist, it show this page 
 */
use Voodoo\Core;

session_start();

$sessionId = session_id();

/**
    * Login 
    */
if($_POST && $_POST["Password"]){

    if(md5($_POST["Password"]) === Core\Config::get("VoodooMagician.Password")){

        session_regenerate_id();

        setcookie("VOODOO_MAGICIAN_COOKIE",session_id(),0,"/");

        Core\Helpers::redirect("./");

    }
}
    
    
/**
 * Login failed 
 */
if($_COOKIE["VOODOO_MAGICIAN_COOKIE"] != $sessionId){
    
?>
<link href="./assets/css/login.css" rel="stylesheet">
<div id="login-container">

	<div id="login-content" class="clearfix">

            <form method="POST">

                <fieldset>
                    <div class="control-group">
                        <label class="control-label">Password</label>
                        <div class="controls">
                                <input type="password" class="" name="Password">
                        </div>
                    </div>
                </fieldset>

                <div class="pull-right">
                    <button type="submit" class="btn btn-warning btn-large">Login</button>
                </div>
            </form>
			
	</div> <!-- /login-content -->
		
</div> <!-- /login-wrapper -->
<?php

exit;

}

?>