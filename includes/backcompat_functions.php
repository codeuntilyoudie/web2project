<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
/*
* This file exists in order to identify individual functions which may not be
*   available in all versions of PHP.  Therefore, we have to wrap the
*   functionality in function_exists stuff and all that.  In the documentation
*   for each function, you must describe:
*
*    * the specific version of PHP or extension the regular function requires.
*
* During Minor releases, this file will grow only to shrink as Major releases
*   allow us to change minimum version for PHP compatibility.
*/

if (!function_exists('mb_strlen')) {
	/**
	 * mb_strlen()
	 * Alternative mb_strlen in case mb_string is not available
	 *
	 * @param mixed $str
	 * @return
	 */
	function mb_strlen($str) {
		global $locale_char_set;

		if (!$locale_char_set) {
			$locale_char_set = 'utf-8';
		}
		return $locale_char_set == 'utf-8' ? strlen(utf8_decode($str)) : strlen($str);
	}
}

if (!function_exists('mb_substr')) {
	/**
	 * mb_substr()
	 * Alternative mb_substr in case mb_string is not available
	 *
	 * @param mixed $str
	 * @param mixed $start
	 * @param mixed $length
	 * @return
	 */
	function mb_substr($str, $start, $length = null) {
		global $locale_char_set;

		if (!$locale_char_set) {
			$locale_char_set = 'utf-8';
		}
		if ($locale_char_set == 'utf-8') {
			return ($length === null) ?
				utf8_encode(substr(utf8_decode($str), $start)) :
				utf8_encode(substr(utf8_decode($str), $start, $length));
		} else {
			return ($length === null) ?
				substr($str, $start) :
				substr($str, $start, $length);
		}
	}
}

if (!function_exists('mb_strpos')) {
	/**
	 * mb_strpos()
	 * Alternative mb_strpos in case mb_string is not available
	 *
	 * @param mixed $str
	 * @return
	 */
	function mb_strpos($haystack, $needle, $offset = null) {
		global $locale_char_set;

		if (!$locale_char_set) {
			$locale_char_set = 'utf-8';
		}
		return $locale_char_set == 'utf-8' ? strpos(utf8_decode($haystack), utf8_decode($needle), $offset) : strpos($haystack, $needle, $offset);
	}
}

if(!function_exists('mb_str_replace')) {
    /**
     * mb_str_replace()
	 * Alternative mb_str_replace in case mb_string is not available
	 * (array aware from here http://www.php.net/manual/en/ref.mbstring.php)
     *
     * @param mixed $search : string or array of strings to be searched.
     * @param mixed $replace : string or array of the strings that will replace the searched string(s)
     * @param mixed $subject : string to be modified.
     * @return string with the replacements made
     */
    function mb_str_replace($search, $replace, $subject) {
        if(is_array($subject)) {
            $ret = array();
            foreach($subject as $key => $val) {
                $ret[$key] = mb_str_replace($search, $replace, $val);
            }
            return $ret;
        }

        foreach((array)$search as $key => $s) {
            if($s == '') {
                continue;
            }
            $r = !is_array($replace) ? $replace : (array_key_exists($key, $replace) ? $replace[$key] : '');
            $pos = mb_strpos($subject, $s);
            while($pos !== false) {
                $subject = mb_substr($subject, 0, $pos) . $r . mb_substr($subject, $pos + mb_strlen($s));
                $pos = mb_strpos($subject, $s, $pos + mb_strlen($r));
            }
        }
        return $subject;
    }
}

if(!function_exists('mb_trim')) {
	/**
	* mb_trim()
	* Alternative mb_trim in case mb_string solution is not available
	* (http://www.php.net/manual/en/ref.mbstring.php)
	*
	* Trim characters from either (or both) ends of a string in a way that is
	* multibyte-friendly.
	*
	* @param string
	* @param charlist list of characters to remove from the ends of this string.
	* @param boolean trim the left?
	* @param boolean trim the right?
	* @return String
	*/
	function mb_trim($string, $charlist='\\\\s', $ltrim=true, $rtrim=true) {
		$both_ends = $ltrim && $rtrim;

		$char_class_inner = preg_replace(
			array( '/[\^\-\]\\\]/S', '/\\\{4}/S' ),
			array( '\\\\\\0', '\\' ),
			$charlist
		);

		$work_horse = '[' . $char_class_inner . ']+';
		$ltrim && $left_pattern = '^' . $work_horse;
		$rtrim && $right_pattern = $work_horse . '$';

		if ($both_ends) {
			$pattern_middle = $left_pattern . '|' . $right_pattern;
		} elseif($ltrim) {
			$pattern_middle = $left_pattern;
		} else {
			$pattern_middle = $right_pattern;
		}
		return preg_replace("/$pattern_middle/usSD", '', $string);
	}
}

/*
* Make function htmlspecialchar_decode for older PHP versions
*/
if (!function_exists('htmlspecialchars_decode')) {
	trigger_error("The htmlspecialchars_decode function is in PHP core as of 5.1.0 so this will be removed in v3.0.", E_USER_NOTICE );
    function htmlspecialchars_decode($str) {
		return strtr($str, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	}
}


if (!function_exists('date_diff2')) {    
    /**
     * date_diff()
     * Alternative to date_diff if we're using PHP pre-5.3.0
     * http://www.php.net/manual/en/function.date-diff.php
     * 
     * @param DateTime
     * @param DateTime
     * @param string representing the desired unit
     * @return int in the desired unit
     * 
     * @todo TODO: This should return a DateInterval as the real date_diff does 
     *   instead of a simple int.
     * @todo TODO: The real date_diff supports months using 'M' but I'm going 
     *   to pass on that for now because I can't tell how they decide what a 
     *   "month" means..
     * 
     */
    function date_diff2(DateTime $date1, DateTime $date2, $units = 'D') {
        $timestamp1 = $date1->format('U');
        $timestamp2 = $date2->format('U');

        if ($timestamp1 == $timestamp2) {
            return 0;
        }

        $difference = $timestamp2 - $timestamp1;
        switch ($units) {
            case 'Y':                          // years
                $factor = 60 * 60 * 24 * 365;
                break;
            case 'W':                          // weeks
                $factor = 60 * 60 * 24 * 7;
                break;
            case 'D':                          // days
                $factor = 60 * 60 * 24;
                break;
            case 'H':                          // hours
                $factor = 60 * 60;
                break;
            case 'I':                          // minutes
                $factor = 60;
                break;
            case 'S':                          // seconds
            default:
                $factor = 1;
        }
        
        return round($difference / $factor, 0);
    }

    /**
     * DateInterval
     * Alternative to DateInterval if we're using PHP pre-5.3.0
     * http://www.php.net/manual/en/class.dateinterval.php
     * 
     * @todo Deprecate this as soon as possible.. web2project v4.0?
     */
    class DateInterval2 {
        public $y = 0;
        public $m = 0;
        public $d = 0;
        public $h = 0;
        public $i = 0;
        public $s = 0;
        public $invert = 0;
        public $days = 0;
        
        public function __construct($interval_spec) {
            if ('P' != $interval_spec[0] ) {
                // is this an exception?
            }

            $interval_spec = strtolower($interval_spec);
            $t_postion = strpos($interval_spec, 't');
            if (false !== $t_postion) {
                //replace the m right of the t with an i
                $interval_spec = substr($interval_spec, 0, $t_postion) . 
                    str_replace('m', 'i', substr($interval_spec, $t_postion + 1));
            }
            $units  = preg_split("/[\d+.]/", $interval_spec, null, 1);
            $values = preg_split("/[\D+.]/", $interval_spec);

            foreach ($units as $k => $unit) {
                switch($unit) {
                    case 'w':
                        $this->d = 7 * $values[$k];
                        break;
                    case 'y':
                    case 'm':
                    case 'd':
                    case 'h':
                    case 'i':
                    case 's':
                        $this->{$unit} = $values[$k];
                        break;
                    case 'p':
                    default:
                        //do nothing
                        break;
                }
            }
        }
        public function createFromDateString() {
            
        }
        public function format() {
            
        }
    }
}