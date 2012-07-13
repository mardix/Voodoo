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
 * @name        Core\Forms
 * @since       July 4, 2009
 * @desc        
 * 
 */
/**
 * Form
 * Class
 * To create a form field (select,input,textarea,checkbox...)
 * Can be used to pass array to fields etc...
 * i.e: Forms::select(Array,Options);
 * @since July 4th 2009
 * @lastupdate: Feb 14 2011 - happy Valentines to my love Rahel <3
 */

namespace Voodoo\Core;

Class Forms{

    private static $newline = "\n";
    //-------------------------------- FOR BASIC FORM FIELD ------------------------

     public static function  textArea($name="",$value="",$rows,$cols,$value_type="html"){
     /*
     *** Same as text_box
     value_type indicate if it has to format the value in HTML or leaves it like it is
     value type accept two arg : html or txt
     */
     if(strtolower($value_type)=="html"){
     $new_value=trim($value);
     $new_value=nl2br($new_value);
     }

     else $new_value=$value;
     ###########
      # Verify if rows and cols are numeric type

      if(is_numeric($rows)) $print_in.=" ROWS=\"$rows\" ";
      else $print_in.=" ROWS=\"5\" ";

      if (is_numeric($cols)) $print_in.=" COLS=\"$cols\" ";
      else $print_in.=" COLS=\"30\" ";

     return "<TEXTAREA NAME=\"$name\"  $print_in >$new_value</TEXTAREA>";
     }



    //------------------------------------------------------------------------------

     public static function  input($Properties){
      /*
      properties=array(
      name
      value
      checked
      input_type: text|password|radio|checkbox|hidden|button|submit|reset|file|image
      disabled
      size
      id
      maxlength
      cssStyle: include CSS. class="myFoms" or style="whatever"
      javaScript: javascript function to execute on behavior: onCLick,onChange etc
      className
      */

      if(!eregi("text|password|radio|checkbox|hidden|button|submit|reset|file|image",$Properties[input_type])) $Properties[input_type]="text";
      if(!$Properties[size]) $Properties[size]=20;
      if($Properties[disabled]) $disabled="DISABLED";
      if($Properties[checked]) $checked="CHECKED";


      return "<INPUT TYPE=\"$Properties[input_type]\" NAME=\"$Properties[name]\" ID=\"$Properties[id]\" VALUE=\"$Properties[value]\" SIZE=\"$Properties[size]\" $disabled $checked $Properties[cssStyle] $Properties[javaScript] MAXLENGTH=\"$Properties[maxlength]\">";

     }

    # Hidden Field
     public static function  hidden($name="",$value=""){
      return self::input(array(
        name=>$name,
            value=>$value,
            input_type=>"hidden"
            ));
     }

    # Radio Field
     public static function  radio($name="",$value="",$check=""){
      return self::input(array(
                                name=>$name,
                                value=>$value,
                                input_type=>"radio",
                                checked=>$check,
                                ));
     }

    # Checkbox field
     public static function  checkbox($name="",$value="",$check=""){
      return self::input(array(
                                name=>$name,
                                value=>$value,
                                input_type=>"checkbox",
                                checked=>$check,
                                ));
     }

    # Password field
     public static function  password($name="",$value=""){
      return self::input(array(
                                name=>$name,
                                value=>$value,
                                input_type=>"password"
                                ));
     }

    # Text field
     public static function  text($name="",$value="",$check=""){
      return self::input(array(
                                name=>$name,
                                value=>$value,
                                input_type=>"text"
                                ));
     }
    //---------------------- SELECT FIELD ------------------------------------------

    private function _add_dropdown_option($optionTitle="",$optionValue="",$selected = false ){
     # To create a select option
      $selected=($selected)? "SELECTED" : "";

     return "<OPTION VALUE=\"$optionValue\" $selected >$optionTitle</OPTION>".self::$newline;
    }


    /**
     * Create a drop menu
     * @param type $Data
     * @param type $Properties
     * @return string 
     */
     public static function  dropDownList(Array $Data, Array $Properties = array()){
    /*
    To create a select field
    $Data = Key/Value array 
    $Properties=array(
        name: name of fields
        id: id of the field
        title: title of field
        size:the size of the field. 1 by default
        selectedValue:selected values,
        defaultValue: default value
        cssStyle: include CSS. class="myFoms" or style="whatever"
        javaScript: javascript function to execute on behavior: onCLick,onChange etc
        disabled:To disable field -> 0|1
        className
        insertBlank | blank: to add a blank option
     )
    */

    $Properties[size]= ($Properties[size]) ?  : 1;

    $Properties[disabled]= ($Properties[disabled]) ? "DISABLED" : "";

        if($Properties[title]) 
            $options.=self::_add_dropdown_option($Properties[title],$Properties[defaultValue]);

        if($Properties[insertBlank] || $Properties[blank]) 
            $options.=self::_add_dropdown_option("","");

        $sV = ($Properties[selectedValue]) ?  : $Properties[defaultValue];

         foreach($Data as $optionValue=>$optionTitle){

           $selected = ($sV == $optionValue) ? true : false;

           $options .= self::_add_dropdown_option($optionTitle,$optionValue,$selected);
         }

         $select = self::$newline;
         $select.= "<SELECT name=\"$Properties[name]\" id=\"$Properties[id]\" size=\"$Properties[size]\" $Properties[cssStyle] $Properties[javaScript] class=\"$Properties[className]\" $Properties[disabled]>".self::$newline;
         $select.= $options;
         $select.= "</SELECT>".self::$newline;

      return $select;

    }

     //------------------------------------------------------------------------------


    /**
     * Create a date drop down field
     * @param array $Properties
     * @return type 
     */
    public static function  dropDownListDate(Array $Properties){
        /*
        $Properties array{
            date YYYY-MM-DD or time()
            name_prefix: a name to prefix the name
            start_year - year to start
            end_year - end year
        }
        */

        $time = (!is_int($Properties["date"])) ? strtotime($Properties["date"]) : $Properties["date"];
 
        $year = date("Y",$time);
        $month = date("m",$time);
        $date = date("d",$time);

        $months__= self::dropDownListDate_Month(array(name=>$Properties[name_prefix]."_month",id=>$Properties[name_prefix]."_month",selectedValue=>$month,insertBlank=>1));

        $years__=  self::dropDownListDate_Year(array(start_year=>$Properties[start_year],end_year=>$Properties[end_year],name=>$Properties[name_prefix]."_year",id=>$Properties[name_prefix]."_year",selectedValue=>$year,insertBlank=>1,));

        $dates__=  self::dropDownListDate_Date(array(name=>$Properties[name_prefix]."_date",id=>$Properties[name_prefix]."_date",selectedValue=>$date,insertBlank=>1));

        return "$months__ $dates__ $years__";    
    }


    /**
     * Create a drop down time
     * @param array $Properties {
     *  date = time() || YYYY-MM-DD HH::II::SS
     *  name_prefix
     *  step : it will be used as the increment between elements in the sequence
     * 
     * }
     * @return type 
     */
    public static function dropDownListTime(Array $Properties){
        
        $time = (!is_int($Properties["date"])) ? strtotime($Properties["date"]) : $Properties["date"];
        
        $step = $Properties["step"]?: 1;
        
        $MinRange = array_map(function($m) use($step){
                                return 
                                    ($m>9) ? $m : "0{$m}";}
                    ,range(0,59,$step));
        
        $HrRange = array_map(function($t){
                                return($t>9) ? $t : "0{$t}";}
                    ,range(1,12)); 
        
        $ampmRange = array("am"=>"AM","pm"=>"PM");    
        
        $ampm = (date("H",$time)<12) ? "am" : "pm";

        return
          self::dropDownList(array_combine($HrRange,$HrRange),array("name"=>"{$Properties["name_prefix"]}_hour","selectedValue"=>date("h",$time),"id"=>"{$Properties["name_prefix"]}_hour"))
         .self::dropDownList(array_combine($MinRange,$MinRange),array("name"=>"{$Properties["name_prefix"]}_minutes","selectedValue"=>date("i",$time),"id"=>"{$Properties["name_prefix"]}_minutes"))
         .self::dropDownList($ampmRange,array("name"=>"{$Properties["name_prefix"]}_ampm","selectedValue"=>$ampm));        
    }
    
    
    /**
     * Create a date range drop down menu
     * @param Array $sP - properties, refer to  dropDownListDate
     * @param Array $eP - properties, refer to  dropDownListDate
     * @param String $separator - A separator for the two field
     * @return string
     */
    public static function  dropDownListDate_Range(Array $sP,Array $eP,$separator = " - "){
        
        $startProp = array(
            "name_prefix"=>($sP["name_prefix"]) ? :"range_start",
            "date"=>$sP["date"] ? : time(),
            "start_year"=>$sP["start_year"] ? : 1900,
            "end_year"=>$sP["end_date"] ? : date("Y")
        );
        $endProp = array(
            "name_prefix"=>($eP["name_prefix"]) ? :"range_end",
            "date"=>$eP["date"] ? : time(),
            "start_year"=>$eP["start_year"] ? : 1900,
            "end_year"=>$eP["end_date"] ? : date("Y")
        );

        $d1 = self::dropDownListDate($startProp);
        $d2 = self::dropDownListDate($endProp);
        
        return ("{$d1} {$separator} {$d2}");
    }


    /**
     * Create a DOB drop down menu
     * @param type $date
     * @param type $minimumAge
     * @return type 
     */
    public static function  dropDownListDate_DOB($date,$minimumAge=13){
    # Selected date: YYYY-MM-DD

        $prop = array(
         "date"=>$date,
         "start_year"=>1900,
         "end_year"=>date(Y)-$minimumAge,
         "name_prefix"=>"dob",
        );

        return self::dropDownListDate($prop);
    }
    
    /**
     * 
     * @param array $Input 
     * @return string in the format: YYYY-MM-DD
     */
    public static function getDropDownListDate_DOB(Array $Input){
       return 
            "{$Input["dob_year"]}-{$Input["dob_month"]}-{$Input["dob_date"]}";
    }
    

    
    
    /**
     * Create a drop down month
     * @param type $Properties
     * @return type 
     */
    public static function  dropDownListDate_Month($Properties){

             $month["01"]="January";
             $month["02"]="Febuary";
             $month["03"]="March";
             $month["04"]="April";
             $month["05"]="May";
             $month["06"]="June";
             $month["07"]="July";
             $month["08"]="August";
             $month["09"]="September";
             $month["10"]="October";
             $month["11"]="November";
             $month["12"]="December";

             return self::dropDownList($month,$Properties);    
    }

    /**
     *
     * @param type $Properties
     * @return type 
     */
    public static function  dropDownListDate_Date($Properties){

      for($i=1;$i<=31;$i++){
       $c=($i<10)?"0$i":$i;
         $data["$c"]="$i";
      }

      return self::dropDownList($data,$Properties);

    }

  
    /**
     *
     * @param type $Properties
     * @return type 
     */
     public static function  dropDownListDate_Year($Properties){

     /*
     extra in properties
     start_year
     end_year
     */

        if($Properties[end_year] == "now") 
            $what_year=date(Y);

        else if($Properties[end_year] == "last") 
            $what_year=date(Y) - 1;

        else if($Properties[end_year] == "next") 
            $what_year=date(Y) + 1;

        else $what_year = $Properties[end_year];

          for($i=$what_year;$i>=$Properties[start_year];$i--){
             $data[$i]=$i;
          }


        return self::dropDownList($data,$Properties);

     }
     
    //------------------------------------------------------------------------------

     /**
      * Return a date time drop down list separated by a separator
      * @param array $Properties
      * @param type $separator
      * @return type 
      */
    public static function dropDownListDateTime(Array $Properties,$separator = " "){
        
        return
            self::dropDownListDate($Properties).$separator.self::dropDownListTime($Properties);

    }
    
    
    /**
     * To catch date from a form request and return it in MySQL date format
     * @param type $name_prefix
     * @param array $Data 
     * @return string YYYY-MM-DD
     */
    public static function catchDate($name_prefix,Array $Data){
    
        $Y = $Data["{$name_prefix}_year"];
        $M = $Data["{$name_prefix}_month"];
        $D = $Data["{$name_prefix}_date"];
        
        if(!$Y)
            throw new Exception("Can't catch date. Invalid year");
        
        if(!$M)
            throw new Exception("Can't catch date. Invalid month");      
        
        if(!$D)
            throw new Exception("Can't catch date. Invalid date");
        
        return
            date("Y-m-d",strtotime("{$Y}-{$M}-{$D}"));
    }
    
    
    /**
     * To catch time from a form request and return it in MySQL time format
     * @param type $name_prefix
     * @param array $Data 
     * @return string HH:II:SS
     */
    public function catchTime($name_prefix,Array $Data){
        
        $H = $Data["{$name_prefix}_hour"];
        $I = $Data["{$name_prefix}_minutes"];
        $S = $Data["{$name_prefix}_seconds"]?:"00";
        $APM = $Data["{$name_prefix}_ampm"];
            if($APM == "pm")
                $H +=12;
        return
            date("H:i:s",strtotime("{$H}:{$I}:{$S}"));
    }
    
    /**
     * To catch date and time from a form request and return it in MySQL datetime format
     * @param type $name_prefix
     * @param array $Data 
     * @return string YYYY-MM-DD HH:II:SS
     */
    public function catchDateTime($name_prefix,Array $Data){
    
        return
            self::catchDate($name_prefix,$Data)." ".self::catchTime($name_prefix,$Data);
    }    
}

