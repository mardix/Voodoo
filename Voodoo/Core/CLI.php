<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/VoodooPHP/Voodoo
 * @package     VoodooPHP
 * 
 * @copyright   (c) 2012 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 * 
 * @name        Core\CLI
 * @since       Jan 1, 2011
 * @desc        For PHP on the command line
 * 
 */

namespace Voodoo\Core;

class CLI{

// Command to quit the application when entered    
CONST CMD_QUIT = ":x";    

// Help
CONST CMD_HELP = ":h";
  

protected static $defHelp = array(
    ":x"=>"To EXIT the whole application",
    ":h"=>"For HELP  show this help message",
);

protected static $HelpOptions = array();

/**
 * Check if the SAPI is CLI or not
 * @return bool
 */
public static function isCLI(){
    return (php_sapi_name()=="cli") ? true : false;
}

 /**
* Verbose
* To output message on the screen
* $param string: the message to ouput
  $param bool: if true it will put text ext o each other instead of return to the line
* */

public static function O($message="",$oneline=false){
     echo($message.(($oneline==true) ? "" : "\n"));
}


/**
 * To input information
 * @param type $message
 * @param type $default - The default value if $input is empty
 * @return type 
 */
public static function I($message,$mixed= null){

    /**
     * If mixed is a closure: 
     * function($input){ 
     *      if(TEST INPUT)
     *          return true;
     * } 
     * 
     * must return true to break out of it, otherwise it will loop over to require it. Usually used if you want to require certain fields to be entered
     * 
     */
   if(is_callable($mixed)){
       
        do{
            
            $input = self::I($message);
            
        }while(!$mixed($input));

        return $input;       
   }
   
   /**
    * if mixed is boolean, it forces input to be entered 
    */
   else if (is_bool($mixed)){
       return
            self::I($message,function($inputt){ return ($inputt != "");});
   }
   
   
   /**
    * Just show the message 
    */
   else{
   
       echo("->>> {$message} ");

        $input = preg_replace("/\n|\r|\t/","",trim(fread(STDIN,1024)));

        // If exit is entered, it will abort the execution
        if(strtolower(trim($input)) === self::CMD_QUIT){
            return self::x();
        }

        else if(strtolower(trim($input)) === self::CMD_HELP){
            return self::help();
        }

        return $input ? : $mixed;       
   }

}


/**
 *
 * @param type $assignNewData 
 */
public static function help(){
    
    self::O("====== COMMAND HELP ======");
    
    $Help = array_merge(self::$HelpOptions,self::$defHelp);
    
   foreach($Help as $k=>$v){
       self::O("{$k} \t $v");
   } 

   self::O("-------------------------------------------\n");
       
    
    
}

/**
 * To set new help otions
 * @param type $assignNewData 
 */
public static function setHelp(Array $assignNewData){
    
    if(is_array($assignNewData) && count($assignNewData))
        self::$HelpOptions = array_merge(self::$HelpOptions,$assignNewData);    
}



public static function curDate(){
    $date = date(DATE_RFC822);
    self::O($date);
}

/**
 * To exit
 */
public static function x(){
        self::O("\n\nExiting CLI ...");
        self::curDate();
        self::O("\n>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n");
        exit;
}


 public static function sleep($sec=0,$echo=true){
	if($echo) {
		 echo("Sleeping for $sec seconds: ");

     do{
	     $sleepCounter++;

         echo(".");

	     sleep(1);

      }while($sec > $sleepCounter);

        echo("|\n");

   }

   else sleep($sec);
 }


/**
* CLI_Arg2Array
*
* */

public static function arg2Array($argv) {
/*
 Return the aruments into array.

If the argument is of the form ?NAME=VALUE it will be represented
in the array as an element with the key NAME and the value VALUE.
If the argument is a flag of the form -NAME it will be represented as a boolean
with the name NAME with a value of true in the associative array.

 ./my.cli.php --name=mardix --run=cronjob -p

 Will return:
 [name]=mardix
 [run]=cronjob
 [p]=true

*/

    $_ARG = array();

    foreach ($argv as $arg) {

      if (preg_match("/\-\-([^=]+)=(.*)/",$arg,$reg)) {
        $_ARG[$reg[1]] = $reg[2];
      }
	  else if(preg_match('/\-([a-zA-Z0-9](.*))/',$arg,$reg)) {
        $_ARG[$reg[1]] = true;
      }
    }

  return $_ARG;
}

}
