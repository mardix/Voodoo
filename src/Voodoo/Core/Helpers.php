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
 * @name        Core\Helpers
 * @since       Costant update....
 * @desc        A collection of useful function wrapped into class... Didn't feel like putting functions in their own file,
 *              so I know if call this static class, I will find my function anywhere... yeah yeah yeah... don't judge... it works... lol
 *              Most of these functions was created for special task, but decided to leave them anyway.
 *              They may not be too clean or PSR-2, but it's ok... lol... my bad on that :)
 */

namespace Voodoo\Core;

Class Helpers{

    /**
     * To create a possesive.
     * echo strPossessive("Chris"); //returns Chris'
        echo strPossessive("David"); //returns David's
     * @param  string $string
     * @return string
     */
    public static function strPossessive($string)
    {
            return $string.'\''.($string[strlen($string) - 1] != 's' ? 's' : '');
    }

//------------------------------------------------------------------------------

    public static function formatInvoice($n,$zeroPad=9)
    {
      $n = str_pad($n,$zeroPad,"0",STR_PAD_LEFT);
      $n = preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{2})([0-9a-zA-Z])/","$1-$2-$3",$n);

      return $n;
    }

    public static function formatPhoneNumber($phone = '',$returnRaw=false,$trim = true,$convert = false)
    {
    /*
     $returnRaw = will return the raw number after it cleans it. That's the number to save in db
     $trim to: to cut it to a specific number
     $convert: to change letters into numbers
    */
        $phone = preg_replace("/[^0-9A-Za-z]/","", $phone);

        if (empty($phone)) return '';

        if($returnRaw) return $phone;

        //Convert Letters to #s
        // Samples are: 1-800-TERMINIX, 1-800-FLOWERS, 1-800-Petmeds
        if ($convert == true) {
            $replace = array(
                             '2'=>array('a','b','c'),
                             '3'=>array('d','e','f'),
                             '4'=>array('g','h','i'),
                             '5'=>array('j','k','l'),
                             '6'=>array('m','n','o'),
                             '7'=>array('p','q','r','s'),
                             '8'=>array('t','u','v'),
                             '9'=>array('w','x','y','z')
                     );

            // Replace each letter with a number. str_ireplace instead of str_replace = case incensitive
            foreach ($replace as $digit=>$letters) {
                $phone = str_ireplace($letters, $digit, $phone);
            }
        }

        // If we have a number longer than 11 digits cut the string down to only 11
        // This is also only ran if we want to limit only to 11 characters
        if ($trim == true && strlen($phone)>11) $phone = substr($phone, 0, 11);

        // Perform phone number formatting here
        if (strlen($phone) == 7) return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);

        else if (strlen($phone) == 10)return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "($1) $2-$3", $phone);

        else if (strlen($phone) == 11) return preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1($2) $3-$4", $phone);

        else return $phone;
    }

    /**
     * Format number to currency
     * @param <type> $digit
     * @return <type>
     */
    public static function toCurrency($digit)
    {
      return number_format($digit,2); //sprintf("%.2f",$digit);
    }

//------------------- COUNTWORDS-----------------------------------------------------------
    // To count total words in a string
    public static function countWords($str)
    {
     $str=trim($str);
      $fullStr =$str;
      $initial_whitespace_rExp = "/^[^A-Za-z0-9]+/i";
      $left_trimmedStr = preg_replace($initial_whitespace_rExp,"",$fullStr);
      $non_alphanumerics_rExp = "/[^A-Za-z0-9]+/i";
      $cleanedStr = preg_replace($non_alphanumerics_rExp, " ",$left_trimmedStr);
      $splitString = explode(" ",$cleanedStr);
      $word_count = count($splitString);

        return (count($splitString)<1) ? 0 : $word_count;
    }

    public static function removeUrl($data)
    {
    // remove url + email

      $lines = explode("\n", $data);

      while (list ($key, $line) = each ($lines)) {
        $line = eregi_replace("([ \t]|^)www\.", " http://www.", $line);
        $line = eregi_replace("([ \t]|^)ftp\.", " ftp://ftp.", $line);
        $line = eregi_replace("((http://|https://|ftp://|news://)[^ )\r\n]+)", "", $line);
        $line = eregi_replace("([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+))", "", $line);

        if (empty($newText)) $newText = $line;
        else $newText .= "\n$line";
      }

      return $newText;
    }


    # TO SHOW ONLY X MAX LETTER, SO EVEREYTHING FITS THE SAME
   # May 27 2005
   public static function truncate($str,$max_chars=3000,$force=1)
   {
     if (strlen($str)>=$max_chars) {

     if ($force==1) {
       $str=substr($str,0,$max_chars);
       $espace=strrpos($str," ");
       $str=substr($str,0,$espace)."...";

       return $str;
      } else {
     $showmax=$max_chars-3;
       $str=substr($str,0,$showmax)."...";

       return $str;
     }

    } else return $str;
   }

    /**
     * Will create an excerpt of the content. Will remove any html tags
     * @param string $text
     * @param int $wordCount
     * @return string plain text
     */
    public static function excerpt($text, $wordCount = 250)
    {
        $text = strip_tags(htmlspecialchars_decode($text));
        return implode(" ",array_slice(explode(" ", $text), 0, $wordCount));
    }


    # CLEAN DIRTY WORD
   public static function filterDirtyWords($list,$hidden="",$str="")
   {
      /*
      list= the list of the word, must be in simple var like $C_W="fuck,bitch,saddam";
      str= is the string to format
      spliter is the spliter used to separate each censored word
      $hidden = replace censored words by hidden
      */
       $hidden_words = $hidden; # replace the censored word with that

       $str = preg_replace('`(^|\W*)('.$list.')s?(\W|$)`Usi','$1 '.$hidden.' $3', $str);
       $str = preg_replace('`(^|\W*)('.$list.')es?(\W|$)`Usi','$1 '.$hidden.' $3', $str);
       return $str;

       return $str;
   }

    /**
     * generateRandomString()
     * To create a random string with a specific length.
     * Can also generate a random number by providing the minLen and randomLen=true
     *
     * @author: Mardix
     * @since: July 6th 2009
     *
     * @param INT $strLen : the maximum length of the new string
     * @param INT $minLen : The minimum length to generate the $randomLen
     * @param BOOL $randomLen : to generate random length between $strLen and $minLen
     * @param BOOL $noCloseSameChar : If you don't want two same chars to be close to each other
     * @param <type> $addZeroOne : To include the numbers 0 and 1. Since they kinda look like i and o Just an extra. For sake of confusion, can be removed
     * @return STRING : a new length of string
     */
    public static function generateRandomString($strLen=7,$minLen=5,$randomLen=false,$noCloseSameChar=true,$addZeroOne=false)
    {
        // Alpha: valid chars. Numbers: 0 and 1 are removed since the look like i and o and capital letters
        $alpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz23456789";
        $chars = $alpha.(($addZeroOne) ? "01_" : "");
        $charsLen = strlen($chars);
        $strLen = ($randomLen == true) ? rand($minLen,$strLen) : $strLen;
        $newStr = $aplha{rand(0,strlen($alpha)-1)};
        for ($i=1;$i<$strLen;$i=strlen($newStr)) {
           $r =  $chars{rand(0,$charsLen)};
           $newStr .= ($noCloseSameChar) ? ((strtolower($r) != strtolower($newStr{$i - 1})) ? $r : "") : $r;
        }
        return $newStr;
    }


    /**
     * Calculate an age
     *
     * @param Datetime $dob
     * @return int
     */
    public static function getAge($dob)
    {
        return (new \DateTime($dob))
                ->diff(new \DateTime)
                ->format("%y");
    }



    public static function validEmail($email)
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Check if a password is valid
     *
     * @param string $str
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function validPassword($str, $min = 6, $max = 32)
    {
        return preg_match("/^[.a-z_0-9\-!@#\$%]{{$min},{$max}}$/ui",$str);
    }

    /**
     * Check if a login is valid
     *
     * @param string $str
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function validLogin($str, $min = 4, $max = 64)
    {
        return preg_match("/^[\w_]{{$min},{$max}}$/",$str);
    }

    public static function validZipCode($zip)
    {
        return preg_match("/^[0-9]{5}([- ]?[0-9]{4})?$/",$zip);
    }

    public static function validIP($str)
    {
        return preg_match("/\d{1,3}(?:\.\d{1,3}){3}/", $str);
    }

    public static function validUrl($str)
    {
        return preg_match("#^((http|https)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie", $str);
    }
    /**
     * Calculate the date diff between 2 dates
     * @param mixed $timeS: Time in time() of mysqlddatetime or date()
     * @param mixed $timeE: Time in time() of mysqlddatetime or date()
     * @param bool $abs: to calculate the date as absolute, all dates will be positive
     * @return Array(seconds,minutes,hours,days...)
     */
    public static function dateDifference($date1, $date2, $abs = true)
    {
        $d1 = (is_string($date1) ? strtotime($date1) : $date1);
        $d2 = (is_string($date2) ? strtotime($date2) : $date2);
        $diff_secs =($abs==true) ? (abs($d1 - $d2)) : ($d1 - $d2);
        $base_year = min(date("Y", $d1), date("Y", $d2));
        $diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
        return array
        (
            "years"         => abs(substr(date('Ymd', $d1) - date('Ymd', $d2), 0, -4)),
            "months_total"  => (date("Y", $diff) - $base_year) * 12 + date("n", $diff) - 1,
            "months"        => date("n", $diff) - 1,
            "days_total"    => floor($diff_secs / (3600 * 24)),
            "days"          => date("j", $diff) - 1,
            "hours_total"   => floor($diff_secs / 3600),
            "hours"         => date("G", $diff),
            "minutes_total" => floor($diff_secs / 60),
            "minutes"       => (int) date("i", $diff),
            "seconds_total" => $diff_secs,
            "seconds"       => (int) date("s", $diff)
        );
    }


    /**
     * To format a YYY-MM-DD HH:II:SS into a human readable
     * @param <type> $datetime or the unix time
     * @param string, tthe format, can use $mask
     * @return <type>
     * //"D, M d Y @ g:i:s a"
     */
     public static function formatDate($datetime,$format="date")
     {
            if(!$datetime || $datetime == "0000-00-00 00:00:00")
                return "";

            $mask = array(
              "date"=>"D, M d Y",
              "dateTime"=>"D, M d Y @ g:i a",
              "dateTimeSecond"=>"D, M d Y @ g:i:s a",
              "time"=>"g:i a",
              "timeSecond"=>"g:i:s a",
              "mysql"=>"Y-m-d H:i:s",
            );

            $toTime = (is_numeric($datetime)) ? $datetime : strtotime($datetime);
            return date(($mask[$format]) ? : $format ,$toTime);
     }



    /**
     * Delete file recursively
     *
     * @param strin $dir
     * @return bool
     */
    public static function recursiveDelete($dir)
    {
        foreach (array_diff(scandir($dir), ['.','..']) as $file) {
          (is_dir("$dir/$file")) ? self::recursiveDelete("$dir/$file")
                                   : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * Copy file recursively
     * @param <type> src, Source
     * @param string $dest, where to save
     * @return <type>
     */
    public static function recursiveCopy($src,$dest)
    {
      // recursive function to delete
      // all subdirectories and contents:
      if(is_dir($src))$dir_handle=opendir($src);
          while ($file=readdir($dir_handle)) {
            if ($file!="." && $file!="..") {
              if (!is_dir($src."/".$file)) {
                    if(!file_exists($dest."/".$file))
                        @copy($src."/".$file,$dest."/".$file);
                  } else {
                    @mkdir($dest."/".$file,0775);
                    self::recursiveCopy($src."/".$file,$dest."/".$file);
                  }
            }
          }
      closedir($dir_handle);
      return true;
    }


# To make an URL Friendly
    public static function toFriendlyUrl($O)
    {
        // Clean up some words, concat 's
        $O = preg_replace("/\s+(a|an|the|and|or|of|for)\s+/i","-", str_replace("'s ","s ",$O));
        // replace non words with - and remove excessive -
        $O = preg_replace("/\-{2,}/","-",preg_replace("/[^a-z0-9_]/i","-",$O));
        return  preg_replace("/^\-|\-$/","",trim($O));
    }


    public static function multiArraySearch($needle, $haystack,$index_starter=0)
    {
       // Search value in multi array
            $value = "";
            $x = $index_starter;
            foreach ($haystack as $temp) {
                     $search = array_search($needle, $temp);
                     if (strlen($search) > 0 && $search >= 0) {
                        $value[0] = $x;
                        $value[1] = $search;
                      }
                     $x++;
                }

        return $value;
      }
//--------------------------- TAGCLOUD -----------------------------------------

/**
 * To create a tag cloud based on the tags provided
 * @param array $Tags - array($tagName=>$tagCount)
 * @param string $Link - Link to connect to the tags
 * @param INT $cloud_spread - max tags to show
 * @param <type> $sort - the sorting (count|tag)
 * @param string $title - the title to enter in the link. Can be formatted with %tag% | %count% to add the tag and count respectively in the a href title
 * @param bool $sizeTags - to allow multiple size of fonts
 * @return String LI
 */

public function createTagCloud(Array $Tags,$Link="",$cloud_spread=0,$sort="count",$title="%tag%",$sizeTags=false)
{
      // Count tags
      $totalTags = count($Tags);

      // The base size of the font-size
      $fontSize_base = 13;

      // the font size ratio, the higher the bigger the font-size will be
      $fontSize_ratio = 1.5;

    // Sorting the tags
    if($sort=="tag")
        ksort($Tags);
    else
        arsort($Tags);

      // Creating the list
       foreach ($Tags as $tagName => $tagCount) {

           $fontSize = (round(($tagCount * 100)/$totalTags) * $fontSize_ratio) + $fontSize_base;

           $urlKey = urlencode($tagName);

           $urlTitle = str_replace(array("%tag%","%count%"),array($tagName,$tagCount),$title);

           $styleTag = ($sizeTags) ? ("style=\"font-size:{$fontSize}px;\"") : "";

           $cloud.="<li {$styleTag} ><a href=\"{$Link}{$urlKey}\" title=\"{$urlTitle}\">{$tagName}</a></li>\n";

                 $count ++; // To count for cloud spread

                 if($cloud_spread && $count >= $cloud_spread)
                     break;
       }

       return $cloud;
 }


    /*
      Return the domain name of a url
    */
    public static function getDomain($url)
    {
      return preg_replace("/http:\/\/([^\/]+)[^\s]*/", "$1", $url);
    }

//------------------------------------------------------------------------------

    /**
     * To compress data in GZIP and print it.
     * Will send the the header etc
     * Good when sending a lot of JSON to the browser
     * @param  <type> $content : the data to be compress
     * @param  <bool> $print   : to print or just resturn the compressed data
     * @return <type>
     * @since Sept 2 2009
     */
    public static function CompressOutput($content, $print = true)
    {
        // Browser can handle gzip data so send it the gzip version
        if (strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
            $gzip = gzencode($content, 9, FORCE_GZIP);
            // output the data to the browser
            if ($print) {
             header ("Content-Encoding: gzip");
              header ("Content-Length: " . strlen($gzip));
              echo $gzip;
            } else {
                return $gzip;
            }
        } else {
          if($print==true) {
              echo $content;
          } else {
             return $content;
          }
      }
    }


    /**
    *
    * formatTweets
    *
    * To convert links on a twitter status to a clickable url. Also convert @ to follow link, and # to search
    *
    * @author: Mardix - http://mardix.wordpress.com, http://www.givemebeats.net
    * @date: March 16 2009
    *
    * @param string : the status
    * @param bool : true|false, allow target _blank
    * @param int : to truncate a link to max length
    * @return String
    *
    * */
    public static function formatTweets($status,$targetBlank=true,$linkMaxLen=250)
    {
        $target=$targetBlank ? " target=\"_blank\" " : "";
        // convert link to url
        $status = preg_replace("/((http:\/\/|https:\/\/)[^ )\r\n]+)/e", "'<a href=\"$1\" title=\"$1\"  $target >'. ((strlen('$1')>=$linkMaxLen ? substr('$1',0,$linkMaxLen).'...':'$1')).'</a>'", $status);
        // convert @ to follow
        $status = preg_replace("/(@([_a-z0-9\-]+))/i","<a href=\"http://twitter.com/$2\" title=\"Follow $2\" $target >$1</a>",$status);
        // convert # to search
        $status = preg_replace("/(#([_a-z0-9\-\.]+))/i","<a href=\"http://search.twitter.com/search?q=%23$2\" title=\"Search $1\" $target >$1</a>",$status);
        return $status;
    }

    /**
     * To calculate a time since the $timestamp and will return it into a human readable date, like 2 hours ago, 7 weeks ago
     *  created for twitter and statuses
     * @param  DATE   $timestamp
     * @return string : the time since date
     * @since Dec 1 2009
     */
    public static function timeSince($time)
    {
        $unix_timestamp = (is_string($time)) ? strtotime($time) : $time;
        $seconds = time() - $unix_timestamp;
        $minutes = $hours = $days = $weeks = $months = $years = 0;

        if ( $seconds == 0 )
            $seconds = 1;

        if ( $seconds> 60 )
            $minutes =  $seconds/60;
        else
            return self::timeSince_read($seconds,'second');

        if ( $minutes >= 60 )
            $hours = $minutes/60;
        else
            return self::timeSince_read($minutes,'minute');

        if ( $hours >= 24)
            $days = $hours/24;
        else
            return self::timeSince_read($hours,'hour');

        if ( $days >= 7 )
            $weeks = $days/7;
        else
            return self::timeSince_read($days,'day');

        if ( $weeks >= 4 )
            $months = $weeks/4;
        else
            return self::timeSince_read($weeks,'week');

        if ($months>= 12) {
            $years = $months/12;

            return self::timeSince_read($years,'year');
        } else

            return self::timeSince_read($months,'month');
    }
    /**
     * To return a the human readable time since of self:timeSince
     * @param  <type> $num  : a number of time since
     * @param  <type> $word (second|minute|hour|day|week|year|month)
     * @return <type>
     */
    private static function timeSince_read($num,$word)
    {
        $num = floor($num);
        return ("$num $word").(($num==1)? "" : "s" ). (" ago");

    }
//------------------------------------------------------------------------------

//-----------------------> Some math convert methods<--------------------------

    /**
     * inches to cm
     * @param  <type> $val
     * @return <type>
     */
    public static function convertIn2Cm($val)
    {
        return (round($val * 2.54 * 100) / 100);
    }

    /**
     * Cm to inches
     * @param  <type> $val
     * @return <type>
     */
    public static function convertCm2In($val)
    {
        return $val * 0.394;
    }

    /**
     * Cm to meters
     * @param  <type> $val
     * @return <type>
     */
    public static function convertCm2M($val)
    {
        return round($val * 0.01 * 100) / 100;
    }

    /**
     * Meters to cm
     * @param  <type> $val
     * @return <type>
     */
    public static function convertM2Cm($val)
    {
        return $val * 0.0254;
    }

    /**
     * Human inches value and convert it to a human readable
     * @param  <type> $val
     * @param  <type> $dumb
     * @return <type>
     */
    public static function convertIn2Ft_human($val,$dumb=false)
    {
        $ft = floor($val/12);
        $in = $val%12;
        return ($dumb) ? ("{$ft}ft. {$in}in.") : ("{$ft}'. {$in}\".");
    }

//------------------------------------------------------------------------------

        /**
         * Merges any number of arrays / parameters recursively, replacing
         * entries with string keys with values from latter arrays.
         * If the entry or the next value to be assigned is an array, then it
         * automagically treats both arguments as an array.
         * Numeric entries are appended, not replaced, but only if they are
         * unique
         *
         * calling: result = array_merge_recursive_distinct(a1, a2, ... aN)
        **/

        public static function array_merge_recursive_distinct ()
        {
          $arrays = func_get_args();
          $base = array_shift($arrays);
          if(!is_array($base)) $base = empty($base) ? array() : array($base);
          foreach ($arrays as $append) {
            if(!is_array($append)) $append = array($append);
            foreach ($append as $key => $value) {
              if (!array_key_exists($key, $base) and !is_numeric($key)) {
                $base[$key] = $append[$key];
                continue;
              }
              if (is_array($value) or is_array($base[$key])) {
                $base[$key] = self::array_merge_recursive_distinct($base[$key], $append[$key]);
              } elseif (is_numeric($key)) {
                if(!in_array($value, $base)) $base[] = $value;
              } else {
                $base[$key] = $value;
              }
            }
          }

          return $base;
        }

//------------------------------------------------------------------------------

    /**
     * To generate a path sequence from a number
     * if str is 546372
     * It will create a 3 level path
     *      000/000/546
     *
     * @param String str the string to pad
     * @param  INT    $paddingLeng : the length of the path
     * @return string new path
     */
    public static function generatePathFromSequencedNumber($str,$paddingLength = 12)
    {
        // Padding with leading 0 with up to 12 chars
        //$paddingLength=12;

        $sequence =  str_pad($str,$paddingLength,"0",STR_PAD_LEFT);

        $levelDown = 3; // total folder down
        $seqSplit = 3; // total chracter per folder name
        $seqLen = strlen($sequence); // length of

        // Reset pass & level
        $pass = 0; // The name of the folder
        $level = 0; // deep in

        // Loop thru the sequence to create the path;
        for ($i=0;$i<$paddingLength;$i++) {
            $pass++;

            $path .= $sequence{$i};

            if ($pass>=$seqSplit) {
                $level++;

                if($level>=$levelDown)
                    break;

                $path .= "/";
                $pass = 0;
            }
        }

        return $path;
    }
//------------------------------------------------------------------------------
    /**
     * To remove repetitive chars in a string that are close to each other
     * @param  <type> $string       - the string
     * @param  <type> $charToRemove - char to remove
     * @param  <type> $max          - max occurence
     * @param  <type> $replacement  - replacement
     * @return <type>
     */
    public static function removeRepetitiveChar($string,$charToRemove="",$max=0,$replacement="")
    {
        return preg_replace("/($charToRemove{{$max},})/",$replacement,$string);
    }
//------------------------------------------------------------------------------

    /**
     * To convert seconds into time -> HH:MM:SS. It will ommit the hours if not available
     * @param  int    $seconds
     * @return string HH:MM:SS
     */
    public static function seconds2Time($secs)
    {
    return ( floor($secs/3600) ? (str_pad(floor($secs/3600),2,"0",STR_PAD_LEFT).":") : "").
        str_pad(floor(($secs%3600)/60),2,"0",STR_PAD_LEFT).":".
        str_pad($secs%60,2,"0",STR_PAD_LEFT);
    }

    /**
     * Convert a time HH:MM:SS to seconds
     * @param  string $time -> HH:MM:SS
     * @return int
     */
    public static function time2Seconds($time)
    {
        $a = explode(":", $time);
        if(count($a) == 1)
            $r = $a[0];
       else if(count($a) == 2)
            $r = 60*$a[0] + 1*$a[1];
        else
            $r = 60*60*$a[0] + 60*$a[1] + 1*$a[2];
        return $r;
    }
//------------------------------------------------------------------------------
    /**
     * To format a number and return it as K,M,T
     * @param <type> $number
     * @param <type> $decimals
     * @return <type>
     */
    public static function number_format_HumanReadable($number,$decimals=0)
    {
      if($number<1000)
          return number_format($number);
      $countSize = array('','K','M','T','G',"Whoa");
        $count=0;
        while($count<3)
        if ($number>1000) {
            $number=$number/1000;
            $count++;
        } else {
            break;
        }
         $number = number_format($number,$decimals);
       return "{$number}{$countSize[$count]}";
    }

//------------------------------------------------------------------------------
    /**
     * Array Multi sort wi
     * $arr2 = array_msort($arr1, array('name'=>array(SORT_DESC,SORT_REGULAR), 'cat'=>SORT_ASC));
     * @param  <type> $array
     * @param  <type> $cols
     * @return Array
     * http://php.net/manual/en/function.array-multisort.php
     */
    public static function array_msort($array, $cols)
    {
        $colarr = array();
        foreach ($cols as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) {
                $colarr[$col]['_'.$k] = strtolower($row[$col]);
            }
        }
        $params = array();
        foreach ($cols as $col => $order) {

            $params[] =&$colarr[$col];
            $order=(array) $order;
            foreach ($order as $order_element) {
                //pass by reference, as required by php 5.3
                $params[]=&$order_element;
            }
        }
        call_user_func_array('array_multisort', $params);
        $ret = array();
        $keys = array();
        $first = true;
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
                if ($first) {
                    $keys[$k] = substr($k,1);
                }
                $k = $keys[$k];

                if (!isset($ret[$k])) {
                    $ret[$k] = $array[$k];
                }

                $ret[$k][$col] = $array[$k][$col];
            }
            $first = false;
        }

        return $ret;
    }
//------------------------------------------------------------------------------
//------------------------------------------------------------------------------
    /**
     * To generarate date in the past. Will return an Array(0=>start time,1=>end time)
     * Can be used when making a date range query, and would like to get the stat and the end
     * @param  string    $date, relative format
     * @return Array(INT time start,INT time end)
     * @since: Sept 22 2010
     *
     * for $date date relative format -> http://www.php.net/manual/en/datetime.formats.relative.php
     *
     */
    public static function getPastDateRange($date="")
    {
        switch ($date) {

            // Today
            case "today":
                return array(
                  strtotime("today 00:00:00"),
                  strtotime("today 23:59:59")
                );
            break;

            case "yesterday":
                return array(
                  strtotime("yesterday 00:00:00"),
                  strtotime("yesterday 23:59:59")
                );
            break;

            case "last week":
                return array(
                  strtotime("last week monday 00:00:00"),
                  strtotime("last week sunday 23:59:59")
                );
            break;

            case "last month":
                return array(
                  strtotime("first day of last month 00:00:00"),
                  strtotime("last day of last month 23:59:59")
                );
            break;

            case "this month":
                return array(
                  strtotime("first day of this month 00:00:00"),
                  strtotime("last day of this month 23:59:59")
                );
            break;

        /**
         * All other dates with 'ago'
         * To be used as: 4 days ago,3months ago
         * Will return to today since that $date
         */
            default:
                if(preg_match("/ago/i",$date))

                    return array(
                      strtotime("{$date} 00:00"),
                      strtotime("today 23:59:59")
                    );

                // return toay
                else
                     return array(
                      strtotime("today 00:00"),
                      strtotime("today 23:59:59")
                    );
            break;
        }
    }
//------------------------------------------------------------------------------

    /**
     * To calculate distance between to coordinaces
     * @param  <type> $lat1
     * @param  <type> $lon1
     * @param  <type> $lat2
     * @param  <type> $lon2
     * @param  <type> $unit (M = miles,K=kilometers,N=nautical)
     * @return <type>
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2, $unit="M")
    {
          $theta = $lon1 - $lon2;
          $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
          $dist = acos($dist);
          $dist = rad2deg($dist);
          $miles = $dist * 60 * 1.1515;
          $unit = strtoupper($unit);
          if ($unit == "K")
            return ($miles * 1.609344);
          else if ($unit == "N")
              return ($miles * 0.8684);
          else
              return $miles;

    }

//------------------------------------------------------------------------------

    /**
     * Read data in array, based on dotNotationKeys
     * @param  array  $Data
     * @param  String $dotNotationKeys - the dot notation, i.e, "key.subkey.subsubkey"
     * @param  mixed  $emptyValue      - A value to return if dotNotArg doesnt find any match
     * @return Mixed: Array, String, Numeric
     * @example
     *  $A = array("location"=>array("City"=>"Charlotte","ZipCode"=>25168));
     *  getArrayDotNotationValue($A,"location.ZipCode")
     *  -> 25168
     */
    public static function getArrayDotNotationValue(Array $Data, $dotNotationKeys = ".", $emptyValue = "")
    {
        // Eliminate the last dot
        $dotNotationKeys = preg_replace("/\.$/","",$dotNotationKeys);
        if(!$dotNotationKeys)
            return $Data;

        $dotKeys = explode(".",$dotNotationKeys);
        foreach ($dotKeys as $key) {
            if (!isset($Data[$key]))
                return $emptyValue;
            $Data = $Data[$key];
        }
        return $Data;
    }

    /**
    * To set value in array with dot notation
    * @param type $root
    * @param type $dotNotationKeys
    * @param type $value
    */
    public static function setArrayToDotNotation(&$root, $dotNotationKeys, $value)
    {
        $keys = explode('.', $dotNotationKeys);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($root[$key])) {
                $root[$key] = array();
            }
            $root = &$root[$key];
        }

        $key = reset($keys);
        $root[$key] = $value;
    }

    /**
     * Convert an array to dot notation
     * @param  type $Arr
     * @return type
     */
    public static function arrayToDotNotation($Arr)
    {
        $d2 = key($Arr);
        $val = "";
            while (count($Arr,COUNT_RECURSIVE) > 1) {
                $Arr = array_shift($Arr);
                if (is_array($Arr)) {
                    $d2 .=".".key($Arr);
                    $val = current($Arr);
                } else {
                $val = $Arr;
                break;
                }
            }
        return array($d2=>$val);
    }
//------------------------------------------------------------------------------

    /**
     * To extend an array with another array by merging the data and update them with new a
     * @param  Array $a - The original array
     * @param  Array $b - The array to merge with
     * @return Array
     */
    public static function arrayExtend($a, $b)
    {
        foreach ($b as $k => $v) {
            if (is_array($v)) {
                $a[$k] = (!isset($a[$k])) ? $v : self::arrayExtend($a[$k], $v);
            } else {
                $a[$k] = $v;
            }
        }
        return $a;
    }

    /**
     * To flatten multi dimensional array into a flat one. Also preserve the key
     * @param  array $ar
     * @param  Array $f  - am array to extend to
     * @return mixed Arr- boolean
     * @since July 22 2012
     */
    public function arrayFlatten(Array $ar, $f = array())
    {
        foreach ($ar as $k=>$v) {
            if(is_array($v)) {
                $f= self::arrayFlatten($v,$f);
            } else {
                $f[$k]=$v;
            }
        }
        return $f;
    }

    /**
     * Camelize
     * @param  type $string
     * @param  type $pascalCase - if true = CamelCase, else camelCase
     * @return type
     */
    public static function camelize($string, $pascalCase = false)
    {
        $string = str_replace(array('-', '_'), ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
        return ($pascalCase) ? $string : lcfirst($string);
    }

    /**
     * To convert camelized case to underscore
     * @param  type   $str - ie: HelloWorld = Hello_World
     * @return string
     */
    public static function toUnderscore($str)
    {
        $str = trim(ucwords($str));
        return preg_replace("/(^_|\s)/","", preg_replace("/([a-z])?([A-Z])/",'$1_$2',$str));
    }

    /**
     * To dasherize
     * HelloWorld = Hello-World
     * @param type $str
     * @return type
     */
    public static function dasherize($str)
    {
        return str_replace("_","-", self::toUnderscore($str));
    }

    /**
     * All the timezone list by region
     * @return Array
     */
    public static function TimeZoneList()
    {
        $regions = array(
            'America' => \DateTimeZone::AMERICA,
            'Africa' => \DateTimeZone::AFRICA,
            'Antarctica' => \DateTimeZone::ANTARCTICA,
            'Asia' => \DateTimeZone::ASIA,
            'Atlantic' => \DateTimeZone::ATLANTIC,
            'Europe' => \DateTimeZone::EUROPE,
            'Indian' => \DateTimeZone::INDIAN,
            'Pacific' => \DateTimeZone::PACIFIC
        );
        foreach ($regions as $name => $mask)
            $tzlist[$name] = \DateTimeZone::listIdentifiers($mask);

        return $tzlist;
    }


    /**
     * Get json last error after json_decode()
     *
     * @return Array if error or FALSE
     */
    public static function getJsonLastError()
    {
        $errorCode = json_last_error();
        switch ($errorCode) {
            case JSON_ERROR_NONE:
                return false;
                break;
            case JSON_ERROR_DEPTH:
                $msg = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $msg =  'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $msg =  'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $msg =  'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $msg =  'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $msg = 'Unknown error';
                break;
        }
        return array(
          "code" => $errorCode,
          "message" => $msg
        );
    }

    /**
     * To get the total pages from set values in pagination
     *
     * @param int $totalItems
     * @param int $itemsPerPage
     * @return int
     */
    public static function paginationGetTotalPages($totalItems, $itemsPerPage)
    {
        return @ceil($totalItems / $itemsPerPage);
    }


    /**
     * Get the max limit in a pagination
     *
     * @param int $pageNumber
     * @param inte $itemsPerPage
     * @return int
     */
    public static function paginationGetMaxLimit($pageNumber = 1, $itemsPerPage)
    {
        return (($pageNumber - 1) * $itemsPerPage);
    }


    /**
     * Get the min limit in a pagination
     *
     * @param int $pageNumber
     * @param inte $itemsPerPage
     * @return int
     */
    public static function paginationGetMinLimit($pageNumber = 1, $itemsPerPage)
    {
        return ($pageNumber <= 1) ? 0 : ((($pageNumber -1) * $itemsPerPage) - 1);
    }


    /**
     * To strip html comments.
     * But will leave conditionals comments such as <!-- [if IE 7]><![endif]-->
     *
     * @param string $content
     * @return string
     */
    public static function stripHtmlComments($content)
    {
        $stripHtmlCommentsRegex = "/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/Uis";
        return preg_replace($stripHtmlCommentsRegex, "", $content);
    }

    /**
     * To generate a nonce token
     *
     * @param int $size - 32bytes = 256bit
     * @return string
     */
    public static function getNonce($size = 32)
    {
        $ret="";
        for ($x=0; $x<$size; $x++) {
            $ret.=chr(mt_rand(0, 255));
        }
        return base64_encode($ret);
    }
}
