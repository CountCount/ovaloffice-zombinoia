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


$session = $db->query(' SELECT xml FROM dvoo_rawdata WHERE id = '.$u.' ORDER BY time DESC LIMIT 1 ');

if ( $data = unserialize($session[0]['xml']) ) {

	$ma = array();
	$mo = '<div id="map" style="width:'.(1 + $data['map']['width'] * 27).'px;height:'.(70 + 1 + $data['map']['height'] * 27).'px;">';
	$ma['zones'] = '<div class="map_zones">';
	$ma['fields'] = '<div class="map_fields">';
	$ma['dvzombies'] = '<div id="map_dvzombies_today" class="map_zombies">';
	$ma['zombies'] = '<div id="map_zombies_today" class="map_zombies">';
	$ma['zombiec'] = '<div id="map_zombies_count" class="map_zombies">';
	$ma['buildings'] = '<div class="map_buildings">';
	$ma['tags'] = '<div id="map_tags" class="map_tags">';
	$ma['visits'] = '<div class="map_visits">';
	$ma['citizens'] = '<div id="map_citizens" class="map_citizens">';
	$ma['citidots'] = '<div id="map_citidots" class="map_citidots">';
	$ma['directions_color'] = '<div id="map_directions_color" class="map_maptools" style="opacity:0;">';
	$ma['directions_border'] = '<div id="map_directions_border" class="map_maptools" style="opacity:0;">';
	$ma['watchtowers'] = '<div id="map_watchtowers" class="map_maptools" style="opacity:0;">';
	$ma['searchings'] = '<div id="map_searchings" class="map_maptools" style="opacity:0;">';
	
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
					'tamer' => array(
						'name' => t('TAMER'),
						'img' => 'tamed_pet',
						'kp' => 2,
					),
					'' => array(
						'name' => '',
						'img' => 'pet_chick',
						'kp' => 0,
					),
				);

	// prep
	for ($y = 0; $y < $data['map']['height']; $y++) {
		for ($x = 0; $x < $data['map']['width']; $x++) {
			$data['map'][$y][$x]['nyv'] = (isset($data['map'][$y][$x]) ? 0 : 1);
			$data['map'][$y][$x]['dir'] = get_dir($x,$y,$data['town']['x'],$data['town']['y']);
			
			// Retrieve main zone data from last visit
			$zli = $db->query(' SELECT z, danger, dried, info, UNIX_TIMESTAMP(stamp) AS stamp, day, visit_on, visit_by, radar_r, radar_z, radar_on, radar_by FROM dvoo_zones WHERE x = '.$x.' AND y = '.$y.' AND tid = '.$data['town']['id'].' AND nvt = 0 ORDER BY stamp DESC LIMIT 1 ');
			if ( count($zli) > 0 ) {
				$data['map'][$y][$x]['last_update'] = $zli[0]['stamp'];
				$data['map'][$y][$x]['last_day'] = $zli[0]['day'];
				$data['map'][$y][$x]['z'] = $zli[0]['z'];
				$data['map'][$y][$x]['danger'] = $zli[0]['danger'];
				#$data['map'][$y][$x]['dried'] = $zli[0]['dried'];
				#$data['map'][$y][$x]['info'] = unserialize($zli[0]['info']);
				$data['map'][$y][$x]['visit_on'] = $zli[0]['visit_on'];
				$data['map'][$y][$x]['visit_by'] = $zli[0]['visit_by'];
				#$data['map'][$y][$x]['radar_on'] = $zli[0]['radar_on'];
				#$data['map'][$y][$x]['radar_by'] = $zli[0]['radar_by'];
				#$data['map'][$y][$x]['radar_z'] = $zli[0]['radar_z'];
				#$data['map'][$y][$x]['radar_r'] = $zli[0]['radar_r'];
			}
			
			// Update core values for not visited zones
			if (isset($data['map'][$y][$x]['nvt']) && $data['map'][$y][$x]['nvt'] == 1 && $data['map'][$y][$x]['nyv'] == 0) {
				$zlu = $zli;
				if ( count($zlu) > 0 ) {
					$data['map'][$y][$x]['last_update'] = $zlu[0]['stamp'];
					$data['map'][$y][$x]['z'] = $zlu[0]['z'];
					$data['map'][$y][$x]['danger'] = $zlu[0]['danger'];
					#$data['map'][$y][$x]['info'] = unserialize($zli[0]['info']);
				}
			}
			
			// get radar information
			/*
			$zlr = $db->query(' SELECT radar_r, radar_z, radar_on, radar_by FROM dvoo_zones WHERE x = '.$x.' AND y = '.$y.' AND tid = '.$data['town']['id'].' ORDER BY stamp DESC LIMIT 1 ');
			if ( count($zlr) > 0 ) {
				$data['map'][$y][$x]['radar_on'] = $zlr[0]['radar_on'];
				$data['map'][$y][$x]['radar_by'] = $zlr[0]['radar_by'];
				$data['map'][$y][$x]['radar_z'] = $zlr[0]['radar_z'];
				$data['map'][$y][$x]['radar_r'] = $zlr[0]['radar_r'];
			}
			*/
			
			// get last zombie count
			if ( $data['map'][$y][$x]['z'] == 0 && $data['map'][$y][$x]['danger'] > 0 ) {
        $zlu = $db->query(' SELECT z, day FROM dvoo_zones WHERE x = '.$x.' AND y = '.$y.' AND tid = '.$data['town']['id'].' AND nvt = 0 AND z > 0 ORDER BY stamp DESC LIMIT 1 ');
        if ( count($zlu) > 0 ) {
          $data['map'][$y][$x]['last_day'] = $zlu[0]['day'];
  				$data['map'][$y][$x]['z'] = $zlu[0]['z'];
				}
				else {
          $data['map'][$y][$x]['z'] = null;
        }
      }
			
			// get last known items list
			$zlo = $db->query(' SELECT info, UNIX_TIMESTAMP(stamp) AS stamp FROM dvoo_zones WHERE x = '.$x.' AND y = '.$y.' AND tid = '.$data['town']['id'].' AND info <> "a:0:{}" ORDER BY stamp DESC LIMIT 1 ');
			if ( count($zlo) == 1 ) {
				$data['map'][$y][$x]['info'] = unserialize($zlo[0]['info']);
			}
			
			// get last known dried status
			$zlo = $db->query(' SELECT dried, UNIX_TIMESTAMP(stamp) AS stamp FROM dvoo_zones WHERE x = '.$x.' AND y = '.$y.' AND tid = '.$data['town']['id'].' AND dried IS NOT NULL ORDER BY stamp DESC LIMIT 1 ');
			if ( count($zlo) == 1 ) {
				$data['map'][$y][$x]['dried'] = $zlo[0]['dried'];
			}
			
			// visit
			$zlv = $db->query(' SELECT visit_on, visit_by FROM dvoo_zones WHERE x = '.$x.' AND y = '.$y.' AND tid = '.$data['town']['id'].' AND visit_on IS NOT NULL ORDER BY stamp DESC LIMIT 1 ');
			if ( count($zlv) == 1 ) {
				$data['map'][$y][$x]['visit_on'] = $zlv[0]['visit_on'];
				$data['map'][$y][$x]['visit_by'] = $zlv[0]['visit_by'];
			}
			
			// radar
			$zlr = $db->query(' SELECT radar_r, radar_z, radar_on, radar_by FROM dvoo_zones WHERE x = '.$x.' AND y = '.$y.' AND tid = '.$data['town']['id'].' AND radar_on IS NOT NULL ORDER BY stamp DESC LIMIT 1 ');
			if ( count($zlr) == 1 ) {
				$data['map'][$y][$x]['radar_on'] = $zlr[0]['radar_on'];
				$data['map'][$y][$x]['radar_by'] = $zlr[0]['radar_by'];
				$data['map'][$y][$x]['radar_z'] = $zlr[0]['radar_z'];
				$data['map'][$y][$x]['radar_r'] = $zlr[0]['radar_r'];
			}			
		}
	}
	$danger = array(0 => t('ZONE_D_0'), 1 => t('ZONE_D_1'), 2 => t('ZONE_D_2'), 3 => t('ZONE_D_3'), 4 => t('ZONE_D_4'));
	$zombies = array(0 => "0", 1 => "1", 2 => "2-3", 3 => "4-7", 4 => '8+');

	// route building
	$routes = $db->query('SELECT id AS route_id, day, name, route, CONCAT(tid,day,cid) AS old_route_id FROM dvoo_expeditions WHERE tid = '.$data['town']['id'].' ORDER BY day DESC, cid ASC');
	$routes_info = array();
	foreach ( $routes AS $rdata ) {
		$routes_info[$rdata['route_id']] = 'Tag' . ' ' . $rdata['day'] . ': ' . $rdata['name'];
		$map_route = array();
		$color = 'rgb('.rand(0,204).', '.rand(0,102).', '.rand(0,255).')';
		$route = unserialize($rdata['route']);
		foreach ( $route AS $i => $c ) {
			$map_route[$c['y']][$c['x']] = $i;
			$data['map'][$c['y']][$c['x']]['expeditions'][$rdata['route_id']]['point'] = $i;
			$data['map'][$c['y']][$c['x']]['expeditions'][$rdata['route_id']]['color'] = $color;
		}
		$ma['expedition_'.$rdata['route_id']] = '<div id="map_expedition_'.$rdata['route_id'].'" class="map_expeditions" style="opacity:0;">';
	}

	// generating output
	for ($y = 0; $y < $data['map']['height']; $y++) {
		for ($x = 0; $x < $data['map']['width']; $x++) {
			$zom = (isset($data['map'][$y][$x]['z'])?$data['map'][$y][$x]['z']:null);
			$zoe = (isset($data['map'][$y][$x]['z'])?$data['map'][$y][$x]['z'] + ($data['current_day'] - $data['map'][$y][$x]['last_day']):null);
			$dan = (isset($data['map'][$y][$x]['danger'])?$data['map'][$y][$x]['danger']:null);
			$nyv = $data['map'][$y][$x]['nyv'];
			$nvt = isset($data['map'][$y][$x]['nvt']) ? $data['map'][$y][$x]['nvt'] : 1;
			$rdr = isset($data['map'][$y][$x]['radar_on']) && $data['map'][$y][$x]['radar_on'] > 13000000 ? 1 : 0;
			$tag = isset($data['map'][$y][$x]['tag']) ? $data['map'][$y][$x]['tag'] : 0;
			$day = isset($data['map'][$y][$x]['day']) ? $data['map'][$y][$x]['day'] : null;
			$time = isset($data['map'][$y][$x]['last_update']) ? $data['map'][$y][$x]['last_update'] : null;
			$cit = (isset($data['map'][$y][$x]['citizens'])?count($data['map'][$y][$x]['citizens']):null);
			$dried = (isset($data['map'][$y][$x]['dried'])?$data['map'][$y][$x]['dried']:null);
			$info = (isset($data['map'][$y][$x]['info'])?$data['map'][$y][$x]['info']:array());
			if (!is_null($cit)) {
				$cl = '';
				foreach ($data['map'][$y][$x]['citizens'] AS $cid => $carray) {
					if ( $cl != '' ) {
						$cl .= ', ';
					}
					$cl .= '<img class="zone-detail-img" src="http://www.die2nite.com/gfx/icons/item_'.$jobs[$carray['job']]['img'].'.gif" />' . $carray['name'];
				}
			}
			
			
			// map zones - top layer
			$building = isset($data['map'][$y][$x]['building']) ? $data['map'][$y][$x]['building'] : null;
			$zoe = (isset($data['map'][$y][$x]['building'])?$zoe + ($data['current_day'] - $data['map'][$y][$x]['last_day']):$zoe);
			
			if ( $x == 6 && $y == 2 )
			{
				#var_dump($data['map'][$y][$x]);
			}
			$tempinfo = '';
			$tempinfo .= (is_array($building)?'<strong><em>'.$building['name'].'</em></strong><br/>':(($x == $data['town']['x'] && $y == $data['town']['y'])?'<em>'.t('CITY').'</em><br/>':''));
			$tempinfo .= (is_array($building) && $building['dig'] > 0 ? '<img class="zone-detail-img" src="http://data.die2nite.com/gfx/icons/r_digger.gif" /> <em>'.t('ZONE_DIG', array('%d' => $building['dig'])).'</em><br/>' : '');
			$tempinfo .= t('COORDS').': ['.($x - $data['town']['x']).'|'.(-1 * ($y - $data['town']['y'])).']';
			if ( !($x == $data['town']['x'] && $y == $data['town']['y']) ) {
				$dx = abs($x - $data['town']['x']);
				$dy = abs($y - $data['town']['y']);
				$ap = $dx + $dy;
				$km = round(sqrt(pow($dx,2) + pow($dy,2)));
				$tempinfo .= ' (<strong>'.$ap.'</strong>'.t('AP').'/<strong>'.$km.'</strong>'.t('KM').')';
			}
			$tempinfo .= '<br/>';
			$tempinfo .= ((isset($dried) && $dried == 1) ? '<img class="zone-detail-img" src="http://data.die2nite.com/gfx/icons/r_broken.gif" /> '.t('ZONE_DRIED').'<br/>' : '');
			$tempinfo .= (!is_null($dan) && ( $y - $data['town']['y'] != 0 && $x - $data['town']['x'] != 0) ? '<span class=\'zi_dan\'>'.t('ZONE_DANGER').': '.$danger[$dan].'</span><br/>' : '');
			if ( isset($zoe) && $zoe > $zom ) {
				$tempinfo .= ( ($zoe > 0 && !is_null($zoe) && !($y - $data['town']['y'] == 0 && $x - $data['town']['x'] == 0)) ? '<img class="zone-detail-img" src="http://data.die2nite.com/gfx/icons/r_killz.gif" /> <span class=\'zi_zom\'>'.$zoe.' '.t('ZOMBIEX', array('%s' => array($zoe, t('ZOMBIE'), t('ZOMBIES')))).(isset($zom)? '</span><br/><span class=\'zi_zoe\'>(Last confirmed observation: '.$zom.' '.t('ZOMBIEX', array('%s' => array($zom, t('ZOMBIE'), t('ZOMBIES')))).')' : '').'</span><br/>' : (isset($dan) ? '<img class="zone-detail-img" src="http://data.die2nite.com/gfx/icons/r_killz.gif" /> <span class=\'zi_zom\'>'.$zombies[$dan].' '.t('ZOMBIEX', array('%s' => array($zombies[$dan], t('ZOMBIE'), t('ZOMBIES')))).'</span><br/>' : '<span></span>'));
			}
			else {
				$tempinfo .= ( ($zom > 0 && !is_null($zom) && !($y - $data['town']['y'] == 0 && $x - $data['town']['x'] == 0)) ? '<img class="zone-detail-img" src="http://data.die2nite.com/gfx/icons/r_killz.gif" /> <span class=\'zi_zom\'>'.$zom.' '.t('ZOMBIEX', array('%s' => array($zom, t('ZOMBIE'), t('ZOMBIES')))).'</span><br/>' : (isset($dan) ? '<img class="zone-detail-img" src="http://data.die2nite.com/gfx/icons/r_killz.gif" /> <span class=\'zi_zom\'>'.$zombies[$dan].' '.t('ZOMBIEX', array('%s' => array($zombies[$dan], t('ZOMBIE'), t('ZOMBIES')))).'</span><br/>' : '<span></span>'));
			}
			$tempinfo .= (!is_null($cit) ? '<img class="zone-detail-img" src="http://www.die2nite.com/gfx/forum/smiley/h_human.gif" /> <span class=\'zi_hum\'>'.$cit.' '.t('CITIZEN').': '.$cl.'</span><br/>' : '');
			$tempinfo .= ($nyv?'<em>'.t('ZONE_NYV').'</em>':($nvt?'<em>'.t('ZONE_NVT').'</em>':''));
			$tempinfo .= (isset($time) ? '<br/>'.t('ZONE_LASTUPDATE').' '.utf8_encode(strftime("%B %e, %Y", (int) ($time))).', '.date('h:ia',$time).t('TIME_APPENDIX').'.' : '');
			$tempinfo .= (!is_null($data['map'][$y][$x]['visit_on']) ? '<br/>'.t('ZONE_LASTVISIT').': <strong>'.$data['map'][$y][$x]['visit_by'].'</strong> am '.utf8_encode(strftime("%B %e, %Y", (int) ($data['map'][$y][$x]['visit_on']))).', '.date('h:ia',$data['map'][$y][$x]['visit_on']).t('TIME_APPENDIX') : '');
			$tempinfo .= (!is_null($data['map'][$y][$x]['radar_on']) ? '<br/>'.t('ZONE_LASTRADAR').': <strong>'.$data['map'][$y][$x]['radar_by'].'</strong> am '.utf8_encode(strftime("%B %e, %Y", (int) ($data['map'][$y][$x]['radar_on']))).', '.date('h:ia',$data['map'][$y][$x]['radar_on']).t('TIME_APPENDIX') : '');
			$tempinfo .= (!is_null($data['map'][$y][$x]['radar_z']) || !is_null($data['map'][$y][$x]['radar_r']) ? '<br/>'.t('ZONE_RADAR_INFO').': ' : '');
			$tempinfo .= (!is_null($data['map'][$y][$x]['radar_z']) ? '<strong class="minus">'.$data['map'][$y][$x]['radar_z'].' '.t('ZOMBIEX', array('%s' => array($data['map'][$y][$x]['radar_z'], t('ZOMBIE'), t('ZOMBIES')))).'</strong>' : '');
			$tempinfo .= (!is_null($data['map'][$y][$x]['radar_z']) && !is_null($data['map'][$y][$x]['radar_r']) ? ' | ' : '');
			$tempinfo .= (!is_null($data['map'][$y][$x]['radar_r']) ? ($data['map'][$y][$x]['radar_r'] == 1 ? '<strong class="plus">'.t('ZONE_REGENERATED').'</strong>' : ($nvt?'<strong class="plus">'.t('ZONE_NEW_REGENERATED').'</strong>':'<strong class="minus">'.t('ZONE_NOT_REGENERATED').'</strong>')) : '' );
			
			$itemcount = 0;
			if ( isset($info['items']) && count($info['items']) > 0 ) { 
				$tempinfo .= '<div class="ground"><h4>'.t('ZONE_ITEMSONGROUND').'</h4>';
				foreach ( $info['items'] AS $item ) {
				
					$tempinfo .= '<div class="item mapitem'.($item['broken'] ? ' broken' : '').($itemlist[$item['id']]['cat'] == 'Armor' ? ' deff' : '').'"><img alt="'.$itemlist[$item['id']]['name'].'" title="'.$itemlist[$item['id']]['name'].'" src="'.$data['system']['icon_url'].'item_'.$itemlist[$item['id']]['img'].'.gif" />&nbsp;'.$item['count'].'</div>';
					$itemcount += $item['count'];
				
				}
				$tempinfo .= '</div>'; 
			}
			
			
			$tempinfo .= '</div>';
			
			if ( $x == $data['user']['x'] && $y == $data['user']['y'] ) {
				$firstZI = '<div>'.$tempinfo;
			}
			$tempinfo = '<div id="zi_x'.$x.'y'.$y.'" style="display:none;">'.$tempinfo;
			
			$tempzone = '';
			$tempzone .= '<div class="map_zone" style="left:'.(1 + $x * 27).'px;top:'.(1 + $y * 27).'px;background:transparent;opacity:1;" onmouseover="var ti = $(\'#zi_x'.$x.'y'.$y.'\').html();$(\'#infoblock_zone .dynacontent\').html(ti).slideDown(200);" onclick="var ti = $(\'#zi_x'.$x.'y'.$y.'\').html();$(\'#infoblock_zone #ib-zone\').html(ti);ib(\'zone\');">';
			$tempzone .= $tempinfo;
			$tempzone .= '</div>';
		
			$ma['zones'] .= $tempzone;
			
			// fields
			$ma['fields'] .= '<div class="map_field'.($y == $data['user']['y'] && $x == $data['user']['x'] ? ' ownfield' : '').'" style="left:'.(1 + $x * 27).'px;top:'.(1 + $y * 27).'px;background:#665874;opacity:1;"></div>';
			
			// zombies
			if ( !is_null($zom) && $zom > 0 ) {
				$ma['zombies'] .= '<div class="map_zombie" style="left:'.(2 + $x * 27).'px;top:'.(2 + $y * 27).'px;background:'.i2c($zom).';"></div>';
			} 
			elseif ( !is_null($dan) ) {
				$ma['zombies'] .= '<div class="map_zombie" style="left:'.(2 + $x * 27).'px;top:'.(2 + $y * 27).'px;background:'.i2e($dan).';"></div>';
			} 
			elseif ( !is_null($data['map'][$y][$x]['radar_z']) ) {
				$ma['zombies'] .= '<div class="map_zombie" style="left:'.(2 + $x * 27).'px;top:'.(2 + $y * 27).'px;background:'.i2c($data['map'][$y][$x]['radar_z']).';"></div>';
			}
			
			// dv zombies
			if ( !is_null($zom) ) {
				$ma['dvzombies'] .= '<div class="map_zombie" style="left:'.(2 + $x * 27).'px;top:'.(2 + $y * 27).'px;background:'.dv_i2c($zom).';"></div>';
			}
			elseif ( !is_null($dan) && $dan > 0 ) {
				$ma['dvzombies'] .= '<div class="map_zombie" style="left:'.(2 + $x * 27).'px;top:'.(2 + $y * 27).'px;background:'.dv_i2e($dan).';"></div>';
			} 
			elseif ( !is_null($data['map'][$y][$x]['radar_z']) ) {
				$ma['dvzombies'] .= '<div class="map_zombie" style="left:'.(2 + $x * 27).'px;top:'.(2 + $y * 27).'px;background:'.dv_i2c($data['map'][$y][$x]['radar_z']).';"></div>';
			}
			
			// zombie numbers
			if ( !is_null($zom) && $zom > 0 ) {
				$ma['zombiec'] .= '<div class="map_zombie" style="left:'.(2 + $x * 27).'px;top:'.(2 + $y * 27).'px;"><p class="zc">'.$zom.'</p></div>';
			} 
			elseif ( !is_null($dan) ) {
				$ma['zombiec'] .= '<div class="map_zombie" style="left:'.(2 + $x * 27).'px;top:'.(2 + $y * 27).'px;"><p class="zc">'.$zombies[$dan].'</p></div>';
			} 
			elseif ( !is_null($data['map'][$y][$x]['radar_z']) ) {
				$ma['zombiec'] .= '<div class="map_zombie" style="left:'.(2 + $x * 27).'px;top:'.(2 + $y * 27).'px;"><p class="zc">~'.$data['map'][$y][$x]['radar_z'].'</p></div>';
			}
			
			// citizens
			if ( !is_null($cit) ) {
				$ma['citizens'] .= '<div class="map_citizen" style="left:'.(2 + $x * 27).'px;top:'.(2 + $y * 27).'px;background:'.i2c($cit).';"></div>';
				if ( !($x == $data['town']['x'] && $y == $data['town']['y']) ) {
					$ma['citidots'] .= '<div class="map_citidot" style="left:'.(2 + $x * 27).'px;top:'.(2 + $y * 27).'px;background:transparent url(\'citidot.php?cc='.$cit.'&r='.mt_rand(0,40).'\') ;"></div>';
				}
			}
			
			// buildings
			if ( is_array($building) ) {
				$ma['buildings'] .= '<div class="map_building" style="left:'.(1 + $x * 27).'px;top:'.(1 + $y * 27).'px;background:transparent;"><div class="map_building_icon type'.$building['type'].'"></div></div>';
			}
			elseif ( $x == $data['town']['x'] && $y == $data['town']['y'] ) {
				$ma['buildings'] .= '<div class="map_building" style="left:'.(1 + $x * 27).'px;top:'.(1 + $y * 27).'px;background:transparent;"><div class="map_building_icon city"></div></div>';
			}
			
			// tags
			if ( !is_null($tag) && $tag > 0 ) {
				$ma['tags'] .= '<div class="map_tag" style="left:'.(2 + $x * 27).'px;top:'.(2 + $y * 27).'px;"><img src="http://data.die2nite.com/gfx/icons/tag_'.$tag.'.gif" /></div>';
			}			
			
			// visiting status
			$ma['visits'] .= '<div class="map_visit'.($nyv ? ($rdr ? ' rdr' : ' nyv') : ($nvt ? ' nvt' : '')).'" style="left:'.(1 + $x * 27).'px;top:'.(1 + $y * 27).'px;"></div>';
			
			// search results
			$ma['searchings'] .= '<div class="map_searching" id="s_x'.$x.'-y'.$y.'" style="left:'.(1 + $x * 27).'px;top:'.(1 + $y * 27).'px;"></div>';
			
			// expeditions
			if ( isset($data['map'][$y][$x]['expeditions']) && is_array($data['map'][$y][$x]['expeditions']) ) {
				foreach ( $data['map'][$y][$x]['expeditions'] AS $rid => $rdata ) {
					$ma['expedition_'.$rid] .= '<div class="map_expedition" style="left:'.(5 + $x * 27).'px;top:'.(5 + $y * 27).'px;background:'.$rdata['color'].';">'.$rdata['point'].'</div>';
				}
			}
			
			// directions
			if ( isset($data['map'][$y][$x]['dir']) && $data['map'][$y][$x]['dir'] > 0 ) {
				$ma['directions_color'] .= '<div class="map_direction_color" style="left:'.(1 + $x * 27).'px;top:'.(1 + $y * 27).'px;background:'.(abs($data['map'][$y][$x]['dir']) % 2 == 0 ? '#f0f' : '#00f').';"></div>';
			}
			
			$direction_classes = '';
			if ( $x > 0 && $data['map'][$y][$x]['dir'] != $data['map'][$y][$x - 1]['dir'] ) {
				$direction_classes .= ' dbL ';
			}
			if ( $y > 0 && $data['map'][$y - 1][$x]['dir'] != $data['map'][$y][$x]['dir'] ) {
				$direction_classes .= ' dbT ';
			}
			if ( $direction_classes != '' ) {
				$ma['directions_border'] .= '<div class="map_direction_border'.$direction_classes.'" style="left:'.(- 1 + $x * 27).'px;top:'.(- 1 + $y * 27).'px;background:transparent;"></div>';
			}
			
			// watch tower
			$rx = $x - $data['town']['x'];
			$ry = $y - $data['town']['y'];
			if ( (abs($rx) + abs($ry) <= 3) && (abs($rx) + abs($ry) > 0) ) {
				if ( round(sqrt(($rx * $rx) + ($ry * $ry))) <= 1 ) {
					$ma['watchtowers'] .= '<div class="map_watchtower" style="left:'.(1 + $x * 27).'px;top:'.(1 + $y * 27).'px;background:#ccf;"></div>';
				}
				elseif ( round(sqrt(($rx * $rx) + ($ry * $ry))) <= 2 ) {
					$ma['watchtowers'] .= '<div class="map_watchtower" style="left:'.(1 + $x * 27).'px;top:'.(1 + $y * 27).'px;background:#fff;"></div>';
				}
			}
		}
	}

	print '<div class="infoblock" id="infoblock_zone">';
if ( (date('z') >= 111 && date('z') <= 114) ) {
	if ( mt_rand(1,1000) > 950 ) {
		print '<div class="easteregg ee'.mt_rand(10000,99999).' egg'.mt_rand(1,4).' rot'.mt_rand(1,5).'" style="top:'.mt_rand(0,300).'px;left:'.mt_rand(0,300).'px;opacity:'.(mt_rand(2,8)/10).';"></div>';
	}
}
	print '<h3 class="handle">'.t('MAP_OP_TITLE').'</h3>';
	print '<div class="option-bar">
		<div id="il-zone" rel="ib-zone" class="active" onclick="ib(\'zone\');">'.t('MAP_OP_INFO').'</div>
		<div id="il-mapoptions" rel="ib-mapoptions" onclick="ib(\'mapoptions\');">'.t('MAP_OP_SETTINGS').'</div>
		<div id="il-expeditions" rel="ib-expeditions" onclick="ib(\'expeditions\');">'.t('MAP_OP_EXPEDITIONS').'</div></div>';
	print '<div id="ib-zone" class="ib-content content">';
	print $firstZI;
	print '</div>';
	print '<div id="ib-mapoptions" class="ib-content content hideme">';
	print '<a href="javascript:void(0);" id="map_hover_dvzombie" class="map_hover ms_on" onclick="return false;" >'.t('MAP_LAYER_ZCORI').'</a>';
	print '<a href="javascript:void(0);" id="map_hover_zombie" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_ZCDET').'</a>';
	print '<a href="javascript:void(0);" id="map_hover_citizen" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_CITCOUNT').'</a>';
	print '<a href="javascript:void(0);" id="map_hover_zombiec" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_ZOMCOUNT').'</a>';
	print '<a href="javascript:void(0);" id="map_hover_tags" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_ZONEMARKER').'</a>';
	print '<a href="javascript:void(0);" id="map_hover_color" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_DIRCOLOR').'</a>';
	print '<a href="javascript:void(0);" id="map_hover_border" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_DIRBORDER').'</a>';
	print '<a href="javascript:void(0);" id="map_hover_watch" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_WATCHTOWER').'</a>';
	print '<br/><a href="javascript:void(0);" id="map_hover_search" class="map_hover ms_off" onclick="return false;" >'.t('MAP_LAYER_SEARCH').'</a>';
	
	if ( $data['user']['id'] == 3137 || 1 == 1 ) {
		
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
		
		print '<div id="map-search-wrapper"><div id="map-search-form"><form id="map-search" onsubmit="mapSearch();return false;"><select name="cat" id="map-search-cat"><option value="0">'.t('MAP_SEARCH_CAT').'</option>';
		foreach ( $ordercat AS $catn ) {
			print '<option value="'.$catn.'">'.$catnames[$catn].'</option>';
		}
		print '</select><input type="hidden" name="u" value="'.$data['user']['id'].'" /><input id="map-search-itemid" type="hidden" name="id" value="0" /><input type="text" name="item" id="map-search-item" onchange="$(\'#map-search-itemid\').val(0);$(\'#map-search-cat\').val(0);" value="'.t('MAP_SEARCH_ITEM').'" onclick="$(this).focus().select();" /><input id="ms-button" type="submit" value="'.t('MAP_SEARCH_FIND').'" /></form></div><div id="map-search-result"></div></div>';
	}
	
	print '</div>';
	print '<div id="ib-expeditions" class="ib-content content hideme">';
	print '<a href="javascript:void(0);" id="exp_hover_all" class="exp_hover_all ms_off" onclick="toggleOpas();"><strong>'.t('MAP_ALL_EXPEDITIONS').'</strong></a>';
	foreach ($routes_info AS $rid => $rname) {
		print '<a href="javascript:void(0);" id="exp_hover_'.$rid.'" class="exp_hover ms_off" onclick="toggleOpa(\'map_expedition_'.$rid.'\', \'exp_hover_'.$rid.'\');">'.$rname.'</a>';
	}
	print '</div>';
	print '<div class="dynacontent">';
	print '';
	print '</div>';
	print '</div>';
if ( (date('z') >= 111 && date('z') <= 114) ) {
	if ( mt_rand(1,1000) > 900 ) {
		print '<div class="easteregg ee'.mt_rand(10000,99999).' egg'.mt_rand(1,4).' rot'.mt_rand(1,5).'" style="top:'.mt_rand(0,900).'px;left:'.mt_rand(0,900).'px;opacity:'.(mt_rand(2,8)/10).';"></div>';
	}
}
	
	foreach ( $ma AS $mid => $mhtml ) {
		$mo .= '<!-- '.$mid.' -->'.$mhtml.'</div>';
	}
	$mo .= '<div id="legend" style="top:'.(7 + $y * 27).'px;">
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
	$mo .= '</div>';
	print $mo;

	print '<div id="info" style="height:100%;width:'.(900 - (1 + $data['map']['width'] * 27)).'px;">';

	print '</div>';

	print '<script type="text/javascript">

	function toggleOpa(divid, el) {
		$("#" + el).toggleClass("ms_on ms_off");
		var curopa = $("#" + divid).css("opacity");
		$("#" + divid).css("opacity", (1-curopa));
		return false;
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

	$("#map_hover_tags").click(function(el) {
		$("#map_hover_tags").toggleClass("ms_on ms_off");
		var curopa_c = $("#map_tags").css("opacity");
		$("#map_tags").css("opacity", (1-curopa_c));
	});

	$("#map_hover_color").click(function(el) {
		$("#map_hover_color").toggleClass("ms_on ms_off");
		var curopa_c = $("#map_directions_color").css("opacity");
		$("#map_directions_color").css("opacity", (1-curopa_c));
	});

	$("#map_hover_border").click(function(el) {
		$("#map_hover_border").toggleClass("ms_on ms_off");
		var curopa_b = $("#map_directions_border").css("opacity");
		$("#map_directions_border").css("opacity", (1-curopa_b));
	});

	$("#map_hover_watch").click(function(el) {
		$("#map_hover_watch").toggleClass("ms_on ms_off");
		var curopa_w = $("#map_watchtowers").css("opacity");
		$("#map_watchtowers").css("opacity", (1-curopa_w));
	});
	
	$("#map_hover_search").click(function(el) {
		$("#map_hover_search").toggleClass("ms_on ms_off");
		var curopa_s = $("#map_searchings").css("opacity");
		$("#map_searchings").css("opacity", (1-curopa_s));
	});
	
	$("#map_hover_zombiec").click(function(el) {
		$("#map_hover_zombiec").toggleClass("ms_on ms_off");
		var curopa_p = $("#map_zombies_count").css("opacity");
		$("#map_zombies_count").css("opacity", (1-curopa_p));
	});	
	
	$("#map_hover_dvzombie").click(function(el) {
		if ($("#map_dvzombies_today").css("opacity") == 1) {
			$("#map_dvzombies_today").css("opacity", 0);
			$("#map_hover_dvzombie").removeClass("ms_on").addClass("ms_off");
		}
		else {
			$("#map_zombies_today").css("opacity", 0);
			$("#map_hover_zombie").removeClass("ms_on").addClass("ms_off");
			$("#map_citizens").css("opacity", 0);
			$("#map_hover_citizen").removeClass("ms_on").addClass("ms_off");
			$("#map_dvzombies_today").css("opacity", 1);
			$("#map_hover_dvzombie").removeClass("ms_off").addClass("ms_on");
		}
	});
	$("#map_hover_zombie").click(function(el) {
		if ($("#map_zombies_today").css("opacity") == 1) {
			$("#map_zombies_today").css("opacity", 0);
			$("#map_hover_zombie").removeClass("ms_on").addClass("ms_off");
		}
		else {
			$("#map_dvzombies_today").css("opacity", 0);
			$("#map_hover_dvzombie").removeClass("ms_on").addClass("ms_off");
			$("#map_citizens").css("opacity", 0);
			$("#map_hover_citizen").removeClass("ms_on").addClass("ms_off");
			$("#map_zombies_today").css("opacity", 1);
			$("#map_hover_zombie").removeClass("ms_off").addClass("ms_on");
		}
	});
	$("#map_hover_citizen").click(function(el) {
		if ($("#map_citizens_today").css("opacity") == 1) {
			$("#map_citizens_today").css("opacity", 0);
			$("#map_hover_citizen").removeClass("ms_on").addClass("ms_off");
		}
		else {
			$("#map_zombies_today").css("opacity", 0);
			$("#map_hover_zombie").removeClass("ms_on").addClass("ms_off");
			$("#map_dvzombies_today").css("opacity", 0);
			$("#map_hover_dvzombie").removeClass("ms_on").addClass("ms_off");
			$("#map_citizens").css("opacity", 1);
			$("#map_hover_citizen").removeClass("ms_off").addClass("ms_on");
		}
	});
	
	function switchInfoBlock(elid) {
		$("#infoblock_"+elid+" .content").slideToggle();
	}
	
	function ib(ibsub) {
		$(".option-bar div").removeClass("active");
		$("#il-"+ibsub).addClass("active");
		$(".ib-content").addClass("hideme");
		$("#ib-"+ibsub).removeClass("hideme");
	}
	
	/*$("#infoblock_zone")
        .bind("dragstart",function( event ){
                if ( !$(event.target).is(".handle") ) return false;
                return $( this ).css("opacity",.5)
                        .clone().addClass("drag-active")
                        .insertAfter( this );
                })
        .bind("drag",function( event ){
                $( event.dragProxy ).css({
                        top: event.offsetY,
                        left: event.offsetX
                        });
                })
        .bind("dragend",function( event ){
                $( event.dragProxy ).remove();
                $( this ).animate({
                        top: event.offsetY,
                        left: event.offsetX,
                        opacity: 1
                        })
                }); */
								
		function getScrollTop(){
				if(typeof pageYOffset!= "undefined"){
						//most browsers
						return pageYOffset;
				}
				else{
						var B= document.body; //IE "quirks"
						var D= document.documentElement; //IE with doctype
						D= (D.clientHeight)? D: B;
						return D.scrollTop;
				}
		}
		
		$("#infoblock_zone")
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
		
		function mapSearch() {  
					$("#ms-button").hide();
					$("#map-search-result").html("<div class=\'loading\'></div>");
					var ms = $.post(  
						"map.search.ajax.php",  
						$("#map-search").serialize(),  
						function(data){  
							//$("#").val("");
							$("#map-search-result").html(data);
							$("#ms-button").fadeIn(500);
						}  
					);
				}

	</script>';		
			
	print '<br style="clear:both;" />';
}
else {
	print '<div class="error">Errorcode [01]: Fehler in der XML-Verarbeitung.</div>';
}
