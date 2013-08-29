<?php
		include_once 'system.php';
		
		$db = new Database();
    
		// get day number
		$t = (string) $_POST['t'];
		$u = (string) $_POST['u'];
		print '<h4>'.$t.' - Top 25</h4>';
		$i = 0;
		$o25 = 0;
		$tinfo = $db->query(' SELECT * FROM dvoo_titles WHERE name = "'.$t.'" ');
		$title = $tinfo[0];
		print '<h5>'.t('OBTAINABLE').' '.$title['min'].'x '.$title['reward'].'</h5>';
		
		$res = $db->query(' SELECT c.name AS citizen, v.count AS anzahl, c.id AS uid FROM dvoo_citizens c INNER JOIN dvoo_citizen_rewards v ON v.uid = c.id WHERE v.reward = "'.$title['reward'].'" AND v.count >= '.$title['min'].($title['max'] > 0 ? ' AND v.count <= '.$title['max'] : ' ').' ORDER BY v.count DESC LIMIT 25 ');
		print '<table class="reward25">';
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
			if ($i < 25 || ($i == 25 && $o25 == 1)) {
				print '<tr'.$ownstyle.' class="'.($i % 2 == 0 ? 'even' : 'odd').($s['uid'] == $u ? ' own' : '').'"><td>'.($s['anzahl'] < $previous_count || !isset($previous_count) ? $i.'.' : '').'</td><td>'.$s['citizen'].'</td><td>'.$s['anzahl'].'</td></tr>';
				$previous_count = (int) $s['anzahl'];
			}
		}
		
		if ( $o25 == 0 ) {
			$r1 = $db->query(' SELECT r.count, c.name FROM dvoo_citizen_rewards r INNER JOIN dvoo_citizens c ON c.id = r.uid WHERE reward = "'.$title['reward'].'" AND r.uid = '.$u );
			if ( is_array($r1[0]) ) {
				print '<tr style="color:#c00;" class="even own"><td></td><td>'.$r1[0]['name'].'</td><td>'.$r1[0]['count'].'</td></tr>';
			}
		}
		print '</table><br style="clear:both;" />';