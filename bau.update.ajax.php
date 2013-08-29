<?php
include_once 'system.php';
$db = new Database();

// get key (ajax)
$u = (int) $_POST['u'];
$b = (int) $_POST['b'];
$p = (int) $_POST['p'];
$a = (int) $_POST['a'];

$session = $db->query(' SELECT xml FROM dvoo_rawdata WHERE id = '.$u.' ORDER BY time DESC LIMIT 1 ');
$data = unserialize($session[0][0]);

$t = $data['town']['id'];
$d = $data['current_day'];

if ( isset($b) && $b > 0 ) {
	if ( $a == 1 ) {
		$db->iquery(' INSERT IGNORE INTO dvoo_town_buildings VALUES ('.$t.', '.$d.', '.$b.') ');
	}
	else {
		$db->iquery(' DELETE FROM dvoo_town_buildings WHERE tid = '.$t.' AND day = '.$d.' AND bid = '.$b.') LIMIT 1 ');
	}
}
if ( isset($p) && $p > 0 ) {
	if ( $a == 1 ) {
		$db->iquery(' INSERT IGNORE INTO dvoo_town_blueprints VALUES ('.$t.', '.$p.') ');
	}
	else {
		$db->iquery(' DELETE FROM dvoo_town_blueprints WHERE tid = '.$t.' AND pid = '.$p.' LIMIT 1 ');
	}
}

print 1;