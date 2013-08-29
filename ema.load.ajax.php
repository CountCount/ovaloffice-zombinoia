<?php
include_once 'system.php';
$db = new Database();

// get key (ajax)
$t = (int) $_POST['t'];
$u = (int) $_POST['u'];
$d = (int) $_POST['d'];

$res = $db->query(' SELECT * FROM dvoo_timeplanner WHERE tid = '.$t.' AND uid = '.$u.' AND day = '.$d.' LIMIT 1 ');

$check = array('thirsty', 'hangover', 'paralyzed', 'clean', 'topform', 'safe', 'water', 'alcohol', 'gamble', 'alarm', 'sleep', 'lunge', 'coffee', 'drug_ster', 'drug_twin');

$s = '';

if ( $r = $res[0] ) {
	foreach ( $check AS $c ) {
		if ( $r[$c] > 0 ) { 
			$s .= "$('#".$c."').attr('checked', 'checked');\n"; 
		}
		else { 
			$s .= "$('#".$c."').attr('checked', '');\n"; 
		}
	}
	if ( $r['food'] == 7 ) {
		$s .= "$('#food_yummy').attr('checked', 'checked');\n";
		$s .= "$('#food_defoe').attr('checked', '');\n";
	}
	elseif ( $r['food'] == 6 ) {
		$s .= "$('#food_yummy').attr('checked', '');\n";
		$s .= "$('#food_defoe').attr('checked', 'checked');\n";
	}
	else {
		$s .= "$('#food_yummy').attr('checked', '');\n";
		$s .= "$('#food_defoe').attr('checked', '');\n";
	}
	if ( $r['drug_ster'] > 0 ) {
		$s .= "$('#drug_ster_count').val(".($r['drug_ster'] / 6).");\n";
	}
	if ( $r['drug_twin'] > 0 ) {
		$s .= "$('#drug_twin_count').val(".($r['drug_twin'] / 8).");\n";
	}
	if ( $r['coffee'] > 0 ) {
		$s .= "$('#coffee_count').val(".($r['coffee'] / 4).");\n";
	}
	
	for ( $i = 0; $i < 24; $i++ ) {
		$s .= "changeTP(".$i.",".$r['tp'.$i].");\n";
	}
	
}

print '<script type="text/javascript">'.$s.'</script>';

