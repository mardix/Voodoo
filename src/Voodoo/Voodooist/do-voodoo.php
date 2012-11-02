<?php
/**
 * do-voodoo
 * It allows the creation of your application
 * 
 */

include_once(dirname(__DIR__)."/init.php");

use Voodoo\Core,
    Voodoo\Voodooist;

function e($msg) {
    echo "$msg \n";
}

if(!Core\Env::isCli()) {
    e("Voodooist must run in CLI mode");
    exit;
} else {
    $jsonFile = __DIR__."/application.json";
    $BlackMagic = new Voodoo\Voodooist\Lib\BlackMagic;
    
    e(Core\Voodoo::NAME." ".Core\Voodoo::VERSION." : The Voodooist!");
    e("-----------------------------------------------------------------------");

    if (! file_exists($jsonFile)) {
        e("Error");
        e("'{$jsonFile}' is missing");
        exit;
    }
    
    $json = file_get_contents($jsonFile);
    $schema = json_decode($json, true);

    if ($error = Core\Helpers::getJsonLastError()) {
        e("Error");
        e("'application.json' contains a JSON error : ({$error["code"]}) {$error["message"]}");
        exit;
    }
    
    e("> checking front controller... ");
    $BlackMagic->createFrontController();
    
    // /VoodooApp
     if (! file_exists(Core\Path::AppConfig()."/Application.ini")) {
        e("> creating Dir: ".Core\Path::VoodooApp());
        $BlackMagic->createVoodooApp();
    }
    
    // /assets
    if ($schema["createPublicAssets"] === true && !is_dir(Core\Path::Assets())) {
        e("> creating Assets dir: ".Core\Path::Assets());
        $BlackMagic->createPublicAssets();
    }

    e("> building application from schema...\n");
    
        $created = " [CREATED] ";
        foreach ($schema["applications"] as $app) {

            $BlackMagic->setApplication($app["name"]);
            e("| {$app["name"]}");

            if (isset($app["modules"])) {

                foreach($app["modules"] as $module){

                    $moduleAction = "";
                    $isApi = (isset($module["isApi"]) && $module["isApi"] === true) ? true : false;
                    $omitViews = (isset($module["omitViews"]) && $module["omitViews"] === true) ? true : false;
                    if ($BlackMagic->createModule($module["name"], $module["template"], $isApi, $omitViews) ){
                        $moduleAction = $created;
                    }
                    e("\t|");
                    e("\t|_{$module["name"]}");


                    // Create controllers
                    if (isset($module["controllers"])) {

                        e("\t\t|_ Controller");

                        foreach ($module["controllers"] as $controller) {

                            $controllerAction = "";
                            $cIsApi = $isApi;
                            if (isset($controller["isApi"]) && $controller["isApi"] === false) {
                                $cIsApi = false;
                            }
                            
                            if ($BlackMagic->createController($controller["name"], $cIsApi)) {
                                $controllerAction = $created;
                            }

                            e("\t\t\t|");
                            e("\t\t\t|_{$controller["name"]} {$controllerAction}");

                            // actions
                            if (isset($controller["actions"])) {
                                foreach ($controller["actions"] as $action) {
                                    $actionAction = "";
                                    if($BlackMagic->addAction($action)) {
                                       $actionAction = $created;
                                    }
                                    e("\t\t\t\t|");
                                    e("\t\t\t\t|_{$action} {$actionAction}");
                                }
                            }
                        }
                    }

                    // Create Models
                    if (isset($module["models"])) {

                        e("\t\t|");
                        e("\t\t|_ Model");

                        foreach ($module["models"] as $model) {
                            $BlackMagic->createModel($model["name"], $model["dbAlias"], $model["table"], $model["primaryKey"], $model["foreignKey"]);

                            e("\t\t\t|");
                            e("\t\t\t|_{$model["name"]}");                       }
                    }
                }
            }
        }

    e("Done!");
}


/**
 * The application's library
 */




/*******************************************************************************/



