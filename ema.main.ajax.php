<?php
include_once 'system.php';
$db = new Database();

// get version number
$v = (int) $_POST['v'];
// get process number
$p = (int) $_POST['p'];
if ( $p == 2 ) {
	$u = (int) $_POST['u'];
}
elseif ( $p == 1 ) {
	$k = htmlspecialchars(strip_tags($_POST['k']));
	$n = htmlspecialchars(strip_tags($_POST['n']));
}
else {
	// no data send -> start
	print '<script type="text/javascript">
			$("#link_einwohnermeldeamt").remove();
			$("#einwohnermeldeamt").remove();
		</script>';
	exit;
}

$session = $db->query(' SELECT xml FROM dvoo_rawdata WHERE id = '.$u.' ORDER BY time DESC LIMIT 1 ');

if ( $data = unserialize($session[0]['xml']) ) {

	if ( !isset($data['user']['name']) || $data['user']['name'] == '' ) {
		$data['user']['name'] = $n;
		if ( is_null($data['user']['name']) || $data['user']['name'] == '' ) {
			print '<div class="error">'.t('EMA_NONAME').'</div>';
		}
	}
	if ( !isset($data['user']['id']) || $data['user']['id'] == '' ) {
		$data['user']['id'] = $u;
	}
	
	// register
	$people = array();
	foreach ($data['citizens'] AS $cid => $cdata) {
		if ( $cdata['job'] != '' ) {
			$people[$cdata['job']][$cid] = $cdata;
		}
		else {
			$people['none'][$cid] = $cdata;
		}
	}
	foreach ($data['cadavers'] AS $cid => $cdata) {
		$people['dead'][$cid] = $cdata;
	}
	
	// new register
	$citlist = $cadlist = array();
	foreach ($data['citizens'] AS $cid => $cdata) {
		$citlist[$cdata['name']] = $cdata;
	}
	#ksort($citlist);
	foreach ($data['cadavers'] AS $cid => $cdata) {
		$cadlist[$cdata['name']] = $cdata;
	}
	#ksort($cadlist);
	
	/* ### OUTPUT ### */
	print '<ul id="sub-ema" class="subtabs">
		<li><a href="#ema_newregister">'.t('EMA_REGISTER').'</a></li>
		<li class="hideme"><a href="#ema_register">'.t('EMA_REGISTER').'</a></li>
		<li><a href="#ema_form">'.t('EMA_OFFICE').'</a></li>
	</ul>';

	print '<div id="ema_form" class="subtabcontent hideme">';
	
?>

	<h3><?php print t('EMA_OFFICE_TITLE'); ?></h3>
	<p><?php print t('EMA_OFFICE_INFO'); ?></p>
	<div style="background:#fed;border:1px solid #dcb;margin:6px;padding:6px;overflow:hidden;"><?php print t('EMA_OFFICE_TUTORIAL'); ?></div>
	<form method="POST" id="tp_form" name="tp_form" onsubmit="submitTPform();return false;">
		<input type="hidden" value="<?php print $data['town']['id']; ?>" name="t" />
		<input type="hidden" value="<?php print $data['user']['id']; ?>" name="u" />
		<input type="hidden" value="<?php print $data['current_day']; ?>" name="c" />
		<div id="tp_form_table_data">
			<h3><?php print $data['user']['name']; ?></h3>
			<div>
				<div class="button" style="float:right;">
					<input type="button" value="<?php print t('PRESET_SAVE'); ?>" onclick="savePreset();" />&nbsp;
					<input type="button" value="<?php print t('PRESET_LOAD'); ?>" onclick="loadPreset();" />
					<div id="presetStatus"></div>
				</div>
				<p><?php print t('EMA_FORM_DAY'); ?> <select id="tp_day" name="tp_day" onchange="getSavedData($('#tp_day').val(), <?php print $data['user']['id']; ?>, <?php print $data['town']['id']; ?>);"><?php for ($i = $data['current_day']; $i < $data['current_day'] + 3; $i++ ) { print '<option value="'.$i.'">'.$i.'</option>'; } ?></select></p>
			</div>
			<h4><?php print t('OWN_STATUS'); ?></h4>
			<fieldset class="tp_status">
				<p><input type="checkbox" name="thirsty" id="thirsty" value="1" /><label for="thirsty"><?php print t('EMA_S_THIRSTY'); ?></label></p>
				<p><input type="checkbox" name="hangover" id="hangover" value="1" /><label for="hangover"><?php print t('EMA_S_HANGOVER'); ?></label></p>
			</fieldset>
			<fieldset class="tp_status">
				<p><input type="checkbox" name="paralyzed" id="paralyzed" value="1" /><label for="paralyzed"><?php print t('EMA_S_TERRORIZED'); ?></label></p>
				<p><input type="checkbox" name="clean" id="clean" value="1" /><label for="clean"><?php print t('EMA_S_CLEAN'); ?></label></p>
			</fieldset>
			<fieldset class="tp_status">
				<p><input type="checkbox" name="topform" id="topform" value="1" /><label for="topform"><?php print t('EMA_S_TOPFORM'); ?></label></p>
				<p><input type="checkbox" name="safe" id="safe" value="1" /><label for="safe"><?php print t('EMA_S_SAFE'); ?></label></p>
			</fieldset>
			<h4><?php print t('EMA_CARE'); ?>:</h4>
			<fieldset class="tp_inventory">
				<p><input type="checkbox" name="water" id="water" value="1" /><label for="water"><?php print t('EMA_S_WATER'); ?></label></p>
				<p><input type="checkbox" name="food_defoe" id="food_defoe" value="1" /><label for="food_ap_6"><?php print t('EMA_S_FOOD_DEFOE'); ?></label></p>
				<p><input type="checkbox" name="food_yummy" id="food_yummy" value="1" /><label for="food_ap_7"><?php print t('EMA_S_FOOD_YUMMY'); ?></label></p>
			</fieldset>
			<fieldset class="tp_inventory">
				<p><input type="checkbox" name="drug_ster" id="drug_ster" value="1" /><label for="drug_ap_6"><?php print t('EMA_S_STEROID'); ?></label></p>
				<div><label for="drug_ster_count" style="padding-left:20px;"><?php print t('AMOUNT'); ?></label> <input type="text" size="2" name="drug_ster_count" id="drug_ster_count" value="1" style="text-align:right;width:40px;padding:0;" /></div>
				<p><input type="checkbox" name="drug_twin" id="drug_twin" value="1" /><label for="drug_ap_8"><?php print t('EMA_S_TWINOID'); ?></label></p>
				<div><label for="drug_twin_count" style="padding-left:20px;"><?php print t('AMOUNT'); ?></label> <input type="text" size="2" name="drug_twin_count" id="drug_twin_count" value="1" style="text-align:right;width:40px;padding:0;" /></div>
			</fieldset>
			<fieldset class="tp_inventory">
				<p><input type="checkbox" name="alcohol" id="alcohol" value="1" /><label for="alcohol"><?php print t('EMA_S_ALCOHOL'); ?></label></p>
				<p><input type="checkbox" name="coffee" id="coffee" value="1" /><label for="coffee"><?php print t('EMA_S_COFFEE'); ?></label></p>
				<div><label for="coffee_count" style="padding-left:20px;"><?php print t('AMOUNT'); ?></label> <input type="text" size="2" name="coffee_count" id="coffee_count" value="1" style="text-align:right;width:40px;padding:0;" /></div>
			</fieldset>
			<fieldset class="tp_inventory">
				<p><input type="checkbox" name="alarm" id="alarm" value="1" /><label for="alarm"><?php print t('EMA_S_ALARM'); ?></label></p>
				<p><input type="checkbox" name="gamble" id="gamble" value="1" /><label for="gamble"><?php print t('EMA_S_GAMBLE'); ?></label></p>
				<p><input type="checkbox" name="sleep" id="sleep" value="1" /><label for="sleep"><?php print t('EMA_S_SIESTA'); ?></label></p>
				<p><input type="checkbox" name="lunge" id="lunge" value="1" /><label for="lunge"><?php print t('EMA_S_2NDLUNG'); ?></label></p>
			</fieldset>

			<table id="tp_form_table">
				<thead>
					<tr>
						<?php for ($i = 0; $i < 24; $i++ ) { print '<th>'.$i.'</th>'; } ?>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<?php $zebra = 1; for ($i = 0; $i < 24; $i++ ) { $zebra++; print '<td id="tpo'.$i.'" class="hc '.($zebra % 2 == 0 ? 'even' : 'odd').'"><div id="tp_'.$i.'_0" class="tpo t0 opa100" onclick="changeTP('.$i.',0);"><div class="hideme tpsymbol tpsymbol0"></div></div><div id="tp_'.$i.'_1" class="tpo t1 opa50" onclick="changeTP('.$i.',1);"><div class="hideme tpsymbol tpsymbol1"></div></div><div id="tp_'.$i.'_2" class="tpo t2 opa50" onclick="changeTP('.$i.',2);"><div class="hideme tpsymbol tpsymbol2"></div></div><div id="tp_'.$i.'_3" class="tpo t3 opa50" onclick="changeTP('.$i.',3);"><div class="hideme tpsymbol tpsymbol3"></div></div><input type="hidden" name="tp'.$i.'" id="tp'.$i.'" value="0"/></td>'; } ?>
						<td class="odd"><input type="submit" name="tp_form_submit" id="tp_form_submit" value="<?php print t('ENTER_VALUES'); ?>" /></td>
					</tr>
				</tbody> 
			</table>
		</div>
	</form>

	<h3 id="tp_pinheader"><?php print t('EMA_LIST'); ?></h3>
	<p style="float:right;"><a href="#" id="symbol-toggler"><?php print t('EMA_TOGGLE_CODES'); ?></a></p>
	<div id="tp_pinboard">
		<div class="loading"></div>
	</div>
</div>

<div id="ema_newregister" class="subtabcontent">
<?php #print '<pre>'.var_export($citlist,true).'</pre>'; ?>
	<table style="font-size:.75em;">
		<thead>
			<th>Avatar</th>
			<th>Name</th>
			<th>Position</th>
			<th>Yearbook message</th>
			<th>Base def</th>
			<th>Soul points</th>
			<th>Last Login</th>
		</thead>
		<tbody>
	<?php
		$z = 0;
		foreach ( $citlist AS $cp ) {
			if ( is_array($cp) && count($cp) > 0 ) {
				$ll = null;
				$q = ' SELECT score FROM dvoo_stat_soul WHERE uid = '.$cp['id'];
				$r = $db->query($q);
				$sp = $r[0][0];
				$q = ' SELECT l.time FROM dvoo_login_log l INNER JOIN dvoo_citizens c ON c.scode = l.k WHERE c.id = '.$cp['id'].' ORDER BY l.time DESC LIMIT 1 ';
				$r = $db->query($q);
				$ll = $r[0][0];
				$z++;
				print '<tr id="cp'.$cp['id'].'" class="citizen-row '.($z % 2 == 0 ? 'even' : 'odd').'">';
				if ($cp['avatar'] != '') {
					print '<td class="cr-avatar"><img class="small-avatar" src="'.(strpos($cp['avatar'],'http://') === FALSE ? $data['system']['avatar_url'] : '').$cp['avatar'].'"></td>';
				}
				else {
					print '<td class="cr-avatar"><img class="small-avatar" src="http://data.die2nite.com/gfx/design/avatarBg.gif"></td>';
				}
				print '<th class="cr-name"><div class="stats-wrapper"><div class="stats">'.minispy($cp['id']).'</div></div><img class="job" src="'.t('GAMESERVER_ITEM').$jobs[$cp['job']]['img'].'.gif" /> '.$cp['name'].($cp['ban'] == 1 ? ' <img src="'.t('GAMESERVER_SMILEY').'h_ban.gif" />' : '').'</th>
					<td class="cr-position">'.($cp['x'] == $data['town']['x'] && $data['town']['y'] == $cp['y'] ? ($cp['out'] == 1 ? t('LOC_ATDOOR') : t('LOC_INTOWN')) : '['.($cp['x'] - $data['town']['x']).'|'.($data['town']['y'] - $cp['y']).']').'</td>
					<td class="cr-message">'.$cp['msg'].'</td>
					<td class="cr-basedef">'.$cp['baseDef'].'</td>
					<td class="cr-soulpoints">'.$sp.'</td>
					<td class="cr-lastlogin">'.($ll > 0 ? (time() - $ll < 60 ? 'some moments ago' : (time() - $ll < 3660 ? floor((time() - $ll)/60).' minutes ago' : (time() - $ll < 90000 ? floor((time() - $ll)/3600).' hours ago' : (floor((time() - $ll)/86400).' days ago')))) : 'never').'</td>
				</tr>';
			}
		}
		foreach ( $cadlist AS $cp ) {
			if ( is_array($cp) && count($cp) > 0 ) {
				$ll = null;
				$q = ' SELECT score FROM dvoo_stat_soul WHERE uid = '.$cp['id'];
				$r = $db->query($q);
				$sp = $r[0][0];
				$q = ' SELECT l.time FROM dvoo_login_log l INNER JOIN dvoo_citizens c ON c.scode = l.k WHERE c.id = '.$cp['id'].' ORDER BY l.time DESC LIMIT 1 ';
				$r = $db->query($q);
				$ll = $r[0][0];
				$z++;
				print '<tr id="cp'.$cp['id'].'" class="citizen-row '.($z % 2 == 0 ? 'even' : 'odd').'">
					<td class="cr-avatar"></td>
					<th class="cr-name"><div class="stats-wrapper"><div class="stats">'.minispy($cp['id']).'</div></div><img src="'.t('GAMESERVER_SMILEY').'h_death.gif" /> '.$cp['name'].'</th>
					<td class="cr-position">'.t('DAY').' '.$cp['day'].': '.t('DC'.$cp['dtype']).'<br/><span class="register_cleanup">'.($cp['cleanup_type'] == 'garbage' ? t('CADAVER_GARBAGED').(trim($cp['cleanup_user']) != '' ? ' '.t('BY').' '.$cp['cleanup_user'] : '') : ($cp['cleanup_type'] == 'water' ? t('CADAVER_SPRINKLED').(trim($cp['cleanup_user']) != '' ? ' '.t('BY').' '.$cp['cleanup_user'] : '') : '')).'</span></td>
					<td class="cr-message">'.$cp['msg'].'</td>
					<td class="cr-basedef"></td>
					<td class="cr-soulpoints">'.$sp.'</td>
					<td class="cr-lastlogin">'.($ll > 0 ? (time() - $ll < 60 ? 'some moments ago' : (time() - $ll < 3660 ? floor((time() - $ll)/60).' minutes ago' : (time() - $ll < 90000 ? floor((time() - $ll)/3600).' hours ago' : (floor((time() - $ll)/86400).' days ago')))) : 'never').'</td>
				</tr>';
			}
		}
	?>
		</tbody>
	</table>
</div>

<div id="ema_register" class="subtabcontent hideme">
	<p><?php print t('EMA_REGISTER_INFO'); ?></p>
	<div class="ema_register">
		
		<?php if ( count($people['basic']) > 0 ) { ?>
		<h4><?php print count($people['basic']).' '.t('CITIZEN'); ?></h4>
		<table id="ema_register_basic">
			<thead>
				<tr><th><?php print t('NAME'); ?></th><th><?php print t('LOCATION'); ?></th></tr>
			</thead>
			<tbody>
				<?php
					$i = 0;
					foreach($people['basic'] AS $cid => $cdata) {
						$i++;
						print '<tr class="'.($i % 2 == 0 ? 'even' : 'odd').($cid == $u ? ' own' : '').'"><td>'.$cdata['name'].'</td><td class="register_coords">'.($cdata['x'] == $data['town']['x'] && $data['town']['y'] == $cdata['y'] ? ($cdata['out'] == 1 ? t('LOC_ATDOOR') : t('LOC_INTOWN')) : '['.($cdata['x'] - $data['town']['x']).'|'.($data['town']['y'] - $cdata['y']).']').'</td></tr>';
					}
				?>
			</tbody>
		</table>
		<?php } ?>
		
		<?php if ( count($people['none']) > 0 ) { ?>
		<h4><?php print count($people['none']).' '.t('CITIZEN_NOJOB'); ?></h4>
		<table id="ema_register_basic">
			<thead>
				<tr><th><?php print t('NAME'); ?></th><th><?php print t('LOCATION'); ?></th></tr>
			</thead>
			<tbody>
				<?php
					$i = 0;
					foreach($people['none'] AS $cid => $cdata) {
						$i++;
						print '<tr class="'.($i % 2 == 0 ? 'even' : 'odd').($cid == $u ? ' own' : '').'"><td>'.$cdata['name'].'</td><td class="register_coords">'.($cdata['x'] == $data['town']['x'] && $data['town']['y'] == $cdata['y'] ? ($cdata['out'] == 1 ? t('LOC_ATDOOR') : t('LOC_INTOWN')) : '['.($cdata['x'] - $data['town']['x']).'|'.($data['town']['y'] - $cdata['y']).']').'</td></tr>';
					}
				?>
			</tbody>
		</table>
		<?php } ?>
	</div>
	<div class="ema_register">
		
		<?php if ( count($people['eclair']) > 0 ) { ?>
		<h4><?php print count($people['eclair']).' '.t('SCOUT'); ?></h4>
		<table id="ema_register_eclair">
			<thead>
				<tr><th><?php print t('NAME'); ?></th><th><?php print t('LOCATION'); ?></th></tr>
			</thead>
			<tbody>
				<?php
					$i = 0;
					foreach($people['eclair'] AS $cid => $cdata) {
						$i++;
						print '<tr class="'.($i % 2 == 0 ? 'even' : 'odd').($cid == $u ? ' own' : '').'"><td>'.$cdata['name'].'</td><td class="register_coords">'.($cdata['x'] == $data['town']['x'] && $data['town']['y'] == $cdata['y'] ? ($cdata['out'] == 1 ? t('LOC_ATDOOR') : t('LOC_INTOWN')) : '['.($cdata['x'] - $data['town']['x']).'|'.($data['town']['y'] - $cdata['y']).']').'</td></tr>';
					}
				?>
			</tbody>
		</table>
		<?php } ?>
		
		<?php if ( count($people['collec']) > 0 ) { ?>
		<h4><?php print count($people['collec']).' '.t('SCAVENGER'); ?></h4>
		<table id="ema_register_collec">
			<thead>
				<tr><th><?php print t('NAME'); ?></th><th><?php print t('LOCATION'); ?></th></tr>
			</thead>
			<tbody>
				<?php
					$i = 0;
					foreach($people['collec'] AS $cid => $cdata) {
						$i++;
						print '<tr class="'.($i % 2 == 0 ? 'even' : 'odd').($cid == $u ? ' own' : '').'"><td>'.$cdata['name'].'</td><td class="register_coords">'.($cdata['x'] == $data['town']['x'] && $data['town']['y'] == $cdata['y'] ? ($cdata['out'] == 1 ? t('LOC_ATDOOR') : t('LOC_INTOWN')) : '['.($cdata['x'] - $data['town']['x']).'|'.($data['town']['y'] - $cdata['y']).']').'</td></tr>';
					}
				?>
			</tbody>
		</table>
		<?php } ?>
		
		<?php if ( count($people['guardian']) > 0 ) { ?>
		<h4><?php print count($people['guardian']).' '.t('GUARDIAN'); ?></h4>
		<table id="ema_register_guardian">
			<thead>
				<tr><th><?php print t('NAME'); ?></th><th><?php print t('LOCATION'); ?></th></tr>
			</thead>
			<tbody>
				<?php
					$i = 0;
					foreach($people['guardian'] AS $cid => $cdata) {
						$i++;
						print '<tr class="'.($i % 2 == 0 ? 'even' : 'odd').($cid == $u ? ' own' : '').'"><td>'.$cdata['name'].'</td><td class="register_coords">'.($cdata['x'] == $data['town']['x'] && $data['town']['y'] == $cdata['y'] ? ($cdata['out'] == 1 ? t('LOC_ATDOOR') : t('LOC_INTOWN')) : '['.($cdata['x'] - $data['town']['x']).'|'.($data['town']['y'] - $cdata['y']).']').'</td></tr>';
					}
				?>
			</tbody>
		</table>
		<?php } ?>
		
		<?php if ( count($people['tamer']) > 0 ) { ?>
		<h4><?php print count($people['tamer']).' '.t('TAMER'); ?></h4>
		<table id="ema_register_tamer">
			<thead>
				<tr><th><?php print t('NAME'); ?></th><th><?php print t('LOCATION'); ?></th></tr>
			</thead>
			<tbody>
				<?php
					$i = 0;
					foreach($people['tamer'] AS $cid => $cdata) {
						$i++;
						print '<tr class="'.($i % 2 == 0 ? 'even' : 'odd').($cid == $u ? ' own' : '').'"><td>'.$cdata['name'].'</td><td class="register_coords">'.($cdata['x'] == $data['town']['x'] && $data['town']['y'] == $cdata['y'] ? ($cdata['out'] == 1 ? t('LOC_ATDOOR') : t('LOC_INTOWN')) : '['.($cdata['x'] - $data['town']['x']).'|'.($data['town']['y'] - $cdata['y']).']').'</td></tr>';
					}
				?>
			</tbody>
		</table>
		<?php } ?>
		
		
		
		<?php if ( count($people['hunter']) > 0 ) { ?>
		<h4><?php print count($people['hunter']).' '.t('HUNTER'); ?></h4>
		<table id="ema_register_hunter">
			<thead>
				<tr><th><?php print t('NAME'); ?></th><th><?php print t('LOCATION'); ?></th></tr>
			</thead>
			<tbody>
				<?php
					$i = 0;
					foreach($people['hunter'] AS $cid => $cdata) {
						$i++;
						print '<tr class="'.($i % 2 == 0 ? 'even' : 'odd').($cid == $u ? ' own' : '').'"><td>'.$cdata['name'].'</td><td class="register_coords">'.($cdata['x'] == $data['town']['x'] && $data['town']['y'] == $cdata['y'] ? ($cdata['out'] == 1 ? t('LOC_ATDOOR') : t('LOC_INTOWN')) : '['.($cdata['x'] - $data['town']['x']).'|'.($data['town']['y'] - $cdata['y']).']').'</td></tr>';
					}
				?>
			</tbody>
		</table>
		<?php } ?>
		
		<?php if ( count($people['tech']) > 0 ) { ?>
		<h4><?php print count($people['tech']).' '.t('TECH'); ?></h4>
		<table id="ema_register_tech">
			<thead>
				<tr><th><?php print t('NAME'); ?></th><th><?php print t('LOCATION'); ?></th></tr>
			</thead>
			<tbody>
				<?php
					$i = 0;
					foreach($people['tech'] AS $cid => $cdata) {
						$i++;
						print '<tr class="'.($i % 2 == 0 ? 'even' : 'odd').($cid == $u ? ' own' : '').'"><td>'.$cdata['name'].'</td><td class="register_coords">'.($cdata['x'] == $data['town']['x'] && $data['town']['y'] == $cdata['y'] ? ($cdata['out'] == 1 ? t('LOC_ATDOOR') : t('LOC_INTOWN')) : '['.($cdata['x'] - $data['town']['x']).'|'.($data['town']['y'] - $cdata['y']).']').'</td></tr>';
					}
				?>
			</tbody>
		</table>
		<?php } ?>
		
	</div>
	<div class="ema_register">
		
		<?php if ( count($people['dead']) > 0 ) { ?>
		<h4><?php print count($people['dead']).' '.t('DEAD_CITIZEN'); ?></h4>
		<table id="ema_register_dead">
			<thead>
				<tr><th><?php print t('NAME'); ?></th><th><?php print t('DAY'); ?></th><th><?php print t('DEATHCAUSE'); ?></th></tr>
			</thead>
			<tbody>
				<?php
					$i = 0;
					foreach($people['dead'] AS $cid => $cdata) {
						$i++;
						print '<tr class="'.($i % 2 == 0 ? 'even' : 'odd').($cid == $u ? ' own' : '').'"><td>'.$cdata['name'].'</td><td>'.$cdata['day'].'</td><td>';
						switch ($cdata['dtype']) {
							case 1:
								print t('DC1');
								break;
								
							case 2:
								print t('DC2');
								break;
								
							case 3:
								print t('DC3');
								break;
							
							case 4:
								print t('DC4');
								break;
								
							case 5:
								print t('DC5');
								break;
								
							case 6:
								print t('DC6');
								break;
								
							case 7:
								print t('DC7');
								break;
								
							case 8:
								print t('DC8');
								break;
								
							case 9:
								print t('DC9');
								break;
								
							case 10:
								print t('DC10');
								break;
								
							case 11:
								print t('DC11');
								break;
								
							case 12:
								print t('DC12');
								break;
								
							case 13:
								print t('DC13');
								break;
								
							case 14:
								print t('DC14');
								break;
								
							default:
								print t('DC0');
								break;
						}
						if ( isset($cdata['cleanup_type']) ) {
							print '<br/><span class="register_cleanup">';
							switch ($cdata['cleanup_type']) {
								case 'garbage':
									print t('CADAVER_GARBAGED');
									print (trim($cdata['cleanup_user']) != '' ? ' '.t('BY').' '.$cdata['cleanup_user'] : '');
									break;
								case 'water':
									print t('CADAVER_SPRINKLED');
									print (trim($cdata['cleanup_user']) != '' ? ' '.t('BY').' '.$cdata['cleanup_user'] : '');
									break;
							}
							print '</span>';
						}
						print '</td></tr>';
						if ( $cdata['msg'] != '' ) {
							print '<tr class="'.($i % 2 == 0 ? 'even' : 'odd').($cid == $u ? ' own' : '').'"><td colspan="3" class="register_msg">'.$cdata['msg'].'</td></tr>';
						}
					}
				?>
			</tbody>
		</table>
		<?php } ?>
	</div>
	
	<p style="clear:both;"><em>* <?php print t('EEO'); ?></em></p>
</div>

<script type="text/javascript">				
				function changeTP(h,o) {
					$('#tp' + h).val(o);
					$('#tpo'+h+' .tpo').removeClass('opa100').addClass('opa50');
					$('#tp_'+h+'_'+o).removeClass('opa50').addClass('opa100');
				}
				
				function submitTPform() {  
					var tp = $.post(  
						"ema.form.ajax.php",  
						$("#tp_form").serialize(),  
						function(data){  
							$('#tp_pinboard').html(data);  
						}  
					);
				}
				
				function getSavedData(d,u,t) {  
					var tp = $.ajax({
						type: 'POST',
						url: 'ema.load.ajax.php',
						data: 'd='+d+'&u='+u+'&t='+t,
						success: function(msg) {
							$('#dynascript').html(msg);
						}
					});
				}
				
				function savePreset() {  
					var sp = $.post(  
						"ema.form.ajax.php",  
						$("#tp_form").serialize() + '&a=sp',  
						function(data){  
							$('#dynascript').html(data);  
						}  
					);
				}
				function loadPreset() {  
					getSavedData(0, <?php print $data['user']['id']; ?>, 0);
				}
				
				var oo_tpl = $.ajax({
						type: 'POST',
						url: 'ema.list.ajax.php',
						data: 't=<?php print $data['town']['id']; ?>&c=<?php print $data['current_day']; ?>&u=<?php print $data['user']['id']; ?>',
						success: function(msg) {
							$('#tp_pinboard').html(msg);
						}
					});
					
				var ema_cur = "#ema_form";
					$("ul#sub-ema li a").click(function (e) { 
						e.preventDefault();
						var newEma = $(this).attr("href");
						$(ema_cur).fadeOut();
						$(newEma).fadeIn("slow");
						ema_cur = newEma;
					});
					
					$("#symbol-toggler").click(function (e) { 
						e.preventDefault();
						$('.t1, .t2, .t3').toggleClass('tplc');
						$('.tpsymbol').toggleClass('hideme');
					});					
					
				getSavedData($('#tp_day').val(), <?php print $data['user']['id']; ?>, <?php print $data['town']['id']; ?>);
	</script>
<?php
}
else {
	print '<div class="error">Errorcode [02]: Fehler in der XML-Verarbeitung.</div>';
}

function minispy($u) {
	include_once 'system.php';
	$db = new Database();
	if ( isset($u) && $u > 0 ) {
		$q = ' SELECT xml FROM dvoo_soul WHERE uid = '.$u.' ORDER BY stamp DESC LIMIT 1 ';
		$r = $db->query($q);
	}
	else {
		return '<h2>'.t('NO_DATA_AVAILABLE').'</h2>';
	}
	

	if ( is_array($r) && count($r[0]) > 0 ) {
		$sxml = simplexml_load_string($r[0]['xml']);
	}
	else {
		return '<h2>'.t('NO_DATA_AVAILABLE').'</h2>';
	}

	$headers = $sxml->headers;
	$game = $sxml->headers->game;
	$rewards = $sxml->data->rewards->r;
	$maps = $sxml->data->maps->m;
	$owner = $sxml->headers->owner->citizen;

	$rare = array();
	$common = array();

	$citizen = array(
		'name' => (string) $owner['name'],
		'avatar' => (string) $owner['avatar'],
		'score' => 0,
	);

	foreach ( $rewards AS $r ) {
		$r_name = (string) $r['name'];
		$r_rare = (int) $r['rare'];
		$r_count = (int) $r['n'];
		$r_img = (string) $r['img'];
		if ( $r_rare == 1 ) {
			$ident = sprintf("%08d", $r_count).substr($r_name,0,3);
			$rare[$ident] = array(
				'name' => $r_name,
				'count' => $r_count,
				'img' => $r_img,
			);
		}
		else {
			$ident = sprintf("%08d", $r_count).substr($r_name,0,3);
			$common[$ident] = array(
				'name' => $r_name,
				'count' => $r_count,
				'img' => $r_img,
			);
		}
	}
	krsort($rare);
	krsort($common);

	/* ### OUTPUT ### */
	$out = '<h2>'.$citizen['name'].'</h2><div class="rewards-wrapper">';
	foreach ( $rare AS $r ) {
		$out .= '<div class="reward-wrapper rare"><div class="reward rare"><img src="'.t('GAMESERVER_ICON').$r['img'].'.gif" title="'.$r['name'].'" /></div>'.$r['count'].'</div>';
	}
	foreach ( $common AS $r ) {
		$out .= '<div class="reward-wrapper"><div class="reward"><img src="'.t('GAMESERVER_ICON').$r['img'].'.gif" title="'.$r['name'].'" /></div>'.$r['count'].'</div>';
	}	
	$out .= '</div>';
	return $out;
}

// -- Piwik Tracking API init -- 
require_once "PiwikTracker.php";
PiwikTracker::$URL = 'http://sindevel.com/piwik/';

$piwikTracker = new PiwikTracker( $idSite = 3 );
// You can manually set the visitor details (resolution, time, plugins, etc.) 
// See all other ->set* functions available in the PiwikTracker.php file
$piwikTracker->setURL('http://dv.sindevel.com/oo/ema.main.ajax.php');
$piwikTracker->setCustomVariable(1, 'user', $data['user']['id']);
$piwikTracker->setCustomVariable(2, 'scode', $k);
$piwikTracker->setCustomVariable(3, 'username', $data['user']['name']);
$piwikTracker->setCustomVariable(4, 'ip', $_SERVER['REMOTE_ADDR']);
$piwikTracker->setCustomVariable(5, 'query', $_SERVER['QUERY_STRING']);
// Sends Tracker request via http
$piwikTracker->doTrackPageView('OO Citizens');