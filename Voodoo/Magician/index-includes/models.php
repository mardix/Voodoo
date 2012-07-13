<?php
/**
 * Module
 *  
 * This page admin modules, controllers and actions
 */
use Voodoo\Core;

$Aliases = $Potion->getModelAliasesName();
$alias = $Potion->createAliasName($_REQUEST["alias"]);


if(!$alias || !in_array($alias, $Aliases))
    $Stove->setError("No DB Alias provided or DB Alias is invalid");

else{
    $tableDoc = $Potion->getModelAlias($alias);
    $dbType = $tableDoc["Type"];
    $hideTableDocName = false;
    switch(strtolower($dbType)){
        
        case "mysql":
        case "sqlite":
           $tableDocName = "Table Name";
           $defaultPrimeKey = "id";
        break;
    
        case "mongodb":
            $tableDocName = "Collection Name";
            $defaultPrimeKey = "_id";
        break;
    
        case "redis":
            $tableDocName = "Bucket Name";
            $defaultPrimeKey = "";
        break;
    
        case "nodb":
            $hideTableDocName = true;
        break;
    }
    
    
    
    $moduleName = $Potion->createModuleName($_REQUEST["module"]);
    $controllerName = $_REQUEST["controller"];
    $selectedController = "";
    
    $ModelsDir = APPLICATION_MODELS_PATH."/{$alias}";
    $ModelsList = array();
    try{
        $DirIt = new \DirectoryIterator($ModelsDir);
        
        foreach($DirIt as $Dir){

            if(!$Dir->isDot() && $Dir->isFile()){
                $name = $Dir->getBasename(".php");
                $Namespace = "Application\\Model\\{$alias}\\".$Potion->createModelName($name);
                
                try{
                    
                    $Ref = new \ReflectionProperty($Namespace,"TableName");
                    $Ref->setAccessible(true);
                    $_tableDocName = $Ref->getValue(new $Namespace);                       
                
                }catch(\Exception $re){
                   $_tableDocName = "<div class='alert alert-danger'>".$re->getMessage()."</div>";
                }
                
                $ModelsList[] = array(
                    "Name"=>$name,
                    "UnQualifiedNS"=>"{$alias}\\".$Potion->createModelName($name),
                    "Namespace"=>$Namespace,
                    "TableName"=>$_tableDocName
                );

                

            }
        }   
    }catch(\Exception $e){
        
    }
    
}

$totalModels = count($ModelsList);

if($_POST && !$Stove->hasErrors()){

    $err = false;
         if($_POST["ModelName"]){
             
            if($tableDoc["Type"] == "NoDB")
                $Potion->createModel($alias,stripslashes($_POST["ModelName"]),stripslashes($_POST["TableName"]),stripslashes($_POST["PrimaryKey"]));
            
            else if($_POST["TableName"])
                $Potion->createModel($alias,stripslashes($_POST["ModelName"]),stripslashes($_POST["TableName"]),stripslashes($_POST["PrimaryKey"]));

            else{
                $Stove->setError("Table or Collection Name is not provided");
                $err = true;
            }
            
            if(!$err)
                 Core\Helpers::redirect("./?view=models&alias={$alias}");            
        }
        else
            $Stove->setError("Model Name is not provided");

}


/*******************************************************************************/
?>

<h1>DB Alias : <u><?php print($alias); ?></u></h1>

        <div class="Soup-Help">    
            This the DB Alias, containing setting to connect to a database
            with Voodoo's Data Mapper. <br><br>
            Alias: <strong><?php print($alias); ?></strong><br>
            DB Type: <u><?php print($tableDoc["Type"]); ?></u><br>
            Host: <u><?php print($tableDoc["Host"]); ?></u><br>
            DB Name: <u><?php print($tableDoc["DBName"]); ?></u><br>
        </div>  


<hr>

<?php

    if($Stove->hasErrors()):
        $Stove->showErrors();

    else:
?>

      <div class="tabbable">
        <ul class="nav nav-tabs">

          <li class="<?php print($totalModels ? "" : "active");?>"><a href="#CHome" data-toggle="tab" ><i class="icon-plus-sign"></i> <strong>Create New Model</strong></a></li>
         <li class="<?php print(!$totalModels ? "" : "active");?>"><a href="#CList" data-toggle="tab" ><i class="icon-list"></i> <strong>All Models</strong></a></li>


        </ul>
          
          
        <div class="tab-content">
            
          <div class="tab-pane <?php print($totalModels ? "" : "active");?>" id="CHome">
            
            <p>Models are associated to a DB Alias. Model represent a table (relational DB: MySQL, SQL...), or document (MongoDB), You can automatically create new Model here. </p>
            
                <div class="alert alert-block">                            
                    <h4  class="alert-heading">Create New Model with DB Alias: <span><?php print($alias); ?></span> (Type: <?php print($dbType); ?>)</h4> 
                    <br>
                    
                        <form method="POST">
                            <input type="hidden" name="new" value="1" >

                                <label class="control-label" for="input01">Model Name</label>
                                <input type="text" name="ModelName" class="input-xlarge" id="input01">
                                
                                <div id="models-with-db" style="display:<?php print(($hideTableDocName) ? "none" : ""); ?>">
                                    <label class="control-label" for="input01"><?php print($tableDocName) ?></label>
                                    <input type="text" name="TableName" class="input-xlarge" id="input01"> 

                                    <label class="control-label" for="input01">Primary Key Name</label>
                                    <input type="text" name="PrimaryKey" value="<?php print($defaultPrimeKey); ?>" class="input-xlarge" id="input01"> 
                                </div>
                                
                                <br>
                                <button type="submit" class="btn">Create Model</button>

                        </form>
                </div>     
            
            <hr>
            
                <div class="well">
                    
                    <p>Models Location: <?php print($Stove->readPath(APPLICATION_MODELS_PATH."/{$alias}")); ?></p>
                </div>
          </div>         
            

            
            
          <div class="tab-pane <?php print(!$totalModels ? "" : "active");?>" id="CList">
            
            <h4>Models associated to DB Alias:  <span><?php print($alias); ?></span></h4>
            <br>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Model Namespace</th>
                    <th>Use in any of your Controllers</th>
                    <th class="span3"><?php print($tableDocName) ?></th>
                    
                    
                </tr>
                </thead>
                
                <tbody>
                    <?php 
                        foreach($ModelsList as $Model):
                          ?>
                                <tr>
                                    <td><?php print($Model["Namespace"]); ?></td>
                                    <td>$this->getModel("<?php print("{$alias}/{$Model["Name"]}"); ?>");</td>
                                    <td class="span3"><?php print($Model["TableName"]); ?></td>
                                    
                                </tr>                   
                          <?php
                        endforeach;
                    ?>


                </tbody>
            </table> 
            
                <div class="well">
                    <p>Location: <?php print($Stove->readPath(APPLICATION_MODELS_PATH."/{$alias}")); ?></p>
                </div>
          </div>  
            
            
            
        </div>
          
          
      </div> <!-- /tabbable -->
      
 <?php
 endif;
 
 