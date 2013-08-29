<?php
include_once 'system.php';

$db = new Database();

$events = array();
$events[1] = array(
	'title' => 'Choque de Héroes',
	'logo' => '<img class="eventLogo" src="img/choc.png" />',
	'towns' => array(
		6361,6362,6350
	),
	'desc' => 'Este es la tabla principal de los pueblos participantes en el gran "Choque de Héroes".',
);


$signup = array();
$signup[1] = array(
	'title' => 'Choque de Héroes',
	'logo' => '<img class="eventLogo" src="img/choc.png" />',
	'options' => array(
		'Guardián' => '<img src="'.t('GAMESERVER_ICON').'r_jguard.gif" />',
		'Excavador' => '<img src="'.t('GAMESERVER_ICON').'r_jcolle.gif" />',
		'Explorador' => '<img src="'.t('GAMESERVER_ICON').'r_jrangr.gif" />',
		'Nuevo oficio' => '<img src="'.t('GAMESERVER_ICON').'item_basic_suit.gif" />',
		/*'Tamers' => '<img src="'.t('GAMESERVER_ICON').'r_jtamer.gif" />',
		'Survivalists' => '<img src="'.t('GAMESERVER_ICON').'r_jermit.gif" />',
		'Technicians' => '<img src="'.t('GAMESERVER_ICON').'r_jtech.gif" />',
		'Shamans' => '<img src="'.t('GAMESERVER_ICON').'item_shaman.gif" />'*/
	),
	'desc' => 'Este es la tabla principal de los pueblos participantes en el gran "Choque de Héroes".',
	'hide' => 0,
  'second' => 0,
  'backup' => 1,
  'any' => 0,
  'start' => 1341792000,
  'limit' => 1
);

// get user code
$k = (string) $_REQUEST['k'];
$q = 'SELECT name FROM dvoo_citizens WHERE scode = "'.$k.'"';
$r = $db->query($q);
$username = $r[0][0];
// get event
$e = (int) $_REQUEST['e'];
if ( $e < 0 ) {
	// it's signup
	$s = 0 - $e;
	$options = $signup[$s]['options'];
	$event = $signup[$s];
	$cols = count($options) + 1;
	$signups = array();
	
	print '<div id="spy-close" class="clickable" onclick="spyclose();"></div>';
	print $event['logo'] . '<h1>'.$event['title'].'</h1><p>'.$event['desc'].'</p>';
  if ( isset($signup[$s]['start']) ) {
    print '<script type="text/javascript">
      $(document).ready(function() {
        window.setInterval("updateTimer()",1000);
      });
      function updateTimer() {
        var startTime = new Date();
        var currentTime = new Date();
        startTime.setTime('.($signup[$s]['start'] * 1000).');

        $(".timeleft").each(function() {
          $(this).html(Math.round((startTime.getTime() - currentTime.getTime()) / 1000));
        });
      }
      </script>';
  }
	print '<table class="eventTable" width="100%">';
	
	$q = ' SELECT COUNT(*) AS totalcount FROM dvoo_events_signup WHERE event = '.$s.' ';
	$r = $db->query($q);
	$total = $r[0][0];
	$tomax = count($options) * 40;
	if ( $signup[$s]['any'] == 1 ) { $tomax -= 40; }
	
	$z = 0;
	print '<tr><th>'.t('TOWN').'</th>';
	foreach ( $options AS $option => $icon ) {
		$z++;
		$q = ' SELECT c.id, c.name FROM dvoo_citizens c INNER JOIN dvoo_events_signup s ON s.user = c.scode WHERE s.event = '.$s.' AND s.`option` = "'.$option.'" ORDER BY s.stamp ASC, c.name ASC ';
		if ( $signup[$s]['second'] != 1 && $signup[$s]['limit'] == 1 ) {
			$q .= ' LIMIT 40 ';
		}
		$r = $db->query($q);
		$signups[$option] = $r;
		if ( $signup[$s]['second'] == 1 ) {
			$q2 = ' SELECT c.id, c.name FROM dvoo_citizens c INNER JOIN dvoo_events_signup s ON s.user = c.scode WHERE s.event = '.($s + 1).' AND s.`option` = "'.$option.'" ORDER BY s.stamp ASC, c.name ASC '; //LIMIT 40 ';
			$r2 = $db->query($q2);
			$signups2[$option] = $r2;
		}
		if ( $signup[$s]['backup'] == 1 ) {
			$q3 = ' SELECT c.id, c.name FROM dvoo_citizens c INNER JOIN dvoo_events_signup s ON s.user = c.scode WHERE s.event = '.($s + 2).' AND s.`option` = "'.$option.'" ORDER BY s.stamp ASC, c.name ASC '; //LIMIT 40 ';
			$r3 = $db->query($q3);
			$signups3[$option] = $r3;
		}
		
		print '<td class="tc '.($z % 2 == 0 ? 'even' : 'odd').'" width="'.($cols == 2 ? '80%' : floor(100/$cols)).'%"><h3>'.$option.'</h3>';
    if ( (isset($signup[$s]['start']) && $signup[$s]['start'] <= time()) || !isset($signup[$s]['start']) ) {
      if ( (!is_array($r[0]) || count($r) < 40 || $signup[$s]['limit'] == 0) ) {
        print '<button class="signup" onclick="eventSignup('.$s.',\''.$k.'\',\''.$option.'\', 0);">'.$icon.' '.t('PRIMARY_CHOICE').'<br/>'.$option.'<br/><strong>'.t('FTW').'</strong></button>';
      }
      else {
        print '<p style="border:2px solid #c00;padding:3px;">'.t('TOWN_FULL').'</p>';
      }
    }
    else {
      print '<button disabled="disabled">'.t('REGISTRATION_TIMER',array('%d' => ($signup[$s]['start'] - time()))).'</button>';
    }
		if ( $signup[$s]['second'] == 1 ) {
			print '<button class="signup" onclick="eventSignup('.($s + 1).',\''.$k.'\',\''.$option.'\', 1);">'.t('SECONDARY_CHOICE').'<br/>'.$icon.' '.$option.'</strong></button></td>';
		}
		elseif( $signup[$s]['backup'] == 1 && ( (isset($signup[$s]['start']) && $signup[$s]['start'] <= time()) || !isset($signup[$s]['start']) ) ) {
			print '<button class="signup" onclick="eventSignup('.($s + 2).',\''.$k.'\',\''.$option.'\', 2);">'.t('BACKUP_CHOICE').'<br/>'.$icon.' '.$option.'</strong></button></td>';
		}
	}
	print '</tr>';
	
	
		
	$z = 0;
	print '<tr><th>'.t('STATUS').'<br/>'.$total.'/'.$tomax.'</th>';
	foreach ( $options AS $option => $icon ) {
		$z++;
		$r = $signups[$option];
		if ( !is_array($r[0]) ) {
			print '<td class="tc '.($z % 2 == 0 ? 'even' : 'odd').'">0 / 40<br/>';
		}
		else {
			print '<td class="tc '.($z % 2 == 0 ? 'even' : 'odd').'">'.count($r).' / 40<br/>';
		}
		if ( $signup[$s]['second'] == 1 ) {
			$r2 = $signups2[$option];
			if ( !is_array($r2[0]) ) {
				print '<em style="color:#333;">+ 0</em></td>';
			}
			else {
				print '<em style="color:#333;">+ '.count($r2).'</em></td>';
			}
		}
		if ( $signup[$s]['backup'] == 1 ) {
			$r3 = $signups3[$option];
			if ( !is_array($r3[0]) ) {
				print '<em style="color:#339;">+ 0</em></td>';
			}
			else {
				print '<em style="color:#339;">+ '.count($r3).'</em></td>';
			}
		}
	}
	print '</tr>';
	$z = 0;
	print '<tr><th>Participantes<br/><button class="signup" onclick="eventSignup('.$s.',\''.$k.'\',\'none\');">'.t('PRIMARY_BAILOUT').'</button></th>';
	foreach ( $options AS $option => $icon ) {
		$z++;
		$r = $signups[$option];
		if ( !is_array($r[0]) ) {
			print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').'"><em>'.t('NONE_YET').'</em></td>';
		}
		else {
			print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').'">';
			if ( $signup[$s]['pager'] || $cols == 2 ) {
				print '<div class="'.$option.'-part-pager '.$option.'-pp1">';
			}
			$i = 0;
			$j = 1;
			foreach ( $r AS $p ) {
				$i++;
				print $icon . ' ';
				if ( $signup[$s]['hide'] == 0 ) {
					print (strlen($p['name']) > 6 ? '<abbr title="'.$p['name'].'">'.substr($p['name'],0,6).'</abbr>..' : $p['name']);
				}
				elseif ( $signup[$s]['hide'] == 1 ) {
					if ( $p['name'] == $username || in_array($username,array('SinSniper','Workshop','Epoq','kiwiluke','BerceaMondialu','NyxOwl','Simius','Deblyn')) ) {
						print (strlen($p['name']) > 6 ? '<abbr title="'.$p['name'].'">'.substr($p['name'],0,6).'</abbr>..' : $p['name']);
					}
					else {
						print '<em>'.t('NAME_HIDDEN').'</em>';
					}
				}
				if ( $i % 40 == 0 && $i < count($r) && $signup[$s]['pager'] ) {
					$j++;
					print '</div><div class="'.$option.'-part-pager '.$option.'-pp'.$j.' hideme">';
				}
				elseif ( $i % 40 == 0 && $cols == 2 ) {
          $j++;
          print '</div><div class="'.$option.'-part-pager '.$option.'-pp'.$j.'">';
        }
        else {
					print '<br/>';
				}
			}
			print '</div>';
			if ( $signup[$s]['pager'] ) {
				print '<p style="text-align:center;color:#666;"><span style="cursor:pointer;" id="'.$option.'-pp-minus">◄</span> <span id="'.$option.'-cp">1</span> of '.$j.' <span style="cursor:pointer;" id="'.$option.'-pp-plus">►</span></p><script type="text/javascript">
				var '.$option.'pp = 1;
				var '.$option.'mp = '.$j.';
				$("#'.$option.'-pp-minus").click(function() {
					var '.$option.'op = '.$option.'pp;
					'.$option.'pp--;
					if ( '.$option.'pp < 1 ) {
						'.$option.'pp = '.$option.'mp;
					}
					$(".'.$option.'-pp"+'.$option.'op).fadeOut("200", function() { $(".'.$option.'-pp"+'.$option.'pp).fadeIn(); });
					$("#'.$option.'-cp").html('.$option.'pp);
				});
				$("#'.$option.'-pp-plus").click(function() {
					var '.$option.'op = '.$option.'pp;
					'.$option.'pp++;
					if ( '.$option.'pp > '.$option.'mp ) {
						'.$option.'pp = 1;
					}
					$(".'.$option.'-pp"+'.$option.'op).fadeOut("200", function() { $(".'.$option.'-pp"+'.$option.'pp).fadeIn(); });
					$("#'.$option.'-cp").html('.$option.'pp);
				});
				</script>';
			}
			print '</td>';
		}
	}
	print '</tr>';
	
	if (in_array($username,array('SinSniper','Epoq','znarf'))) {
		print '<tr><th>Participant IDs<br/><span style="font-size:0.75em;color:#66c;cursor:pointer;" onclick="$(\'.epids\').toggleClass(\'hideme\');">Click to toggle lists</span><br/><em class="epids hideme" style="font-size:0.75em;color:#c66;">Only visible for admins<br/>Ordered by time of registration (matching the list above)</em></th>';
		foreach ( $options AS $option => $icon ) {
			$z++;
			$r = $signups[$option];
			if ( !is_array($r[0]) ) {
				print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').'"><em>'.t('NONE_YET').'</em></td>';
			}
			else {
				print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').'"><p class="epids hideme">';
				$i = 0;
				$j = 1;
				foreach ( $r AS $p ) {
					$i++;
					print $p['id'];
					print '<br/>';
				}
				print '</p></td>';
			}
		}
		print '</tr>';
	}
	
	if ( $signup[$s]['second'] == 1 ) {
		print '<tr><th>Second choices<br/><button class="signup" onclick="eventSignup('.($s + 1).',\''.$k.'\',\'none\', 1);">'.t('SECONDARY_BAILOUT').'</button></th>';
		foreach ( $options AS $option => $icon ) {
			$z++;
			$r = $signups2[$option];
			if ( !is_array($r[0]) ) {
				print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').'"><em style="color:#333;">'.t('NONE_YET').'</em></td>';
			}
			else {
				print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').'">';
				foreach ( $r AS $p ) {
					print '<em style="color:#333;">'.$icon . ' ';
					if ( $signup[$s]['hide'] == 0 ) {
						print (strlen($p['name']) > 6 ? '<abbr title="'.$p['name'].'">'.substr($p['name'],0,6).'</abbr>..' : $p['name']);
					}
					elseif ( $signup[$s]['hide'] == 1 ) {
						if ( $p['name'] == $username || in_array($username,array('SinSniper','Workshop','Epoq','kiwiluke','BerceaMondialu','NyxOwl','Simius','Deblyn')) ) {
							print (strlen($p['name']) > 6 ? '<abbr title="'.$p['name'].'">'.substr($p['name'],0,6).'</abbr>..' : $p['name']);
						}
						else {
							print '<em>'.t('NAME_HIDDEN').'</em>';
						}
					}
					print '</em><br/>';
				}
				print '</td>';
			}
		}
		print '</tr>';
	}
	
	if ( $signup[$s]['backup'] == 1 ) {
		print '<tr><th>Reserva<br/><button class="signup" style="display:block;padding:3px;width:80px;margin:0 auto;" onclick="eventSignup('.($s + 2).',\''.$k.'\',\'none\', 2);">'.t('BACKUP_BAILOUT').'</button></th>';
		foreach ( $options AS $option => $icon ) {
			$z++;
			$r = $signups3[$option];
			if ( !is_array($r[0]) ) {
				print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').'"><em style="color:#333;">'.t('NONE_YET').'</em></td>';
			}
			else {
				print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').'">';
				foreach ( $r AS $p ) {
					print '<em style="color:#333;">'.$icon . ' ';
					if ( $signup[$s]['hide'] == 0 ) {
						print (strlen($p['name']) > 6 ? '<abbr title="'.$p['name'].'">'.substr($p['name'],0,6).'</abbr>..' : $p['name']);
					}
					elseif ( $signup[$s]['hide'] == 1 ) {
						if ( $p['name'] == $username || in_array($username,array('SinSniper','Workshop','Epoq')) ) {
							print (strlen($p['name']) > 6 ? '<abbr title="'.$p['name'].'">'.substr($p['name'],0,6).'</abbr>..' : $p['name']);
						}
						else {
							print '<em>'.t('NAME_HIDDEN').'</em>';
						}
					}
					print '</em><br/>';
				}
				print '</td>';
			}
		}
		print '</tr>';
	}

	print '</table>';
}
elseif ( $e > 1000 ) {
	// it's graphical comparison
	$e = $e - 1000;
	$xmls = $available = array();
	$towns = $events[$e]['towns'];
	$cols = count($towns) + 1;
	$event = $events[$e];

	foreach ($towns AS $t) {
		$days = array();
		$q = ' SELECT day FROM dvoo_xml WHERE tid = '.$t.' GROUP BY day ';
		$r = $db->query($q);
		if ( is_array($r) && count($r) > 0 ) {
			foreach ( $r AS $s ) {
				$days[] = $s['day'];
			}
		}
		rsort($days);
		$available[$t] = $days;
		
		$d = $days[0];
		foreach ( $days AS $d ) {
			if ( $xml_string = file_get_contents('xml/history/'.$t.'-'.$d.'.xml') ) {
				$xmls[$t][$d] = simplexml_load_string($xml_string);
			}
			else {
				$xmls[$t][$d] = false;
			}
		}
	}
	$g = $m = array();
	foreach ( $xmls AS $t => $days ) {
		$maxdays = max(count($days),$maxdays);
		foreach ( $days AS $d => $xml ) {
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
			
			$townnames[$t] = $town['name'];
			
			$g['Town points'][$t][$d] = ($d == 1 ? 0 : getPoints($t,$d,true));
      if ( !isset($m['Town points']) || $m['Town points'] < $g['Town points'][$t][$d] ) { $m['Town points'] = $g['Town points'][$t][$d]; }
      
      // citizen count
			$cc = 0;
			foreach ( $citizens->children() AS $ca ) {
				$cc++;
			}
			$g['Citizens'][$t][$d] = $cc;
			if ( !isset($m['Citizens']) || $m['Citizens'] < $cc ) { $m['Citizens'] = $cc; }
			
			// attack
			$g['Zombie attack'][$t][$d] = (int) $news['z'];
			if ( !isset($m['Zombie attack']) || $m['Zombie attack'] < $g['Zombie attack'][$t][$d] ) { $m['Zombie attack'] = $g['Zombie attack'][$t][$d]; }
			
			// town def
			$g['Town defense'][$t][$d] = (int) $news['def'];
			if ( !isset($m['Town defense']) || $m['Town defense'] < $g['Town defense'][$t][$d] ) { $m['Town defense'] = $g['Town defense'][$t][$d]; }
			
			// bank/well stuff
			$def = $food = $water = $well = $jerry = $drugs = $alcohol = $coffee = 0;
			$metal = $wood = array(0 => 0, 1 => 0, 2 => 0);
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
			}
			$g['Defensive items'][$t][$d] = $def;
			$g['Food supplies'][$t][$d] = $food;
			$g['Water in bank'][$t][$d] = $water;
			$g['Jerry cans'][$t][$d] = $jerry;
			$g['Coffee'][$t][$d] = $coffee;
			$g['Drugs'][$t][$d] = $drugs;
			$g['Alcohol'][$t][$d] = $alcohol;
			$g['Water in well'][$t][$d] = (int) $town['water'];
			if ( !isset($m['Defensive items']) || $m['Defensive items'] < $g['Defensive items'][$t][$d] ) { $m['Defensive items'] = $g['Defensive items'][$t][$d]; }
			if ( !isset($m['Food supplies']) || $m['Food supplies'] < $g['Food supplies'][$t][$d] ) { $m['Food supplies'] = $g['Food supplies'][$t][$d]; }
			if ( !isset($m['Water in bank']) || $m['Water in bank'] < $g['Water in bank'][$t][$d] ) { $m['Water in bank'] = $g['Water in bank'][$t][$d]; }
			if ( !isset($m['Jerry cans']) || $m['Jerry cans'] < $g['Jerry cans'][$t][$d] ) { $m['Jerry cans'] = $g['Jerry cans'][$t][$d]; }
			if ( !isset($m['Coffee']) || $m['Coffee'] < $g['Coffee'][$t][$d] ) { $m['Coffee'] = $g['Coffee'][$t][$d]; }
			if ( !isset($m['Drugs']) || $m['Drugs'] < $g['Drugs'][$t][$d] ) { $m['Drugs'] = $g['Drugs'][$t][$d]; }
			if ( !isset($m['Alcohol']) || $m['Alcohol'] < $g['Alcohol'][$t][$d] ) { $m['Alcohol'] = $g['Alcohol'][$t][$d]; }
			if ( !isset($m['Water in well']) || $m['Water in well'] < $g['Water in well'][$t][$d] ) { $m['Water in well'] = $g['Water in well'][$t][$d]; }
			
			// map size
			$ez = $dz = $uz = 0;
			foreach ( $map->children() AS $zdata ) {
				$ez++;
				if ( (int) $zdata['tag'] == 5 ) {
					$dz++;
				}
				else {
					$uz++;
				}
			}
			$g['Explored map'][$t][$d] = round($ez * 100 / ((int) $map['hei'] * (int) $map['wid']),1);
			$g['Explored zones'][$t][$d] = $ez;
			$g['Depleted zones'][$t][$d] = $dz;
			$g['Depletion ratio'][$t][$d] = round($dz / $ez * 100, 1);
			if ( !isset($m['Explored map']) || $m['Explored map'] < $g['Explored map'][$t][$d] ) { $m['Explored map'] = $g['Explored map'][$t][$d]; }
			if ( !isset($m['Explored zones']) || $m['Explored zones'] < $g['Explored zones'][$t][$d] ) { $m['Explored zones'] = $g['Explored zones'][$t][$d]; }
			if ( !isset($m['Depleted zones']) || $m['Depleted zones'] < $g['Depleted zones'][$t][$d] ) { $m['Depleted zones'] = $g['Depleted zones'][$t][$d]; }
			if ( !isset($m['Depletion ratio']) || $m['Depletion ratio'] < $g['Depletion ratio'][$t][$d] ) { $m['Depletion ratio'] = $g['Depletion ratio'][$t][$d]; }
		}
	}
	
	// define colors
	$color_template = array(
		'#00c',
		'#c0c',
		'#f00',
		'#0cc',
		'#963',
		'#cc0',
		'#90f',
		'#0f9',
		'#9f0',
		'#f09',
		'#09f',
		'#f90'
	);
	$i = 0;
	foreach ($towns AS $tid) {
		$colors[$tid] = $color_template[$i];
		$i++;
	}
	
	/*$colors = array(
		19089 => '#00c',
		19090 => '#c0c',
		19091 => '#c00',
		19092 => '#0cc',
		19093 => '#0c0',
		19094 => '#cc0',
	);*/
	// define labels
	$labels = array();
	for ( $i = 1; $i <= $maxdays; $i++ ) {
		$labels[] = ($maxdays <= 16 || $i == 1 ? 'day ' : '').$i;
	}
	// define max values
	$maxvalues = array(
    'Town points' => 500,
		'Citizens' => 40,
		'Explored map' => 100,
		'Explored zones' => 700,
		'Zombie attack' => 200,
		'Town defense' => 500,
		'Jerry cans' => 30,
		'Water in well' => 90,
		'Water in bank' => 90,
		'Food supplies' => 100,
		'Drugs' => 30,
		'Alcohol' => 30,
		'Coffee' => 10,
		'Defensive items' => 70,
		'Depleted zones' => 200,
		'Depletion ratio' => 100,
	);
	
	foreach ( $m AS $mt => $mv ) {
		$maxvalues[$mt] = 10 * ceil($mv/10);
	}
	
	$maxvalues['Explored map'] = 100;
	$maxvalues['Depletion ratio'] = 100;
	
	$postunits = array(
    'Town points' => '',
    'Citizens' => '',
		'Explored map' => '%',
		'Explored zones' => '',
		'Zombie attack' => '',
		'Town defense' => '',
		'Jerry cans' => '',
		'Water in well' => '',
		'Water in bank' => '',
		'Food supplies' => '',
		'Drugs' => '',
		'Alcohol' => '',
		'Coffee' => '',
		'Defensive items' => '',
		'Depleted zones' => '',
		'Depletion ratio' => '%',
	);
	
	// generate graphs
	$out = '<div title="close popup" id="spy-close" class="clickable" onclick="spyclose();"></div><div title="reload &amp; update content" id="spy-reload" class="clickable" onclick="eventSpy('.(1000 + $e).');"></div><div title="switch to table view" id="spy-switch2table" class="clickable" onclick="eventSpy('.$e.');"></div>'.$event['logo'] . '<h1>'.$event['title'].'</h1><p>'.$event['desc'].'</p>';
	$out .= '<div style="clear:both;margin-top: 10px;">';
	foreach ( $colors AS $t => $c ) {
		$out .= '<div class="graph-legend" style="background:'.$c.';"><p>'.$townnames[$t].'</p></div>';
	}
	$out .= '</div>';
	foreach ( $g AS $key => $data ) {
    if ( $key == 'Town points' ) {
      foreach ($data AS $t => $d) {
        ksort($data[$t]);
        #array_pop($data[$t]);
      }      
    }
		$id = strtolower(str_replace(' ','',$key));
		$out .= '<div id="ED_'.$id.'">';
		$out .= '<canvas id="CG_'.$id.'" width="858" height="250">[No canvas support]</canvas>';
		$out .= "<script>var data_".$id." = [";
		$i = $j = 0;
		foreach ( $data AS $t => $days ) {
			$out .= $i > 0 ? "," : "";
			$i++;
			$out .= "[";
			$j = 0;
			foreach ( $days AS $d => $ez ) {
				$out .= $j > 0 ? "," : "";
				$j++;
				$out .= "[".($d - .5).",".$ez.",'".$colors[$t]."','".$ez."']";
			}
			$out .= "]";
		}
		$out .= "]";
		
		$out .= "
	var scatter_".$id." = new RGraph.Scatter('CG_".$id."', data_".$id.");
	scatter_".$id.".Set('chart.title', '".t($key)."');
	scatter_".$id.".Set('chart.labels', ['".implode("','",$labels)."']);
	scatter_".$id.".Set('chart.xmax', ".$maxdays.");
	scatter_".$id.".Set('chart.ymax', ".$maxvalues[$key].");
	scatter_".$id.".Set('chart.scale.decimals', 0);
	scatter_".$id.".Set('chart.units.post', '".$postunits[$key]."');
	scatter_".$id.".Set('chart.gutter.left', 60);
	scatter_".$id.".Set('chart.line', true);
	scatter_".$id.".Set('chart.line.linewidth', 3);
	scatter_".$id.".Set('chart.line.curvy', true);
	scatter_".$id.".Set('chart.line.colors', ['#00c','#c0c','#f00','#0cc','#963','#cc0','#90f','#0f9','#9f0','#f09','#09f','#f90']);
	scatter_".$id.".Set('chart.line.shadow.color', 'rgba(0,0,0,.3)');
	scatter_".$id.".Set('chart.line.shadow.offsetx', 3);
	scatter_".$id.".Set('chart.line.shadow.offsety', 3);
	scatter_".$id.".Set('chart.line.shadow.blur', 5);
	scatter_".$id.".Set('chart.tickmarks', 'circle');
	scatter_".$id.".Set('chart.background.grid.autofit.numvlines', ".$maxdays.");
	scatter_".$id.".Set('chart.background.barcolor1', 'rgba(255,255,255,.5)');
	scatter_".$id.".Set('chart.background.barcolor2', 'rgba(247,247,247,.5)');
	scatter_".$id.".Set('chart.tooltips.effect', 'fade');
	scatter_".$id.".Set('chart.contextmenu', [['Get PNG', RGraph.showPNG],null,['Cancel', function () {}]]);
	scatter_".$id.".Draw();
	</script>";
		$out .= '</div>';
	}
	print $out;
}
else {
	// it's event comparison
	$xmls = $available = array();
	$towns = $events[$e]['towns'];
	$cols = count($towns) + 1;
	$event = $events[$e];

	foreach ($towns AS $t) {
		$days = array();
		$q = ' SELECT day FROM dvoo_xml WHERE tid = '.$t.' GROUP BY day ';
		$r = $db->query($q);
		if ( is_array($r) && count($r) > 0 ) {
			foreach ( $r AS $s ) {
				$days[] = $s['day'];
			}
		}
		rsort($days);
		$available[$t] = $days;
		
		$d = $days[0];
		if ( file_exists('xml/history/'.$t.'-'.$d.'.xml') && $xml_string = file_get_contents('xml/history/'.$t.'-'.$d.'.xml') ) {
			$xmls[$t] = simplexml_load_string($xml_string);
		}
		else {
			#$xmls[$t] = false;
		}
	}
	if (count($xmls) > 0) {
		foreach ( $xmls AS $xml ) {
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
			$residents = $campers = $basic = $hero = $scout = $scavenger = $tamer = $guardian = $hunter = $tech = $banned = $dead = 0;
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
				if ( $cit[$n]['out'] == 1 ) {
					$campers++;
				}
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
			$metal = $wood = array(0 => 0, 1 => 0, 2 => 0);
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
				if ( $bi_id == 59 ) {
					$wood[1] += $bi_count;
				}
				if ( $bi_id == 60 ) {
					$metal[1] += $bi_count;
				}
				if ( $bi_id == 159 ) {
					$wood[2] += $bi_count;
				}
				if ( $bi_id == 160 ) {
					$metal[2] += $bi_count;
				}			
				if ( $bi_id == 162 ) {
					$wood[0] += $bi_count;
				}
				if ( $bi_id == 161 ) {
					$metal[0] += $bi_count;
				}
				$well = $town['water'];
			}

			// buildings
			$townbuildings = array();
			$tempbuildings = array();
			foreach ( $buildings AS $b ) {
				$townbuildings[] = array('name' => (string) $b['name'], 'img' => (string) $b['img']);
				if ( (int) $b['temporary'] == 1 ) {
					$tempbuildings[] = array('name' => (string) $b['name'], 'img' => (string) $b['img']);
				}
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
			// <defense base="10" items="50" citizen_guardians="510" citizen_homes="141" upgrades="0" buildings="470" total="1541" itemsMul="1"/>
			$def_total = (int) $defense['total'];
			$def_guard = (int) $defense['citizen_guardians'];
			$def_items = (int) $defense['items'] * (int) $defense['itemsMul'];
			$def_build = (int) $defense['buildings'];
			$def_upgds = (int) $defense['upgrades'];
			$def_homes = (int) $defense['citizen_homes'];

			// estimations
			$est = array();
			if ( !is_null($estimations) ) {
				foreach ( $estimations AS $es ) {
					$eday = (int) $es['day'];
					$emin = (int) $es['min'];
					$emax = (int) $es['max'];
					$ebest = (int) $es['maxed'];
					$est[$eday] = array(
						'min' => $emin,
						'max' => $emax,
						'best' => $ebest,
					);
				}
			}
			
			// upgrades
			$town_upgrades = array();
			if ( !is_null($upgrades) ) {
				foreach ( $upgrades->children() AS $up ) {
					$town_upgrades[(string) $up['name']] = (int) $up['level']; 
				}
			}
			/* ### OUTPUT ### */
			$tid = $town['id'];
			// DAY
			$out_day[$tid] = (int) $game['days'];
			// DAY
			$out_upd[$tid] = explode(' ',(string) $game['datetime']);
			// TOWN
			$out_name[$tid] = $town['name'];
			// UPDATE

			// CITIZEN
			$out_citizen[$tid] = str_repeat('<img class="comp-job" src="'.t('GAMESERVER_SMILEY').'h_basic.gif" />',$basic);
			$out_citizen[$tid] .= str_repeat('<img class="comp-job" src="'.t('GAMESERVER_ICON').'r_jrangr.gif" />',$scout);
			$out_citizen[$tid] .= str_repeat('<img class="comp-job" src="'.t('GAMESERVER_ICON').'r_jcolle.gif" />',$scavenger);
			$out_citizen[$tid] .= str_repeat('<img class="comp-job" src="'.t('GAMESERVER_ICON').'r_jtamer.gif" />',$tamer);
			$out_citizen[$tid] .= str_repeat('<img class="comp-job" src="'.t('GAMESERVER_ICON').'r_jguard.gif" />',$guardian);
			$out_citizen[$tid] .= str_repeat('<img class="comp-job" src="'.t('GAMESERVER_ICON').'r_jermit.gif" />',$hunter);
			$out_citizen[$tid] .= str_repeat('<img class="comp-job" src="'.t('GAMESERVER_ICON').'r_jtech.gif" />',$tech);
			if ( $town['chaos'] == 1 ) {
				$out_citizen[$tid] .= '<br/><span style="font-weight:bold;color:#c00;">'.t('CHAOS_MODE').'</span>';
			}
			// CADAVER
			$out_cadaver[$tid] = str_repeat('<img class="comp-job" src="'.t('GAMESERVER_SMILEY').'h_death.gif" />',$dead);
			// PROVIANT
			$out_proviant[$tid] = '<img alt="'.t('WWATER').'" src="'.t('GAMESERVER_ITEM').'water.gif" />&nbsp;'.($water < 10 ? '&nbsp;' : '').$water.'&nbsp;|&nbsp;'
				.'<img title="'.t('WWELL').'" src="'.t('GAMESERVER_SMILEY').'h_well.gif" />&nbsp;'.($well < 10 ? '&nbsp;' : '').$well.'<br/>'
				.'<img title="'.t('STAT_JERRY_BANK').'" src="'.t('GAMESERVER_ITEM').'jerrycan.gif" />&nbsp;'.($jerry < 10 ? '&nbsp;' : '').$jerry.'<br/>'
				.'<img title="'.t('FOOD').'" src="'.t('GAMESERVER_ITEM').'dish_tasty.gif" />&nbsp;'.($food < 10 ? '&nbsp;' : '').$food.'<br/>'
				.'<img title="'.t('DRUGS').'" src="'.t('GAMESERVER_ITEM').'drug_hero.gif" />&nbsp;'.($drugs < 10 ? '&nbsp;' : '').$drugs.'<br/>'
				.'<img title="'.t('ALCOHOL').'" src="'.t('GAMESERVER_ITEM').'vodka.gif" />&nbsp;'.($alcohol < 10 ? '&nbsp;' : '').$alcohol.'<br/>'
				.'<img title="'.t('COFFEE').'" src="'.t('GAMESERVER_ITEM').'coffee.gif" />&nbsp;'.($coffee < 10 ? '&nbsp;' : '').$coffee.'<br/>'
				.'<img src="'.t('GAMESERVER_ITEM').'wood_bad.gif" />&nbsp;'.($wood[0] < 10 ? '&nbsp;' : '').$wood[0].'&nbsp;|&nbsp;<img src="'.t('GAMESERVER_ITEM').'metal_bad.gif" />&nbsp;'.($metal[0] < 10 ? '&nbsp;' : '').$metal[0].'<br/>'
				.'<img src="'.t('GAMESERVER_ITEM').'wood2.gif" />&nbsp;'.($wood[1] < 10 ? '&nbsp;' : '').$wood[1].'&nbsp;|&nbsp;<img src="'.t('GAMESERVER_ITEM').'metal.gif" />&nbsp;'.($metal[1] < 10 ? '&nbsp;' : '').$metal[1].'<br/>'
				.'<img src="'.t('GAMESERVER_ITEM').'wood_beam.gif" />&nbsp;'.($wood[2] < 10 ? '&nbsp;' : '').$wood[2].'&nbsp;|&nbsp;<img src="'.t('GAMESERVER_ITEM').'metal_beam.gif" />&nbsp;'.($metal[2] < 10 ? '&nbsp;' : '').$metal[2];
				
			// TOWN INFO
			$out_town[$tid] = $town['devast'] == 1 ? '<span style="font-weight:bold;color:#c00;">'.t('TOWN_DEVASTATED').'</span>' :((int) $city['door'] == 1 ? '<span style="color:#c00;">'.t('GATES_OPEN').'</span>' : '<span style="color:#0c0;">'.t('GATES_CLOSED').'</span>');
			$out_town[$tid] .= '<br/>('. $campers . '&nbsp;'.t('CITIZENS_OUTSIDE').')<br/>';
			$out_town[$tid] .= '<br/>'.count($townbuildings) . '&nbsp;'.t('BUILDINGS').'<br/>';
			$out_town[$tid] .= '('.count($tempbuildings) . '&nbsp;'.t('TEMPORARY').')<br/><br/>';
			foreach ($town_upgrades AS $name => $level) {
				$out_town[$tid] .= $name . ' ('.$level.')<br/>';
			}
			
			// DEFENSE
			//$out_def[$tid] = $def.'&nbsp;<img title="'.t('DEFENSIVE_OBJECTS').'" src="'.t('GAMESERVER_ITEM').'plate.gif" /><br/>';
			$out_def[$tid] = '<p class="deflist" style="text-align:right;margin:0 20% 0 0;">10&nbsp;<img title="'.t('TOWN_DEF_BASE').'" src="'.t('GAMESERVER_ICON').'small_door_closed.gif" /><br/>';
			$out_def[$tid] .= $def_upgds.'&nbsp;<img title="'.t('TOWN_DEF_UPGRADES').'" src="'.t('GAMESERVER_ICON').'small_city_up.gif" /><br/>';
			$out_def[$tid] .= $def_build.'&nbsp;<img title="'.t('TOWN_DEF_BUILDINGS').'" src="'.t('GAMESERVER_ITEM').'metal_beam.gif" /><br/>';
			$out_def[$tid] .= $def_items.'&nbsp;<img title="'.t('TOWN_DEF_ITEMS').'" src="'.t('GAMESERVER_ITEM').'plate.gif" /><br/>';
			$out_def[$tid] .= $def_homes.'&nbsp;<img height="16" title="'.t('TOWN_DEF_HOMES').'" src="'.t('GAMESERVER_SMILEY').'h_home.gif" /><br/>';
			$out_def[$tid] .= $def_guard.'&nbsp;<img title="'.t('TOWN_DEF_GUARDS').'" src="'.t('GAMESERVER_SMILEY').'h_guard.gif" /><br/><br/>';
			$out_def[$tid] .= $def_total.'&nbsp;<img title="'.t('TOWN_DEFENSE').'" src="'.t('GAMESERVER_ICON').'small_def.gif" /><br/>';
			$out_def[$tid] .= (isset($est[$d]) ? $est[$d]['min'].' - '.$est[$d]['max'] : '???').'<img title="'.t('ATTACK_TODAY').'" src="'.t('GAMESERVER_SMILEY').'h_zombie.gif" />';
			$out_def[$tid] .= (isset($est[($d + 1)]) ? '<br/>' . $est[($d + 1)]['min'].' - '.$est[($d + 1)]['max'] . '<img title="'.t('ATTACK_TOMORROW').'" src="'.t('GAMESERVER_SMILEY').'h_zombie.gif" />' : '').'</p>';
		}
	}
	 
	print '<div title="close popup" id="spy-close" class="clickable" onclick="spyclose();"></div><div title="reload &amp; update content" id="spy-reload" class="clickable" onclick="eventSpy('.$e.');"></div><div title="switch to graph view" id="spy-switch2graph" class="clickable" onclick="eventSpy('.(1000 + $e).');"></div>';
	print $event['logo'] . '<h1>'.$event['title'].'</h1><p>'.$event['desc'].'</p>';
	print '<table class="eventTable">';

	$z = 0;
	print '<tr><th>'.t('TOWN').'</th>';
	ksort($out_name);
	foreach ( $out_name AS $tid => $name ) {
		$z++;
		print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').'" width="'.floor(100/$cols).'%"><h3>'.$name.'</h3></td>';
	}
	print '</tr>';
	$z = 0;
	print '<tr><th></th>';
	foreach ( $out_name AS $tid => $name ) {
		$z++;
		print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').'" width="'.floor(100/$cols).'%"><span class="gameid">game id: '.$tid.'</span><p onclick="spyontown('.$tid.','.$out_day[$tid].');" class="eventSpy">'.t('TOWN_SPY').'</p></td>';
	}
	print '</tr>';
	$z = 0;
	print '<tr><th>'.t('ESTIMATED_POINTS').'</th>';
	foreach ( $out_name AS $tid => $name ) {
		$z++;
		print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').' tc" width="'.floor(100/$cols).'%">'.getPoints($tid,$out_day[$tid]).'</td>';
	}
	print '</tr>';

	$z = 0;
	print '<tr><th>'.t('DAY').'</th>';
	ksort($out_day);
	foreach ( $out_day AS $tid => $day ) {
		$z++;
		print '<td class="tc '.($z % 2 == 0 ? 'even' : 'odd').'">'.t('DAY').' '.$day.'<br/><span class="gameid">'.t('LAST_UPDATE').':<br/>'.$out_upd[$tid][0].'<br/>'.$out_upd[$tid][1].'</span></td>';
	}
	print '</tr>';

	$z = 0;
	print '<tr><th>'.t('CITIZENS').'</th>';
	ksort($out_citizen);
	foreach ( $out_citizen AS $tid => $cit ) {
		$z++;
		print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').'">'.$cit.'</td>';
	}
	print '</tr>';

	$z = 0;
	print '<tr><th>'.t('DEAD_MEAT').'</th>';
	ksort($out_cadaver);
	foreach ( $out_cadaver AS $tid => $cad ) {
		$z++;
		print '<td class="'.($z % 2 == 0 ? 'even' : 'odd').'">'.$cad.'</td>';
	}
	print '</tr>';

	$z = 0;
	print '<tr><th>'.t('DEFENSE').'</th>';
	ksort($out_def);
	foreach ( $out_def AS $tid => $di ) {
		$z++;
		print '<td class="tc '.($z % 2 == 0 ? 'even' : 'odd').'">'.$di.'</td>';
	}
	print '</tr>';

	$z = 0;
	print '<tr><th>'.t('SUPPLIES').'</th>';
	ksort($out_proviant);
	foreach ( $out_proviant AS $tid => $pro ) {
		$z++;
		print '<td class="tc '.($z % 2 == 0 ? 'even' : 'odd').'">'.$pro.'</td>';
	}
	print '</tr>';

	$z = 0;
	print '<tr><th>'.t('STATISTICS').'</th>';
	ksort($out_town);
	foreach ( $out_town AS $tid => $tii ) {
		$z++;
		print '<td class="tc '.($z % 2 == 0 ? 'even' : 'odd').'">'.$tii.'</td>';
	}
	print '</tr>';

	print '</table>';
}

function getPoints($t,$d,$raw=false) {
	global $db;
	$file = 'xml/history/'.$t.'-'.$d.'.xml';
	if ( file_exists($file) ) {
		$xml_string = file_get_contents($file);
		$xml = simplexml_load_string($xml_string);
	}
	else {
		return $raw ? 0 : '<span style="color:#c00;font-weight:bold;">x</span>';
	}
	if ($xml) {
		$citizens = $xml->data->citizens;
		$cadavers = $xml->data->cadavers;
		$game = $xml->headers->game;
		
		$xmldt = (int) date('Yz',strtotime((string) $game['datetime']));
		$today = (int) date('Yz',time());
		
		$xmldtH = (int) date('H',strtotime((string) $game['datetime']));
		$todayH = (int) date('H',time());
		
		
		// cit
		$residents = $deadguys = $points = 0;
		$cit = array();
		foreach ( $citizens->children() AS $ca ) {
			$residents++;	
			$points += ($d - 1);
			if ( ($today > $xmldt || ($today == $xmldt && $xmldtH < 23 && $todayH == 23)) && !$raw ) {
				$points++;
			}
		}
		
		// get devast day for better calculation
		$r = $db->query('SELECT devast_on FROM dvoo_towns WHERE id = '.$t);
		$devast = $r[0][0];

		// cad
		$tcadavers = array();
		foreach ( $cadavers->children() AS $ca ) {	
			$deadguys++;
			$points += ((int) $ca['day'] - 1);
			if ( (int) $ca['dtype'] == 6 || ((int) $ca['dtype'] == 5 && (int) $ca['day'] >= $devast && $devast > 0)) { $points++; }
		}
		
		return $raw ? $points : '<span title="'.t('RANKINGPOINTS', array('%r' => $residents, '%d' => $deadguys)).'">'.$points.'</span>'.( $today > $xmldt + 1 ? '<br/><span style="color:#c00;font-weight:bold;">'.t('TOWN_DEAD').'</span>' : ( $today > $xmldt || ($today == $xmldt && $xmldtH < 23 && $todayH == 23) ? '<br/><span style="color:#c60;font-weight:bold;">'.t('TOWN_DEAD_MAYBE').'</span>' : ''));
	}
	else {
		return $raw ? 0 : '<span style="color:#c00;font-weight:bold;">x</span>';
	}
}