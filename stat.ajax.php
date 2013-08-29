<?php
include_once 'system.php';
$db = new Database();

// get version number
$v = (int) $_POST['v'];
// get process number
$p = (int) $_POST['p'];
if ( $p == 2 ) {
	$k = htmlspecialchars(strip_tags($_POST['k']));
	$u = (int) $_POST['u'];
}
elseif ( $p == 1 ) {
	$k = htmlspecialchars(strip_tags($_POST['k']));
	$n = htmlspecialchars(strip_tags($_POST['n']));
}
else {
	// no data send -> start
	print '<script type="text/javascript">
			$("#link_statistikamt").remove();
			$("#statistikamt").remove();
		</script>';
	exit;
}

$session = $db->query(' SELECT xml FROM dvoo_rawdata WHERE id = '.$u.' ORDER BY time DESC LIMIT 1 ');
if ( $data = unserialize($session[0]['xml']) ) {
	$cd = $data['current_day'];
}
else {
	$cd = 0;
}

$seasons = array(
	1 => array(
		'start' => null,
		'end' => null
	),
);

$attack_data = array();

foreach ( $seasons AS $sno => $sdates ) {

	$attack_data[$sno]['NO']['zombies'] = $db->query(' 
		SELECT s.day, MIN(s.z) AS zmin, MAX(s.z) AS zmax, AVG(s.z) AS zavg, COUNT(*) AS zahl 
		FROM dvoo_stat_zombies s 
		INNER JOIN dvoo_towns t ON t.id = s.tid AND t.hard = 0 
		WHERE 1 = 1 
		'.(!is_null($sdates['start']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) > '.$sdates['start'] : '').'
		'.(!is_null($sdates['end']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) < '.$sdates['end'] : '').'
		GROUP BY s.day HAVING s.day > 0 
	');
	$attack_data[$sno]['NO']['attacks'] = $db->query(' 
		SELECT COUNT(DISTINCT(tid)) AS zahl 
		FROM dvoo_stat_zombies s 
		INNER JOIN dvoo_towns t ON t.id = s.tid AND t.hard = 0 
		'.(!is_null($sdates['start']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) > '.$sdates['start'] : '').'
		'.(!is_null($sdates['end']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) < '.$sdates['end'] : '').' 
	');
	$attack_data[$sno]['NO']['towns'] = $db->query(' 
		SELECT COUNT(DISTINCT(tid)) AS zahl 
		FROM dvoo_stat_zombies s 
		INNER JOIN dvoo_towns t ON t.id = s.tid AND t.hard = 0 		
		'.(!is_null($sdates['start']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) > '.$sdates['start'] : '').'
		'.(!is_null($sdates['end']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) < '.$sdates['end'] : '').' 
	');
		 
	$attack_data[$sno]['HC']['zombies'] = $db->query(' 
		SELECT s.day, MIN(s.z) AS zmin, MAX(s.z) AS zmax, AVG(s.z) AS zavg, COUNT(*) AS zahl 
		FROM dvoo_stat_zombies s 
		INNER JOIN dvoo_towns t ON t.id = s.tid AND t.hard = 1 
		WHERE 1 = 1 
		'.(!is_null($sdates['start']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) > '.$sdates['start'] : '').'
		'.(!is_null($sdates['end']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) < '.$sdates['end'] : '').'
		GROUP BY s.day HAVING s.day > 0 
	');
	$attack_data[$sno]['HC']['attacks'] = $db->query(' 
		SELECT COUNT(DISTINCT(tid)) AS zahl 
		FROM dvoo_stat_zombies s 
		INNER JOIN dvoo_towns t ON t.id = s.tid AND t.hard = 1 
		'.(!is_null($sdates['start']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) > '.$sdates['start'] : '').'
		'.(!is_null($sdates['end']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) < '.$sdates['end'] : '').' 
	');
	$attack_data[$sno]['HC']['towns'] = $db->query(' 
		SELECT COUNT(DISTINCT(tid)) AS zahl 
		FROM dvoo_stat_zombies s 
		INNER JOIN dvoo_towns t ON t.id = s.tid AND t.hard = 1 		
		'.(!is_null($sdates['start']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) > '.$sdates['start'] : '').'
		'.(!is_null($sdates['end']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) < '.$sdates['end'] : '').' 
	');
}
 
$i = 0;
$owndata = array();
if (isset($data['town']['id'])) {
	$od = $db->query(' SELECT day, z, v FROM dvoo_stat_zombies WHERE tid = ' . $data['town']['id']);
	foreach ( $od AS $e ) {
		$owndata[$e['day']] = array('z' => $e['z'], 'v' => $e['v']);
	}
}

#var_dump($owndata);
$rewards = array();
$rdata = $db->query(' SELECT * FROM dvoo_rewards ORDER BY rare DESC, name ASC ');

foreach ( $rdata AS $r ) {
	$rewards[] = array(
		'name' => $r['name'],
		'img' => $r['img'],
		'rare' => $r['rare']
	);
}

$titles = array();
$adata = $db->query(' SELECT t.name AS name, r.name AS reward, r.img, r.rare, t.min, t.max FROM dvoo_titles t INNER JOIN dvoo_rewards r ON r.name = t.reward ORDER BY r.rare DESC, r.name ASC, t.min ASC ');

foreach ( $adata AS $a ) {
	$titles[] = array(
		'name' => $a['name'],
		'reward' => $a['reward'],
		'img' => $a['img'],
		'rare' => $a['rare'],
		'min' => $a['min'],
		'max' => $a['max']
	);
}

$towns = array();

// attack time
$at = 1; // DE,FR => 0, EN => 23, ES => 1

switch ($at) {
	case 1:
	{
		$stat_day = mktime (1, 10, 0, date("n"), date("j"), date("Y"));
		if ( $stat_day > $today ) {
			$stat_day -= 86400;
		}
	}
	case 23:
	{
		$yesterday = time() - 86400;
		$stat_day = mktime (1, 10, 0, date("n"), date("j"), date("Y"));
		if ( $stat_day > $yesterday ) {
			$stat_day -= 86400;
		}
		elseif ( $stat_day < $yesterday - 86400 ) {
			$stat_day += 86400;
		}
	}
	case 0:
	default:
	{
		$yesterday = time() - 86400;
		$stat_day = mktime (0, 20, 0, date("n", $yesterday), date("j", $yesterday), date("Y", $yesterday));
	}
}
	
// current towns' data
$tdata = $db->query(' SELECT t.id AS townid, t.hard, t.*, MAX(t.day) AS maxday, s.z, s.v, b.icount AS bwater, j.icount AS bjerry FROM `dvoo_towns` t LEFT JOIN dvoo_stat_zombies s ON s.tid = t.id AND s.day = (t.day - 1) LEFT JOIN dvoo_bankitems b ON b.tid = t.id AND b.cday = t.day AND b.iid = 1 LEFT JOIN dvoo_bankitems j ON j.tid = t.id AND j.cday = t.day AND j.iid = 58 WHERE t.stamp > '.$stat_day.' GROUP BY t.id ORDER BY t.day DESC ');
// old towns' data
$todata = $db->query(' SELECT t.*, t.hard, MAX(t.day) AS maxday, t.stamp FROM `dvoo_towns` t WHERE t.stamp < '.$stat_day.' AND t.day > 0 GROUP BY t.id ORDER BY t.day DESC ');
// own towns' ids
$own_towns = array();
if (isset($data['user']['id'])) {
	$ot_res = $db->query(' SELECT town_id FROM dvoo_town_citizens WHERE citizen_id = '.$data['user']['id']);
	if ( is_array($ot_res) && count($ot_res) > 0 ) {
		foreach ($ot_res AS $ot ) {
			$own_towns[] = $ot['town_id'];
		}
	}
}

$souls = array();
$i = 0;
$q = ' SELECT c.id, c.name, s.score FROM dvoo_citizens c INNER JOIN dvoo_stat_soul s ON c.id = s.uid ORDER BY s.score DESC, c.name ASC ';
$r = $db->query($q);
foreach ( $r AS $s ) {
	$i++;
	$souls[$i] = array('id' => $s['id'], 'name' => $s['name'], 'score' => $s['score']);
}

// ##################
// ##### OUTPUT #####
// ##################

print '<ul class="subtabs" id="sub-stat">
	<li><a href="#stat_zombie">'.t('STAT_ZOMBIE_TITLE').'</a></li>
	<li><a href="#stat_souls">'.t('STAT_SOULS_TITLE').'</a></li>
	<li><a href="#stat_rewards">'.t('STAT_REWARDS_TITLE').'</a></li>
	<li><a href="#stat_titles">'.t('STAT_TITLES_TITLE').'</a></li>
	<li><a href="#stat_towns_active">'.t('STAT_ACTIVETOWNS_TITLE').'</a></li>
	<li><a href="#stat_towns_old">'.t('STAT_OLDTOWNS_TITLE').'</a></li>
</ul><div class="clearfix">';

print '<div id="stat_zombie" class="subtabcontent"><p>'.t('STAT_ZOMBIE_INFO').'</p>';

print '<ul class="subtabs" id="sub-zomstat">';
foreach ( $seasons AS $sno => $sdates ) {
	print '<li><a href="#stats-defoe'.$sno.'">'.t('STATS_DEFOE',array('%d'=>$sno)).'</a></li>
	<li><a href="#stats-hard'.$sno.'">'.t('STATS_HARD',array('%d'=>$sno)).'</a></li>';
}
print '</ul>';

// Graphs
print '<div style="float:right;width:320px;border-left:1px solid #ccc; padding-left:1px;">';
print '<h4>'.t('GRAPH_SETTINGS').'</h4>
<div>'.t('GRAPH_MAX_VALUES').' <input type="text" id="c" value="12" size="3" /><br/>'.t('GRAPH_TYPE').' <select id="ztype"><option value="1">'.t('GRAPH_BARS').'</option><option value="2">'.t('GRAPH_LINES').'</option></select></div>
<div id="dayzgraph"></div>';
print '</div>';

	foreach ($seasons AS $sno => $sdates) {
	# SEASON DEFOE TOWNS
	print '<div id="stats-defoe'.$sno.'" class="zomstats stats-defoe-wrapper"><h4>'.strtoupper(t('DEFOE_TOWN')).' - <span style="color:#c00;">SEASON '.$sno.'</span><br/>'.t('STAT_ZOMBIE_BASE', array('%d' => $attack_data[$sno]['NO']['attacks'], '%s' => $attack_data[$sno]['NO']['towns'])).'</h4><table class="stats" border="0"><thead><tr><th rowspan="2">'.t('DAY').'</th><th rowspan="2">'.t('MIN').' o<sub>min</sub></th><th rowspan="2">'.t('AVG').' ō</th><th rowspan="2">'.t('MED').' õ</th><th rowspan="2" scope="col">'.t('MAX').' o<sub>max</sub></th><th rowspan="2">'.t('COUNT_DATASETS').'</th><th colspan="2">'.t('YOUR_CITY').'</th></tr><tr><th><img src="'.t('GAMESERVER_SMILEY').'h_zombie.gif" /></th><th><img src="'.t('GAMESERVER_SMILEY').'h_guard.gif" /></th></tr></thead><tbody>';

	$luck = 1;
	$processed_own_days = 0;
	foreach ( $attack_data[$sno]['NO']['zombies'] AS $d ) {

		$sumc = 0;
		$median = 0;
		
		$median_sql = $db->query(' 
			SELECT x.z 
			FROM dvoo_stat_zombies x 
			INNER JOIN dvoo_towns t ON t.id = x.tid AND t.hard = 0 
			WHERE x.day = '.$d['day'].
			(!is_null($sdates['start']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) > '.$sdates['start'] : '').
			(!is_null($sdates['end']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) < '.$sdates['start'] : '').
			' ORDER BY x.day ASC, x.z ASC LIMIT '.(ceil($d['zahl']/2)).', 1');
		$median = $median_sql[0][0];

		$i++;
		$c = ($i % 2 == 0 ? 'even' : 'odd');
		$od = '';
		if ($d['day'] == $cd) {
			$od = ' current_day';
		}
		if ( $d['day'] < 50 ) {
			print '<tr class="'.$c.$od.'" onclick="zgraph('.$d['day'].',3);"><th>'.$d['day'].'</th><td class="min">'.$d['zmin'].'</td><td>'.round($d['zavg'],0).'</td><td>'.($d['zahl'] > 4 ? $median : '').'</td><td class="max">'.$d['zmax'].'</td><td>'.$d['zahl'].'</td>'; //  ('.(round($d['zahl'] * 100 / $ct[0][0])).'%)
			if ( is_array($owndata[$d['day']]) ) {
				$processed_own_days++;
				print '<td>'.$owndata[$d['day']]['z'].'</td><td>'.$owndata[$d['day']]['v'].'</td>';
			}
			elseif ( is_array($data['estimations'][$d['day']]) ) {
				print '<td style="font-size:12px;">'.$data['estimations'][$d['day']]['min'].' - '.$data['estimations'][$d['day']]['max'].'</td><td>'.$data['defense']['total'].'</td>';
			}
			elseif ($processed_own_days >= count($owndata)) {
				print '<td colspan="2">'.($luck == 1 ? t('GOOD_LUCK') : '').'</td>';
				$luck = 0;
			}
			print '</tr>';
	 } else {
			print '<tr class="'.$c.$od.'"><td>'.$d['day'].'</td><td>'.$d['zmin'].'</td><td>'.round($d['zavg'],0).'</td><td>'.($d['zahl'] > 2 ? $median : '').'</td><td>'.$d['zmax'].'</td><td>'.$d['zahl'].' ('.(round($d['zahl'] * 100 / $ct[0][0])).'%)</td>';
			
			print '</tr>';
	 }
	}
	print '</tbody></table></div>';


	# SEASON HC TOWNS
	print '<div id="stats-hard'.$sno.'" class="zomstats stats-hard-wrapper hideme"><h4>'.strtoupper(t('HARD_TOWN')).' - <span style="color:#c00;">SEASON '.$sno.'</span><br/>'.t('STAT_ZOMBIE_BASE', array('%d' => $attack_data[$sno]['HC']['attacks'], '%s' => $attack_data[$sno]['HC']['towns'])).'</h4><table class="stats" border="0"><thead><tr><th rowspan="2">'.t('DAY').'</th><th rowspan="2">'.t('MIN').' o<sub>min</sub></th><th rowspan="2">'.t('AVG').' ō</th><th rowspan="2">'.t('MED').' õ</th><th rowspan="2" scope="col">'.t('MAX').' o<sub>max</sub></th><th rowspan="2">'.t('COUNT_DATASETS').'</th><th colspan="2">'.t('YOUR_CITY').'</th></tr><tr><th><img src="'.t('GAMESERVER_SMILEY').'h_zombie.gif" /></th><th><img src="'.t('GAMESERVER_SMILEY').'h_guard.gif" /></th></tr></thead><tbody>';

	$luck = 1;
	$processed_own_days = 0;
	foreach ( $attack_data[$sno]['HC']['zombies'] AS $d ) {

		$sumc = 0;
		$median = 0;
		
		$median_sql = $db->query(' 
			SELECT x.z 
			FROM dvoo_stat_zombies x 
			INNER JOIN dvoo_towns t ON t.id = x.tid AND t.hard = 1 
			WHERE x.day = '.$d['day'].
			(!is_null($sdates['start']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) > '.$sdates['start'] : '').
			(!is_null($sdates['end']) ? ' AND (t.stamp - ((t.day - 1) * 86400)) < '.$sdates['start'] : '').
			' ORDER BY x.day ASC, x.z ASC LIMIT '.(ceil($d['zahl']/2)).', 1');
		$median = $median_sql[0][0];

		$i++;
		$c = ($i % 2 == 0 ? 'even' : 'odd');
		$od = '';
		if ($d['day'] == $cd) {
			$od = ' current_day';
		}
		if ( $d['day'] < 50 ) {
			print '<tr class="'.$c.$od.'" onclick="zgraph('.$d['day'].',3);"><th>'.$d['day'].'</th><td class="min">'.$d['zmin'].'</td><td>'.round($d['zavg'],0).'</td><td>'.($d['zahl'] > 4 ? $median : '').'</td><td class="max">'.$d['zmax'].'</td><td>'.$d['zahl'].'</td>'; //  ('.(round($d['zahl'] * 100 / $ct[0][0])).'%)
			if ( is_array($owndata[$d['day']]) ) {
				$processed_own_days++;
				print '<td>'.$owndata[$d['day']]['z'].'</td><td>'.$owndata[$d['day']]['v'].'</td>';
			}
			elseif ( is_array($data['estimations'][$d['day']]) ) {
				print '<td style="font-size:12px;">'.$data['estimations'][$d['day']]['min'].' - '.$data['estimations'][$d['day']]['max'].'</td><td>'.$data['defense']['total'].'</td>';
			}
			elseif ($processed_own_days >= count($owndata)) {
				print '<td colspan="2">'.($luck == 1 ? t('GOOD_LUCK') : '').'</td>';
				$luck = 0;
			}
			print '</tr>';
	 } else {
			print '<tr class="'.$c.$od.'"><td>'.$d['day'].'</td><td>'.$d['zmin'].'</td><td>'.round($d['zavg'],0).'</td><td>'.($d['zahl'] > 2 ? $median : '').'</td><td>'.$d['zmax'].'</td><td>'.$d['zahl'].' ('.(round($d['zahl'] * 100 / $ct[0][0])).'%)</td>';
			
			print '</tr>';
	 }
	}
	print '</tbody></table></div>';
}
print '</div>';

// Soul scores
print '<div id="stat_souls" class="subtabcontent hideme">';
print '<h4>'.t('STAT_SOULS_TITLE').'</h4>
<table class="souls">';
$break = ceil(count($souls) / 4);
$tscore = -1;
foreach ($souls AS $i => $s) {
	if ( ($i - 1) > 0 && ($i - 1) % $break == 0 ) {
		print '</table><table class="souls">';
	}
	print '<tr class="'.($i % 2 == 0 ? 'even' : 'odd').($s['name'] == $data['user']['name'] ? ' own' : '').'"><td class="tr">'.($s['score'] < $tscore || $tscore < 0 ? $i.'.' : '').'</td><td><span class="clickable" onclick="spyoncitizen('.$s['id'].');">'.(strlen($s['name']) > 16 ? '<abbr title="'.$s['name'].'">'.substr($s['name'],0,14).'..</abbr>' : $s['name']).'</span></td><td>'.$s['score'].'</td></tr>';	
	$tscore = $s['score'];
}
print '</table><br style="clear:left;" /></div>';

// Rewards
print '<div id="stat_rewards" class="subtabcontent hideme">';
print '<h4>'.t('STAT_REWARDS_TITLE').'</h4>';
print '<div class="rewards">';
foreach ($rewards AS $r) {
	print '<div class="reward'.($r['rare'] == 1 ? ' rare' : '').'"><img src="'.t('GAMESERVER_ICON').$r['img'].'.gif" title="'.$r['name'].'" onclick="rlist(\''.addslashes($r['name']).'\','.($data['user']['id'] ? $data['user']['id'] : 0).');" /></div>';
}
print '</div>';
print '<div id="allrgraph" style="clear:both;"></div>';
print '</div>';

// Titel
print '<div id="stat_titles" class="subtabcontent hideme">';
print '<h4>'.t('STAT_TITLES_TITLE').'</h4>';
print '<div class="titles">';
$i = 0;
$break = ceil(count($titles) / 3);
foreach ($titles AS $t) {
	if ( $i > 0 && $i % $break == 0 ) {
		print '</div><div class="titles">';
	}
	$i++;
	print '<div class="rtitle'.($t['rare'] == 1 ? ' rare' : '').'" onclick="tlist(\''.addslashes($t['name']).'\','.($data['user']['id'] ? $data['user']['id'] : 0).');" ><img src="'.t('GAMESERVER_ICON').$t['img'].'.gif" title="'.$t['name'].'" />&nbsp;'.(strlen($t['name']) > 30 ? substr($t['name'],0,30).'.' : $t['name']).'</div>';
}
print '</div>';
print '<div id="alltgraph" style="max-width:310px;"></div>';
print '<br style="clear:both;" />';
print '</div>';

// ### Aktive Städte
print '<div id="stat_towns_active" class="subtabcontent hideme">';
print '<h4>'.t('STAT_ACTIVETOWNS_TITLE').'</h4>
<p>'.t('STAT_ACTIVETOWNS_INFO').'</p>';
print '<div class="town-filter">
<input type="checkbox" id="activetowns-own" onchange="filterTowns(\'a\');" />&nbsp;'.t('SHOW_ONLY_OWN_TOWNS').'<br/>
<input type="checkbox" checked="checked" id="activetowns-defoe" onchange="filterTowns(\'a\');" />&nbsp;'.t('SHOW_DEFOE_TOWNS').' <img src="'.t('GAMESERVER_ICON').'r_surlst.gif" /><br/>
<input type="checkbox" checked="checked" id="activetowns-hard" onchange="filterTowns(\'a\');" />&nbsp;'.t('SHOW_HARD_TOWNS').' <img src="'.t('GAMESERVER_ICON').'r_suhard.gif" /><br/>
<input type="checkbox" id="activetowns-interpolated" onchange="filterTowns(\'a\');" />&nbsp;'.t('SHOW_INTERPOLATED_TOWNS').'<br/>
</div>
<div id="town_stat_graphs" style="float:right;"></div><div>';

print '<table id="stat-towns-table-active" class="stat-towns-table" border="0">';
print '<tr><th>'.t('STAT_CITY_NAME').'</th><th>'.t('POINTS').'</th><th></th><th>'.t('DAY').'</th><th><img src="'.t('GAMESERVER_ICON').'r_explor.gif" title="'.t('STAT_MAP_SIZE').'" /></th><th class="tc"><img src="'.t('GAMESERVER_SMILEY').'h_human.gif" title="'.t('STAT_SURVIVORS').'" /></th><th class="tc"><img src="'.t('GAMESERVER_SMILEY').'h_well.gif" title="'.t('STAT_WATER_WELL').'" /></th><th class="tc"><img src="'.t('GAMESERVER_SMILEY').'h_water.gif" title="'.t('STAT_WATER_BANK').'" /></th><th class="tc"><img src="'.t('GAMESERVER_ITEM').'jerrycan.gif" title="'.t('STAT_JERRY_BANK').'" /></th><th class="tc"><img src="'.t('GAMESERVER_ITEM').'shield.gif" title="'.t('STAT_DEF_ITEMS').'" /></th><th class="tc"><img src="'.t('GAMESERVER_SMILEY').'h_zombie.gif" title="'.t('STAT_LAST_ZOMBIES').'" /></th><th class="tc"><img src="'.t('GAMESERVER_SMILEY').'h_door.gif" title="'.t('STAT_LAST_DEF').'" /></th><th>'.t('STAT_CHAOS').'</th><th>'.t('STAT_DEVAST').'</th><th>'.t('STAT_LAST_UPDATE').'</th></tr>';
$i = 0;
foreach ( $tdata AS $t ) {
	$deff_res = $db->query('SELECT SUM( sb.icount ) 
FROM dvoo_bankitems sb
INNER JOIN dvoo_items si ON si.iid = sb.iid
AND si.icat =  "Armor" WHERE sb.tid = '.$t['id'].' AND sb.cday = '.$t['day']);
	$deff = $deff_res[0][0];
	$i++;
	$min = floor((int) date('i', $t['stamp']) / 10) * 10;
	print '<tr class="'.(($t['stamp'] < $stat_day) ? 'interpolated hideme ' : 'current ').($i % 2 == 0 ? 'even' : 'odd').(in_array($t['id'],$own_towns) ? ' own' : ' not-own').($t['hard'] == 1 ? ' hard' : ' defoe').'"><td>'.($t['hard'] == 1 ? '<img src="'.t('GAMESERVER_ICON').'r_suhard.gif" />' : '<img src="'.t('GAMESERVER_ICON').'r_surlst.gif" />').'&nbsp;'.$t['name'].'</td><td class="tr">'.getPoints($t['id'], $t['day']).'</td><td><img class="clickable" src="'.t('GAMESERVER_ITEM').'tagger.gif" onclick="spyontown('.$t['id'].','.$t['day'].');" /></td><td class="tr">'.$t['day'].'</td><td>'.$t['h'].'x'.$t['w'].'</td><td class="tr">'.($t['citizens'] > 0 ? $t['citizens'] : '').'</td><td class="tr">'.$t['water'].'</td><td class="tr">'.$t['bwater'].'</td><td class="tr">'.$t['bjerry'].'</td><td class="tr">'.$deff.'</td><td class="tr">'.$t['z'].'</td><td class="tr">'.$t['v'].'</td><td class="tc">'.($t['chaos'] ? '<img src="css/img/check.png" title="'.t('STAT_CHAOS_X').'">' : '').'</td><td class="tc">'.($t['devast'] ? '<img src="css/img/check.png" title="'.t('STAT_DEVAST_X').'">' : '').'</td><td>'.(date('Ymd',time()) == date('Ymd',$t['stamp']) ? t('TODAY') : ((int) date('Ymd',time()) == (int) date('Ymd',$t['stamp']) + 1 ? t('YESTERDAY') : date('M jS',$t['stamp']) ) ).', '.date('H:', $t['stamp']).($min == 0 ? '00' : $min).t('TIME_APPENDIX').'</td></tr>';
	if ( $i > 0 && $i % 20 == 0 ) {
		print '<tr class="not-own"><th>'.t('STAT_CITY_NAME').'</th><th>'.t('POINTS').'</th><th></th><th>'.t('DAY').'</th><th><img src="'.t('GAMESERVER_ICON').'r_explor.gif" title="'.t('STAT_MAP_SIZE').'" /></th><th class="tc"><img src="'.t('GAMESERVER_SMILEY').'h_human.gif" title="'.t('STAT_SURVIVORS').'" /></th><th class="tc"><img src="'.t('GAMESERVER_SMILEY').'h_well.gif" title="'.t('STAT_WATER_WELL').'" /></th><th class="tc"><img src="'.t('GAMESERVER_SMILEY').'h_water.gif" title="'.t('STAT_WATER_BANK').'" /></th><th class="tc"><img src="'.t('GAMESERVER_ITEM').'jerrycan.gif" title="'.t('STAT_JERRY_BANK').'" /></th><th class="tc"><img src="'.t('GAMESERVER_ITEM').'shield.gif" title="'.t('STAT_DEF_ITEMS').'" /></th><th class="tc"><img src="'.t('GAMESERVER_SMILEY').'h_zombie.gif" title="'.t('STAT_LAST_ZOMBIES').'" /></th><th class="tc"><img src="'.t('GAMESERVER_SMILEY').'h_door.gif" title="'.t('STAT_LAST_DEF').'" /></th><th>'.t('STAT_CHAOS').'</th><th>'.t('STAT_DEVAST').'</th><th>'.t('STAT_LAST_UPDATE').'</th></tr>';
	}
}
print '</table>';
print '</div>';
print '</div>';


// Alte Städte
print '<div id="stat_towns_old" class="subtabcontent hideme">';
print '<h4>'.t('STAT_OLDTOWNS_TITLE').'</h4>
<p>'.t('STAT_OLDTOWNS_INFO').'</p>
<div class="town-filter">
<input type="checkbox" id="oldtowns-own" onchange="filterOwnTowns(\'o\');" />&nbsp;'.t('SHOW_ONLY_OWN_TOWNS').'
</div><div>';
print '<ul id="oldtownmenu">';
foreach ( $seasons AS $sno => $sdates ) {
	print '<li id="otl-s'.$sno.'"><a href=".ot-s5">'.t('SEASON').' '.$sno.'</a></li>';
}
print '</ul>';

$j = array();
$oldtown_seasons = array();
foreach ( $todata AS $t ) {
	$town_season_number = getTownSeasonNumber($t['stamp'], $t['day'], $seasons);
	$town_data = '<tr class="'.($i % 2 == 0 ? 'even' : 'odd').(in_array($t['id'],$own_towns) ? ' own' : ' not-own').'"><td class="tr">'.$i.'.</td><td>'.($t['hard'] == 1 ? '<img src="http://data.dieverdammten.de/gfx/icons/r_suhard.gif" />' : '<img src="http://data.dieverdammten.de/gfx/icons/r_surlst.gif" />').'&nbsp;'.$t['name'].'</td><td>'.($t['id'] >= 10000 ? '<img class="clickable" src="'.t('GAMESERVER_ITEM').'tagger.gif" onclick="spyontown('.$t['id'].','.$t['day'].');" />' : '').'</td><td class="tr">'.getPoints($t['id'], $t['day']).'</td><td class="tr">'.$t['day'].'</td><td class="tc">'.($t['chaos'] ? '<img src="css/img/check.png" title="'.t('STAT_CHAOS_X').'">' : '').'</td><td class="tc">'.($t['devast'] ? '<img src="css/img/check.png" title="'.t('STAT_DEVAST_X').'">' : '').'</td></tr>'; // '.getPoints($t['id'], $t['day']).'
	$oldtown_seasons[$town_season_number] .= $town_data;
}

foreach ($oldtown_seasons AS $sno => $data) {
	print '<table id="stat-towns-table-old" class="stat-towns-table ot-s'.$sno.'" border="0">';
	print '<tr><th></th><th>'.t('STAT_CITY_NAME').'</th><th></th><th>'.t('POINTS').'</th><th>'.t('STAT_LAST_DAY').'</th><th>'.t('STAT_CHAOS').'</th><th>'.t('STAT_DEVAST').'</th></tr>';
	print $data;
	print '</table>';
}
print '</div>';
print '</div>';

// JS
print '<script type="text/javascript">
	function zgraph(d,s) {
		// load stat content
		$("#dayzgraph").html("<img src=\"stat.dayz.ajax.php?d=" + d + "&s=" + s + "&c=" + $("#c").val() + "&cb='.date("Ymd",time()).'&g=" + $("#ztype").val() + "\" />");
	}
	
	function rlist(r,u) {
		// load stat content
		var rl = $.ajax({
			type: "POST",
			url: "stat.rewz.ajax.php",
			data: "r="+r+"&u="+u,
			success: function(msg) {
				$("#allrgraph").html(msg);
			}
		});
	}
	
	function tlist(t,u) {
		// load stat content
		var rl = $.ajax({
			type: "POST",
			url: "stat.retz.ajax.php",
			data: "t="+t+"&u="+u,
			success: function(msg) {
				$("#alltgraph").html(msg);
				$("html, body").animate({scrollTop:350}, "slow");
			}
		});
	}
	
	function submitSCform() {  
		var sc = $.post(  
			"stat.scalc.ajax.php",  
			$("#scalc").serialize(),  
			function(data){  
				$("#scalc_result").html(data);							
			}  
		);
	}
	
	function filterTowns(l) {
		if ( l == "o") {
			$("#stat-towns-table-old tr.not-own").toggleClass("hideme");
		}
		else if ( l == "a") {
			var own = $("#activetowns-own").attr("checked");
			var def = $("#activetowns-defoe").attr("checked");
			var pan = $("#activetowns-hard").attr("checked");
			var ipd = $("#activetowns-interpolated").attr("checked");
			$("#stat-towns-table-active tr").addClass("hideme");
			var classes1 = "";
			var classes2 = "";
			if ( def ) {
				classes1 += ".defoe";
				if ( ipd ) {
					classes1 += ".interpolated";
				}
				else {
					classes1 += ".current";
				}
			}
			if ( pan ) {
				classes2 += ".hard";
				if ( ipd ) {
					classes2 += ".interpolated";
				}
				else {
					classes2 += ".current";
				}
			}
			if ( classes1 != "" ) {
				$("#stat-towns-table-active tr"+classes1).removeClass("hideme");
			}
			if ( classes2 != "" ) {
				$("#stat-towns-table-active tr"+classes2).removeClass("hideme");
			}
			if ( own ) {
				$("#stat-towns-table-active tr.not-own").addClass("hideme");
			}
		}
	}
	
	function filterOwnTowns(l) {
		if ( l == "o") {
			$("#stat-towns-table-old tr.not-own").toggleClass("hideme");
		}
		else if ( l == "a") {
			$("#stat-towns-table-active tr.not-own").toggleClass("hideme");
		}
	}
	
	function filterAllTowns(l,d) {
		if ( l == "o") {
			$("#stat-towns-table-old tr").each(function(index) {
				if ( $(this).attr("rel") < d ) {
					$(this).addClass("hideme");
				}
				else {
					$(this).removeClass("hideme");
				}
			});
		}
		else if ( l == "a") {
			$("#stat-towns-table-active tr.sd"+d).toggleClass("hideme");
		}
	}
	
	function spyontown(t,d) {
		// load stat content
		var st = $.ajax({
			type: "POST",
			url: "stat.spyt.ajax.php",
			data: "t="+t+"&d="+d,
			success: function(msg) {
				$("#spy-content").hide();
				$("html, body").animate({scrollTop:90}, "slow");
				$("#spy").animate({
					width: "12px",
					height: "720px",
					left: "489px",
					top: "95px"
				}, 250, function() {
					$("#spy").animate({
						width: "930px",
						left: "15px"
					}, 250, function() {
						$("#spy-content").html(msg).fadeIn(500);
					});
				});
			}
		});
	}
	function spyclose() {
		$("#spy-content").fadeOut(250, function() {
			$("#spy").animate({
				width: "0px",
				height: "0px",
				left: "495px",
				top: "200px"
			}, 250, function() {});
		});
	}
	function spyoncitizen(c) {
		// load stat content
		var sc = $.ajax({
			type: "POST",
			url: "stat.spyc.ajax.php",
			data: "u="+c+"&s='.$u.'",
			success: function(msg) {
				$("#spy-content").hide();
				$("html, body").animate({scrollTop:90}, "slow");
				$("#spy").animate({
					width: "12px",
					height: "720px",
					left: "489px",
					top: "95px"
				}, 250, function() {
					$("#spy").animate({
						width: "930px",
						left: "15px"
					}, 250, function() {
						$("#spy-content").html(msg).fadeIn(500);
					});
				});
			}
		});
	}
			
	var stat_cur = "#stat_zombie";
	$("ul#sub-stat li a").click(function (e) { 
		e.preventDefault();
		var newStat = $(this).attr("href");
		$(stat_cur).fadeOut();
		$(newStat).fadeIn("slow");
		stat_cur = newStat;
	});
	var zomc = "#stats-defoe'.max(array_keys($seasons)).'";
	$("ul#sub-zomstat li a").click(function (e) { 
		e.preventDefault();
		var zomn = $(this).attr("href");
		$(zomc).fadeOut();
		$(zomn).fadeIn("slow");
		zomc = zomn;
	});
	var curOTL = ".ot-s'.max(array_keys($seasons)).'";
	$("ul#oldtownmenu li a").click(function (e) { 
		e.preventDefault();
		var newOTL = $(this).attr("href");
		$(curOTL).fadeOut();
		$(newOTL).fadeIn("slow");
		curOTL = newOTL;
	});
	$("#dc_button").click();
</script>';

function getPoints($t,$d) {
	$file = 'xml/history/'.$t.'-'.$d.'.xml';
	if ( file_exists($file) ) {
		$xml_string = file_get_contents($file);
		$xml = simplexml_load_string($xml_string);
	}
	else {
		return '<span style="color:#c00;font-weight:bold;">x</span>';
	}
	if ($xml) {
		$citizens = $xml->data->citizens;
		$cadavers = $xml->data->cadavers;
		
		// cit
		$residents = $deadguys = $points = 0;
		$cit = array();
		foreach ( $citizens->children() AS $ca ) {
			$residents++;	
			$points += ($d - 1);
		}

		// cad
		$tcadavers = array();
		foreach ( $cadavers->children() AS $ca ) {	
			$deadguys++;
			$points += ((int) $ca['day'] - 1);
			if ( (int) $ca['dtype'] == 6 ) { $points++; }
		}
		
		return '<span title="'.t('RANKINGPOINTS', array('%r' => $residents, '%d' => $deadguys)).'">'.$points.'</span>';
	}
	else {
		return '<span style="color:#c00;font-weight:bold;">x</span>';
	}
}
function getTownSeasonNumber($stamp, $day, $seasons) {
	$townstart = $stamp - (86400 * $day);
	foreach ( $seasons AS $sno => $sdates ) {
		if ( is_null($sdates['start']) && is_null($sdates['end']) ) {
			return $sno;
		}
		elseif ( is_null($sdates['start']) && $townstart < $sdates['end']) {
			return $sno;
		}
		elseif ( is_null($sdates['end']) && $townstart > $sdates['start']) {
			return $sno;
		}
		elseif ( $townstart > $sdates['start'] && $townstart < $sdates['end']) {
			return $sno;
		}
		else {
			// this is not the season
		}
	}
}