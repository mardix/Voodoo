<?php
/**
 * @name    install
 * @author  Mardix
 * @since   June 7 2012
 * @desc    To install Soup 
 */

setcookie("VOODOO_CHEF_COOKIE","",0,"/");

use Voodoo\Core;

$BASE_PATH = "../..";

include("{$BASE_PATH}/Voodoo/init.php");

$Potion = new Voodoo\Magician\Lib\Potion;;

if(is_file(APPLICATION_CONFIG_PATH."/Config.ini"))
   $setupCompleted = true;


elseif($_POST && is_array($_POST["Setup"])){
    
    $info = array_map(function($v){
        return stripslashes(trim($v));
    },$_POST["Setup"]);
    
    $info["VoodooMagician.Password"] = ($info["VoodooMagician.Password"]) ? md5($info["VoodooMagician.Password"]) : "";
    
    $Potion->newApplication(BASE_PATH,$info);

    $Potion->createModule("Main",$_POST["Template"]?:"Default");
 
    $setupCompleted = true;
}
/*******************************************************************************/

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php print(VOODOO_GENERATOR); ?> Setup Wizard!</title>
        
        <link href="./assets/bootstrap/css/bootstrap.css" rel="stylesheet">
        <link href="./assets/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
        <link href="./assets/css/style.css" rel="stylesheet">
        <style type="text/css">
            body {
               
                padding-bottom:0px;
                margin-bottom:0px;
            }
        </style>    
        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->    

        <script src="./assets/jquery/jquery-1.7.js"></script>
        <script src="./assets/underscorejs/underscore.js"></script>
        <script src="./assets/bootstrap/js/bootstrap.js"></script>    
        <script src="./assets/js/application.js"></script> 
        
        <style>
            .well .info{
                color:#666;
                font-size:11px;
            }
            .container .header{
                font-size:42px;
                line-height:50px;
                color:#fff;
                text-align:center;
                height:50px;
                padding:4px;
                font-weight:bold;
            }
            .navbar{
                margin-bottom:10px;
            }
        </style>
    </head>
    <body>  
        
      <div class="navbar">
      <div class="navbar-inner">
        <div class="container" >
            <div class="header">
                 <?php print(VOODOO_GENERATOR); ?>
            </div>
        </div>
      </div>
    </div>   
        

<?php 

if($setupCompleted){
    ?>
          <div class="alert alert-success" style="text-align:center">
            <strong><?php print(VOODOO_GENERATOR); ?></strong>  has been setup properly<br> 
            <a href="./" class="btn">Login to Magician</a>
        </div>    
<?php
}


else{  
    ?>
    <div class="container offset7" >
        <div class="row" style="margin-top:20px !important">
            <div class="span4">
         
        <div style="text-align:center">
            <h2>Setup Wizard</small></h2>
        </div>
        
                
        <div class="alert alert-info" style="text-align:center">
            Welcome to <strong><?php print(VOODOO_GENERATOR); ?> Setup Wizard</strong> <br> 
            We are going to setup <strong>Voodoo</strong> on your platform. <br>
            It's straight forward settings, once done we'll create the Voodoo environment
            which will include the following directories: AddOn, Application, SharedAssets, and files: .htaccess, 
            index.php, robots.txt
        </div>                
        <form method="POST">                
                
        <div class="well">
            <h3>Magician's Admin Password</h3>
            <div class="info">Magician is a web interface for developers to quickly create controllers, models, routes, etc for their application. You need a password to access it  </div>
            
            <label>
                <strong>Password</strong>
                <input type="text" name="Setup[VoodooMagician.Password]">
            </label>
        </div>
 

        <hr>

        <div class="well">
            <h3>Your Project Info </h3>

            <label>
                <strong>Project's Name</strong>
                <div class="info">The name of your project. It will be included in all the files created by the Magician</div>
                <input type="text" name="Setup[Project.Name]">
            </label>
            <label>
                <strong>Admin Email</strong> 
                <div class="info">This email will be used to notify admin of any issue on the server</div>
                <input type="text" name="Setup[System.AdminEmail]">
            </label>

            <label>
                <strong>Server Timezone</strong>
                <div class="info">The Timezone for your server</div>
                
                <select name="Setup[System.Timezone]">
                    <?php 
                        foreach(Voodoo\Core\Helpers::TimeZoneList() as $region=>$tzlist){
                            
                          print("<optgroup label=\"{$region}\">");
                            foreach($tzlist as $tz){
                                $selected = ($tz == "America/New_York") ? " SELECTED " : "";
                                
                                print("<option {$selected} value=\"{$tz}\">".str_replace($region."/","",$tz)."</option>");
                            }
                          print("</optgroup>");
                          
                        }
                    ?>
                </select>
                
            </label>          
        
        </div>
     
     
    <div class="control-group well">
        
        <h3>Default Module Template</h3>
        <div class="info">
            Once Soup is setup, a default Module named Main will be created. This module is the one that is accessed from the root of your site.
            
        </div>
        <div class="controls">
            
 <?php
 
             // DISPLAY ALL MODULES
            foreach($Potion->listModuleTemplates() as $mTpl){

                    
                 ?>
                    <label class="radio">
                    <input type="radio" id="optionsCheckbox2" name="Template" value="<?php print($mTpl["Path"]); ?>"><i class="icon-folder-open"></i> <strong><?php print($mTpl["Name"]); ?></strong>
                    <em><?php print($mTpl["Description"]); ?></em>
                    </label>            
            
                 <?php
          
            }
 ?>        
        </div>        
        
     </div>
        
           
        <div class="pull-right">
            <input type="submit" class="btn btn-success btn-large" value="Install Voodoo"> 
        </div>           
        
        </div>

        </form>
            
    </div>         

    </div>

<?php 
}
?>
    
</html>