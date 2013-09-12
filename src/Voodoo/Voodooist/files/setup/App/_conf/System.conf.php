;<?php exit(); // Always exit out, if file is called explicitely ?>
; NOTE: *.conf.php is an INI file
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
;
; VoodooPHP - _conf/System.ini.php
; 
; System.conf.php is a simple config file that contains system wide configuration
;
; To access data from this file use the Config class as so:
; Voodoo\Core\Config::System()->get($dotNotation);
; where $dotNotation is a string with dot notation to access data, like: 'x.y'
;
; i.e: $timezone = Voodoo\Core\Config::System()->get("timezone");
; 
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

; By default the url will contain question mark: ie: site.com/?Controller/action
; If your server has MOD_REWRITE enabled for apache, or something similar nginx or other server
; Set it to false.
; When false, url will be site.com/Index/hello
useUrlQuestionMark = false

; Set the timezone of your system. ie: America/New_York    
timezone = "America/New_York" 

; Error reporting
errorReporting = E_ALL & ~E_NOTICE 
