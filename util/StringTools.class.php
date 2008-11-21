<?php
/**
 * StringTools contains static methods for manipulating strings.
 *
 * @version $Id$
 * @copyright 2005
 */
class StringTools {
	/**
	 * Removes characters that could potentially cause query problems and/or injection.
	 * Removes ;, ", \r, and \n
	 *
	 * @param string $text
	 * @return string
	 */
	static function removeBadMySQLChars($text) {
		$retVal = preg_replace('/[;"\r\n]/', "", $text);
		return $retVal;
	}

	/**
	 * Converts a date to a MySQL formatted date (Y-m-d)
	 * @param string $date
	 * @return string
	 */
	static function convertDateToMySQL($date) {
		$retVal = '';
		if(strlen($date) > 0) {
			$timestamp = strtotime($date);
			if($timestamp !== -1 && $timestamp !== false) {
				$retVal = date("Y-m-d", $timestamp);
			} else {
				$retVal = '';
			}
		}
		return $retVal;
	}

	/**
	 * Converts a date from MySQL to a given format.  Alias for <code>date('m/d/Y', strtotime($date))</code>
	 * @param string $date
	 * @param string $format
	 * @return string
	 */
	static function convertDateFromMySQL($date, $format='m/d/Y') {
		$retVal = '';
		if(strlen($date) > 0) {
			$timestamp = strtotime($date);
			if($timestamp !== -1 && $timestamp !== false) {
				$retVal = date($format, $timestamp);
			} else {
				$retVal = '';
			}
		}
		return $retVal;
	}

	/**
	 * Performs a debug backtrace that can be sent to the error log easily
	 * @param array $backtrace
	 * @return string
	 */
	static function getDebugBacktraceForLogs($backtrace = null) {
		$retVal = '';
		if(is_null($backtrace)) {
			$backtrace = debug_backtrace();
		}
		while(($arr_elmnt = array_shift($backtrace)) !== null) {
			if($retVal == '') {
				$retVal .= "\n\tfrom ";
			} else {
				$retVal .= "\n\tat ";
			}
			$retVal .= $arr_elmnt['function'] . "() called at [" . basename($arr_elmnt['file']) . ":" . $arr_elmnt['line'] . "]";
		}
		return $retVal;
	}

	/**
	 * Truncates the string making sure it does not exceed the character length including the trailing
	 * The trailing is only included if the string is truncated.
	 * @param string $str
	 * @param int $char_len
	 * @param string $trailing
	 * @return string
	 */
	static function truncate($str, $char_len = 30, $trailing = '...') {
		$ret_val = '';
		if(strlen($str) > $char_len) {
			$char_len -= strlen($trailing);
			$ret_val = substr($str, 0, $char_len) . $trailing;
		} else {
			$ret_val = $str;
		}
		return $ret_val;
	}

	/**
	 * Returns the Human Readable equivalent for filesizes
	 * @param integer $size
	 * @return string
	 */
	static function getHumanReadable($size){
		$i=0;
		$iec = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
		while (($size/1024)>1) {
			$size=$size/1024;
			$i++;
		}
		return substr($size,0,strpos($size,'.') + 4) . $iec[$i];
	}
	
	/**
	 * Returns the days from a seconds argument
	 * @param integer $seconds
	 * @param boolean $remove_days
	 * @return integer
	 */
	static function getDays($seconds){
		return floor($seconds / 86400);
	}
	
	/**
	 * Returns the hours from a seconds argument
	 * @param integer $seconds
	 * @param boolean $remove_days
	 * @return integer
	 */
	static function getHours($seconds, $remove_days = true){
		if ($remove_days) {
			$new_seconds = $seconds - (86400 * floor($seconds / 86400));
			return floor($new_seconds / 3600);
		} else {
			return floor($seconds / 3600);
		}
	}
	
	/**
	 * Returns the minutes from a seconds argument
	 * @param integer $seconds
	 * @param boolean $remove_hours
	 * @return integer
	 */
	static function getMinutes($seconds, $remove_hours = true){
		if ($remove_hours) {
			$new_seconds = $seconds - (3600 * floor($seconds / 3600));
			return floor($new_seconds / 60);
		} else {
			return floor($seconds / 60);
		}
	}
	
	/**
	 * Returns the seconds from a seconds argument
	 * @param integer $size
	 * @param boolean $remove_minutes
	 * @return integer
	 */
	static function getSeconds($seconds, $remove_minutes = true) {
		if ($remove_minutes) {
			$new_seconds = $seconds - (60 * floor($seconds / 60));
			return $new_seconds;
		} else {
			return $seconds;	
		}
	}
	
	/**
	 * Returns a formatted phone number as %d (%d) %d-%d
	 * @param string $phone 
	 * @param string $separator
	 * @param string $area_code_separator
	 * @return string
	 */
	static function formatPhone($phone) {
		// For phone formatting the format is usually (3 chars) 3 chars-4 chars, so for simplicity sake
		// we'll split the string as (3 chars) 3 chars-3 chars + 1 char.
		if ($phone != "") {
			// Since the last 4 digits should be together, shift off the last digit and we'll add it to the last triplet.
			$suffix = substr($phone, -1);
			// Now to split the string, we reverse the string, take off the first character (from above line) and split it 
			// by 3 character chunks (any remaining characters will be at the front - such as a 1).
			$phone_pieces = str_split(substr(strrev($phone), 1), 3);
			// And add the last character to the first triplet (remember that the phone pieces contains a backwards phone number
			$phone_pieces[0] = $suffix . $phone_pieces[0];
			// Now loop through the array and reverse (strrev) each element (so that it's in the right order) and build the format string
			$format = "";
			foreach ($phone_pieces as $key => $phone_piece) {
				if ($key == 0) {
					$format = "%s" . $format;
				} else if ($key == 1) {
					$format = "%s-" . $format;	
				} else if ($key == 2) {
					$format = "(%s) " . $format;
				} else if ($key == count($phone_pieces) - 1 && $key > 2) {
					$format = "+%s " . $format;
				} else {
					$format = "%s " . $format;
				}
				$phone_pieces[$key] = strrev($phone_pieces[$key]);
			}
			// Now we just have to reverse the array so that the 1st triplet (4 characters) is at the end
			$phone_pieces = array_reverse($phone_pieces);
			// And finally output the formatted string.
			return vsprintf($format, $phone_pieces);
		} else {
			return $phone;	
		}
	}
	
	/**
	 * underscored string to make into camelcase, first letter is left alone.
	 *
	 * @param underscored string $key
	 * @return camel cased string
	 */
	static function camelCase($key) {
		return preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",$key);
	}
}
?>