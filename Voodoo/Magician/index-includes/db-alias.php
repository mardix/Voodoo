<?php
use Voodoo\Core;


$aliasDBTypes = array(
    "MySQL"=>"MySQL",
    "SQLite"=>"SQLite",
    "MongoDB"=>"MongoDB",
    //"Redis"=>"Redis",
    //"NoDB"=>"NoDB: For Model Without A Database "
);

$dropDownAliasDBType = Core\Forms::dropDownList($aliasDBTypes,array("name"=>"Config[Type]","id"=>"db-alias-select-db-type"));


if($_POST){
    
    $AliasName = $Potion->createAliasName($_POST["AliasName"]);
    
    $Config = $_POST["Config"];
    
    switch(strtolower($Config["Type"])){
        
        case "mongodb":
            $Config["ReplicaSet"] = ($_POST["MongoDB"]["replicaset"] == 1) ? "1" : "0";
            $Config["SlaveOK"] = ($_POST["MongoDB"]["slaveok"] == 1) ? "1" : "0";            
        break;
    
        case "sqlite":
            unset($Config["Host"],$Config["Username"],$Config["Password"]);
        break;
    
        case "nodb":
            $Config = array();
            $Config["Type"] = "NoDB";
        break;
    }


    $Potion->addModelConfig($AliasName,$Config);
    
    Core\Helpers::redirect("./?view=db-alias&alias=$AliasName");
}

?>

<script>
    Soup.DBAlias.Data = <?php print(json_encode($Potion->getModelAliases())); ?>;
</script>


<div class="Soup-Help">                            
    <strong>DB Alias</strong> allow you to set global settings for your database, that will be assigned to Models <br>
    DB Alias are used to create your Models to work with Voodoo DB/Mapper<br>
    
</div> 

<h1>DB Alias</h1>
 
<hr>

    <?php
        
    ?>

      <div class="tabbable tabs-left">
        <ul class="nav nav-tabs span3">

          <li class="<?php print((!$selectedAlias)? " active " : "");?>"><a href="#CHome" data-toggle="tab" id="db-alias-new"><i class="icon-plus-sign"></i> <strong>Create New DB Alias</strong></a></li>
          <?php 
      
            foreach($Potion->getModelAliasesName() as $alias):
                ?>
                  <li class="<?php print(($selectedAlias == $alias)? " active " : "");?>"><a href="#" data-toggle="tab" class="db-alias-load-info" rel="<?php print($alias);?>"><?php print($alias); ?></a></li>
                <?php
            endforeach;
          ?>

        </ul>
          
          
        <div class="tab-content">
            
          <div class="tab-pane <?php print((!$selectedController)? " active " : "");?>" id="CHome">
            
            <p>DB Alias maps database settings with a name that can be used with different Models  </p>
            
                <div class="alert alert-block">                            
                    <form method="POST" id="form-db-alias">

                    <h4 class="alert-heading">Edit Model Alias <span id="text-set-aliasname" class="db-alias-set-aliasname"></span> </h4>
                            
                            <div id="db-alias-edit-aliasname">
                                <label>Alias Name</label>
                                <input type="text" class="span3"  id="db-alias-input-aliasname" name="AliasName">
                            </div>
                    
                            <label>Type:</label>
                            <strong><div id="db-alias-type-description"></div></strong>
                            <?php print($dropDownAliasDBType); ?>
                            
                            <div class="db-alias-no-sqlite-options">
                                <label>Host</label>
                                <input type="text" class="span3" id="db-alias-input-host"      name="Config[Host]" >                            
                                <label>Username</label>
                                <input type="text" class="span3" id="db-alias-input-username"  name="Config[Username]" >
                                <label>Password</label>
                                <input type="text" class="span3" id="db-alias-input-password"  name="Config[Password]">
                            </div>
                            
                            <div id="db-alias-db-name-options">
                                <label>DB Name</label>
                                <input type="text" class="span3" id="db-alias-input-dbname"  name="Config[DBName]" > 
                            </div>
                            
                            <div id="db-alias-mongodb-options" class="db-alias-no-sqlite-options">
                                <label>MongoDB - Use Replica Set</label>
                                <input type="radio" name="MongoDB[replicaset]" value="1"> Yes - <input type="radio" name="MongoDB[replicaset]" value="0"> No
                                <label>MongoDB - Read From Slave</label>
                                <input type="radio" name="MongoDB[slaveok]" value="1"> Yes - <input type="radio" name="MongoDB[slaveok]" value="0"> No
                            </div>
                            
                            <p></p><p></p>
                            <button type="submit" class="btn">Create New Alias</button>

                    </form>  
                </div>     
            
            <hr>
            
                <div class="well">
                    
                    <p>Location: <?php print(str_replace(BASE_PATH,"",APPLICATION_CONFIG_PATH)."/DB.ini"); ?></p>
                </div>
          </div>         
            

        </div>
          
          
      </div> <!-- /tabbable -->
      