<?php
		include_once 'system.php';
		
		$db = new Database();
    
		// get day number
		$r = (string) $_POST['r'];
		$u = (string) $_POST['u'];
		print '<h4 style="clear:both;">'.$r.' - Top 100</h4>';
		print '<table class="reward25">';
		$i = 0;
		$o25 = 0;
		$res = $db->query(' SELECT c.name AS citizen, v.count AS anzahl, c.id AS uid FROM dvoo_citizens c INNER JOIN dvoo_citizen_rewards v ON v.uid = c.id WHERE v.reward = "'.$r.'" ORDER BY v.count DESC LIMIT 100 ');
		foreach ( $res AS $s ) {
			if ( $i % 25 == 0 && $i > 0 ) {
				print '</table><table class="reward25">';
			}
			$i++;
			$ownstyle = '';
			if ( $s['uid'] == $u ) {
				$ownstyle = ' style="color:#c00;"';
				$o25 = 1;
			}
			if ($i < 100 || ($i == 100 && $o25 == 1)) {
				print '<tr'.$ownstyle.' class="'.($i % 2 == 0 ? 'even' : 'odd').($s['uid'] == $u ? ' own' : '').'"><td>'.($s['anzahl'] < $previous_count || !isset($previous_count) ? $i.'.' : '').'</td><td>'.$s['citizen'].'</td><td>'.$s['anzahl'].'</td></tr>';
				$previous_count = (int) $s['anzahl'];
			}
		}
		
		if ( $o25 == 0 ) {
			$r1 = $db->query(' SELECT r.count, c.name FROM dvoo_citizen_rewards r INNER JOIN dvoo_citizens c ON c.id = r.uid WHERE reward = "'.$r.'" AND r.uid = '.$u );
			if ( is_array($r1[0]) ) {
				$r2 = $db->query(' SELECT COUNT(*) AS place FROM dvoo_citizen_rewards WHERE reward = "'.$r.'" AND count > '.$r1[0]['count'].' LIMIT 1 ');
				if ( is_array($r2[0]) ) {
					print '<tr style="color:#c00;" class="even own"><td>'.($r2[0]['place'] + 1).'.</td><td>'.$r1[0]['name'].'</td><td>'.$r1[0]['count'].'</td></tr>';
				}
			}
		}
		print '</table><br style="clear:both;" />';