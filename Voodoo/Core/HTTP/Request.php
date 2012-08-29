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
 * @name        Core\HTTP\Request
 * @desc        To read and sanitize POST/GET/REQUEST and COOKIE vars
 */


namespace Voodoo\Core\HTTP;

use Voodoo\Core\Exception;

class Request{

    private $Data = array();

    /**
     * Bool to protect against XSS
     * @var type 
     */
    public static $XSSProof = false;

    /**
     * Constructor
     * 
     * @param string $method - The request method
     */
    public function __construct($method = "REQUEST"){
        

        switch(strtoupper($method)){
            case "POST":
                $data = $_POST;
            break;
        
            case "GET":
                $data = $_GET;
            break;
        
            case "COOKIE":
                $data = $_COOKIE;
            break;
        
            default:
                $data = $_REQUEST;
            break;
        }
        
        
        /**
         * Sanitize the data 
         */
        if(is_array($data))
           $this->Data = $this->sanitize($data);

    }
    
    
    // Disable __set
    final public function __set($key,$val){}
    
    
    /**
     * Get data
     * @param type $key
     * @return type 
     */
    public function __get($key){
        return
            $this->get($key);
    }
    
    /**
     * Merge some data in the request
     * @param array $Data 
     */
    public function merge(Array $Data){
        $this->Data = array_merge($Data,$this->Data);
        return
            $this;
    }
    
    /**
     * Get data
     * @param type $key
     * @return type 
     */
    public function get($key = ""){
        return
            ($key) ? $this->Data[$key] : $this->Data;
    }
    
//------------------------------------------------------------------------------


    
    /**
     * To check the request Method
     * @param string $method
     */
    public static function is($method="POST"){
        return (strtolower(self::getMethod()) === strtolower($method));
    }    
    
    /**
     * Return the request method
     * @return string
     */
    public static function getMethod(){
        return $_SERVER['REQUEST_METHOD'];
    }
    
    
    /**
        * Verify is the access is from ajax.
        * @return bool
        */
    public static function isAjax(){
        return (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=="xmlhttprequest") 
               ? true : false;
    }    
//------------------------------------------------------------------------------

    /**
     * Too sanitize data
     * @param array $Data
     * @return Array
     */
    public function sanitize(Array $Data){
        
        $Sane = array();
        
        foreach($Data as $K=>$V){
            
            $K = $this->cleanKeys($K);
            
            if(is_array($V))
                $Sane[$K] = $this->sanitize($V);
            
            else
               $Sane[$K] = $this->cleanData($V);
        }

        return $Sane;
    }
    
    
    /**
     * Clean up the key
     * @return type 
     */
    public function cleanKeys($key){

        if(!preg_match("/^[\w:\-\/]+$/i",$key)){
            throw new Exception("Disallowed Key name: {$key}");
        }
        
        return $key;

    }
    
    
    protected function cleanData($str){
        if($this->$XSSProof)
            $str = $this->xssClean($str);
                
        return 
           preg_replace("/\015\012|\015|\012/", "\n", stripslashes($str));
    }
    
    
    /**
     * Get HTTP Code Statis
     * @param type $status
     * @return type 
     */
    public static function getHTTPCode($status){
            // these could be stored in a .ini file and loaded
            // via parse_ini_file()... however, this will suffice
            // for an example
            $statusCodes = array(
                100 => 'Continue',
                101 => 'Switching Protocols',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content Found', // use 204 instead of 404 when server response ok, but not the content requested
                205 => 'Reset Content',
                206 => 'Partial Content',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                306 => '(Unused)',
                307 => 'Temporary Redirect',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                420 => 'Rate Limit Reached',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported'
            );

            return ($statusCodes[$status]) ? : '';
    }  
    
    
//------------------------------------------------------------------------------

    /**
         * I, MARDIX, DIDN'T CREATE THIS PIECE. LOL. SO TO WHOEVER MADE IT, THANK YOU AND CREDIT TO YOU
         * 
         * (I kept the original comment from the author)
	 * XSS Clean
	 *
	 * Sanitizes data so that Cross Site Scripting Hacks can be
	 * prevented.� This function does a fair amount of work but
	 * it is extremely thorough, designed to prevent even the
	 * most obscure XSS attempts.� Nothing is ever 100% foolproof,
	 * of course, but I haven't been able to get anything passed
	 * the filter.
	 *
	 * Note: This function should only be used to deal with data
	 * upon submission.� It's not something that should
	 * be used for general runtime processing.
	 *
	 * This function was based in part on some code and ideas I
	 * got from Bitflux: http://blog.bitflux.ch/wiki/XSS_Prevention
	 *
	 * To help develop this script I used this great list of
	 * vulnerabilities along with a few other hacks I've
	 * harvested from examining vulnerabilities in other programs:
	 * http://ha.ckers.org/xss.html
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function xssClean($str){
		/*
		 * Remove Null Characters
		 *
		 * This prevents sandwiching null characters
		 * between ascii characters, like Java\0script.
		 *
		 */
		$str = preg_replace('/\0+/', '', $str);
		$str = preg_replace('/(\\\\0)+/', '', $str);

		/*
		 * Validate standard character entities
		 *
		 * Add a semicolon if missing.  We do this to enable
		 * the conversion of entities to ASCII later.
		 *
		 */
		$str = preg_replace('#(&\#?[0-9a-z]+)[\x00-\x20]*;?#i', "\\1;", $str);

		/*
		 * Validate UTF16 two byte encoding (x00)
		 *
		 * Just as above, adds a semicolon if missing.
		 *
		 */
		$str = preg_replace('#(&\#x?)([0-9A-F]+);?#i',"\\1\\2;",$str);

		/*
		 * URL Decode
		 *
		 * Just in case stuff like this is submitted:
		 *
		 * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
		 *
		 * Note: Normally urldecode() would be easier but it removes plus signs
		 *
		 */
		$str = preg_replace("/(%20)+/", '9u3iovBnRThju941s89rKozm', $str);
		$str = preg_replace("/%u0([a-z0-9]{3})/i", "&#x\\1;", $str);
		$str = preg_replace("/%([a-z0-9]{2})/i", "&#x\\1;", $str);
		$str = str_replace('9u3iovBnRThju941s89rKozm', "%20", $str);

		/*
		 * Convert character entities to ASCII
		 *
		 * This permits our tests below to work reliably.
		 * We only convert entities that are within tags since
		 * these are the ones that will pose security problems.
		 *
		 */

		$str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", array(self, '_attribute_conversion'), $str);

		$str = preg_replace_callback("/<([\w]+)[^>]*>/si", array(self, '_html_entity_decode_callback'), $str);

		/*
		 * Convert all tabs to spaces
		 *
		 * This prevents strings like this: ja	vascript
		 * NOTE: we deal with spaces between characters later.
		 * NOTE: preg_replace was found to be amazingly slow here on large blocks of data,
		 * so we use str_replace.
		 *
		 */

		$str = str_replace("\t", " ", $str);

		/*
		 * Not Allowed Under Any Conditions
		 */
		$bad = array(
						'document.cookie'	=> '[removed]',
						'document.write'	=> '[removed]',
						'.parentNode'		=> '[removed]',
						'.innerHTML'		=> '[removed]',
						'window.location'	=> '[removed]',
						'-moz-binding'		=> '[removed]',
						'<!--'			=> '&lt;!--',
						'-->'			=> '--&gt;',
						'<!CDATA['		=> '&lt;![CDATA['
					);

		foreach ($bad as $key => $val)
		{
			$str = str_replace($key, $val, $str);
		}

		$bad = array(
						"javascript\s*:"	=> '[removed]',
						"expression\s*\("	=> '[removed]', // CSS and IE
						"Redirect\s+302"	=> '[removed]'
					);

		foreach ($bad as $key => $val)
		{
			$str = preg_replace("#".$key."#i", $val, $str);
		}

		/*
		 * Makes PHP tags safe
		 *
		 *  Note: XML tags are inadvertently replaced too:
		 *
		 *	<?xml
		 *
		 * But it doesn't seem to pose a problem.
		 *
		 */
		$str = str_replace(array('<?php', '<?PHP', '<?', '?'.'>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);

		/*
		 * Compact any exploded words
		 *
		 * This corrects words like:  j a v a s c r i p t
		 * These words are compacted back to their correct state.
		 *
		 */
		$words = array('javascript', 'expression', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');
		foreach ($words as $word)
		{
			$temp = '';
			for ($i = 0; $i < strlen($word); $i++)
			{
				$temp .= substr($word, $i, 1)."\s*";
			}

			// We only want to do this when it is followed by a non-word character
			// That way valid stuff like "dealer to" does not become "dealerto"
			$str = preg_replace('#('.substr($temp, 0, -3).')(\W)#ise', "preg_replace('/\s+/s', '', '\\1').'\\2'", $str);
		}

		/*
		 * Remove disallowed Javascript in links or img tags
		 */
		do
		{
			$original = $str;

			if ((version_compare(PHP_VERSION, '5.0', '>=') === TRUE && stripos($str, '</a>') !== FALSE) OR
				 preg_match("/<\/a>/i", $str))
			{
				$str = preg_replace_callback("#<a.*?</a>#si", array($this, '_js_link_removal'), $str);
			}

			if ((version_compare(PHP_VERSION, '5.0', '>=') === TRUE && stripos($str, '<img') !== FALSE) OR
				 preg_match("/img/i", $str))
			{
				$str = preg_replace_callback("#<img.*?".">#si", array($this, '_js_img_removal'), $str);
			}

			if ((version_compare(PHP_VERSION, '5.0', '>=') === TRUE && (stripos($str, 'script') !== FALSE OR stripos($str, 'xss') !== FALSE)) OR
				 preg_match("/(script|xss)/i", $str))
			{
				$str = preg_replace("#</*(script|xss).*?\>#si", "", $str);
			}
		}
		while($original != $str);

		unset($original);

		/*
		 * Remove JavaScript Event Handlers
		 *
		 * Note: This code is a little blunt.  It removes
		 * the event handler and anything up to the closing >,
		 * but it's unlikely to be a problem.
		 *
		 */
		$event_handlers = array('onblur','onchange','onclick','onfocus','onload','onmouseover','onmouseup','onmousedown','onselect','onsubmit','onunload','onkeypress','onkeydown','onkeyup','onresize', 'xmlns');
		$str = preg_replace("#<([^>]+)(".implode('|', $event_handlers).")([^>]*)>#iU", "&lt;\\1\\2\\3&gt;", $str);

		/*
		 * Sanitize naughty HTML elements
		 *
		 * If a tag containing any of the words in the list
		 * below is found, the tag gets converted to entities.
		 *
		 * So this: <blink>
		 * Becomes: &lt;blink&gt;
		 *
		 */
		$str = preg_replace('#<(/*\s*)(alert|applet|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title|xml|xss)([^>]*)>#is', "&lt;\\1\\2\\3&gt;", $str);

		/*
		 * Sanitize naughty scripting elements
		 *
		 * Similar to above, only instead of looking for
		 * tags it looks for PHP and JavaScript commands
		 * that are disallowed.  Rather than removing the
		 * code, it simply converts the parenthesis to entities
		 * rendering the code un-executable.
		 *
		 * For example:	eval('some code')
		 * Becomes:		eval&#40;'some code'&#41;
		 *
		 */
		$str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);

		/*
		 * Final clean up
		 *
		 * This adds a bit of extra precaution in case
		 * something got through the above filters
		 *
		 */
		$bad = array(
						'document.cookie'	=> '[removed]',
						'document.write'	=> '[removed]',
						'.parentNode'		=> '[removed]',
						'.innerHTML'		=> '[removed]',
						'window.location'	=> '[removed]',
						'-moz-binding'		=> '[removed]',
						'<!--'			=> '&lt;!--',
						'-->'			=> '--&gt;',
						'<!CDATA['		=> '&lt;![CDATA['
					);

		foreach ($bad as $key => $val)
		{
			$str = str_replace($key, $val, $str);
		}

		$bad = array(
						"javascript\s*:"	=> '[removed]',
						"expression\s*\("	=> '[removed]', // CSS and IE
						"Redirect\s+302"	=> '[removed]'
					);

		foreach ($bad as $key => $val)
		{
			$str = preg_replace("#".$key."#i", $val, $str);
		}


		//log_message('debug', "XSS Filtering completed");
		return $str;
	}    

}
