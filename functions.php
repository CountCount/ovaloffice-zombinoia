<?php
function i2c($n) {
	switch ($n) {
		case 0: return '#393';
		case 1: return '#6c0';
		case 2: return '#9c0';
		case 3: return '#fc0';
		case 4: return '#f90';
		case 5: return '#f60';
		case 6: return '#f33';
		case 7: return '#f30';
		case 8: return '#f00';
		case 9: return '#06c';
		case 10: return '#06c';
		case 11: return '#06c';
		case 12: return '#06c';
		case 13: return '#369';
		case 14: return '#369';
		case 15: return '#369';
		case 16: return '#369';
		case 17: return '#369';
		case 18: return '#369';
		case 19: return '#66c';
		case 20: return '#66c';
		case 21: return '#66c';
		case 22: return '#66c';
		case 23: return '#66c';
		case 24: return '#66c';
		case 25: return '#93c';
		case 26: return '#93c';
		case 27: return '#93c';
		case 28: return '#93c';
		case 29: return '#93c';
		case 30: return '#93c';
		default: return '#90c';
		
	}
}

function dv_i2c($n) {
	switch ($n) {
		case 0: return 'transparent'; //'#475613';
		case 1: return '#8f990b';
		case 2: return '#8f7324';
		case 3: return '#8f7324';
		case 4: return '#8e340b';
		case 5: return '#8e340b';
		case 6: return '#8e340b';
		case 7: return '#8e340b';
		case 8: return '#524053';
		case 9: return '#524053';
		case 10: return '#524053';
		case 11: return '#524053';
		case 12: return '#524053';
		case 13: return '#524053';
		case 14: return '#524053';
		case 15: return '#524053';
		case 16: return '#524053';
		case 17: return '#524053';
		case 18: return '#524053';
		case 19: return '#524053';
		case 20: return '#524053';
		case 21: return '#524053';
		case 22: return '#524053';
		case 23: return '#524053';
		case 24: return '#524053';
		case 25: return '#524053';
		case 26: return '#524053';
		case 27: return '#524053';
		case 28: return '#524053';
		case 29: return '#524053';
		case 30: return '#524053';
		default: return 'transparent'; //'#475613';	
	}
}

function dv_i2ca($n,$a) {
	switch ($n) {
		case 0: return 'rgba(0,201,0,'.$a.')';
		case 1: return 'rgba(143,153,11,'.$a.')';
		case 2: return 'rgba(143,115,36,'.$a.')';
		case 3: return 'rgba(143,115,36,'.$a.')';
		case 4: return 'rgba(142,52,11,'.$a.')';
		case 5: return 'rgba(142,52,11,'.$a.')';
		case 6: return 'rgba(142,52,11,'.$a.')';
		case 7: return 'rgba(142,52,11,'.$a.')';
		case 8: return 'rgba(82,64,83,'.$a.')';
		case 9: return 'rgba(82,64,83,'.$a.')';
		case 10: return 'rgba(82,64,83,'.$a.')';
		case 11: return 'rgba(82,64,83,'.$a.')';
		case 12: return 'rgba(82,64,83,'.$a.')';
		case 13: return 'rgba(82,64,83,'.$a.')';
		case 14: return 'rgba(82,64,83,'.$a.')';
		case 15: return 'rgba(82,64,83,'.$a.')';
		case 16: return 'rgba(82,64,83,'.$a.')';
		case 17: return 'rgba(82,64,83,'.$a.')';
		case 18: return 'rgba(82,64,83,'.$a.')';
		case 19: return 'rgba(82,64,83,'.$a.')';
		case 20: return 'rgba(82,64,83,'.$a.')';
		case 21: return 'rgba(82,64,83,'.$a.')';
		case 22: return 'rgba(82,64,83,'.$a.')';
		case 23: return 'rgba(82,64,83,'.$a.')';
		case 24: return 'rgba(82,64,83,'.$a.')';
		case 25: return 'rgba(82,64,83,'.$a.')';
		case 26: return 'rgba(82,64,83,'.$a.')';
		case 27: return 'rgba(82,64,83,'.$a.')';
		case 28: return 'rgba(82,64,83,'.$a.')';
		case 29: return 'rgba(82,64,83,'.$a.')';
		case 30: return 'rgba(82,64,83,'.$a.')';
		default: return 'rgba(71,86,19,'.$a.')';	
	}
}

function i2e($n) {
	switch ($n) {
		case 0: return '#393';
		case 1: return '#6c0';
		case 2: return '#9c0';
		case 3: return '#f90';
		case 4: return '#06c';
		default: return '#90c';
		
	}
}

function dv_i2e($n) {
	switch ($n) {
		case 0: return '#475613';
		case 1: return '#8f990b';
		case 2: return '#8f7324';
		case 3: return '#8e340b';
		case 4: return '#524053';
		default: return '#475613';
		
	}
}


function i2w($n, $min = 0, $max = 32) {
	if ( is_null($n) ) { return 'transparent'; }
	if ( $n > $max ) { $n = $max; }
	$minWL = 450;
	$maxWL = 730;
	$gamma = 1;
	$intmax = 255;
	/*abs($n - (($max - $min)/2))*/
	#$wl = ($n - $min) / ($max - $min) * ($maxWL - $minWL) + $minWL;
	$modWL = ($maxWL - $minWL)/2;
	$mod = ($max - $min)/2;
	$wl = $modWL - ((($n - $mod) / ($max - $mod)) * ($maxWL - $modWL));
	
	if ( $wl < 439 ) {
		$r = -($wl - 440) / (440 - 380);
		$g = 0;
		$b = 1;
	}
	elseif ( $wl < 489 ) {
		$r = 0;
		$g = ($wl - 440) / (490 - 440);
		$b = 1;
	}
	elseif ( $wl < 509 ) {
		$r = 0;
		$g = 1;
		$b = -($wl - 510) / (510 - 490);
	}
	elseif ( $wl < 579 ) {
		$r = ($wl - 510) / (580 - 510);
		$g = 1;
		$b = 0;
	}
	elseif ( $wl < 644 ) {
		$r = 1;
		$g = -($wl - 645) / (645 - 580);
		$b = 0;
	}
	elseif ( $wl < 780 ) {
		$r = 1;
		$g = 0;
		$b = 0;
	}
	
	return 'rgb('.floor($r*$gamma*$intmax).','.floor($g*$gamma*$intmax).','.floor($b*$gamma*$intmax).')';
}

function get_dir ($zx,$zy,$tx,$ty) {
	$x = $zx - $tx;
	$y = $zy - $ty;
	if ($x == 0 && $y == 0) return 0;
	if ($x > floor($y/2) && $y > floor($x/2)) return 2;
	if ($x > floor(-$y/2) && -$y > floor($x/2)) return 4;
	if (-$x > floor($y/2) && $y > floor(-$x/2)) return 8;
	if (-$x > floor(-$y/2) && -$y > floor(-$x/2)) return 6;
	if (abs($x) > abs($y)) return ($x > 0) ? 3 : 7;
	return ($y > 0) ? 1 : 5;
}
function get_dir_name ($zx,$zy,$tx,$ty) {
	$x = $zx - $tx;
	$y = $ty - $zy;
	if ($x == 0 && $y == 0) return t('CITY');
	if ($x > floor($y/2) && $y > floor($x/2)) return t('NEx');
	if ($x > floor(-$y/2) && -$y > floor($x/2)) return t('SEx');
	if (-$x > floor($y/2) && $y > floor(-$x/2)) return t('NWx');
	if (-$x > floor(-$y/2) && -$y > floor(-$x/2)) return t('SWx');
	if (abs($x) > abs($y)) return ($x > 0) ? t('Ex') : t('Wx');
	return ($y > 0) ? t('Nx') : t('Sx');
}
function get_dir_abbr ($zx,$zy,$tx,$ty) {
	$x = $zx - $tx;
	$y = $ty - $zy;
	if ($x == 0 && $y == 0) return t('CITY');
	if ($x > floor($y/2) && $y > floor($x/2)) return t('NE');
	if ($x > floor(-$y/2) && -$y > floor($x/2)) return t('SE');
	if (-$x > floor($y/2) && $y > floor(-$x/2)) return t('NW');
	if (-$x > floor(-$y/2) && -$y > floor(-$x/2)) return t('SW');
	if (abs($x) > abs($y)) return ($x > 0) ? t('E') : t('W');
	return ($y > 0) ? t('N') : t('S');
}
function get_dir_color ($zx,$zy,$tx,$ty) {
	$x = $zx - $tx;
	$y = $zy - $ty;
	if ($x == 0 && $y == 0) return "transparent";
	if ($x > floor($y/2) && $y > floor($x/2)) return "#00f";
	if ($x > floor(-$y/2) && -$y > floor($x/2)) return "#00f";
	if (-$x > floor($y/2) && $y > floor(-$x/2)) return "#00f";
	if (-$x > floor(-$y/2) && -$y > floor(-$x/2)) return "#00f";
	if (abs($x) > abs($y)) return ($x > 0) ? "#f0f" : "#f0f";
	return ($y > 0) ? "#f0f" : "#f0f";
}
function get_dir_class ($zx,$zy,$tx,$ty) {
	$o = get_dir($zx,$zy,$tx,$ty);
	$adjacent = array(
		'n' => (get_dir($zx,$zy-1,$tx,$ty) == $o || $zy == 0 ? 0 : 1),
		#'e' => (get_dir($zx+1,$zy,$tx,$ty) == $o ? 0 : 1),
		#'s' => (get_dir($zx,$zy+1,$tx,$ty) == $o ? 0 : 1),
		'w' => (get_dir($zx-1,$zy,$tx,$ty) == $o || $zx == 0 ? 0 : 1),
	);
	return $adjacent;
}
function get_radius ($zx,$zy,$tx,$ty,$km = null,$ap = null) {
	if ( (is_null($km) || $km == 0) && (is_null($ap) || $ap == 0) ) {
		return null;
	}
	
	if ( !is_null($km) && $km > 0 ) {
		$func = 'get_km';
		$cv = $km;
	} elseif ( !is_null($ap) && $ap > 0 ) {
		$func = 'get_ap';
		$cv = $ap;
	}
	else {
		return null;
	}
	
	$o = $func($zx,$zy,$tx,$ty);
	$adjacent = array(
		'n' => (($func($zx,$zy-1,$tx,$ty) > $cv && $o > $cv) || ($func($zx,$zy-1,$tx,$ty) <= $cv && $o <= $cv) || $zy == 0 ? 0 : 1),
		'w' => (($func($zx-1,$zy,$tx,$ty) > $cv && $o > $cv) || ($func($zx-1,$zy,$tx,$ty) <= $cv && $o <= $cv) || $zx == 0 ? 0 : 1),
	);
	
	return $adjacent;
}
function get_km ($zx,$zy,$tx,$ty) {
	return round(sqrt(pow(abs($zx - $tx),2) + pow(abs($zy - $ty),2)));
}
function get_ap ($zx,$zy,$tx,$ty) {
	return abs($zx - $tx) + abs($zy - $ty);
}

function item_list() {
	$itemlist = array();
	$db = new Database();
	$sql = $db->query(' SELECT * FROM dvoo_items ORDER BY iid ');
	foreach ( $sql AS $res )
	{
		$itemlist[$res['iid']] = array(
			'name' => $res['iname'],
			'cat' => $res['icat'],
			'img' => $res['iimg']
		);
	}
	return $itemlist;
}

// jobs info
$jobs = array(
					'basic' => array(
						'name' => t('CITIZEN'),
						'img' => 'basic_suit',
						'kp' => 2,
					),
					'collec' => array(
						'name' => t('SCAVENGER'),
						'img' => 'pelle',
						'kp' => 2,
					),
					'guardian' => array(
						'name' => t('GUARDIAN'),
						'img' => 'shield',
						'kp' => 4,
					),
					'eclair' => array(
						'name' => t('SCOUT'),
						'img' => 'vest_on',
						'kp' => 2,
					),
					'' => array(
						'name' => t('CITIZEN_NOJOB'),
						'img' => 'pet_chick',
						'kp' => 0,
					),
					'tamer' => array(
						'name' => t('TAMER'),
						'img' => 'tamed_pet',
						'kp' => 2,
					),
					'hunter' => array(
						'name' => t('HUNTER'),
						'img' => 'surv_book',
						'kp' => 2,
					),					
					'tech' => array(
						'name' => t('TECH'),
						'img' => 'keymol',
						'kp' => 2,
					),
				);
				
// items for autocomplete
$aitems = array();
$q = ' SELECT iname AS name FROM dvoo_items ORDER BY iname ASC ';
$r = $db->query($q);
foreach ( $r AS $o ) {
	$aitems[] = $o['name'];
}