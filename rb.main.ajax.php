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
			$("#link_reisebuero").remove();
			$("#reisebuero").remove();
		</script>';
	exit;
}

$session = $db->query(' SELECT xml FROM dvoo_rawdata WHERE id = '.$u.' ORDER BY time DESC LIMIT 1 ');

if ( $data = unserialize($session[0]['xml']) ) {
	if ( $data['town']['chaos'] == 0 && $data['town']['devast'] == 0 ) {
		// route building
		$routes = $db->query('SELECT id, day, name, route, CONCAT(tid,day,cid) AS route_id FROM dvoo_expeditions WHERE tid = '.$data['town']['id'].' ORDER BY day DESC, cid ASC');
		$routes_info = array();
		foreach ( $routes AS $rdata ) {
			$routes_info[$rdata['id']] = 'Tag' . ' ' . $rdata['day'] . ': ' . $rdata['name'];
			$routes_points[$rdata['id']] = unserialize($rdata['route']);
		}
		
		// get own group
		$res = $db->query('SELECT * FROM dvoo_groups WHERE uid = '.$data['user']['id'].' AND tid = '.$data['town']['id']);
		if ( $res ) {
			$owngroup = $res[0];
		}
		else {
			$owngroup = array();
		}
		
		// get other group
		$res = $db->query('SELECT g.* FROM dvoo_groups g INNER JOIN dvoo_group_citizens c ON c.gid = g.gid WHERE c.uid = '.$data['user']['id'].' AND g.tid = '.$data['town']['id']);
		if ( $res ) {
			$group = $res[0];
		}
		else {
			$group = array();
		}
		
		if ( !isset($owngroup['name']) && !isset($group['name']) ) {
			// keine Gruppenzugehörigkeit
			$rbmode = 0;	
		}
		elseif ( !isset($group['name']) ) {
			// eigene Gruppe gegründet, aber keine Mitglieder
			$rbmode = 2;		
		}
		elseif ( !isset($owngroup['name']) ) {
			// Mitglied einer anderen Gruppe
			$rbmode = 1;
		}
		else {
			// Mitglied der eigenen Gruppe
			$rbmode = 3;
		}
		if ( $rbmode > 0 && $rbmode % 2 == 1 ) {
			// route
			$res = $db->query(' SELECT * FROM dvoo_expeditions WHERE id = '.$group['route']);
			$rbroute = $res[0];
			$rbrpoints = unserialize($rbroute['route']);
			$cp = null;
			$np = null;
			$xa = null;
			$xb = null;
			$ya = null;
			$yb = null;
			$mkd = array();
			$maxz = null;
			$robj = array();
			$robc = array();
			foreach ( $rbrpoints AS $p => $k ) {
				if ($k['x'] == $data['user']['x'] && $k['y'] == $data['user']['y']) {
					$cp = $p;
				}
				if ( is_null($xa) || $k['x'] < $xa ) { $xa = $k['x']; }
				if ( is_null($xb) || $k['x'] > $xb ) { $xb = $k['x']; }
				if ( is_null($ya) || $k['y'] < $ya ) { $ya = $k['y']; }
				if ( is_null($yb) || $k['y'] > $yb ) { $yb = $k['y']; }
				$mkd[$k['y']][$k['x']]['p'] = $p;
				if ( $p > 1 ) {
					$pp = $p - 1;
					if ( $rbrpoints[$pp]['x'] > $rbrpoints[$p]['x'] ) {
						$cf = 3;
					}
					elseif ( $rbrpoints[$pp]['x'] < $rbrpoints[$p]['x'] ) {
						$cf = 7;
					}
					elseif ( $rbrpoints[$pp]['y'] > $rbrpoints[$p]['y'] ) {
						$cf = 5;
					}
					elseif ( $rbrpoints[$pp]['y'] < $rbrpoints[$p]['y'] ) {
						$cf = 1;
					}
					$mkd[$k['y']][$k['x']]['f'] = $cf;
				}
				
				$zi = $db->query(' SELECT z FROM dvoo_zones WHERE x = '.$k['x'].' AND y = '.$k['y'].' AND tid = '.$data['town']['id'].' AND nvt = 0 ORDER BY stamp DESC LIMIT 1 ');
				
				if ( count($zi) == 1 ) {
					// max zombies
					$z = $zi[0]['z'];
					if ( is_null($maxz) || $z > $maxz ) {
						$maxz = $z;
					}
				}
				
				$ki = $k['x'].'.'.$k['y'];
				if ( !in_array($ki, $robc) ) {
					$zr = $db->query(' SELECT items FROM dvoo_zones_visit WHERE x = '.$k['x'].' AND y = '.$k['y'].' AND tid = '.$data['town']['id'].' AND items <> "a:0:{}" AND items IS NOT NULL ORDER BY `on` DESC LIMIT 1 ');
					
					if ( count($zr) == 1 ) {
						// items en route
						$items = unserialize($zr[0]['items']);
						if ( is_array($items) && count($items) > 0 ) {
							foreach ( $items AS $item ) {
								$in = $db->query(' SELECT iname AS name, iimg AS img, icat AS cat FROM dvoo_items WHERE iid = ' . $item['id'] . ' LIMIT 1');
								$ii = $in[0];
									
								if ( is_array($robj[$ii['cat']][$item['id']]) ) {
									$robj[$ii['cat']][$item['id']]['count'] += $item['count'];
								}
								else {
									$robj[$ii['cat']][$item['id']] = array(
										'name' => $ii['name'],
										'count' => $item['count'],
										'img' => $ii['img'],
									);
								}
							}
						}
					}
					$robc[] = $ki;
				}
			}
			if ( is_null($xa) || $data['user']['x'] < $xa ) { $xa = $data['user']['x']; }
			if ( is_null($xb) || $data['user']['x'] > $xb ) { $xb = $data['user']['x']; }
			if ( is_null($ya) || $data['user']['y'] < $ya ) { $ya = $data['user']['y']; }
			if ( is_null($yb) || $data['user']['y'] > $yb ) { $yb = $data['user']['y']; }
			// next step
			if ( !is_null($cp) ) {
				$np = $cp + 1;
				if ( $rbrpoints[$np]['x'] > $data['user']['x'] ) {
					$nd = t('Ego');
				}
				elseif ( $rbrpoints[$np]['x'] < $data['user']['x'] ) {
					$nd = t('Wgo');
				}
				elseif ( $rbrpoints[$np]['y'] > $data['user']['y'] ) {
					$nd = t('Sgo');
				}
				elseif ( $rbrpoints[$np]['y'] < $data['user']['y'] ) {
					$nd = t('Ngo');
				}
			}
			
			// minikarte
			$mw = $xb - $xa;
			$mh = $yb - $ya;
			$mk = '<table class="rb_minimap"><tr><th></th>';
			for ( $i = $xa; $i <= $xb; $i++ ) {
				$mk .= '<th>'.($i - $data['town']['x']).'</th>';
			}
			$mk .= '</tr>';
			for ( $j = $ya; $j <= $yb; $j++ ) {
				$mk .= '<tr><th>'.($data['town']['y'] - $j).'</th>';
				for ( $i = $xa; $i <= $xb; $i++) {
					$class = array();
					if ($i == $data['town']['x'] && $j == $data['town']['y']) { $class[] = "town"; }
					if ($i == $data['user']['x'] && $j == $data['user']['y']) { $class[] = "current"; }
					if ($i == $rbrpoints[$np]['x'] && $j == $rbrpoints[$np]['y']) { $class[] = "next"; }
					
					if ( isset($mkd[$j][$i]['p']) && $mkd[$j][$i]['p'] > 0 ) {
						if ( $mkd[$j][$i]['p'] < $cp ) {
							$class[] = "routepoint_av"; 
						}
						elseif ( $mkd[$j][$i]['p'] > $np ) {
							$class[] = "routepoint_nv"; 
						}
					}
					$mk .= '<td class="'.	implode(' ',$class).'"><span class="cf cf'.$mkd[$j][$i]['f'].'"></span></td>';
				}			
				$mk .= '</tr>';
			}
			$mk .= '</table>';
			
			// group members
			$members = array();
			$res = $db->query(' SELECT c.name FROM dvoo_citizens c INNER JOIN dvoo_group_citizens r ON r.uid = c.id WHERE r.gid = '.$group['gid']);
			foreach ( $res AS $m ) {
				$members[] = $m['name'];
			}
			
		}
		else {
			$rbroute = array();
		}
		
		// aktive Reisegruppen
		$tgroups = array();
		$res = $db->query(' SELECT g.gid, g.uid AS fid, g.name AS gname, c.id AS cid, c.name AS cname, j.job, t.clean, t.topform, t.safe, t.paralyzed FROM dvoo_groups g INNER JOIN dvoo_group_citizens r ON r.gid = g.gid INNER JOIN dvoo_citizens c ON c.id = r.uid INNER JOIN dvoo_town_citizens j ON j.town_id = g.tid AND j.citizen_id = c.id LEFT JOIN dvoo_timeplanner t ON t.uid = c.id AND t.day = '.$data['current_day'].' AND t.tid = g.tid WHERE g.tid = '.$data['town']['id'].' ORDER BY g.gid ');
		foreach ( $res AS $row ) {
			#print var_export($row,true);
			$tgroups[$row['gid']]['name'] = $row['gname'];
			$tgroups[$row['gid']]['fid'] = $row['fid'];
			$tgroups[$row['gid']]['members'][$row['cid']] = $row['cname'];
			$kp = 2;
			if ($row['job'] == 'guardian') {
				$kp += 2;
			}
			if ($row['safe'] == 1) {
				$kp++;
			}
			if ($row['clean'] == 1 && $row['topform'] == 1) {
				$kp++;
			}
			if ($row['paralyzed'] == 1) {
				$kp = 0;
			}
			$tgroups[$row['gid']]['control'][$row['cid']] = $kp;
		}
		
		// available members
		$acits = array();
		$res = $db->query(' SELECT c.id, c.name FROM dvoo_citizens c INNER JOIN dvoo_town_citizens t ON c.id = t.citizen_id LEFT JOIN dvoo_group_citizens g ON g.uid = c.id WHERE g.gid IS NULL AND t.town_id = '.$data['town']['id'].' AND t.dead = 0 ORDER BY c.name ASC ');
		foreach ( $res AS $r ) {
			$acits[$r['id']] = strtoupper($r['name']).'|'.$r['name'];
		}
	#print $rbmode.'<br/>';	
	?>

	<h3><?php print t('TRAVEL_TITLE'); ?></h3>
<?php 
if ( (date('z') >= 111 && date('z') <= 114) ) {
	if ( mt_rand(1,1000) > 900 ) {
		print '<div class="easteregg ee'.mt_rand(10000,99999).' egg'.mt_rand(1,4).' rot'.mt_rand(1,5).'" style="top:'.mt_rand(0,900).'px;left:'.mt_rand(0,900).'px;opacity:'.(mt_rand(2,8)/10).';"></div>';
	}
}
?>
	<p><?php print t('TRAVEL_INFO'); ?></p>

	<div id="rb_owngroup">
	<?php if ( $rbmode == 0 ) { ?>
		<form method="POST" id="rb_ng_form" name="rb_ng_form" onsubmit="submitRBNGform();return false;">
			<input type="hidden" value="<?php print $data['user']['id']; ?>" name="u" />
			<input type="hidden" value="<?php print $data['town']['id']; ?>" name="t" />
			<input type="hidden" value="1" name="a" />
			<h4><?php print t('TRAVEL_YOUR_GROUP'); ?></h4>
			<p><?php print t('TRAVEL_NO_GROUP'); ?></p>
			<fieldset>
				<div><label for="newgroup_name" style=""><?php print t('TRAVEL_GROUP_NAME'); ?></label> <input type="text" size="32" name="newgroup_name" id="newgroup_name" value="<?php print $data['user']['name'].t('TRAVEL_NEW_GROUP_NAME'); ?>" /></div>
				<p><input type="checkbox" id="newgroup_persistence" name="newgroup_persistence" value="persistent" /><label for="newgroup_persistence"><?php print t('TRAVEL_GROUP_PERSISTENT'); ?></label></p>
				<div><label for="newgroup_route" style=""><?php print t('TRAVEL_GROUP_ROUTE'); ?></label> <select name="newgroup_route" id="newgroup_route">
				<?php
				foreach ( $routes_info AS $rid => $rname ) {
					print '<option value="'.$rid.'">'.$rname.'</option>';
				}
				?>
				</select></div>
				<div><label></label><input type="submit" name="newgroup_create" id="newgroup_create" value="<?php print t('TRAVEL_GROUP_CREATE'); ?>" /></div>
			</fieldset>
		</form>
		
		
	<?php } elseif ( $rbmode == 2 ) { ?>
		<form method="POST" id="rb_og_form" name="rb_og_form" onsubmit="submitRBOGform();return false;">
			<input type="hidden" value="<?php print $data['user']['id']; ?>" name="u" />
			<input type="hidden" value="<?php print $data['town']['id']; ?>" name="t" />
			<input type="hidden" value="2" name="a" />
			<input type="hidden" value="<?php print $owngroup['gid']; ?>" name="owngroup_id" />
			<h4><?php print t('TRAVEL_YOUR_GROUP'); ?>: <?php print $owngroup['name']; ?></h4>
			<p><?php print t('TRAVEL_GROUP_NO_MEMBERS'); ?></p>
			<fieldset>
				<h5><?php print t('TRAVEL_GROUP_MEMBERS'); ?></h5>
				<?php
				#foreach ( $data['citizens'] AS $cid => $cname ) {
				$ac = count($acits);
				$pc = ceil($ac/4);
				asort($acits);
				$cc = 0;
				foreach ( $acits AS $cid => $cname ) {
					$cname = explode('|',$cname);
					$cname = $cname[1];
					if ( $cc % $pc == 0 ) {
						if ( $cc > 0 ) {
							print '</fieldset>';
						}
						if ( $ac > $cc ) {
							print '<fieldset class="rb_member_column">';
						}
					}
					print '<p class="rb_member"><input type="checkbox" '.($cid == $owngroup['uid'] ? 'checked="checked" disabled="disabled"' : '').' id="member_'.$owngroup['gid'].'_'.$cid.'" name="member_'.$owngroup['gid'].'[]" value="'.$cid.'" /><label for="member_'.$owngroup['gid'].'_'.$cid.'">'.$cname.'</label></p>';
					$cc++;
				}
				if ( $cc < $ac ) {
					print '</fieldset>';
				}
				?>
				<div><input type="submit" name="owngroup_save" id="owngroup_save" value="<?php print t('TRAVEL_GROUP_UPDATE'); ?>" /></div>
			</fieldset>
		</form>
	<?php } else { ?>
			<div class="groupInfo">
			<?php print implode('<br/>',$members); ?>
			<?php print ($rbmode == 1) ? '<br/><input type="button" value="'.t('TRAVEL_GROUP_LEAVE').'" onclick="leaveGroup();" />' : (($rbmode == 3) ? '<br/><input type="button" value="'.t('TRAVEL_GROUP_CLOSE').'" onclick="closeGroup();" />' : ''); ?>
			</div>
			<div class="mapInfo">
			<?php print $mk; ?>
			</div>
			<h4><?php print t('TRAVEL_YOUR_GROUP'); ?>: <?php print $group['name']; ?></h4>
			<p><?php print t('TRAVEL_YOUR_LOCATION'); ?>: [<?php print $data['user']['rx']; ?>|<?php print $data['user']['ry']; ?>]<br/>
			<?php print (!is_null($cp) ? t('TRAVEL_ROUTE_PROGRESS', array('%n' => $cp, '%p' => round($cp/$rbroute['length']*100))).'<br/>' : '<div class="error">'.t('TRAVEL_ROUTE_AWAY').'</div>'); ?>
			<?php print (!is_null($np) ? t('TRAVEL_ROUTE_NEXT', array('%s' => $nd)).'<br/>' : ''); ?>
			<?php print (!is_null($maxz) ? t('TRAVEL_ROUTE_MAX_ZOMBIES', array('%d' => $maxz)).'<br/>' : ''); ?>
			<?php if (count($robj) > 0 ) {
				print t('TRAVEL_ROUTE_ITEMS').':<br/><span style="font-size:12px;">';
				foreach ( $robj AS $cat => $ritems ) {
					if ( is_array($ritems) ) {
						print '<strong>'.t($cat).':</strong> ';
						foreach ( $ritems AS $ri ) {
							print '<img alt="'.$ri['name'].'" title="'.$ri['name'].'" src="'.$data['system']['icon_url'].'item_'.$ri['img'].'.gif" />&nbsp;'.$ri['count'].'&nbsp;&nbsp;&nbsp;';
						}
						print '<br/>';
					}
				}
				print '</span>';
			}		
			?></p>
			<br style="clear:both;" />
	<?php } ?>
	</div>

	<div id="rb_towngroups">
	<h4><?php print t('TRAVEL_ALL_GROUPS'); ?></h4>
	<?php
		$r = 0;
		foreach ( $tgroups AS $tg ) {
			$r++;
			print '<p>#'.$r.' '.$tg['name'].': ';
			$members = $tg['members'];
			asort($members);
			foreach ( $members AS $mid => $mname ) {
				if ( $mid == $tg['fid'] ) { print '<strong>';}
				print $mname . ' ';
				if ( $mid == $tg['fid'] ) { print '</strong>';}
			}
			$kp = $tg['control'];
			print '- '. array_sum($kp) . ' '.t('CP');
			print '</p>';
			#print var_export($tg,true);
		}
	?>
	</div>

	<script type="text/javascript">				
					
					function submitRBNGform() {  
						var tp = $.post(  
							"rb.form.ajax.php",  
							$("#rb_ng_form").serialize(),  
							function(data){  
								$('#rb_owngroup').html(data);  
							}  
						);
					}
					
					function submitRBOGform() {  
						var tp = $.post(  
							"rb.form.ajax.php",  
							$("#rb_og_form").serialize(),  
							function(data){  
								$('#rb_owngroup').html(data);  
							}  
						);
					}
					
					function leaveGroup() {
						var tp = $.post(  
							"rb.form.ajax.php",  
							"groupAction=leave&groupID=<?php print $group['gid']; ?>&userID=<?php print $u; ?>",  
							function(data){  
								$('#rb_owngroup').html(data);  
							}  
						);
					}
					
					function closeGroup() {
						var tp = $.post(  
							"rb.form.ajax.php",  
							"groupAction=close&groupID=<?php print $group['gid']; ?>&userID=<?php print $u; ?>",  
							function(data){  
								$('#rb_owngroup').html(data);  
							}  
						);
					}
		</script>
		<?php
	}
	else {
	?>
	
		<h3><?php print t('TRAVEL_TITLE'); ?></h3>
		<p style="color:#c00;"><?php print t('TRAVEL_CHAOS'); ?></p>
	
	<?php
	}
}
else {
	print '<div class="error">Errorcode [06]: Fehler in der XML-Verarbeitung.</div>';
}