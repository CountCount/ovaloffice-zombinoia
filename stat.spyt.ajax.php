<?php
include_once 'system.php';

$db = new Database();

// get day number
$t = (int) $_REQUEST['t'];
$d = (int) $_REQUEST['d'];

$days = array();
$q = ' SELECT day FROM dvoo_xml WHERE tid = '.$t.' GROUP BY day ';
$r = $db->query($q);

if ( is_array($r) && count($r) > 0 ) {
	foreach ( $r AS $s ) {
		$days[] = $s['day'];
	}
}
rsort($days);

if ( $xml_string = file_get_contents('xml/history/'.$t.'-'.$d.'.xml') ) {
	$xml = simplexml_load_string($xml_string);
}
else {
	print '<div id="spy-close" class="clickable" onclick="spyclose();"></div>';
	print '<h2>'.t('NO_DATA_AVAILABLE').'</h2>';
	exit;
}
/*
$q = ' SELECT xml FROM dvoo_xml WHERE tid = '.$t.' AND day = '.$d.' ORDER BY stamp DESC LIMIT 1 ';
$r = $db->query($q);

if ( is_array($r) && count($r[0]) > 0 ) {
	$xml = simplexml_load_string($r[0]['xml']);
}
else {

}
*/

$headers = $xml->headers;
$game = $xml->headers->game;
$city = $xml->data->city;
$map = $xml->data->map;
$citizens = $xml->data->citizens;
$cadavers = $xml->data->cadavers;
$expeditions = $xml->data->expeditions;
$bank = $xml->data->bank;
$estimations = $xml->data->estimations->e;
$upgrades = $xml->data->upgrades;
$news = $xml->data->city->news;
$defense = $xml->data->city->defense;
$buildings = $xml->data->city->building;

// town data
$town['id'] = (int) $game['id'];
$town['name'] = (string) $city['city'];
$town['x'] = (int) $city['x'];
$town['y'] = (int) $city['y'];
$town['door'] = (int) $city['door'];
$town['water'] = (int) $city['water'];
$town['chaos'] = (int) $city['chaos'];
$town['devast'] = (int) $city['devast'];
$town['hard'] = (int) $city['hard'];

// cit
$residents = $basic = $hero = $scout = $scavenger = $tamer = $guardian = $hunter = $tech = $shaman = $banned = $dead = 0;
$cit = array();
foreach ( $citizens->children() AS $ca ) {
	$n = (string) $ca['name'];
	$cit[$n] = array(
		'id' => (int) $ca['id'],
		'name' => (string) $ca['name'],
		'avatar' => (string) $ca['avatar'],
		'out' => (int) $ca['out'],
		'ban' => (int) $ca['ban'],
		'hero' => (int) $ca['hero'],
		'job' => (string) $ca['job'],
		'dead' => (int) $ca['dead'],
		'x' => (!isset($ca['x']) ? $data['town']['x'] : (int) $ca['x']),
		'y' => (is_null($ca['y']) ? $data['town']['y'] : (int) $ca['y']),
		'rx' => (is_null($ca['x']) ? 0 : (int) $ca['x'] - $data['town']['x']),
		'ry' => (is_null($ca['y']) ? 0 : (int) $ca['y'] - $data['town']['y']),
	);
	
	$residents++;
	if ( $cit[$n]['hero'] == 1 ) { 
		$hero++; 
		if ( $cit[$n]['job'] == 'eclair' ) { 
			$scout++; 
		}
		elseif ( $cit[$n]['job'] == 'collec' ) { 
			$scavenger++; 
		}
		elseif ( $cit[$n]['job'] == 'guardian' ) { 
			$guardian++; 
		}
		elseif ( $cit[$n]['job'] == 'hunter' ) { 
			$hunter++; 
		}
		elseif ( $cit[$n]['job'] == 'tamer' ) { 
			$tamer++; 
		}
		elseif ( $cit[$n]['job'] == 'tech' ) { 
			$tech++; 
		}
		elseif ( $cit[$n]['job'] == 'shaman' ) { 
			$shaman++; 
		}
	}
	else {
		$basic++;
	}
	if ( $cit[$n]['ban'] == 1 ) { 
		$banned++; 
	}	
}

// cad
$tcadavers = array();
foreach ( $cadavers->children() AS $ca ) {	
	$tcadavers[(int) $ca['day']][(int) $ca['id']] = array(
		'name' => (string) $ca['name'],
		'day' => (int) $ca['day'],
		'dtype' => (int) $ca['dtype'],
		'msg' => (string) $ca->msg,
		'id' => (int) $ca['id'],
 	);
	
	if ( isset($ca->cleanup) ) {
		$cleanup = $ca->cleanup;
		$tcadavers[(int) $ca['day']][(int) $ca['id']]['cleanup_type'] = (string) $cleanup['type'];
		$tcadavers[(int) $ca['day']][(int) $ca['id']]['cleanup_user'] = (string) $cleanup['user'];
	}
	$dead++;
}
krsort($tcadavers);
$cad = array();
foreach ( $tcadavers AS $tcad ) {
	$cad = array_merge($cad, $tcad);
}

//bank
$def = $food = $water = $well = $jerry = $drugs = $alcohol = $coffee = 0;
$dfa = array();
foreach ( $bank->children() AS $bia ) {
	$bi_name = (string) $bia['name'];
	$bi_count = (int) $bia['count'];
	$bi_id = (int) $bia['id'];
	$bi_cat = (string) $bia['cat'];
	$bi_img = (string) $bia['img'];
	$bi_broken = (int) $bia['broken'];
	
	if ( $bi_cat == 'Armor' ) {
		$def += $bi_count;
		$dfa[$bi_id] = array('name' => $bi_name, 'img' => $bi_img, 'count' => $bi_count);
	}
	if ( $bi_cat == 'Food' && !in_array($bi_id,array(1,97,69,222,98,246,247,248)) ) {
		$food += $bi_count;
	} 
	if ( $bi_id == 1 || $bi_id == 222 ) {
		$water += $bi_count;
	}
	if ( $bi_id == 58 ) {
		$jerry += $bi_count;
	}
	if ( $bi_id == 98 ) {
		$coffee += $bi_count;
	}
	if ( in_array($bi_id, array(89,51,223)) ) {
		$drugs += $bi_count;
	}
	if ( in_array($bi_id, array(97,69)) ) {
		$alcohol += $bi_count;
	}
	/*
	$bank[$bi_id] = array(
		'id' => (int) $bi_id,
		'name' => (string) $bi_name,
		'count' => (int) $bi_count,
		'category' => (string) $bi_cat,
		'image' => (string) $bi_img,
		'broken' => (int) $bi_broken,
		
	);
	*/
	$well = $town['water'];
}

// buildings
$townbuildings = array();
foreach ( $buildings AS $b ) {
	$townbuildings[] = array('name' => (string) $b['name'], 'img' => (string) $b['img']);
}

// map size
$map['height'] = (int) $map['hei'];
$map['width'] = (int) $map['wid'];
foreach ( $map->children() AS $zdata ) {
	$zx = (int) $zdata['x']; // x
	$zy = (int) $zdata['y']; // y
	$zz = (isset($zdata['z']) ? (int) $zdata['z'] : null); // zombies
	$zv = (int) $zdata['nvt']; // visited (bool)
	$zt = (int) $zdata['tag']; // tag
	$zd = (int) $zdata['danger']; // danger
	$zones[$zy][$zx] = array('x' => $zx, 'y' => $zy, 'z' => $zz, 'nvt' => $zv, 'tag' => $zt, 'danger' => $zd);
	
	if ( $building = $zdata->building ) {
		$zb = array('name' => (string) $building['name'], 'type' => (int) $building['type'], 'dig' => (int) $building['dig']);
		$zones[$zy][$zx]['building'] = $zb;
	}
}

// defense
$def_total = (int) $defense['total'];

// estimations
if ( !is_null($estimations) ) {
	foreach ( $estimations AS $e ) {
		$eday = (int) $e['day'];
		$emin = (int) $e['min'];
		$emax = (int) $e['max'];
		$ebest = (int) $e['maxed'];
		$est[$eday] = array(
			'min' => $emin,
			'max' => $emax,
			'best' => $ebest,
		);
	}
}


/* ### OUTPUT ### */

print '<div id="spy-history"><span class="current-day">'.t('DAY').': </span>';

foreach ( $days AS $h) {
	if ( $h == $d ) {
		print '<span class="current-day">&nbsp;'.$h.'&nbsp;</span>';
	}
	else {
		print '<span onclick="spyontown('.$town['id'].','.$h.');">&nbsp;'.$h.'&nbsp;</span>';
	}
}
print '</div>';
print '<div id="spy-close" class="clickable" onclick="spyclose();"></div>';
print '<h2>'.$town['name'].'</h2>';

?>
<div class="spy-box" id="spy-cit">
	<h3><?php print '<img src="'.t('GAMESERVER_ICON').'r_surgrp.gif" /> '.t('CITIZEN'); ?></h3>
	<table border="0">
	<tr><td><?php print '<img src="'.t('GAMESERVER_SMILEY').'h_human.gif" /></td><td class="tr">'.$residents.'</td><td> '.t('CITIZEN'); ?>s</td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_SMILEY').'h_basic.gif" /></td><td class="tr">'.$basic.'</td><td> '.t('RESIDENT'); ?>s</td></tr>
	<tr class="hideme"><td><?php print '<img src="'.t('GAMESERVER_ICON').'r_heroac.gif" /></td><td class="tr">'.$hero.'</td><td> '.t('HEROS'); ?></td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_ICON').'r_jrangr.gif" /></td><td class="tr">'.$scout.'</td><td> '.t('SCOUT'); ?>s</td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_ICON').'r_jcolle.gif" /></td><td class="tr">'.$scavenger.'</td><td> '.t('SCAVENGER'); ?>s</td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_ICON').'r_jguard.gif" /></td><td class="tr">'.$guardian.'</td><td> '.t('GUARDIAN'); ?>s</td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_ICON').'r_jtamer.gif" /></td><td class="tr">'.$tamer.'</td><td> '.t('TAMER'); ?>s</td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_ITEM').'surv_book.gif" /></td><td class="tr">'.$hunter.'</td><td> '.t('HUNTER'); ?>s</td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_ITEM').'keymol.gif" /></td><td class="tr">'.$tech.'</td><td> '.t('TECH'); ?>s</td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_ITEM').'shaman.gif" /></td><td class="tr">'.$shaman.'</td><td> '.t('SHAMAN'); ?></td></tr>
	<tr style="display:none;"><td><?php print '<img src="'.t('GAMESERVER_SMILEY').'h_ban.gif" /></td><td class="tr">'.$banned.'</td><td> '.t('BANNED'); ?></td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_SMILEY').'h_death.gif" /></td><td class="tr">'.$dead.'</td><td> '.t('DEADS'); ?></td></tr></table>
</div>
<div class="spy-box" id="spy-pro">
	<h3><?php print '<img src="'.t('GAMESERVER_ITEM').'bag.gif" /> '.t('PROVIANT'); ?></h3>
	<table border="0">
	<tr><td><?php print '<img src="'.t('GAMESERVER_ITEM').'water.gif" /></td><td class="tr">'.$water.'</td><td> '.t('WWATER'); ?></td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_SMILEY').'h_well.gif" /></td><td class="tr">'.$well.'</td><td> '.t('WWELL'); ?></td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_ITEM').'dish_tasty.gif" /></td><td class="tr">'.$food.'</td><td> '.t('FOOD'); ?></td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_ITEM').'drug_hero.gif" /></td><td class="tr">'.$drugs.'</td><td> '.t('DRUGS'); ?></td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_ITEM').'vodka.gif" /></td><td class="tr">'.$alcohol.'</td><td> '.t('ALCOHOL'); ?></td></tr>
	<tr><td><?php print '<img src="'.t('GAMESERVER_ITEM').'coffee.gif" /></td><td class="tr">'.$coffee.'</td><td> '.t('COFFEE'); ?></td></tr></table>
</div>
<div class="spy-box" id="spy-svs">
	<h3><?php print '<img src="'.t('GAMESERVER_ICON').'r_surgrp.gif" /> '.t('SURVIVORS'); ?></h3>
	<table border="0">
		<?php foreach ( $cit AS $c ) { 
			switch ($c['job']) {
				case 'basic': $ci = t('GAMESERVER_SMILEY').'h_basic.gif'; break;
				case 'eclair': $ci = t('GAMESERVER_ICON').'r_jrangr.gif'; break;
				case 'guardian': $ci = t('GAMESERVER_ICON').'r_jguard.gif'; break;
				case 'collec': $ci = t('GAMESERVER_ICON').'r_jcolle.gif'; break;
				case 'tamer': $ci = t('GAMESERVER_ICON').'r_jtamer.gif'; break;
				case 'hunter': $ci = t('GAMESERVER_ICON').'r_jermit.gif'; break;
				case 'tech': $ci = t('GAMESERVER_ITEM').'keymol.gif'; break;
				case 'shaman': $ci = t('GAMESERVER_ITEM').'shaman.gif'; break;
				default: $ci = t('GAMESERVER_ICON').'small_calim.gif'; break;
			}
			print '<tr><td><span class="clickable" onclick="spyoncitizen('.$c['id'].');"><img src="'.$ci.'" />&nbsp;'.$c['name'].'</span></td></tr>';
		} ?>
	</table>
</div>
<div class="spy-box" id="spy-cad">
	<h3><?php print '<img src="'.t('GAMESERVER_SMILEY').'h_death.gif" /> '.t('CADAVER'); ?></h3>
	<table border="0">
		<?php
			foreach ( $cad AS $k ) {
				print '<tr><td><img src="css/img/dc'.$k['dtype'].'.gif" title="'.t('DC'.$k['dtype']).'" /></td><td><span class="clickable" onclick="spyoncitizen('.$k['id'].');">'.$k['name'].'</span></td><td class="tr">'.$k['day'].'</td></tr>';
			}
		?>
	</table>
</div>
<div class="spy-box" id="spy-bld">
	<h3><?php print '<img src="'.t('GAMESERVER_ICON').'small_refine.gif" /> '.t('BUILDINGS'); ?></h3>
	<table border="0">
		<?php foreach ( $townbuildings AS $b ) { 
			print '<tr><td><img src="'.t('GAMESERVER_ICON').$b['img'].'.gif" />&nbsp;'.$b['name'].'</td></tr>';
		} ?>
	</table>
</div>
<div class="spy-box" id="spy-est">
	<h3><?php print '<img src="'.t('GAMESERVER_SMILEY').'h_death.gif" /> '.t('ATTACKS'); ?></h3>
	<table border="0" width="100%">
		<tr><th><?php print t('LAST_ATTACK'); ?></th><td class="tr"><?php print (int) $news['z'].'<img src="'.t('GAMESERVER_SMILEY').'h_zombie.gif" /> '; ?></td><td class="tr"><?php print (int) $news['def'].'<img src="'.t('GAMESERVER_SMILEY').'h_guard.gif" /> '; ?></td></tr>
		<tr><th><?php print t('NEXT_ATTACK'); ?></th><td class="tr"><?php print (isset($est[$d]) ? $est[$d]['min'].' - '.$est[$d]['max'] : '???').'<img src="'.t('GAMESERVER_SMILEY').'h_zombie.gif" /> '; ?></td><td class="tr"><?php print $def_total.'<img src="'.t('GAMESERVER_SMILEY').'h_guard.gif" /> '; ?></td></tr>
	</table>
</div>
<div class="spy-box" id="spy-def">
	<h3><?php print '<img src="'.t('GAMESERVER_ITEM').'shield_mt.gif" /> '.$def.' '.t('DEFENSIVE_OBJECTS'); ?></h3>
	<table border="0">
		<?php foreach ( $dfa AS $d ) { 
			print '<tr><td><img src="'.t('GAMESERVER_ITEM').$d['img'].'.gif" /></td><td class="tr">'.$d['count'].'</td><td>'.$d['name'].'</td></tr>';
		} ?>
	</table>
</div>
<div class="spy-box" id="spy-map">
	<div class="spy-box-map">
	<!-- <h3><?php print '<img src="'.t('GAMESERVER_ICON').'r_explor.gif" /> '.t('MAP'); ?></h3> -->
	<?php for ( $y = 0; $y < $map['width']; $y++ ) { 
		for ( $x = 0; $x < $map['height']; $x++ ) { 	
			if ( isset($zones[$y][$x]) ) {
				$t = '['.($x - $town['x']).'|'.($town['y'] - $y).']';
				$z = $zones[$y][$x];
				if ( $x == $town['x'] && $y == $town['y'] ) {
					$w = $h = "6px";
					$b = "1px";
					$t .= ' - '.t('CITY');
					$f = "#ff0";
				}
				elseif ( isset($zones[$y][$x]['building']) ) {
					$w = $h = "4px";
					$b = "2px";
					$t .= ' - '.$z['building']['name'];
					$f = "#fc0";
				}
				else {
					$w = $h = "0px";
					$b = "4px";
					$f = "rgba(102,102,153,.75)";
				}
				$zz = $z['z'];
				if ( is_null($zz) ) {
					$zombies = array(0 => "0", 1 => "1", 2 => "2", 3 => "4", 4 => '8', 5 => '12');
					$zz = $zombies[$z['danger']];
				}
				$d = ( $x == $town['x'] && $y == $town['y'] ? "#fff" : ((is_null($zz) || $zz == 0) ? '#475613' : dv_i2c($zz)));
			}
			else {
				$w = $h = "0px";
				$b = "4px";
				$d = "rgba(255,255,255,.1)";
				$f = "transparent";
			}
			
			$c = '';
			if ( $x == 0 ) {
				$c = 'clear:left;';
			}
			print '<div title="'.$t.'" style="'.$c.'width:'.$w.';height:'.$h.';border:'.$b.' solid '.$d.';background-color:'.$f.';margin-right:1px;margin-bottom:1px;float:left;"></div>';
		} 
	} ?>
</div>