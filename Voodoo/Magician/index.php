<?php

$BASE_PATH = "../..";

use Voodoo\Core;

/**
 * Initialize Soup 
 */
require ("{$BASE_PATH}/Voodoo/init.php");

/**
 * Config doesn't exist, redirect to setup
 */
if(!is_file(APPLICATION_CONFIG_PATH."/Config.ini")){
    @header("location: ./setup.php");
    exit;
}

/**
 * This class if for the admin section 
 */
class Stove{
    
 public $errors = array();   
 

    
    public function rootUrl(){
        return
            str_replace(dirname($_SERVER["SCRIPT_NAME"]),"",VOODOO_APP_ROOT_URL);
    }
    
    public function setError($error){
        
        $this->errors[] = $error;
        
        return
            $this;
    }
    
    public function hasErrors(){
        return
            count($this->errors) ? true : false;
    }
    
    public function showErrors(){
        
        print("<div class='alert alert-error'><h3 class='alert-heading'>
                <i class=\"icon-warning-sign\"></i> Error Found!</h3>
              <ul>");
        
            foreach($this->errors as $error)
                print("<li>$error</li>");
            
        print("</ul></div>");
    }
    
    
    public function readPath($location){
        return
            str_replace(BASE_PATH,"",$location);
    }    

}

$Stove = new Stove;
$Potion = new Voodoo\Magician\Lib\Potion;


/*******************************************************************************/
/**
 * Voodoo/Magician is not enabled for the web 
 */
if(!Core\Config::get("VoodooMagician.EnableWeb")){
 print("<h1>403 : Restricted Access</h1>");   
 exit;   
}

/*******************************************************************************/

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php print(VOODOO_GENERATOR); ?> The Magician</title>
        
        <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="./assets/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
        <link href="./assets/css/style.css" rel="stylesheet">
        <style type="text/css">
            body {
                margin-top:50px;
                padding-bottom:0px;
                margin-bottom:0px;
            }
            .Soup-Help{
                display:none;
            }
            .small-info{
                font-size:12px;
                color:#555;
                font-weight:normal;
            }
            .alert.alert-block.content{
                color:#444 !important;
            }
        </style>    
        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->    

        <script src="./assets/jquery/jquery-1.8.min.js"></script>
        <script src="./assets/underscorejs/underscore.js"></script>
        <script src="./assets/bootstrap/js/bootstrap.min.js"></script>    
        <script src="./assets/js/application.js"></script>        
    </head>
    <body>       
        
      
     <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="brand" href="#"><?php print(VOODOO_NAME); ?> &middot The Magician</a>
          <div class="nav-collapse">
            <ul class="nav">
              <li><a href="https://github.com/VoodooPHP/Voodoo/wiki" target="_blank">Developer's Wiki on Github</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>       
        
        
 <div class="container-fluid">
  <div class="row-fluid">

      
          <?php
            /**
             * Login 
             */
            include(__DIR__."/index-includes/login.php");
          ?>      
            
      
      
    <!--Sidebar content-->  
    <div class="span3">
      <div class="well" style="padding: 8px 0;">
        <ul class="nav nav-list">

          
          <li class="nav-header">Modules & Controllers</li>
          <li><a href="./?view=module&new=1"><i class="icon-cog"></i> New Module</a>
              <ul class="nav nav-list">
                    <?php
                            // DISPLAY ALL MODULES
                            $DirIt = new \DirectoryIterator(APPLICATION_MODULES_PATH);
                            foreach($DirIt as $Dir){

                                if(!$Dir->isDot() && $Dir->isDir()){
                                    $moduleName = $Dir->getBasename();
                                    $activeModule = ($_REQUEST["view"] == "module" && $_GET["module"] == $moduleName) ? " active " : "";
                                    print("<li class='{$activeModule}'><a href=\"./?view=module&module=$moduleName\"><i class=\"icon-folder-open\"></i> {$moduleName}</a></li>");

                                }
                            }
                    ?>
              </ul>
          </li>

           <li class="divider"></li>
          
           
           
          <li class="nav-header">Models</li>
          <li class="<?php print(($_REQUEST["view"]=="db-alias") ? "active" : ""); ?>"><a href="./?view=db-alias"><i class="icon-user"></i> DB Alias</a>
              <ul class="nav nav-list">
                    <?php
                        // Models list
                        foreach($Potion->getModelAliasesName() as $alias){
                            $active = ($_REQUEST["view"] == "models" && $_GET["alias"] == $alias) ? " active " : "";        
                            print("<li class='{$active}'><a href=\"./?view=models&alias={$alias}\"><i class=\"icon-book\"></i> {$alias}</a></li>");
                        }
                    ?>                  
              </ul>
          </li>
          
        <li class="<?php print(($_REQUEST["view"]=="routes") ? "active" : ""); ?>"><a href="./?view=routes"><i class="icon-road"></i> Routes</a></li>
                    
        </ul>
      </div>      
      
      <div class="well" style="padding: 8px">
          <strong><i class=" icon-info-sign"></i>Info</strong>
          <p></p>
          
          <div id="Soup-Help"></div>
      </div>
        
        
    </div>
      
      
     <!--Body content--> 
    <div class="span9">
   
        
        <?php
            /**
             * Site content 
             */
            $view = $_REQUEST["view"];
            $view_file = __DIR__."/index-includes/{$view}.php";
            
            if(file_exists($view_file))
                include($view_file);
            else
                include(__DIR__."/index-includes/home.php");
        ?>
        
        
    </div>
     

  </div>
          <div class="well" style="text-align: center">
              
         <strong><?php print(VOODOO_NAME); ?></strong> <br>
          
   
          Version: <?php print(VOODOO_VERSION); ?> - License: MIT
          <br>
           <a href="https://github.com/VoodooPHP/Voodoo" target="_blank">GitHub Repo</a> 
           - <a href="https://github.com/VoodooPHP/Voodoo/wiki" target="_blank">Wiki</a>
          <br><br>
          
         Copyright &copy; 2012 Mardix 
         <br> 
         Twitter: <a href="http://twitter.com/mardix/" target="_blank">@Mardix</a> - 
         Github:<a href="https://github.com/mardix/" target="_blank">Mardix</a>
     </div>
</div>
        
        
    </body>
</html>
