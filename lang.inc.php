<?php
// Choose language to display at start
// current options: de, en
$l = 'es';

$f = 'lang.'.$l.'.php';
include_once $f;

// function to alter constant strings
function t($con, $sub = array()) {
	if ( !isset($con) || is_null($con) || $con == '' ) {
		return '';
	}
	if ( defined($con) ) {
		$ret = constant($con);
		
		if ( count($sub) > 0 ) {
			foreach ( $sub AS $k => $v ) {
				if ( is_array($v) ) {
					$rv = ((int) $v[0] == 1) ? $v[1] : $v[2];
					$ret = str_replace($k, $rv, $ret);
				}
				else {
					$ret = str_replace($k, $v, $ret);
				}
			}
		}
	}
	else {
		$ret = 'MISSING LANGUAGE STRING ['.$con.']';
	}
	
	return $ret;
}