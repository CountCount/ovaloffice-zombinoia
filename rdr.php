<?php
include_once 'system.php';
$db = new Database();

$k = htmlspecialchars(strip_tags($_REQUEST['key']));

$q = ' SELECT c.id AS uid, c.name, r.ban, r.job, t.id AS tid, t.day, t.x, t.y FROM dvoo_citizens c INNER JOIN dvoo_town_citizens r ON r.citizen_id = c.id INNER JOIN dvoo_towns t ON t.id = r.town_id WHERE c.scode = "'.$k.'" ORDER BY t.id DESC LIMIT 1 ';
$r = $db->query($q);

if ( !is_array($r[0]) ) {
	print t('RADAR_ERROR');
	exit;
}

$uid = $r[0]['uid'];
$day = $r[0]['day'];
$tid = $r[0]['tid'];
$ban = $r[0]['ban'];
$job = $r[0]['job'];
$username = $r[0]['name'];
$tx = $r[0]['x'];
$ty = $r[0]['y'];

if ( $ban == 1 ) {
	print t('RADAR_BANNED');
}

$jobs = array(
	'eclair' => t('SCOUT'),
	'collec' => t('SCAVENGER'),
	'guardian' => t('GUARDIAN'),
	'basic' => t('CITIZEN'),
);

$fd = $_REQUEST;
$nr = $fd['nr'];
$nz = $fd['nz'];
$wr = $fd['wr'];
$wz = $fd['wz'];
$or = $fd['or'];
$oz = $fd['oz'];
$sr = $fd['sr'];
$sz = $fd['sz'];
$cx = $fd['cx'];
$cy = $fd['cy'];

$ze = $fd['ze'];
$gc = $fd['gc'];
$zc = $fd['zc'];

$ux = $r[0]['x'] + $cx;
$uy = $r[0]['y'] - $cy;

$nx = $ux;
$ny = $uy - 1;

$ox = $ux + 1;
$oy = $uy;

$sx = $ux;
$sy = $uy + 1;

$wx = $ux - 1;
$wy = $uy;

if ( $job == 'eclair' ) {
	$sq = ' INSERT IGNORE INTO dvoo_stat_camouflage VALUES ('.$uid.', '.$tid.', '.$day.', '.$cx.', '.$cy.', '.$ze.', '.$zc.', '.$gc.') ';
	$db->iquery($sq);
}

foreach ( array('n','w','o','s') AS $h ) {
	$vr = $h . 'r';
	$vz = $h . 'z';
	$vx = $h . 'x';
	$vy = $h . 'y';
	
	if ( $vz == -1 ) {
		$vz = "NULL";
	}
	
	$up = array();
	$rz = 'NULL';
	$rr = 'NULL';
	if ( isset($fd[$vz]) && !is_null($fd[$vz]) && $fd[$vz] > -1 && $job == 'eclair' && !($$vx == $tx && $$vy == $ty) ) {
		$up[] = ' radar_z = '.$fd[$vz].' ';
		$rz = $fd[$vz];
		
		// map2
		$q = ' INSERT INTO dvoo_zones_scout VALUES ('.$tid.', '.$day.', '.($$vx).', '.($$vy).', '.$rz.', '.time().', "'.$username.'") ON DUPLICATE KEY UPDATE z = '.$rz.', `on` = '.time().', `by` = "'.$username.'" ';
		$db->iquery($q);
	}
	if ( isset($fd[$vr]) && !is_null($fd[$vr]) && $job == 'collec' && !($$vx == $tx && $$vy == $ty) ) {
		$up[] = ' radar_r = '.$fd[$vr].' ';
		$rr = $fd[$vr];
		
		// map2
		$q = ' INSERT INTO dvoo_zones_regen VALUES ('.$tid.', '.$day.', '.($$vx).', '.($$vy).', '.$rr.', '.time().', "'.$username.'") ON DUPLICATE KEY UPDATE r = '.$rr.', `on` = '.time().', `by` = "'.$username.'" ';
		$db->iquery($q);
	}
	if ( count($up) > 0 && !($$vx == $tx && $$vy == $ty) ) {
		$q = ' INSERT INTO dvoo_zones VALUES ('.$tid.', '.$day.', '.($$vx).', '.($$vy).', 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, '.time().', "'.$username.'", NULL, NULL, '.$rr.', '.$rz.', '.time().', "'.$username.'") ON DUPLICATE KEY UPDATE '.( implode(',', $up) ).', radar_on = '.time().', radar_by = "'.$username.'" ';

		$db->iquery($q);
	}
}

// todo  return OK?
switch ( $job ) {
	case 'eclair':
		print t('RADAR_SCOUT');
		break;
	case 'collec':
		print t('RADAR_SCAVENGER');
		break;
	case 'guardian':
		print t('RADAR_GUARDIAN');
		break;
	case 'basic':
		print t('RADAR_CITIZEN');
		break;
}