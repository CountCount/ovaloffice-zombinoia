<?php
include_once 'system.php';
$db = new Database();

// get key (ajax)
$u = (int) $_POST['u'];
$t = (int) $_POST['t'];
$d = (int) $_POST['d'];
$x = (int) $_POST['x'];
$y = (int) $_POST['y'];
$a = (string) $_POST['a'];

$r = $db->query('SELECT name FROM dvoo_citizens WHERE id = '.$u);
$uname = $r[0][0];

switch ( $a ) {
	// load current status
	case 'load':
	{
		$r = $db->query('SELECT * FROM dvoo_towns WHERE id = '.$t);
		$town = $r[0];
		
		$q = ' SELECT * FROM dvoo_zones_visit WHERE tid = '.$t.' AND x = '.$x.' AND y = '.$y.' ORDER BY `on` DESC LIMIT 1';
		$r = $db->query($q);
		if ( isset($r[0]) && count($r[0]) > 0 ) {
			$visit = $r[0];
			if ( $visit['items'] != '' ) {
				$visit['items'] = unserialize($visit['items']);
			}
			else {
				$visit['items'] = array();
			}
		}
		else {
			$visit = null;
		}
		
		$q = ' SELECT * FROM dvoo_zones_buildings WHERE tid = '.$t.' AND x = '.$x.' AND y = '.$y.' ORDER BY `stamp` DESC LIMIT 1';
		$r = $db->query($q);
		if ( isset($r[0]) && count($r[0]) > 0 ) {
			$building = $r[0];
		}
		else {
			$building = null;
		}
		
		print '<div onclick="$(\'#zone-edit-box\').removeClass(\'activated\').delay(500).addClass(\'hideme\');" id="zone-edit-close">close</div><h3>'.t('EDIT_ZONE_INFO').': '.($x - $town['x']).'|'.($town['y'] - $y).'</h3>';
		print '<div class="current-zone-detail" id="current-zone-zombies"><h4>Zombie count</h4><form id="edit-zone-zombies" onsubmit="zoneEditSaveZone('.$x.','.$y.','.$t.','.$town['day'].','.$u.',\'edit-zone-zombies\',\'saveZombies\');return false;"><input type="submit" value="'.t('SAVE').'" /><input type="text" maxlength="2" size="2" name="zombies" value="'.$visit['z'].'" /> '.t('ZOMBIES').'</form></div>';
		
		print '<div class="current-zone-detail" id="current-zone-depletion"><h4>Zone depletion</h4><form id="edit-zone-depletion" onsubmit="zoneEditSaveZone('.$x.','.$y.','.$t.','.$town['day'].','.$u.',\'edit-zone-depletion\',\'saveZoneStatus\');return false;"><input type="submit" value="'.t('SAVE').'" /><input type="checkbox" name="zdried" value="1" '.($visit['dried'] == 1 ? 'checked="checked"' : '' ).' /> '.t('ZONE_DRIED').'</form></div>';
		
		if ( !is_null($building) ) {
			print '<div class="current-zone-detail" id="current-zone-building"><h4>Depletion of building '.$building['name'].'</h4><form id="edit-zone-building" onsubmit="zoneEditSaveZone('.$x.','.$y.','.$t.','.$town['day'].','.$u.',\'edit-zone-building\',\'saveBuildingStatus\');return false;"><input type="submit" value="'.t('SAVE').'" /><input type="checkbox" name="bdried" value="1" '.($building['depleted'] == 1 ? 'checked="checked"' : '' ).' /> '.t('BUILDING_DRIED').'<br/><textarea style="width:280px;height:120px;" name="bcontent">'.($building['content'] == '' ? "Scavenging results (exqmple):\nDay 1 - Player1: Bag of cement\n\n[Delete this text whenstarting.]" : $building['content']).'</textarea></form></div>';
		}
		
		print '<div class="current-zone-detail" id="current-zone-itemlist"><h4>Items on the ground</h4><form name="edit-zone-items" id="edit-zone-items" onsubmit="zoneEditSaveZone('.$x.','.$y.','.$t.','.$town['day'].','.$u.',\'edit-zone-items\',\'saveZoneItems\');return false;"><input type="submit" value="'.t('SAVE').'" />';
		if ( is_array($visit['items']) && count($visit['items']) > 0 ) {
			foreach ( $visit['items'] AS $i ) {
				print '<input class="zoneitem" id="zi'.$i['id'].'" type="hidden" name="zi['.$i['id'].']" value="'.$i['count'].'" />';
			}
		}
		
		print '</form><div id="current-zone-items">';
		if ( is_array($visit['items']) && count($visit['items']) > 0 ) {
			foreach ( $visit['items'] AS $i ) {
				$q = ' SELECT iname AS name, iimg AS img FROM dvoo_items WHERE iid = '.$i['id'].' ';
				$r = $db->query($q);
				$imd = $r[0];
				print '<div id="di'.$i['id'].'" title="'.$imd['name'].'" class="current-zone-itemlist-item" style="background:transparent url('.t('GAMESERVER_ITEM').$imd['img'].'.gif) 0px 1px no-repeat;" onclick="zoneEditItem('.$i['id'].',-1);">x'.$i['count'].'</div>';
			}
		}
		print '</div><br style="clear:left;" />&nbsp;</div>';
		
		print '<div class="current-zone-detail" id="current-zone-result"></div>';
		#print styleItems($items);	
		break;
	}
	
	case 'saveZoneItems':
	{
		#print '<p><pre>'.var_export($_POST,true).'</pre></p>';
		$zi = $_POST['zi'];
		$items = array();
		if ( is_array($zi) && count($zi) > 0 ) {
			$itemcount = count($zi);
			foreach ( $zi AS $id => $ic ) {
				$items[] = array(
					'id' => $id,
					'count' => $ic,
					'broken' => 0,
				);
			}
		}
		$q = ' INSERT INTO dvoo_zones_visit VALUES ('.$t.', '.$d.', '.$x.', '.$y.', 0, 0, 0, "'.mysql_real_escape_string(serialize($items)).'", '.time().', "'.$uname.'") ON DUPLICATE KEY UPDATE items = "'.mysql_real_escape_string(serialize($items)).'", `on` = '.time().', `by` = "'.mysql_real_escape_string($uname.' (via OO)').'" ';
		$db->iquery($q);
		print '<p>'.date('H:i:s',time()).' | '.$itemcount.' item'.($itemcount == 1 ? '' : 's').' have been saved.</p>';
		break;
	}
	
	case 'saveZombies':
	{
		$z = (int) $_POST['zombies'];
		$q = ' INSERT INTO dvoo_zones_visit VALUES ('.$t.', '.$d.', '.$x.', '.$y.', 0, 0, '.$z.', "", '.time().', "'.$uname.'") ON DUPLICATE KEY UPDATE z = '.$z.', `on` = '.time().', `by` = "'.mysql_real_escape_string($uname.' (via OO)').'" ';
		$db->iquery($q);
		print '<p>'.date('H:i:s',time()).' | '.$z.' zombie'.($z == 1 ? '' : 's').' ha'.($z == 1 ? 's' : 've').' been saved.</p>';
		break;
	}
	
	case 'saveZoneStatus':
	{
		$z = (int) $_POST['zdried'];
		$q = ' INSERT INTO dvoo_zones_visit VALUES ('.$t.', '.$d.', '.$x.', '.$y.', 0, '.$z.', 0, "", '.time().', "'.$uname.'") ON DUPLICATE KEY UPDATE dried = '.$z.', `on` = '.time().', `by` = "'.mysql_real_escape_string($uname.' (via OO)').'" ';
		$db->iquery($q);
		print '<p>'.date('H:i:s',time()).' | Zone has been marked as '.($z == 0 ? 'replenished':'depleted').'.</p>';
		break;
	}
	
	case 'saveBuildingStatus':
	{
		$z = (int) $_POST['bdried'];
		$c = (string) $_POST['bcontent'];
		$q = ' UPDATE dvoo_zones_buildings SET depleted = '.$z.', content = "'.mysql_real_escape_string($c).'", stamp = '.time().' WHERE tid = '.$t.' AND x = '.$x.' AND y = '.$y.' ';
		$db->iquery($q);
		print '<p>'.date('H:i:s',time()).' | Building has been marked as '.($z == 0 ? 'replenished':'depleted').'.</p>';
		break;
	}
}