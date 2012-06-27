<?php
/**
 * email2png.php
 *
 * A simple file that convert an email address to png, to prevent bot from getting an email address.
 *
 * How to use it?
 * <img src="./email2png.php?u=username&d=yahoo.com" border="0">
 *   u=username
 *   d=domain name
 *     * no @ necessary
 *
 * @required: GD library
 *
 * @since May 26, 2009
 */


// My Lib
/**
 * To validate an email address
 * @param <type> $str
 * @return <type>
 */
function validEmail($str){
 return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
}

$user = trim(stripslashes($_GET[u]));
$domain = trim(stripslashes($_GET[d]));

 $email = "{$user}@{$domain}";

   if(validEmail($email)){

      header("content-type: image/png");
      
        $len = strlen($email) * 8;
        
        $img=@imagecreate($len,20);

        $bgColor = imagecolorallocate($img,255,255,255); // White

        $txtColor = imagecolorallocate($img,55,103,122);

        imagestring($img,3,5,6,$email,$txtColor);

        imagepng($img);

        imagedestroy($img);
   }

  else
    die();
