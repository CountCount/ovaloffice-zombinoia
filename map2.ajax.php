<?php
include_once 'system.php';
$db = new Database();

$itemlist = item_list();

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
			$("#link_auswaertigesamt").remove();
			$("#auswaertigesamt").remove();
		</script>';
	exit;
}

$session = $db->query(' SELECT r.xml FROM dvoo_rawdata r INNER JOIN dvoo_citizens c ON r.id = c.id WHERE c.scode = "'.$k.'" ORDER BY r.time DESC LIMIT 1 ');

if ( $data = unserialize($session[0]['xml']) ) {
	$tid = $data['town']['id'];
	$uid = $data['user']['id'];
	$day = $data['current_day'];
	
	// items for autocomplete
	$all_items = array();
	$q = ' SELECT iname AS name FROM dvoo_items ORDER BY iname ASC ';
	$r = $db->query($q);
	foreach ( $r AS $o ) {
		$all_items[] = $o['name'];
	}
	
	$maps = array();
	$maps['zones'] = '<div class="map_zones" id="map2" style="width:'.($data['map']['width'] * 33).'px;height:'.($data['map']['height'] * 33).'px;">';

	$danger = array(0 => t('ZONE_D_0'), 1 => t('ZONE_D_1'), 2 => t('ZONE_D_2'), 3 => t('ZONE_D_3'), 4 => t('ZONE_D_4'));
	$zombies = array(0 => "0", 1 => "1", 2 => "2-3", 3 => "4-7", 4 => '8+');
	
	// route building
	$route_points = array();
	$routes = $db->query('SELECT id AS route_id, day, name, route, CONCAT(tid,day,cid) AS old_route_id FROM dvoo_expeditions WHERE tid = '.$data['town']['id'].' ORDER BY day DESC, cid ASC');
	$routes_info = array();
	foreach ( $routes AS $rdata ) {
		$routes_info[$rdata['route_id']] = t('DAY') . ' ' . $rdata['day'] . ': ' . $rdata['name'];
		$color = 'rgb('.rand(0,204).', '.rand(0,102).', '.rand(0,255).')';
		$route = unserialize($rdata['route']);
		$tx = null;
		$ty = null;
		$tc = count($route);
		foreach ( $route AS $i => $c ) {
			if ( is_null($tx) && is_null($ty) ) {
				$rfrom = 'c';
			}
			elseif ( $tx == $c['x'] ) {
				if ( $ty < $c['y'] ) {
					$rfrom = 'n';
				}
				elseif ( $ty > $c['y'] ) {
					$rfrom = 's';
				}
			}
			elseif ( $ty == $c['y'] ) {
				if ( $tx < $c['x'] ) {
					$rfrom = 'w';
				}
				elseif ( $tx > $c['x'] ) {
					$rfrom = 'e';
				}
			}
			
			if ( $tx == $c['x'] ) {
				if ( $ty < $c['y'] ) {
					$rto = 's';
				}
				elseif ( $ty > $c['y'] ) {
					$rto = 'n';
				}
			}
			elseif ( $ty == $c['y'] ) {
				if ( $tx < $c['x'] ) {
					$rto = 'e';
				}
				elseif ( $tx > $c['x'] ) {
					$rto = 'w';
				}
			}
			if ( !is_null($tx) && !is_null($ty) ) {
				$route_points[$ty][$tx]['expeditions'][$rdata['route_id']]['pathto'] = $rto;
			}
			$route_points[$c['y']][$c['x']]['expeditions'][$rdata['route_id']]['point'] = $i;
			$route_points[$c['y']][$c['x']]['expeditions'][$rdata['route_id']]['color'] = $color;
			$route_points[$c['y']][$c['x']]['expeditions'][$rdata['route_id']]['pathfrom'] = $rfrom;
			$route_points[$c['y']][$c['x']]['expeditions'][$rdata['route_id']]['pathto'] = 'c';
			$route_points[$c['y']][$c['x']]['expeditions'][$rdata['route_id']]['opa'] = ($i / $tc * .5) + .5;
			
			$tx = $c['x'];
			$ty = $c['y'];
		}
	}
	
	$out = '';
	
	// prep
	$zones = array();
	for ($y = 0; $y < $data['map']['height']; $y++) {
		for ($x = 0; $x < $data['map']['width']; $x++) {
			$zone = array(
				'x' => $x,
				'y' => $y,
				'rx' => $x - $data['town']['x'],
				'ry' => $data['town']['y'] - $y,
				'tag' => null,
				'danger' => null,
				'dried' => null,
				'lastupdate' => null,
				'day' => null,
				'uptodate' => null,
				'z' => null,
				'e' => null,
				'nyv' => 1,
				'nvt' => 1,
				'building' => null,
				'items' => null,
				'create_on' => null,
				'create_by' => null,
				'lastvisit_by' => null,
				'lastvisit_on' => null,
				'lastscout' => null,
				'lastscout_by' => null,
				'lastscout_on' => null,
				'lastregen' => null,
				'lastregen_by' => null,
				'lastregen_on' => null,
				'direction' => get_dir($x,$y,$data['town']['x'],$data['town']['y']),
				'direction_class' => get_dir_class($x,$y,$data['town']['x'],$data['town']['y']),
				'city' => ($x == $data['town']['x'] && $data['town']['y'] == $y ? 1 : 0),
				'dx' => abs($x - $data['town']['x']),
				'dy' => abs($y - $data['town']['y']),
				'ap' => abs($x - $data['town']['x']) + abs($y - $data['town']['y']),
				'km' => round(sqrt(pow(abs($x - $data['town']['x']),2) + pow(abs($y - $data['town']['y']),2))),
				'watch1' => get_radius($x,$y,$data['town']['x'],$data['town']['y'],1),
				'watch2' => get_radius($x,$y,$data['town']['x'],$data['town']['y'],2),
				'radius6' => get_radius($x,$y,$data['town']['x'],$data['town']['y'],0,6),
				'radius9' => get_radius($x,$y,$data['town']['x'],$data['town']['y'],0,9),
				'radius11' => get_radius($x,$y,$data['town']['x'],$data['town']['y'],11),
				'radius15' => get_radius($x,$y,$data['town']['x'],$data['town']['y'],15),
			);
			// check for DB entry
			$q = ' SELECT * FROM dvoo_zones_zones WHERE tid = '.$tid.' AND x = '.$x.' AND y = '.$y.' ORDER BY stamp DESC LIMIT 1 ';
			$r = $db->query($q);
			if ( is_array($r[0]) AND count($r[0]) > 0 ) {
				// zone found: at least visited once or checked by radar
				$d = $r[0]; // retrieve data
				// check day of last entry
				$zone['day'] = $d['day'];
				$zone['uptodate'] = ($d['day'] == $day ? 1 : 0);
				$zone['tag'] = $d['tag'];
				$zone['danger'] = $d['danger'];
				$zone['lastupdate'] = $d['stamp'];
				$zone['create_on'] = $d['on'];
				$zone['create_by'] = $d['by'];
				$zone['nvt'] = $d['nvt'];
				$zone['nyv'] = 0;
				$zone['z'] = $d['z'];
				$zone['e'] = $d['z'] + ($day - $d['day']);
				
				// check for building
				$q = ' SELECT * FROM dvoo_zones_buildings WHERE tid = '.$tid.' AND x = '.$x.' AND y = '.$y.' LIMIT 1 ';
				$r = $db->query($q);
				if ( is_array($r[0]) AND count($r[0]) > 0 ) {
					$b = $r[0];
					$zone['building'] = array(
						'name' => $b['name'],
						'type' => $b['type'],
						'dig' => $b['dig'],
						'content' => $b['content'],
						'lastupdate' => $b['stamp'],
						'depleted' => $b['depleted'],
					);
					$zone['e'] = 2 * $zone['e'] - $zone['z'];
				}
				
				// zone details
				$q = ' SELECT * FROM dvoo_zones_visit WHERE tid = '.$tid.' AND x = '.$x.' AND y = '.$y.' ORDER BY `on` DESC LIMIT 1  ';
				$r = $db->query($q);
				if ( is_array($r[0]) AND count($r[0]) > 0 ) {
					$i = $r[0];
					$zone['items'] = (is_null($i['items']) ? array() : unserialize($i['items']));
					$zone['dried'] = $i['dried'];
					$zone['z_proof'] = $i['z'];
					$zone['lastvisit_on'] = $i['on'];
					$zone['lastvisit_by'] = $i['by'];
					if ( is_null($zone['z']) && is_null($zone['building']) ) {
						$zone['e'] = $zone['z_proof'] + 1 * ($day - $i['day']);
					}
					elseif ( is_null($zone['z']) ) {
						$zone['e'] = $zone['z_proof'] + 2 * ($day - $i['day']);
					}
				} else {
					$q = ' SELECT * FROM dvoo_zones_visit WHERE tid = '.$tid.' AND x = '.$x.' AND y = '.$y.' AND auto = 0 ORDER BY `on` DESC LIMIT 1  ';
					$r = $db->query($q);
					if ( is_array($r[0]) AND count($r[0]) > 0 ) {
						$i = $r[0];
						$zone['m_items'] = (is_null($i['items']) ? array() : unserialize($i['items']));
						$zone['m_dried'] = $i['dried'];
						$zone['m_z_proof'] = $i['z'];
						$zone['m_visit_on'] = $i['on'];
						$zone['m_visit_by'] = $i['by'];
						if ( is_null($zone['z']) && is_null($zone['building']) ) {
							$zone['e'] = $zone['m_z_proof'] + 1 * ($day - $i['day']);
						}
						elseif ( is_null($zone['z']) ) {
							$zone['e'] = $zone['m_z_proof'] + 2 * ($day - $i['day']);
						}
					}
				}
			}
			else {
				// no information yet for this zone
				// (not included in xml)
				// no action required only applying to nyv zones
			}
			
			// scout information
			$q = ' SELECT * FROM dvoo_zones_scout WHERE tid = '.$tid.' AND x = '.$x.' AND y = '.$y.' ORDER BY `on` DESC LIMIT 1 ';
			$r = $db->query($q);
			if ( is_array($r[0]) AND count($r[0]) > 0 ) {
				$s = $r[0];
				$zone['lastscout_day'] = $s['day'];
				$zone['lastscout_on'] = $s['on'];
				$zone['lastscout_by'] = $s['by'];
				$zone['lastscout'] = $s['z'];
			}
			// regen(erate) information
			$q = ' SELECT * FROM dvoo_zones_regen WHERE tid = '.$tid.' AND x = '.$x.' AND y = '.$y.' ORDER BY `on` DESC LIMIT 1 ';
			$r = $db->query($q);
			if ( is_array($r[0]) AND count($r[0]) > 0 ) {
				$p = $r[0];
				$zone['lastregen_day'] = $p['day'];
				$zone['lastregen_on'] = $p['on'];
				$zone['lastregen_by'] = $p['by'];
				$zone['lastregen'] = $p['r'];
			}
			
			// save information to array
			$zones[$y][$x] = $zone;

			// generating output
			$zone['citizen_count'] = (isset($data['map'][$y][$x]['citizens'])?count($data['map'][$y][$x]['citizens']):null);
			if (!is_null($zone['citizen_count'])) {
				$cl = $clt = '';
				foreach ($data['map'][$y][$x]['citizens'] AS $cid => $carray) {
					if ( $cl != '' ) {
						$cl .= '<br/>';
						$clt .= ', ';
					}
					$cl .= '<img class="zone-detail-img" src="'.t('GAMESERVER_ITEM').$jobs[$carray['job']]['img'].'.gif" />' . $carray['name'];
					$clt .= '<img class="zone-detail-img" src="'.t('GAMESERVER_ITEM').$jobs[$carray['job']]['img'].'.gif" />' . $carray['name'];
				}
			}
			$zone['citizen_list_formatted'] = $cl;
			$zone['citizen_town_formatted'] = $clt;
			
			// display color for zombies
			$zone['display_z'] = $zone['z'];
			if ( is_null($zone['z']) ) {
				$zone['display_z'] = $zombies[$zone['danger']];
			}
			if ( $zone['lastscoutvisit_on'] > $zone['lastupdate'] ) {
				$zone['display_z'] = $zone['lastscout'];
			}
			if ( $zone['lastvisit_on'] > $zone['lastupdate'] || date('Ymd',$zone['lastvisit_on']) == date('Ymd') ) {
				$zone['display_z'] = $zone['z_proof'];
			} 
			
			
			
			/* ### zone info box ### */
			$info = '';
			$alpha = .75;
			
			// 0. Edit zone Button
			$info .= ($zone['city'] == 0 ? '<div class="zib-full zib-box zone-edit-button-wrapper" style="background:#bbbbe9;"><button class="zone-edit-button" onclick="zoneEditLoadInfo('.$zone['x'].','.$zone['y'].','.$data['town']['id'].','.$data['current_day'].','.$data['user']['id'].');">EDIT ZONE</button></div>' : '');
			
			// 1. Zone
			$info .= '<div class="zib-half zib-box zib-clear" style="background:'.($zone['dried'] == 1 ? 'rgba(201,0,0,'.$alpha.')' : ($zone['dried'] == 0 ? 'rgba(0,201,0,'.$alpha.')' : '#bbbbe9')).';">';
			$info .= '<h4>'.t('COORDS').': ['.$zone['rx'].'|'.$zone['ry'].']</h4>';
			if ( $zone['city'] == 0 ) {				
				$info .= '<p><strong>'.$zone['ap'].'</strong>'.t('AP').' | <strong>'.$zone['km'].'</strong>'.t('KM').'</p>';
			}
			$info .= '<p>'.($zone['dried'] == 1 ? '<img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_broken.gif" /> '.t('M_ZONE_DEPLETED') : '<img class="zone-detail-img" src="'.t('GAMESERVER_ITEM').'pelle.gif" /> '.t('M_ZONE_REGENERATED')).'</p>';
			$info .= '</div>';
			
			// 2. City/building
			$info .= '<div class="zib-half zib-box" style="background:'. ( $zone['city'] == 1 || (!is_null($zone['building']) && $zone['building']['depleted'] == 0) ? 'rgba(0,201,0,'.$alpha.')' : ($zone['building']['depleted'] == 1 ? 'rgba(201,0,0,'.$alpha.')' : '#bbbbe9') ).';">';
			$info .= '<h4><em>'.($zone['city'] == 1 ? $data['town']['name'] : (!is_null($zone['building']) ? $zone['building']['name'] : t('DESERT'))).'</em></h4>';
			if ( !is_null($zone['building']) ) {
				$info .= '<p>';
				$info .= ( $zone['building']['dig'] > 0 ? '<img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_digger.gif" /> <em>'.t('M_ZONE_DIG', array('%d' => $zone['building']['dig'])).'</em><br/>' : '');
				$info .= ($zone['building']['depleted'] == 1 ? '<img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_broken.gif" /> '.t('M_BUILDING_DEPLETED') : '<img class="zone-detail-img" src="'.t('GAMESERVER_ITEM').'pelle.gif" /> '.t('M_BUILDING_REGENERATED'));
				$info .= '</p>';
			}
			$info .= '</div>';
			
			// 3. Zombies
			$info .= '<div class="zib-half zib-box zib-clear" style="background:'.dv_i2ca($zone['display_z'], $alpha).';">';
			$info .= '<h4></h4>';
			
			if ( $zone['city'] == 0 && !is_null($zone['e']) && ($zone['e'] > $zone['z_proof'] || is_null($zone['z_proof'])) ) {
				$info .= ( ($zone['e'] > 0 ) ? '<h4><img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_killz.gif" /> '.$zone['e'].' '.t('ZOMBIEX', array('%s' => array($zone['e'], t('ZOMBIE'), t('ZOMBIES')))).(!is_null($zone['z_proof'])? '</h4><p>('.t('LAST_OFFICIAL_COUNT').': '.$zone['z_proof'].' '.t('ZOMBIEX', array('%s' => array($zone['z_proof'], t('ZOMBIE'), t('ZOMBIES')))).')' : '').'</p>' : (!is_null($zone['danger']) ? '<h4><img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_killz.gif" /> '.$zombies[$zone['danger']].' '.t('ZOMBIEX', array('%s' => array($zombies[$zone['danger']], t('ZOMBIE'), t('ZOMBIES')))).'</h4>' : ''));
			}
			else {
				$info .= ( $zone['city'] == 0 && !is_null($zone['z']) ? '<h4><img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_killz.gif" /> '.$zone['z'].' '.t('ZOMBIEX', array('%s' => array($zone['z'], t('ZOMBIE'), t('ZOMBIES')))).'</h4>' : (!is_null($zone['z_proof']) ? '<h4><img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_killz.gif" /> '.$zone['z_proof'].' '.t('ZOMBIEX', array('%s' => array($zone['z_proof'], t('ZOMBIE'), t('ZOMBIES')))).'</h4>' : (!is_null($zone['danger']) ? '<h4><img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_killz.gif" /> '.$zombies[$zone['danger']].' '.t('ZOMBIEX', array('%s' => array($zombies[$zone['danger']], t('ZOMBIE'), t('ZOMBIES')))).'</h4>' : '')));
			}			
			$info .= '<p>'.(!is_null($zone['danger']) && $zone['city'] == 0 ? t('ZONE_DANGER').': '.$danger[$zone['danger']] : '').'</p>';
			$info .= (!is_null($zone['lastscout']) ? '<p><strong>'.t('SCOUT_INFO').': '.$zone['lastscout'].' '.t('ZOMBIEX', array('%s' => array($zone['lastscout'], t('ZOMBIE'), t('ZOMBIES')))).'</strong></p>' : '' );
			$info .= '</div>';
			
			// 4. Humans
			$info .= '<div class="zib-half zib-box" style="background:#bbbbe9;">';
			$info .= ( $zone['citizen_count'] > 0 ? '<h4><img class="zone-detail-img" src="'.t('GAMESERVER_SMILEY').'h_human.gif" /> '.$zone['citizen_count'].' '.t('CITIZEN').'</h4>'.($zone['city'] == 0 ? '<p>'.$zone['citizen_list_formatted'].'</p>' : '') : '<h4><img class="zone-detail-img" src="'.t('GAMESERVER_SMILEY').'h_human.gif" /> 0 '.t('CITIZEN').'</h4>');
			$info .= '</div>';	

			// 5. Items
			if ($zone['city'] == 0) {
				$info .= '<div class="zib-full zib-box" style="background:#bbbbe9;">';
				$info .= '<h4>'.t('ZONE_ITEMSONGROUND').'</h4>';
				if ( is_array($zone['items']) ) {
					foreach ( $zone['items'] AS $item ) {				
						$info .= '<div class="item mapitem'.($item['broken'] ? ' broken' : '').($itemlist[$item['id']]['cat'] == 'Armor' ? ' deff' : '').'"><img alt="'.$itemlist[$item['id']]['name'].'" title="'.$itemlist[$item['id']]['name'].'" src="'.$data['system']['icon_url'].'item_'.$itemlist[$item['id']]['img'].'.gif" />&nbsp;'.$item['count'].'</div>';
						$itemcount += $item['count'];				
					}
				}
				$info .= '</div>';
			}
			else {
				$info .= '<div class="zib-full zib-box" style="background:#bbbbe9;">';
				$info .= '<h4>'.t('CITIZEN').'</h4><p>'.$zone['citizen_town_formatted'].'</p>';
				$info .= '</div>';
			}
			
			// 6. Stamps
			if ($zone['city'] == 0) {
				$info .= '<div class="zib-full zib-box" style="border:2px solid #bbbbe9; background: #fff; padding: 4px; min-height: 0px;">';
				$info .= (!is_null($zone['lastupdate']) && is_null($zone['lastvisit_on']) ? '<p>'.t('ZONE_LASTUPDATE').': <strong>'.($zone['lastvisit_on'] > $zone['create_on'] ? $zone['lastvisit_by'] : $zone['create_by']).'</strong> ('.utf8_encode(strftime(t('DATETIME_FULL'), (int) ($zone['lastupdate']))).t('TIME_APPENDIX').').</p>' : '');
			
				$info .= (!is_null($zone['lastvisit_on']) ? '<p>'.t('ZONE_LASTVISIT').': <strong>'.$zone['lastvisit_by'].'</strong> ('.utf8_encode(strftime(t('DATETIME_FULL'), (int) ($zone['lastvisit_on']))).t('TIME_APPENDIX').').</p>' : '');
				$info .= '</div>';
			}

			
/*			
			// 1. City name if applicable
			$info .= ($zone['city'] == 1 ? '<strong><em>'.t('CITY').'</em></strong><br/>' : '');
			
			// 2. Building info if applicable
			if ( !is_null($zone['building']) ) {
				$info .= '<strong><em>'.$zone['building']['name'].'</em></strong><br/>';
				$info .= ($zone['building']['dig'] > 0 ? '<img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_digger.gif" /> <em>'.t('ZONE_DIG', array('%d' => $zone['building']['dig'])).'</em><br/>' : '');
			}
			
			// 3. Coordinates
			$info .= t('COORDS').': ['.$zone['rx'].'|'.$zone['ry'].']';
			if ( $zone['city'] == 0 ) {				
				$info .= ' (<strong>'.$zone['ap'].'</strong>'.t('AP').'/<strong>'.$zone['km'].'</strong>'.t('KM').')';
			}
			$info .= '<br/>';
			
			// 4. Zone dried?
			$info .= ($zone['dried'] == 1 ? '<img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_broken.gif" /> '.t('ZONE_DRIED').'<br/>' : '');
			$info .= ($zone['lastregen'] == 1 ? '<strong class="plus">'.t('ZONE_REGENERATED').'</strong><br/>('.$zone['lastregen_by'].', '.utf8_encode(strftime(t('DATETIME_MINI'), (int) ($zone['lastregen_on']))).t('TIME_APPENDIX').')<br/>' : '' );
			$info .= (!is_null($zone['lastregen']) && $zone['lastregen'] == 0 ? '<strong class="minus">'.t('ZONE_NOT_REGENERATED').'</strong><br/>('.$zone['lastregen_by'].', '.utf8_encode(strftime(t('DATETIME_MINI'), (int) ($zone['lastregen_on']))).t('TIME_APPENDIX').')<br/>' : '' );
			
			// 5. Danger level
			$info .= (!is_null($zone['danger']) && $zone['city'] == 0 ? '<span class=\'zi_dan\'>'.t('ZONE_DANGER').': '.$danger[$zone['danger']].'</span><br/>' : '');
			
			// 6. Zombies
			if ( $zone['city'] == 0 && !is_null($zone['e']) && ($zone['e'] > $zone['z_proof'] || is_null($zone['z_proof'])) ) {
				$info .= ( ($zone['e'] > 0 ) ? '<img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_killz.gif" /> <span class=\'zi_zom\'>'.$zone['e'].' '.t('ZOMBIEX', array('%s' => array($zone['e'], t('ZOMBIE'), t('ZOMBIES')))).(!is_null($zone['z_proof'])? '</span><br/><span class=\'zi_zoe\'>('.t('LAST_OFFICIAL_COUNT').': '.$zone['z_proof'].' '.t('ZOMBIEX', array('%s' => array($zone['z_proof'], t('ZOMBIE'), t('ZOMBIES')))).')' : '').'</span><br/>' : (!is_null($zone['danger']) ? '<img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_killz.gif" /> <span class=\'zi_zom\'>'.$zombies[$zone['danger']].' '.t('ZOMBIEX', array('%s' => array($zombies[$zone['danger']], t('ZOMBIE'), t('ZOMBIES')))).'</span><br/>' : '<span></span>'));
			}
			else {
				$info .= ( $zone['city'] == 0 && !is_null($zone['z']) ? '<img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_killz.gif" /> <span class=\'zi_zom\'>'.$zone['z'].' '.t('ZOMBIEX', array('%s' => array($zone['z'], t('ZOMBIE'), t('ZOMBIES')))).'</span><br/>' : (!is_null($zone['z_proof']) ? '<img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_killz.gif" /> <span class=\'zi_zom\'>'.$zone['z_proof'].' '.t('ZOMBIEX', array('%s' => array($zone['z_proof'], t('ZOMBIE'), t('ZOMBIES')))).'</span><br/>' : (!is_null($zone['danger']) ? '<img class="zone-detail-img" src="'.t('GAMESERVER_ICON').'r_killz.gif" /> <span class=\'zi_zom\'>'.$zombies[$zone['danger']].' '.t('ZOMBIEX', array('%s' => array($zombies[$zone['danger']], t('ZOMBIE'), t('ZOMBIES')))).'</span><br/>' : '<span></span>')));
			}
			$info .= (!is_null($zone['lastscout']) ? '<strong class="minus">'.t('SCOUT_INFO').': '.$zone['lastscout'].' '.t('ZOMBIEX', array('%s' => array($zone['lastscout'], t('ZOMBIE'), t('ZOMBIES')))).'</strong> ('.$zone['lastscout_by'].', '.utf8_encode(strftime(t('DATETIME_FULL'), (int) ($zone['lastscout_on']))).t('TIME_APPENDIX').')' : '' );
			
			// 7. Citizens
			$info .= ( $zone['citizen_count'] > 0 ? '<img class="zone-detail-img" src="'.t('GAMESERVER_SMILEY').'h_human.gif" /> <span class=\'zi_hum\'>'.$zone['citizen_count'].' '.t('CITIZEN').': '.$zone['citizen_list_formatted'].'</span><br/>' : '');
			
			// 8. Visiting state
			$info .= ( $zone['nyv'] == 1 ? '<em>'.t('ZONE_NYV').'</em>' : ( $zone['nvt'] == 1 ? '<em>'.t('ZONE_NVT').'</em>' :'' ) );
			
			// 9. Last update
			$info .= (!is_null($zone['lastupdate']) ? '<br/>'.t('ZONE_LASTUPDATE').': <strong>'.($zone['lastvisit_on'] > $zone['create_on'] ? $zone['lastvisit_by'] : $zone['create_by']).'</strong> ('.utf8_encode(strftime(t('DATETIME_MINI'), (int) ($zone['lastupdate']))).t('TIME_APPENDIX').').' : '');
			
			// 10. Last visit
			$info .= (!is_null($zone['lastvisit_on']) ? '<br/>'.t('ZONE_LASTVISIT').': <strong>'.$zone['lastvisit_by'].'</strong> ('.utf8_encode(strftime(t('DATETIME_FULL'), (int) ($zone['lastvisit_on']))).t('TIME_APPENDIX').').' : '');
			
			// 11. Items on ground
			$itemcount = 0;
			if ( isset($zone['items']) && is_array($zone['items']) && count($zone['items']) > 0 ) { 
				$info .= '<div class="ground"><h4>'.t('ZONE_ITEMSONGROUND').'</h4>';
				foreach ( $zone['items'] AS $item ) {				
					$info .= '<div class="item mapitem'.($item['broken'] ? ' broken' : '').($itemlist[$item['id']]['cat'] == 'Armor' ? ' deff' : '').'"><img alt="'.$itemlist[$item['id']]['name'].'" title="'.$itemlist[$item['id']]['name'].'" src="'.$data['system']['icon_url'].'item_'.$itemlist[$item['id']]['img'].'.gif" />&nbsp;'.$item['count'].'</div>';
					$itemcount += $item['count'];				
				}
				$info .= '</div>'; 
			}
			if ( isset($zone['m_items']) && count($zone['m_items']) > 0 ) { 
				$info .= '<div class="ground" style="opacity:.7;"><h4>manuell eingetragen: '.t('ZONE_ITEMSONGROUND').'</h4>';
				foreach ( $zone['m_items'] AS $item ) {				
					$info .= '<div class="item mapitem'.($item['broken'] ? ' broken' : '').($itemlist[$item['id']]['cat'] == 'Armor' ? ' deff' : '').'"><img alt="'.$itemlist[$item['id']]['name'].'" title="'.$itemlist[$item['id']]['name'].'" src="'.$data['system']['icon_url'].'item_'.$itemlist[$item['id']]['img'].'.gif" />&nbsp;'.$item['count'].'</div>';
					$itemcount += $item['count'];				
				}
				$info .= '</div>'; 
			}	
*/				
			// add manual update form data
			$info .= '<input type="hidden" name="x" value="'.$x.'" /><input type="hidden" name="y" value="'.$y.'" />';
			
			// Final. end div
			$info .= '</div>';
			
			if ( $x == $data['user']['x'] && $y == $data['user']['y'] ) {
				$firstZI = '<div>'.$info;
			}
			$info = '<div id="z2i_x'.$x.'y'.$y.'" style="display:none;">'.$info;
			
			// start output
			// add zone info
			$out .= '<div class="zone" style="left:'.(1 + $x * 33).'px;top:'.(1 + $y * 33).'px;background:transparent;opacity:1;" onmouseover="var ti = $(\'#z2i_x'.$x.'y'.$y.'\').html();$(\'#infoblock-zone .dynacontent\').html(ti).slideDown(200);" onclick="var ti = $(\'#z2i_x'.$x.'y'.$y.'\').html();$(\'#infoblock-zone #i2b-zone-content\').html(ti);i2b(\'zone\');">';

			$out .= $info;

			// zone standard
			$out .= '<div class="zone-zone"></div>';
			// zone standard
			$out .= ($y == $data['user']['y'] && $x == $data['user']['x'] ? '<div class="zone-own"></div>' : '');
			
			// zombies
			$out .= '<div class="zone-color-zone zone-zombie hideme" style="background:'.i2c($zone['display_z']).';"></div>';
			
			// dv zombies
			$out .= '<div class="zone-color-zone zone-dvzombie" style="background:'.dv_i2c($zone['display_z']).';"></div>';
			
			// zombie numbers
			if ( $zone['display_z'] > 0 ) {
				$out .= '<div class="zone-zombie-count hideme"><p class="zc">'.$zone['display_z'].'</p></div>';
			}
			
			// citizen numbers
			if ( $zone['citizen_count'] > 0 ) {
				$out .= '<div class="zone-human-count hideme"><p class="hc">'.$zone['citizen_count'].'</p></div>';
			}
			
			// citizens
			if ( !is_null($zone['citizen_count']) ) {
				$out .= '<div class="zone-color-zone zone-citizen hideme" style="background:'.i2c($zone['citizen_count']).';"></div>';
				if ( $zone['city'] == 0 ) {
					$out .= '<div class="zone-citidot" style="background:transparent url(\'citidot2.php?cc='.$zone['citizen_count'].'&r='.mt_rand(0,3).'\') ;"></div>';
				}
			}
			
			// buildings/city
			if ( is_array($zone['building']) ) {
				$out .= '<div class="zone-building"><div class="zone-building-icon'.($zone['building']['depleted'] == 1 ? '-depleted' : '').' type'.$zone['building']['type'].'"></div></div>';
			}
			elseif ( $zone['city'] == 1 ) {
				$out .= '<div class="zone-building"><div class="zone-building-icon city"></div></div>';
			}
			
			// tags
			if ( !is_null($zone['tag']) && $zone['tag'] > 0 ) {
				$out .= '<div class="zone-tag hideme"><img src="'.t('GAMESERVER_TAG').$zone['tag'].'.gif" /></div>';
			}

			// regeneration
			if ( $zone['city'] == 0 && !is_null($zone['lastregen']) ) {
				$out .= '<div class="zone-regen hideme '.( $zone['lastregen'] == 1 ? 'pelle-ok' : 'pelle-no').'"></div>';
			}
			// buddel status
			/*if ( $zone['city'] == 0 && !is_null($zone['dried']) && $zone['dried'] == 1 ) {
				$out .= '<div class="zone-status"><img src="'.t('GAMESERVER_ICON').'tag_5.gif" /></div>';
			}
			*/
			if ( $zone['city'] == 0 && !is_null($zone['dried']) ) {
				$out .= '<div class="zone-status"><img src="/oo/css/img/dried'.$zone['dried'].'.png" /></div>';
			}
			
			// visiting status
			$out .= '<div class="zone-visit'.($zone['nyv'] == 1 ? (!is_null($zone['lastscout_on']) ? ' rdr' : ' nyv') : ($zone['nvt'] ? ' nvt' : '')).'"></div>';
			
			// search results
			$out .= '<div class="zone-searching hideme" id="s2_x'.$x.'-y'.$y.'"></div>';
			
			// expeditions
			if ( is_array($route_points[$zone['y']][$zone['x']]['expeditions']) && count($route_points[$zone['y']][$zone['x']]['expeditions']) > 0 ) {
				foreach ($route_points[$zone['y']][$zone['x']]['expeditions'] AS $rid => $rdata ) {
					$out .= '<div class="expedition hideme xpd'.$rid.' '.$rdata['pathfrom'].'2'.$rdata['pathto'].'" style="opacity:'.$rdata['opa'].';">'.$rdata['point'].'</div>';
				}
			}
			
			// directions
			if ( $zone['city'] == 0 ) {
				$out .= '<div class="zone-direction-color hideme" style="background:'.(abs($zone['direction']) % 2 == 0 ? '#f0f' : '#00f').';"></div>';
			}			
			foreach ( $zone['direction_class'] AS $d => $c ) {
				if ( $c == 1 ) {
					$out .= '<div class="zone-direction-border zone-direction-border-'.$d.' hideme"></div>';
				}
			}
			// radius
			foreach ( $zone['watch1'] AS $d => $c ) {
				if ( $c == 1 ) {
					$out .= '<div class="zone-watch1-border zone-border-'.$d.' hideme"></div>';
				}
			}
			foreach ( $zone['watch2'] AS $d => $c ) {
				if ( $c == 1 ) {
					$out .= '<div class="zone-watch2-border zone-border-'.$d.' hideme"></div>';
				}
			}
			foreach ( $zone['radius6'] AS $d => $c ) {
				if ( $c == 1 ) {
					$out .= '<div class="zone-radius6-border zone-border-'.$d.' hideme"></div>';
				}
			}
			foreach ( $zone['radius9'] AS $d => $c ) {
				if ( $c == 1 ) {
					$out .= '<div class="zone-radius9-border zone-border-'.$d.' hideme"></div>';
				}
			}
			foreach ( $zone['radius11'] AS $d => $c ) {
				if ( $c == 1 ) {
					$out .= '<div class="zone-radius11-border zone-border-'.$d.'"></div>';
				}
			}
			foreach ( $zone['radius15'] AS $d => $c ) {
				if ( $c == 1 ) {
					$out .= '<div class="zone-radius15-border zone-border-'.$d.' hideme"></div>';
				}
			}
			
			$out .= '</div>';
		}
	}
	
	$q = ' SELECT * FROM dvoo_items ORDER BY icat ASC, iname ASC, iid ASC ';
	$r = $db->query($q);

	// internal toolbox
	print '<div class="infoblock" id="infoblock-zone">';
	print '<h3 class="handle">'.t('MAP_OP_TITLE').'</h3>';
	print '<div id="zone-edit-box" class="hideme"><div id="zone-edit-tools"><div id="zone-edit-tools-content"><div class="zone-edit-tools-cat first clearfix">';
	
	$cat = '';
	foreach ( $r AS $i ) {
		if ( $i['icat'] != $cat ) {
			if ( $cat != '' ) {
			print '</div><div class="zone-edit-tools-cat clearfix">';
			#print '<hr style="color:#666;width:100%;margin: 6px 0 3px;" />';
			}
			$cat = $i['icat'];
			print '<h4>'.t($cat).'</h4>';
		}
		print '<img id="i'.$i['iid'].'" class="zone-edit-tool-item" src="'.t('GAMESERVER_ITEM').$i['iimg'].'.gif" alt="'.$i['iname'].'" title="'.$i['iname'].' ('.$i['iid'].')" />';
	}
	
	print '</div></div></div><div id="edit-zone-content"></div></div>';
	/*print '<div class="option-bar">
		<div id="i2l-zone" rel="ib-zone" class="active" onclick="i2b(\'zone\');">'.t('MAP_OP_INFO').'</div>
		<div id="i2l-storm" rel="ib-storm" onclick="i2b(\'storm\');">'.t('MAP_OP_STORM').'</div>
		<div id="i2l-mapoptions" rel="i2b-mapoptions" onclick="i2b(\'mapoptions\');">'.t('MAP_OP_SETTINGS').'</div>
		<div id="i2l-expeditions" rel="i2b-expeditions" onclick="i2b(\'expeditions\');">'.t('MAP_OP_EXPEDITIONS').'</div></div>';*/
	print '<div class="option-bar">
		<div id="i2l-zone" rel="ib-zone" class="active" onclick="i2b(\'zone\');"><img src="img/zoneinfo.gif" title="'.t('MAP_OP_INFO').'"/></div>
		<div id="i2l-search" rel="ib-search" onclick="i2b(\'search\');"><img src="img/search.png" title="'.t('MAP_OP_SEARCH').'"/></div>
		<div id="i2l-storm" rel="ib-storm" onclick="i2b(\'storm\');"><img src="img/storm.gif" title="'.t('MAP_OP_STORM').'"/></div>
		<div id="i2l-mapoptions" rel="i2b-mapoptions" onclick="i2b(\'mapoptions\');"><img src="img/options.gif" title="'.t('MAP_OP_SETTINGS').'"/></div>
		<div id="i2l-expeditions" rel="i2b-expeditions" onclick="i2b(\'expeditions\');"><img src="img/expeditions.gif" title="'.t('MAP_OP_EXPEDITIONS').'"/></div></div>';
		
	// zone info
	print '<div id="i2b-zone" class="ib-content content">';
	
	print '<div id="i2b-zone-content">'.$firstZI.'</div>';
	
	print '</div>';
	
	// item search
	print '<div id="i2b-search" class="ib-content content hideme">';
	print '<h5>'.t('MAP_SEARCHFORM').'</h5>';
	print '<br/><a href="javascript:void(0);" id="map2_hover_search" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_SEARCH').'</a>';
		
	$catnames = array(
		'Rsc' => t('Rsc'),
		'Food' => t('Food'),
		'Armor' => t('Armor'),
		'Drug' => t('Drug'),
		'Weapon' => t('Weapon'),
		'Misc' => t('Misc'),
		'Furniture' => t('Furniture'),
		'Box' => t('Box'),
	);
	$catorder = array(
		'Rsc' => 0,
		'Food' => 5,
		'Armor' => 4,
		'Drug' => 3,
		'Weapon' => 6,
		'Misc' => 7,
		'Furniture' => 2,
		'Box' => 1
	);
	$ordercat = array_flip($catorder);
	ksort($ordercat);
	
	print '<div id="zone-search-wrapper"><div id="zone-search-form"><form id="zone-search" onsubmit="zoneSearch();return false;"><select name="cat" id="zone-search-cat"><option value="0">'.t('MAP_SEARCH_CAT').'</option>';
	foreach ( $ordercat AS $catn ) {
		print '<option value="'.$catn.'">'.$catnames[$catn].'</option>';
	}
	print '</select><input type="hidden" name="u" value="'.$data['user']['id'].'" /><input id="zone-search-itemid" type="hidden" name="id" value="0" /><input type="text" name="item" id="zone-search-item" onchange="$(\'#zone-search-itemid\').val(0);$(\'#zone-search-cat\').val(0);" value="'.t('MAP_SEARCH_ITEM').'" onclick="$(this).focus().select();" /><input id="zs-button" type="submit" value="'.t('MAP_SEARCH_FIND').'" /></form></div><div id="zone-search-result"></div></div>';
	
	print '</div>';
	
	// storm
	print '<div id="i2b-storm" class="ib-content content hideme">';
	print '<form id="register-storm" onsubmit="registerStorm();return false;">';
	print '<input type="hidden" name="t" value="'.$data['town']['id'].'" />
		<input type="hidden" name="u" value="'.$data['user']['id'].'" />
		<input type="hidden" name="d" value="'.$data['current_day'].'" />
		<input type="hidden" name="formid" value="et-register-storm" />';
	
	print '<div id="manual-update-storm"><strong>'.t('STORM_LOG').'</strong>';
	print '<div id="storm-direction"><select name="musd">
		<option value="1">'.t('Nx').'</option>
		<option value="2">'.t('NEx').'</option>
		<option value="3">'.t('Ex').'</option>
		<option value="4">'.t('SEx').'</option>
		<option value="5">'.t('Sx').'</option>
		<option value="6">'.t('SWx').'</option>
		<option value="7">'.t('Wx').'</option>
		<option value="8">'.t('NWx').'</option>
	</select><input type="submit" value="'.t('REGISTER_STORM').'"></div>';
	print '</div></form><hr/>';
	
	$q = ' SELECT s.*, c.name FROM dvoo_storm s INNER JOIN dvoo_citizens c ON c.id = s.uid WHERE s.tid = '.$data['town']['id'].' ORDER BY s.day DESC ';
	$r = $db->query($q);
	if ( is_array($r) && count($r) > 0 ) {
		$dir = array(
				1 => t('Nx'),
				2 => t('NEx'),
				3 => t('Ex'),
				4 => t('SEx'),
				5 => t('Sx'),
				6 => t('SWx'),
				7 => t('Wx'),
				8 => t('NWx'),
		);
		foreach ( $r AS $s ) {
			print t('DAY') . ' ' . $s['day'] . ': ' . $dir[$s['dir']] . ' -' . $s['name'] . '<br/>';
		}
	}
	
	print '</div>';
	
	// zone options
	print '<div id="i2b-mapoptions" class="ib-content content hideme">';
	print '<h5>'.t('MAP_COLOR_BACK').'</h5>';
	print '<a href="javascript:void(0);" id="map2_hover_dvzombie" class="map_hover_color map_hover ms_on" onclick="return false;" >'.t('MAP_LAYER_ZCORI').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_zombie" class="map_hover_color map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_ZCDET').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_citizen" class="map_hover_color map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_CITCOUNT').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_colornone" class="map_hover_color map_hover ms_off" onclick="return false;" >'.t('NO_BACKCOLOR').'</a>';
	
	print '<h5>'.t('MAP_INFOLAYER').'</h5>';
	print '<a href="javascript:void(0);" id="map2_hover_citidot" class="map_hover ms_on" onclick="return false;" >'.t('MAP_LAYER_CITIDOT').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_humanc" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_HUMCOUNT').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_zombiec" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_ZOMCOUNT').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_collec" class="map_hover ms_on" onclick="return false;" >'.t('MAP_LAYER_REGENSTATUS').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_regen" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_REGENERATION').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_tags" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_ZONEMARKER').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_visit" class="map_hover ms_on" onclick="return false;" >'.t('MAP_LAYER_VISITSTATUS').'</a>';
	
	print '<h5>'.t('MAP_DIRECTIONS').'</h5>';
	print '<a href="javascript:void(0);" id="map2_hover_color" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_DIRCOLOR').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_border" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_DIRBORDER').'</a>';
	
	print '<h5>'.t('MAP_RADIUS').'</h5>';
	print '<a href="javascript:void(0);" id="map2_hover_watch1" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_WATCHTOWER1').'</a>';	
	print '<a href="javascript:void(0);" id="map2_hover_watch2" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_WATCHTOWER2').'</a>';
	
	print '<a href="javascript:void(0);" id="map2_hover_radius6" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_RADIUS6').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_radius9" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_RADIUS9').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_radius11" class="map_hover ms_on" onclick="return false;" >'.t('MAP_LAYER_RADIUS11').'</a>';
	print '<a href="javascript:void(0);" id="map2_hover_radius15" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_RADIUS15').'</a>';
	
	print '</div>';
	
	// expeditions
	print '<div id="i2b-expeditions" class="ib-content content hideme">';
	print '<a href="javascript:void(0);" id="exp_hover_all" class="exp_hover_all ms_off" onclick="toggleAXPD();"><strong>'.t('MAP_ALL_EXPEDITIONS').'</strong></a>';
	foreach ($routes_info AS $rid => $rname) {
		print '<a href="javascript:void(0);" id="exp_hover_'.$rid.'" class="exp_hover ms_off" onclick="toggleXPD('.$rid.');">'.$rname.'</a>';
	}
	print '</div>';
	print '<div class="dynacontent">';
	print '';
	print '</div>';
	print '</div>';
	
	if ( is_array($ma) && count($ma) > 0 ) {
		foreach ( $ma AS $mid => $mhtml ) {
			$mo .= '<!-- '.$mid.' -->'.$mhtml.'</div>';
		}
	}
	$mo .= '<div id="legend" style="top:'.(7 + $y * 33).'px;">
		<div class="legtit">'.t('MAP_LEGEND').'</div>
		<div class="zomleg" style="background:#393;">0</div>
		<div class="zomleg" style="background:#6c0;">1</div>
		<div class="zomleg" style="background:#9c0;">2</div>
		<div class="zomleg" style="background:#fc0;">3</div>
		<div class="zomleg" style="background:#f90;">4</div>
		<div class="zomleg" style="background:#f60;">5</div>
		<div class="zomleg" style="background:#f33;">6</div>
		<div class="zomleg" style="background:#f30;">7</div>
		<div class="zomleg" style="background:#f00;">8</div>
		<div class="zomleg" style="background:#06c;">9 - 12</div>
		<div class="zomleg" style="background:#369;">13 - 18</div>
		<div class="zomleg" style="background:#66c;">19 - 24</div>
		<div class="zomleg" style="background:#93c;">25 - 30</div>
		<div class="zomleg" style="background:#90c;">&gt; 30</div>
		<div class="zomleg" style="background:#669;">n/a</div>
	</div>';
	print $maps['zones'].$out.'</div>';

	print '<div id="info" style="height:100%;width:'.(900 - (1 + $data['map']['width'] * 33)).'px;">';

	print '</div>';

	print '<script type="text/javascript">
	
	$(function() {
		var availableItems = [ "'.implode('","', $all_items).'"	];
		$( "#item-name" ).autocomplete({
			source: availableItems,
			minLength: 3
		});
	});



	function toggleOpa(divid, el) {
		$("#" + el).toggleClass("ms_on ms_off");
		var curopa = $("#" + divid).css("opacity");
		$("#" + divid).css("opacity", (1-curopa));
		return false;
	}
	
	function toggleXPD(r) {
		$("#exp_hover_"+r).toggleClass("ms_on ms_off");
		$(".xpd"+r).toggleClass("hideme");
	}
	function toggleAXPD() {
		$(".exp_hover").toggleClass("ms_on ms_off");
		$(".expedition").toggleClass("hideme");
	}
	
	function toggleOpas() {
		$("#exp_hover_all").toggleClass("ms_on ms_off");
		var allopa = $("#exp_hover_all").hasClass("ms_on");
		if ( allopa ) {
			var newOpa = 1;
		} else {
			var newOpa = 0;
		}
		$("#map .map_expeditions").css("opacity", newOpa);
		$("a.exp_hover").toggleClass("ms_on ms_off");
		return false;
	}

	$("#map2_hover_tags").click(function(el) {
		$("#map2_hover_tags").toggleClass("ms_on ms_off");
		$(".zone-tag").toggleClass("hideme");
	});
	$("#map2_hover_regen").click(function(el) {
		$("#map2_hover_regen").toggleClass("ms_on ms_off");
		$(".zone-regen").toggleClass("hideme");
	});
	$("#map2_hover_collec").click(function(el) {
		$("#map2_hover_collec").toggleClass("ms_on ms_off");
		$(".zone-status").toggleClass("hideme");
	});

	$("#map2_hover_color").click(function(el) {
		$("#map2_hover_color").toggleClass("ms_on ms_off");
		$(".zone-direction-color").toggleClass("hideme");
	});
	$("#map2_hover_border").click(function(el) {
		$("#map2_hover_border").toggleClass("ms_on ms_off");
		$(".zone-direction-border").toggleClass("hideme");
	});
	$("#map2_hover_watch").click(function(el) {
		$("#map2_hover_watch").toggleClass("ms_on ms_off");
		$(".zone-watchtower").toggleClass("hideme");
	});
	$("#map2_hover_search").click(function(el) {
		$("#map2_hover_search").toggleClass("ms_on ms_off");
		$(".zone-searching").toggleClass("hideme");
	});
	$("#map2_hover_zombiec").click(function(el) {
		$("#map2_hover_zombiec").toggleClass("ms_on ms_off");
		$(".zone-zombie-count").toggleClass("hideme");
	});	
	$("#map2_hover_visit").click(function(el) {
		$("#map2_hover_visit").toggleClass("ms_on ms_off");
		$(".zone-visit").toggleClass("hideme");
	});	
	$("#map2_hover_citidot").click(function(el) {
		$("#map2_hover_citidot").toggleClass("ms_on ms_off");
		$(".zone-citidot").toggleClass("hideme");
	});
	$("#map2_hover_humanc").click(function(el) {
		$("#map2_hover_humanc").toggleClass("ms_on ms_off");
		$(".zone-human-count").toggleClass("hideme");
	});
	$("#map2_hover_watch1").click(function(el) {
		$("#map2_hover_watch1").toggleClass("ms_on ms_off");
		$(".zone-watch1-border").toggleClass("hideme");
	});
	$("#map2_hover_watch2").click(function(el) {
		$("#map2_hover_watch2").toggleClass("ms_on ms_off");
		$(".zone-watch2-border").toggleClass("hideme");
	});
	$("#map2_hover_radius6").click(function(el) {
		$("#map2_hover_radius6").toggleClass("ms_on ms_off");
		$(".zone-radius6-border").toggleClass("hideme");
	});	
	$("#map2_hover_radius9").click(function(el) {
		$("#map2_hover_radius9").toggleClass("ms_on ms_off");
		$(".zone-radius9-border").toggleClass("hideme");
	});
	$("#map2_hover_radius11").click(function(el) {
		$("#map2_hover_radius11").toggleClass("ms_on ms_off");
		$(".zone-radius11-border").toggleClass("hideme");
	});
	$("#map2_hover_radius15").click(function(el) {
		$("#map2_hover_radius15").toggleClass("ms_on ms_off");
		$(".zone-radius15-border").toggleClass("hideme");
	});
	
	
	
	$("#map2_hover_dvzombie").click(function(el) {
		$(".zone-color-zone").addClass("hideme");
		$(".zone-dvzombie").toggleClass("hideme");
		$(".map_hover_color").removeClass("ms_on").addClass("ms_off");
		$("#map2_hover_dvzombie").toggleClass("ms_on ms_off");
	});
	$("#map2_hover_zombie").click(function(el) {
		$(".zone-color-zone").addClass("hideme");
		$(".zone-zombie").toggleClass("hideme");
		$(".map_hover_color").removeClass("ms_on").addClass("ms_off");
		$("#map2_hover_zombie").toggleClass("ms_on ms_off");
	});
	$("#map2_hover_citizen").click(function(el) {
		$(".zone-color-zone").addClass("hideme");
		$(".zone-citizen").toggleClass("hideme");
		$(".map_hover_color").removeClass("ms_on").addClass("ms_off");
		$("#map2_hover_citizen").toggleClass("ms_on ms_off");
	});
	$("#map2_hover_colornone").click(function(el) {
		$(".zone-color-zone").addClass("hideme");
		$(".map_hover_color").removeClass("ms_on").addClass("ms_off");
		$("#map2_hover_colornone").toggleClass("ms_on ms_off");
	});
	
	function switchInfoBlock(elid) {
		$("#infoblock_"+elid+" .content").slideToggle();
	}
	
	function i2b(ibsub) {
		$("#infoblock-zone .option-bar div").removeClass("active");
		$("#infoblock-zone #i2l-"+ibsub).addClass("active");
		$(".ib-content").addClass("hideme");
		$("#infoblock-zone #i2b-"+ibsub).removeClass("hideme");
	}
						
	function getScrollTop() {
		if(typeof pageYOffset!= "undefined") {
				//most browsers
				return pageYOffset;
		}
		else {
				var B = document.body; //IE "quirks"
				var D = document.documentElement; //IE with doctype
				D = (D.clientHeight) ? D : B;
				return D.scrollTop;
		}
	}
		
		$("#infoblock-zone")
			.addClass("hideme")
			.prependTo("body")
        .bind("dragstart",function( event ){
                if ( !$(event.target).is(".handle") ) return false;
                $( this ).addClass("drag-active");
                })
        .bind("drag",function( event ){
                $( this ).css({
                        top: (event.offsetY - getScrollTop()),
                        left: (event.offsetX - $("html").scrollLeft())
                        });
                })
        .bind("dragend",function( event ){
                $( this ).removeClass("drag-active");
                });
		
		function zoneSearch() {  
					$("#zs-button").hide();
					$("#zone-search-result").html("<div class=\'loading\'></div>");
					var ms = $.post(  
						"map2.search.ajax.php",  
						$("#zone-search").serialize(),  
						function(data){  
							//$("#").val("");
							$("#zone-search-result").html(data);
							$("#zs-button").fadeIn(500);
						}  
					);
				}
		
		function zoneEditLoadInfo(x,y,t,d,u) {
			$("#zone-edit-tools").addClass("hideme");
			$("#zone-edit-box").removeClass("hideme").addClass("activated");
			$("#edit-zone-content").html("<div class=\'loading\'></div>");
			var ze = $.post(  
				"map2.editzone.ajax.php",  
				"a=load&t="+t+"&u="+u+"&d="+d+"&x="+x+"&y="+y,  
				function(data){  
					$("#edit-zone-content").html(data);
					$("#zone-edit-tools").removeClass("hideme");
				}  
			);
		}
		
		$(".zone-edit-tool-item").live("click", function(index) {
			zoneEditItem($(this).attr("id").substring(1),1);
		});
		//onclick="zoneEditItem('.$i['iid'].',1)"
		$(".current-zone-itemlist-item").live("click", function(index) {
			zoneEditItem($(this).attr("id").substring(2),-1);
		});
		
		function zoneEditItem(id,n) {
			var litem = $("#i"+id);
			var zitem = $("#zi"+id);
			var ditem = $("#di"+id);
			if ( 0 in zitem ) {
				if ( zitem.val() > 0 ) {
					var newcnt = parseInt(zitem.val()) + n;
					if ( newcnt <= 0 ) {
						zitem.remove();
						ditem.remove();
					}
					else {
						zitem.val(newcnt);
						ditem.html("x"+newcnt);
					}
				}
			}
			else if(n == 1) {
				var ilist = $("#current-zone-items");
				var iform = $("#edit-zone-items");
				
				iform.append("<input class=\'zoneitem\' id=\'zi"+id+"\' type=\'hidden\' name=\'zi["+id+"]\' value=\'1\' />");
				ilist.append("<div id=\'di"+id+"\' title=\'"+litem.attr("title")+"\' class=\'current-zone-itemlist-item\' style=\'background:transparent url("+litem[0].src+") 0px 1px no-repeat;\'>x1</div>");
			}
		}
		
		function zoneEditSaveZone(x,y,t,d,u,fid,a) {
			var zesz = $.post(  
				"map2.editzone.ajax.php",  
				"a="+a+"&t="+t+"&u="+u+"&d="+d+"&x="+x+"&y="+y+"&"+$("#"+fid).serialize(),  
				function(data){  
					$(data).hide().prependTo("#current-zone-result").fadeIn("slow");
				}  
			);
		}

		function registerStorm() { 
			var daten = $("#register-storm").serialize()+"&a=storm";
			$("#storm-direction").html("<div class=\'loading\'></div>");
			var rs = $.post(  
				"map2.update.ajax.php",  
				daten,  
				function(data){  
					$("#storm-direction").html(data);
				}  
			);
		}
	</script>';		
			
	print '<br style="clear:both;" />';
}
else {
	print '<div class="error">Errorcode [01]: XML.</div>';
}
