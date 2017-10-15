<?php

if ((!function_exists('mb_str_replace')) &&
	(function_exists('mb_substr')) && (function_exists('mb_strlen')) && (function_exists('mb_strpos'))) {
	function mb_str_replace($search, $replace, $subject) {
		if(is_array($subject)) {
			$ret = array();
			foreach($subject as $key => $val) {
				$ret[$key] = mb_str_replace($search, $replace, $val);
			}
			return $ret;
		}

		foreach((array) $search as $key => $s) {
			if($s == '') {
				continue;
			}
			$r = !is_array($replace) ? $replace : (array_key_exists($key, $replace) ? $replace[$key] : '');
			$pos = mb_strpos($subject, $s, 0, 'UTF-8');
			while($pos !== false) {
				$subject = mb_substr($subject, 0, $pos, 'UTF-8') . $r . mb_substr($subject, $pos + mb_strlen($s, 'UTF-8'), 65535, 'UTF-8');
				$pos = mb_strpos($subject, $s, $pos + mb_strlen($r, 'UTF-8'), 'UTF-8');
			}
		}
		return $subject;
	}
}