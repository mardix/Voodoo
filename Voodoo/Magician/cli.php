<?php
/**
 * Chef's Command Line
 * 
 */

$BASE_PATH = "../..";


include_once("{$BASE_PATH}/Voodoo/init.php");


use Voodoo\Core,
    Voodoo\Magician,
    Voodoo\Core\CLI;


if(!CLI::IsCLI())
    exit("BOOM! EXPLOSION! ".VOODOO_GENERATOR." Requires this file to executed via Command Line.");

/*******************************************************************************/

$Potion = new Magician\Lib\Potion;

/*******************************************************************************/

/**
 * To create a title
 * @param type $title 
 */
function TITLE($title){
   Core\CLI::O("\n>-------------- {$title} -------------------<");
}

/**
 * To output content
 * @param type $str 
 */
function O($str){
  Core\CLI::O("\n{$str}");  
}


function LINE(){
    Core\CLI::O("------------------------------------------------------------------------");
}

/*******************************************************************************/
// SETUP
/**
 * The application's library 
 */
if(!is_file(APPLICATION_CONFIG_PATH."/Config.ini")){
CLI::O("==================================================================");
CLI::O("       ||    ".VOODOO_GENERATOR.": Setup   ||");
CLI::O("==================================================================");
CLI::O("****************************************************************** \n");    

        $info = array();
        
        CLI::O("We're going to setup ".VOODOO_NAME." on your system...");
        
        CLI::O("\nYour Project name can be your site name, or anything");
        $info["Project.Name"] = CLI::I("Project's Name?","My Soup Project");
        
        CLI::O("\nAdmin email will be used to notify admin of any server error");
        $info["System.AdminEmail"] = CLI::I("Admin Email?:");
        
        CLI::O("\nThe Magician. It's the web and CLI interface that allows you to create modules, controllers, views, models etc...");
        $pw = CLI::I("Magician's Password?");
        $info["VoodooMagician.Password"] = ($pw) ? md5($pw) : "";
        
        $info["System.Timezone"] = "America/New_York";
        
        Core\Config::set($info);

        $Potion->newApplication(BASE_PATH,$info);

        $Potion->createModule("Main","Default");

        CLI::O("\n!!! Setup Completed !!!\n");
        CLI::I("Voila, Magic!\n\n Happy developing... \n\nPress enter to continue with the Magician...");
        CLI::O("");
        
        // set it up by default in here
        Core\Config::set(array("VoodooMagician"=>array("EnableCLI"=>true)));
        
CLI::O("------------------------------------------------------------------------\n");
}

/**
 * Soup/Chef is not enabled for the web 
 */
if(!Core\Config::get("VoodooMagician.EnableCLI")){
 print("403 : Restricted Access \n Go to /Application/Config/Config.ini change VoodooMagician.EnableCLI to true");   
 exit;   
}

/*******************************************************************************/

Core\CLI::setHelp(array(
""=>
"
--create=modules
        --name
        --controllers
        --actions
    
--create=routes


--list=modules
        --name
    
--list=routes

--list=dbalias
"
));



CLI::O("==================================================================");
CLI::O("       ||    ".VOODOO_GENERATOR.": The Magician   ||");
CLI::O("==================================================================");
CLI::O("******************************************************************");
CLI::O("(i) For help, type :h - To exit, type :x");
CLI::O("******************************************************************\n");
do{
    
    $cmd = explode(" ",Core\CLI::I("Enter command:\>"));
    
    $ARGV = Core\CLI::arg2Array($cmd);
    
    if($ARGV["list"]){
        
        switch($ARGV["list"]){
            
            /**
             * List Modules 
             */
            case "modules":
                        $O = "";
                        
                        TITLE("List:Modules");
                        
                        $moduleName = $Potion->createModuleName($ARGV["name"]);

                        $ControllersDir = APPLICATION_MODULES_PATH."/{$moduleName}/Controller";

                        if(is_dir($ControllersDir)){
                            O("- Module's Controllers: {$moduleName}");

                            $O .= "\t|\n\t|_{$moduleName}\n";

                            $DirIt = new \DirectoryIterator($ControllersDir);
                            foreach($DirIt as $Dir){

                                if(!$Dir->isDot() && $Dir->isFile()){
                                    $name = $Dir->getBasename(".php");
                                    $O .= "\t\t|_{$name}\n";

                                        // Get Actions
                                        $Reflection = new \ReflectionClass("Application\\Module\\{$moduleName}\\Controller\\{$name}");
                                        $methods = $Reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                                        foreach($methods as $method){
                                            if(preg_match("/^action_/",$method->getName())){
                                                $fa_name = $method->getName();
                                                $a_name = preg_replace("/^action_/","",$fa_name);
                                                $ns = "\\Application\\Module\\{$moduleName}\\Controller\\{$Controller["Name"]}::{$fa_name}()";
                                                $O .= "\t\t\t|_{$a_name}\n";
                                                }
                                        }                           
                                }
                            }  
                        }

                        else{
                        O("- All Modules");

                                // DISPLAY ALL MODULES
                                $DirIt = new \DirectoryIterator(APPLICATION_MODULES_PATH);
                                foreach($DirIt as $Dir){

                                    if(!$Dir->isDot() && $Dir->isDir()){
                                        $moduleName = $Dir->getBasename();

                                        $O .= "\t|\n\t|_{$moduleName}\n";

                                            $ControllersDir = APPLICATION_MODULES_PATH."/{$moduleName}/Controller";
                                            if(is_dir($ControllersDir)){                            
                                                $DirIt2 = new \DirectoryIterator($ControllersDir);
                                                foreach($DirIt2 as $Dir2){

                                                    if(!$Dir2->isDot() && $Dir2->isFile()){
                                                        $name = $Dir2->getBasename(".php");
                                                        $O .= "\t\t|_{$name}\n";

                                                    }
                                                }
                                            }

                                    }
                                } 
                        }


                    O($O);                
            break;
        

            /*******************************************************************************/
            
            
            /**
             * ROUTES
             */
            case "routes":
                
                    $Routes = $Potion->getRoutes();

                    TITLE("List: Routes");
                    foreach($Routes as $r){
                        $line = "{$r["requestMethod"]} {$r["From"]} \t\t => {$r["To"]}";
                        O($line);
                    }
        
            break;
        
            
            
            
            /*******************************************************************************/
            
            
            case "db-alias":
                
                    TITLE("List: DB Aliases");               
            break;
                
        }
    }

/*******************************************************************************/
    
    /**
     * CREATE 
     */
    else if($ARGV["create"]){
        
        switch($ARGV["create"]){
            
            case "modules":
                
                    /**
                    * Create Module templates 
                    */
                    $TPL = array();

                    // Browse all the templates
                    $c = 0;
                    $TplOptions ="ID \tName \tDescription\n"; 
                    foreach($Potion->listModuleTemplates() as $mTpl){
                        ++$c;
                            $TPL[$c] = array(
                                "Path"=>$mTpl["Path"],
                                "Name"=>$mTpl["Name"],
                                "Description"=>$mTpl["Description"]
                            );  
                         $TplOptions .="{$c} \t{$mTpl["Name"]} \t{$mTpl["Description"]}\n";  
                    }
   

                    $Modules = $ARGV["name"] ?: "Main"; // By default use the Main module
                    $Controllers = $ARGV["controllers"]?: "Index"; // By default use the Index controller
                    $Actions = $ARGV["actions"]?: "index"; // by default use index
                    
                    if(!$TPL[$ARGV["template"]]){
                        do{
                            O("Select a Module Template, by selecting a template ID in the list below");
                            O($TplOptions);
                            $tplId = Core\CLI::I("Template N");
                            
                        }while(!$TPL[$tplId]);
                    }
                        
                    if(count($ARGV)){

                        $bareTpl = $tplI["bare"] ?: false;

                        // Module
                        foreach(explode(",",$Modules) as $module){

                            if(!$Potion->moduleExists($module)){
                                $m2 = $Potion->createModule($module,$tplI["Path"]?:"Default",$bareTpl);
                                O("\t|_ Module: {$m2} [Created!]");
                            }
                            else
                                O("\t|_ Module: {$m2}");

                            // Controller
                            foreach(explode(",",$Controllers) as $controller){
                                if(!$Potion->controllerExists($module,$controller)){
                                    $c2 = $Potion->createController($module,$controller); 
                                    O("\t\t|_ Controller: {$c2} [Created!]");
                                }
                                else
                                    O("\t\t|_ Controller: {$controller}");
                                
                                    
                                    // Action
                                    foreach(explode(",",$Actions) as $action){
                                            $a2 = $Potion->createAction($module,$controller,$action);  
                                            O("\t\t\t|_ Action: {$a2} [Created!]");
                                    }             
                            }
                        }
                    }                
                
                
            break;
            
        }
        
        
    }


LINE();

}while(true);    
    
exit;  

$CI
   ->register("m","Create and Edit Modules, Controllers and Actions",function() use ($Potion){
       


       
$Option ="
COMMAND OPTIONS:
ie: --module=Main,AnotherOne --controller=Index,OtherController --action=index,otheraction --template=1
        
--module\tThe module name. Separate multiple name with a comma.
--controller\tThe controller name. Separate multiple name with a comma.
--action\tThe action to create. Separate multiple name with a comma.   
--template\tThe template id form the list below. Only one template id

- Module Templates:
Id. \tName
";        
foreach($TPL as $i=>$T){
    $Option .="{$i} \t{$T["Name"]} \t{$T["Description"]}\n";
}     

     Core\CLI::O($Option."\n");
    
     $cmd = explode(" ",Core\CLI::I("Enter the command:\>"));
    
     $ARGV = Core\CLI::arg2Array($cmd);

        Core\CLI::O("ERROR: Invalid Command");
     
   })
/*******************************************************************************/
   
   ->register("l","To list Modules, Models, Routes",function() use ($Potion){
$Option ="
COMMAND OPTIONS:
ie: --module --routes
        
--module\tThe module name. Will the show the module's controolers with all their actions. Leave blank to show all models
--routes

";  


     Core\CLI::O($Option."\n");
    
     $cmd = explode(" ",Core\CLI::I("Enter the command:\>"));
    
     $ARGV = Core\CLI::arg2Array($cmd);
     
     if($ARGV["module"]){
         

     }
     
     
     /**
      * Routes 
      */
     if($ARGV["routes"]){



     }

   })
   
    ->register("r","Create New Route",function() use ($Potion){
        
$Option = <<<WC
ie: POST /MyModule/MyController/(:num)/(:any)  -> /MyOtherModule/Main/$1/$2
** Wildcard options:
 *  (:any)      : Will match anything
 *  (:num)      : will match only numeric values
 *  (:alpha)    : will match only alphabetical values
 *  (:alphanum) : will match only alpha and numeric values
 
WC;
        
                Core\CLI::O($Option);
                
                $method = strtoupper(Core\CLI::I("1. Request Method: (POST or GET or Blank): ")." ");
                $from = Core\CLI::I("2. From: ie Module/Controller/Action/(:any)/(:alpha)/: ");
                $to = Core\CLI::I("3. To: ie /MyOtherModule/OtherController/$1/$2: ");
                
                if(!in_array($method,array("POST ","GET ")))
                  $method = "";
                
                $Routes = Core\INI::Routes()->get("Routes") ? : array();
                
                foreach($Routes as $rk=>$rv)
                    $nRoutes["Routes[\"{$rk}\"]"] = $rv;
               
                if($from && $to)
                    $nRoutes["Routes[\"{$method}{$from}\"]"] = $to;
  
                $Potion->buildRoutes($nRoutes);              
            })    
   ->run();
   
   
   
