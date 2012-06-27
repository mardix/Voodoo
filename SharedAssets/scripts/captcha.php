<?php

/**
 * -----------------------------------------------------------------------------
 * @author      Mardix @ http://github.com/mardix | http://twitter.com/mardix 
 * @copyright   2010 - 2011 Mardix
 * @license     MIT
 *------------------------------------------------------------------------------
 * Captcha
 *
 * It will create a captcha image and output it
 *
 * Use the class Captcha
 *
 * To create the image=> Captcha::generateImage();
 *
 * To verfiy the user input=> Captcha::valid();
 *
 * MyEZFW contain a shortcut to show the captcha: <img src="/captcha.phg">
 * 
 */


$BASE_PATH = "../..";

include($BASE_PATH."/Application/init.php");


/**
 * Setup the colors to RGB
 * HEX Colors can be passed in the URL with the #
 * @last update: Jan 17 2011
 */
$Options = array();
if($_REQUEST["background"])
    $Options["colors"]["background"] = Utils::HEX2RGB($_REQUEST["background"]);
if($_REQUEST["text"])
    $Options["colors"]["text"] = Utils::HEX2RGB($_REQUEST["text"]);
if($_REQUEST["noise"])
    $Options["colors"]["noise"] = Utils::HEX2RGB($_REQUEST["noise"]);


Core\Captcha::generateImage();