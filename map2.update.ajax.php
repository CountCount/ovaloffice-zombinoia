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

#$session = $db->query(' SELECT xml FROM dvoo_rawdata WHERE id = '.$u.' ORDER BY time DESC LIMIT 1 ');
#$data = unserialize($session[0][0]);

$r = $db->query('SELECT name FROM dvoo_citizens WHERE id = '.$u);
$uname = $r[0][0];


switch ( $a ) {
	// load current status
	case 'load':
	{
		$q = ' SELECT items FROM dvoo_zones_visit WHERE tid = '.$t.' AND x = '.$x.' AND y = '.$y.' ORDER BY `on` DESC LIMIT 1';
		$r = $db->query($q);
		if ( isset($r[0]) && count($r[0]) > 0 ) {
			$items = unserialize($r[0][0]);
		}
		else {
			$items = array();
		}
		print styleItems($items);	
		break;
	}
	
	// add an item
	case 'additem':
	{
		$n = (string) $_POST['item-name'];
		$z = (int) $_POST['czu_z'];
		
		$q = ' SELECT items FROM dvoo_zones_visit WHERE tid = '.$t.' AND day = '.$d.' AND x = '.$x.' AND y = '.$y.' ORDER BY `on` DESC LIMIT 1 ';
		$r = $db->query($q);
		if ( isset($r[0][0]) ) {
			$items2 = unserialize($r[0][0]);
		}
		else {
			$items2 = array();
		}
		
		$q = ' SELECT iid FROM dvoo_items WHERE iname = "'.$n.'" ';
		$r = $db->query($q);
		if ( isset($r[0][0]) ) {
			$pro = 0;
			if ( count($items2) > 0 ) {
				foreach ( $items2 AS $i => $item ) {
					if ( $item['id'] == (int) $r[0][0] ) {
						$items2[$i]['count']++;
						$pro = 1;
					}
				}
			}
			if ( $pro == 0 ) {
				$items2[] = array(
					'id' => (int) $r[0][0],
					'count' => 1,
					'broken' => 0,
				);
			}
		}
		
		$q = 'INSERT INTO dvoo_zones_visit VALUES ('.$t.', '.$d.', '.$x.', '.$y.', 0, 0, '.$z.', "'.mysql_escape_string(serialize($items2)).'", '.time().', "'.mysql_real_escape_string($uname).'") ON DUPLICATE KEY UPDATE z = '.$z.', items = "'.mysql_escape_string(serialize($items2)).'", `on` = '.time().', `by` = "'.mysql_real_escape_string($uname.' (via OO)').'" ';
		$db->iquery($q);
		
		print styleItems($items2);
		break;
	}
	case 'changeitem':
	{
		
		$i = (int)  $_POST['i'];
		$c = (int)  $_POST['c'];
		
		$q = ' SELECT name FROM dvoo_citizens WHERE id = ' . $u;
		$r = $db->query($q);
		$uname = $r[0][0];
		
		$q = ' SELECT items FROM dvoo_zones_visit WHERE tid = '.$t.' AND day = '.$d.' AND x = '.$x.' AND y = '.$y.' ORDER BY `on` DESC LIMIT 1 ';
		$r = $db->query($q);
		if ( isset($r[0][0]) ) {
			$items = unserialize($r[0][0]);
		}
		else {
			$items = array();
		}
		if ( count($items) > 0 ) {
			foreach ( $items AS $n => $item ) {
				if ( $item['id'] == $i ) {
					if ( $c > 0 ) {
						$items[$n]['count'] = $c;
					}
					elseif ( $c == 0 ) {
						unset($items[$n]);
					}
					elseif ( $c == -1 ) {
						$items[$n]['broken'] = 1 - $items[$n]['broken'];
					}
				}
			}
		}
		
		$q = 'UPDATE dvoo_zones_visit SET items = "'.mysql_escape_string(serialize($items)).'" WHERE tid = '.$t.' AND day = '.$d.' AND x = '.$x.' AND y = '.$y.' ';
		$db->iquery($q);
		
		print styleItems($items);
		break;
	}
	
	// load current status
	case 'storm':
	{
		#if ($u == 3137) { print var_export($_POST,true); }
		$s = (int) $_POST['musd'];
		
		$q = ' SELECT * FROM dvoo_storm WHERE tid = '.$t.' AND day = '.$d.' AND uid = '.$u.' LIMIT 1 ';
		$r = $db->query($q);
		if ( isset($r[0]) && count($r[0]) > 0 ) {
			$m = $r[0]['dir'];
			switch ( $m ) {
				case 1: $mt = t('Nx'); break;
				case 2: $mt = t('NEx'); break;
				case 3: $mt = t('Ex'); break;
				case 4: $mt = t('SEx'); break;
				case 5: $mt = t('Sx'); break;
				case 6: $mt = t('SWx'); break;
				case 7: $mt = t('Wx'); break;
				case 8: $mt = t('NWx'); break;
			}
			print 'Today, you already have registered a storm ('.$mt.').';
		}
		else {
			$q = ' INSERT INTO dvoo_storm VALUES ('.$t.','.$d.','.$s.','.$u.') ';
			$db->iquery($q);
			print 'Your storm observation has been recorded.';
		}
		break;
	}
	
	
}

function styleItems($items) {
		global $db;
		$out = '';
		if ( is_array($items) && count($items) > 0 ) {		
			foreach ( $items AS $item ) {
				$bis = $db->query(' SELECT i.iid AS id, i.iimg AS img, i.iname AS name, i.icat AS cat FROM dvoo_items i WHERE i.iid = '.$item['id'].' ');
				$bh = $bis[0];
				$out .= '<div class="item '.($item['broken'] ? ' broken' : '').'"><span class="break" onclick="javascript:changeItem(-1, '.$item['id'].');"></span>&nbsp;<img src="'.t('GAME_ICON_SERVER').$bh['img'].'.gif" title="'.$bh['name'].'" />&nbsp;<span class="minus" onclick="javascript:changeItem('.($item['count'] - 1).', '.$item['id'].');"></span><span class="count">'.$item['count'].'</span><span class="plus" onclick="javascript:changeItem('.($item['count'] + 1).', '.$item['id'].');"></span></div>';
			}
		}
		return $out.'<br style="clear:left;" />';
}
