<?php
/**
 * Module
 *  
 * This page admin modules, controllers and actions
 */
use Voodoo\Core;


/******** NEW MODULE ***********************************************************/

if($_REQUEST["new"] == 1 && !$_REQUEST["module"]):
    
    
    if($_POST){
        
        $moduleName = stripslashes($_POST["ModuleName"]);
        
        if($Potion->moduleExists($moduleName))
            $Stove->setError("Module name: '{$moduleName}' exists already");
            
       else{
           
           $template = $_POST["Template"];

           $bare = $template == "__BARE__TPL__" ? true : false;
           
           $newModule = $Potion->createModule($moduleName,$template,$bare);
           
           Core\Helpers::redirect("./?view=module&module={$newModule}");
           
       }
       
    }
?>

<div class="Soup-Help">  
    <strong>Modules</strong> are independent MVC application under which they don't affect each other 
    but share the same Soup code base. 
    <br>
    Modules can be created, removed or copied over without affecting the rest of your application.
    
    <br> This gives you the benefit of creating independent sections of your applications,
    such as admin section, corporate portal etc..
</div>  



<form method="POST" class="form-horizontal">
  <input type="hidden" name="new" value="1" >
  <fieldset>
    <h1>Create New Module</h1>

    <p></p>
    <?php
    if($Stove->hasErrors())
        $Stove->showErrors();
    ?>
    
    <div class="control-group well">
        <h3>Module Name</h3>
        <p class="small-info">The name of the module that will be placed at <em>Application/Module/$YourModuleName/</em> and can be accessed via: <br>
            <strong><em><?php print($Stove->rootUrl()); ?>/$YourModuleName/</em></strong></p>

      <div class="controls">
        <input type="text" name="ModuleName" class="input-xlarge" id="input01" placeholder="Module Name">
      </div>
    </div>
    
    <div class="control-group well">
        <h3>Module Templates</h3>
        <p class="small-info">Module Template are pre-made templates, that quickly setup your MVC environment with at least the necessary views. If a template is not selected, the Default template will be selected.</p>
        
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

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create Module</button>
        <a href="./" class="btn" >Cancel</a>
    </div>
    
  </fieldset>
</form>

<?php
    
    
/****** OTHER STUFF ************************************************************/
    

else:
    if($_POST){
        
        switch($_POST["Action"]){
            
            // NEW CONTROLLER  ACTION
            case "new-controller":
                if(!$_POST["ModuleName"])
                    $alert_error = "Module Name is empty";
                else if(!$_POST["ControllerName"])
                    $alert_error = "Controller Name is empty";
                else{
                    
                    $Potion->createController(stripslashes($_POST["ModuleName"]),stripslashes($_POST["ControllerName"]));
                    
                    Core\Helpers::redirect("./?view=module&module={$_POST["ModuleName"]}&controller={$_POST["ControllerName"]}");
                }
                
            break;
            
            
            // NEW CONTROLLER  ACTION
            case "new-controller-action":
                if(!$_POST["ModuleName"])
                    $alert_error = "Module Name is empty";
                else if(!$_POST["ControllerName"])
                    $alert_error = "Controller Name is empty";
                else if(!$_POST["ActionName"])
                    $alert_error = "Action Name is empty";
                
                else{
                    
                    $Potion->createAction(stripslashes($_POST["ModuleName"]),stripslashes($_POST["ControllerName"]),stripslashes($_POST["ActionName"]),stripslashes($_POST["Description"]));
                    
                    Core\Helpers::redirect("./?view=module&module={$_POST["ModuleName"]}&controller={$_POST["ControllerName"]}");
                }
                
            break;            
            
        }
     
        if($alert_error)
            $Stove->setError($alert_error);
    }

    
    
    $moduleName = $Potion->createModuleName($_REQUEST["module"]);
    $controllerName = $_REQUEST["controller"];
    $selectedController = "";
    
    $ControllersDir = APPLICATION_MODULES_PATH."/{$moduleName}/Controller";
    $ControllersList = array();
    
        $DirIt = new \DirectoryIterator($ControllersDir);
        
        foreach($DirIt as $Dir){

            if(!$Dir->isDot() && $Dir->isFile()){
                $name = $Dir->getBasename(".php");
                $ControllersList[] = array("Name"=>$name,"Namespace"=>"Application\\Module\\{$moduleName}\\Controller\\{$name}");
                
                if($controllerName==$name)
                    $selectedController = $name;
            }
        }    
?>

<div class="Soup-Help">  
    Module allows you to create Controllers, Controller's Actions and Views files automatically. Voodoo create an HTML view file
    for each action created. If the action <strong>welcome</strong> is created, a view <strong>welcome.html</strong> 
    will be created be associated to it.
</div> 

<h1>Module: <u><?php print($moduleName); ?></u></h1>

    <?php
        if($Stove->hasErrors())
            $Stove->showErrors();
    ?>

<br>

      <div class="tabbable  tabs-left">
        <ul class="nav nav-tabs span2">

          <li class="<?php print((!$selectedController)? " active " : "");?>"><a href="#CHome" data-toggle="tab"><i class="icon-plus-sign"></i> <strong>Create Controller</a></strong></li>
          <li>&nbsp;</li>
          <?php 
            
            foreach($ControllersList as $Controller):
                ?>
                  <li class="<?php print(($selectedController == $Controller["Name"])? " active " : "");?>"><a href="#Controller-<?php print($Controller["Name"]); ?>" data-toggle="tab"><?php print($Controller["Name"]); ?></a></li>
                <?php
            endforeach;
          ?>
                  
        </ul>
          
          
        <div class="tab-content">
            
          <div class="tab-pane <?php print((!$selectedController)? " active " : "");?>" id="CHome">

                <div class="alert alert-block content">                            
                    <form method="POST">
                    <input type="hidden" name="Action" value="new-controller" >
                    <input type="hidden" name="ModuleName" value="<?php print($moduleName); ?>" >

                    <h4 class="alert-heading">Create New Controller in <em><?php print($moduleName); ?></em></h4>
                           <p></p>
                           
                            <input type="text" class="span3" name="ControllerName" placeholder="Controller Name">
                            <p></p><p></p>
                            <button type="submit" class="btn">Create</button>
                    </form>  
                </div>     
            
            <hr>
            
            <div class="well">
                <p>Namespace: <?php print("\\Application\\Module\\{$moduleName}\\Controller"); ?></p>
                <p>URL: <?php print($Stove->rootUrl()."/{$moduleName}"); ?></p>
            </div>
          </div>

          <?php 
            
            foreach($ControllersList as $Controller):
                ?>
                    <div class="tab-pane <?php print(($selectedController == $Controller["Name"])? " active " : "");?>" id="Controller-<?php print($Controller["Name"]); ?>">

                        <div class="well">
                            <h3>Controller Info: <u><?php print("{$moduleName}/{$Controller["Name"]}");?></u></h3>
                            <p></p>
                            <p>Namespace: <?php print("\\Application\\Module\\{$moduleName}\\Controller\\{$Controller["Name"]}"); ?></p>
                            <p>Location: <?php print(str_replace(BASE_PATH,"",APPLICATION_MODULES_PATH)."/{$moduleName}/Controller/{$Controller["Name"]}.php"); ?></p>
                        </div>
                        
                        <p></p>
                        <i class="icon-plus-sign"></i> <a href="javascript:" rel="Module-Edit-Action-<?php print($Controller["Name"]); ?>" class="module-add-action-toggle">Create New Action</a>
                        
                        <div class="module-edit-action" id="Module-Edit-Action-<?php print($Controller["Name"]); ?>">
                            
                            <div class="alert alert-block content">                            
                                <form method="POST">
                                <input type="hidden" name="Action" value="new-controller-action" >
                                <input type="hidden" name="ModuleName" value="<?php print($moduleName); ?>" >
                                <input type="hidden" name="ControllerName" value="<?php print($Controller["Name"]); ?>" >
                                
                                <h4 class="alert-heading">Create A New Action </h4>
                                
                                        <p></p>

                                        Action Name: <input type="text" class="span3" name="ActionName" placeholder="Action Name">
                                        
                                        <p></p>
                                        
                                        Description: <input type="text" class="span4" name="Description" placeholder="Action Description">

                                        <p></p><p></p>
                                        <button type="submit" class="btn">Create</button>
                                </form>  
                            </div>       
                            
                        </div>
                        <hr> 
                        <h3>Actions</h3>
                        <p></p><p></p>
                        <?php
                            
                            $Reflection = new \ReflectionClass($Controller["Namespace"]);
                        
                            $methods = $Reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                            
                            foreach($methods as $method):
                                if(preg_match("/^action_([a-z0-9]+)/",$method->getName())):

                                    $fa_name = $method->getName();
                                    $a_name = preg_replace("/action_/i","",$fa_name);

                                ?>
                                    <li>
                                        <a name="<?php print($a_name); ?>"></a>
                                        
                                        <strong><?php print($a_name); ?></strong>
                                        
                                        <div class="alert alert-block content">
                                          <p>Url: <strong>
                                              <?php 
                                              $url = strtolower("/{$moduleName}/{$Controller["Name"]}/{$a_name}/");
                                              $url = str_replace(array("main/","index/"),"",$url);
                                              print($Stove->rootUrl().$url); ?>
                                              </strong></p>
                                          
                                          
                                          <p>Namespace: <?php print("\\Application\\Module\\{$moduleName}\\Controller\\{$Controller["Name"]}::{$fa_name}()"); ?></p>
                                          
                                          <br>
                                          
                                          <?php print(nl2br($method->getDocComment())); ?>
                                            
                                          
                                        </div>
                    
                                    </li>
                                <?php
                                endif;
                            endforeach;
                            
                        ?>
                        
                        
                    </div>      
            
                <?php
            endforeach;
          ?>           
            

        </div>
          
          
      </div> <!-- /tabbable -->
      
      
      <?php
      
endif;
