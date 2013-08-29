<?php
function t($s, $l = 'de') {
	include_once $l.'.trans.php';
	return (isset($translation[$s]) ? $translation[$s] : $s);
}