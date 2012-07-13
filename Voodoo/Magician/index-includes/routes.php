<?php

use Voodoo\Core;

$requestMethods = array(""=>"","GET"=>"GET","POST"=>"POST");
$controllers = array();



if($_POST){
    $nRoutes = array();
    
    foreach($_POST["RFrom"] as $k=>$v){

        $rMethod = $_POST["RRequestMethod"][$k] ? "{$_POST["RRequestMethod"][$k]} " : "";
        
        if($_POST["RFrom"][$k])
            $nRoutes["Routes[\"{$rMethod}{$_POST["RFrom"][$k]}\"]"] = "{$_POST["RTo"][$k]}";
    }
  
    $Potion->buildRoutes($nRoutes);
    
    Core\Helpers::redirect("./?view=routes");
}

/**
 * Build all the controllers 
 */
$ModulesDir = new RecursiveDirectoryIterator(APPLICATION_MODULES_PATH);

foreach($ModulesDir as $xDir){

    if($xDir->isDir()){

        $ModuleName = $xDir->getBasename();
        $xDirDir = new RecursiveDirectoryIterator($xDir->getPathname()."/Controller");

        foreach($xDirDir as $d2){
            if($d2->isFile()){
                $ControllerName = $d2->getBasename(".php");
                $Namespace = "Application\\Module\\{$ModuleName}\\Controller\\{$ControllerName}";

                    $Reflection = new \ReflectionClass($Namespace);

                    $methods = $Reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

                    foreach($methods as $method):

                        if(preg_match("/^([a-z0-9]+)__Action/",$method->getName())):

                            $fa_name = $method->getName();

                            $a_name = str_replace("__Action","",$fa_name)."/";
                            if($a_name == "index/") 
                                $a_name = "";

                            $controllers_from[] = "/{$ModuleName}/{$ControllerName}/{$a_name}(:any)";
                            $controllers_to[] = "/{$ModuleName}/{$ControllerName}/{$a_name}$1";

                        endif;

                    endforeach;                    
            }
        }
    }
}    

$controllerRoutesFrom = json_encode($controllers_from);
$controllerRoutesTo = json_encode($controllers_to);
$Routes = $Potion->getRoutes();
$defSelectForm = Core\Forms::dropDownList($requestMethods,array("name"=>"RRequestMethod[]","className"=>"span2"));

?>

<style>
    .small{
        font-size:11px;
        color:#666;
        font-weight:normal;
    }
</style>
<h1>Routes</h1>
<div class="Soup-Help">                            
    <strong>Routes </strong> allows you to route a matching URL to a  Module/Controller/Action <br>
    <br>
    
    <strong>From Controller</strong> accept any regular expression to capture segment of your URL, or you can use those below<br><br>
    
<b>(:any)</b>      : Will match anything <br>
<b>(:num)</b>      : will match only numeric values <br>
<b>(:alpha)</b>    : will match only alphabetical values <br> 
<b>(:alphanum)</b> : will match only alpha and numeric values  <br>  
<br>
<br>
</div>  

<!-- The template -->
<script type="text/htm" id="routes-entry-tpl">
    <tr id="table-entry">
        <td><?php print($defSelectForm); ?></td>
        <td><input type="text" class="span4"  name="RFrom[]" style="margin: 0 auto;" data-provide="typeahead" data-items="4" data-source='<?php print($controllerRoutesFrom); ?>'></td>
        <td><input type="text" class="span4"  name="RTo[]" style="margin: 0 auto;" data-provide="typeahead" data-items="4" data-source='<?php print($controllerRoutesTo); ?>'></td>
        <td><a href="javascript:" class="routes-remove"><i class="icon-minus-sign "></i></a></td>
    </tr>              
</script>


<form method="POST">

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Request Method  <div class="small">(POST | GET)</div></th>
                    <th>From Controller/Path
                       <div class="small"> /Module/Controller/(:any)</div>
                    </th>
                    <th>To <br> 
                        <div class="small">/MyOtherModule/OtherController/MyAction/$1/</div></th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                
                
                    
                    <tbody id="routes-table-trs">
                    <?php 
                        foreach($Routes as $route):
                        $selectForm = Core\Forms::dropDownList($requestMethods,array("name"=>"RRequestMethod[]","className"=>"span2","selectedValue"=>$route["RequestMethod"]));
                    ?>
                            <tr id="table-entry">
                                <td><?php print($selectForm); ?></td>
                                <td><input type="text" name="RFrom[]" value="<?php print($route["From"]); ?>" class="span4" style="margin: 0 auto;" data-provide="typeahead" data-items="5" data-source='<?php print($controllerRoutesFrom); ?>'></td>
                                <td><input type="text" name="RTo[]"   value="<?php print($route["To"]); ?>"  class="span4" style="margin: 0 auto;" data-provide="typeahead" data-items="5" data-source='<?php print($controllerRoutesTo); ?>'></td>
                                <td><a href="javascript:" class="routes-remove"><i class="icon-minus-sign "></i></a></td>
                            </tr> 

                    <?php
                        endforeach;
                    ?>
                            
                    </tbody>
            
                
            </table> 
            <div class="pull-right">
                <a href="javascript:" class="btn" id="btn-routes-add"><i class="icon-plus-sign"></i> Add</a>    
            </div>
    
        <p></p><p></p>
        <input class="btn-large btn-primary" type="submit" value="Save Routes">


</form>

                <div class="well">
                    <p>Location: <?php print($Stove->readPath(APPLICATION_CONFIG_PATH."/Routes.ini")); ?></p>
                </div>