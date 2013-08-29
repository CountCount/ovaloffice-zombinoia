<?php 

include_once 'system.php';
$db = new Database();

$m = (string) $_REQUEST['mode'];

switch ( $m ) {
	case 'citylist':
	{
		header('Content-Type: text/xml');
		$x = (int) $_REQUEST['timestamp'];
		$g = (int) $_REQUEST['gameid'];
		$xmlstr = <<<XML
<?xml version="1.0"?>
<api xmlns:dc="http://purl.org/dc/elements/1.1" xmlns:content="http://purl.org/rss/1.0/modules/content/">
 <cities></cities>
</api>
XML;

		$xml = new SimpleXMLElement($xmlstr);
		$q = ' SELECT * FROM dvoo_towns WHERE id > 0 '.($x > 0 ? ' AND stamp >= ' . $x : '').' '.($g > 0 ? ' AND id >= ' . $g : '').' ORDER BY id ASC ';
		$r = $db->query($q);
		foreach ( $r AS $t ) {
			$city = $xml->cities->addChild('city');
			$city->addAttribute('gameid', $t['id']);
			$city->addAttribute('cityname', $t['name']);
			$city->addAttribute('day', $t['day']);
			$city->addAttribute('time', $t['stamp']);
			
			$s = ' SELECT * FROM dvoo_stat_zombies WHERE tid = ' . $t['id'] . ' ORDER BY day ASC ';
			$u = $db->query($s);
			foreach ( $u AS $v ) {
				$save = $city->addChild('save');
				$save->addAttribute('day', $v['day']);
				$save->addAttribute('zombies', $v['z']);
				$save->addAttribute('defense', $v['v']);
			}
		}
		
		 
		echo $xml->asXML();
		break;
	}
	
	case 'zoneinfo':
	case 'nitelight':
	{
		header('Content-Type: text/xml');
		$t = (int) $_REQUEST['town'];
		$x = (int) $_REQUEST['x'];
		$y = (int) $_REQUEST['y'];
		
		if ( !isset($t) || $t == 0 || !isset($x) || $x == 0 || !isset($y) || $y == 0 ) {
			$xmlstr = <<<XML
<?xml version="1.0" encoding="iso-8859-1"?>
<api xmlns:dc="http://purl.org/dc/elements/1.1" xmlns:content="http://purl.org/rss/1.0/modules/content/">
 <error code="1" msg="Invalid parameters"></error>
</api>
XML;

			$xml = new SimpleXMLElement($xmlstr);
		
		}
		else {
		
			$xmlstr = <<<XML
<?xml version="1.0" ?>
<api xmlns:dc="http://purl.org/dc/elements/1.1" xmlns:content="http://purl.org/rss/1.0/modules/content/">
 <zone></zone>
</api>
XML;

			$xml = new SimpleXMLElement($xmlstr);
			$zone = $xml->zone;
			$zone->addAttribute('x',$x);
			$zone->addAttribute('y',$y);
			
			$z = array(
				'zones' => array(),
				'visit' => array(),
				'building' => array(),
				'day' => 0,
				'updated' => 0,
				'zombies' => 0,
				'replen' => 1,
				'items' => array(),
			);
			
			$q = 'SELECT * FROM dvoo_zones_zones WHERE tid = '.$t.' AND x = '.$x.' AND y = '.$y.' ORDER BY stamp DESC LIMIT 1';
			$r = $db->query($q);
			if ( is_array($r[0]) ) {
				$z['zones'] = $r[0];
				$z['day'] = ($z['zones']['day'] > $z['day'] ? $z['zones']['day'] : $z['day']);
				$z['updated'] = ($z['zones']['stamp'] > $z['updated'] ? $z['zones']['stamp'] : $z['updated']);
				$z['zombies'] = (!is_null($z['zones']['z']) && $z['zones']['z'] > $z['zombies'] ? $z['zones']['z'] : $z['zombies']);
			}
			
			$q = 'SELECT * FROM dvoo_zones_visit WHERE tid = '.$t.' AND x = '.$x.' AND y = '.$y.' ORDER BY `on` DESC LIMIT 1';
			$r = $db->query($q);
			if ( is_array($r[0]) ) {
				$z['visit'] = $r[0];
				$z['day'] = ($z['visit']['day'] > $z['day'] ? $z['visit']['day'] : $z['day']);
				$z['updated'] = ($z['visit']['on'] > $z['updated'] ? $z['visit']['on'] : $z['updated']);
				$z['zombies'] = (is_null($z['zones']['z']) || $z['visit']['on'] > $z['zones']['stamp'] ? $z['visit']['z'] : $z['zombies']);
				$z['replen'] = 1 - $z['visit']['dried'];
				$z['items'] = unserialize($z['visit']['items']);
			}
			
			$q = 'SELECT * FROM dvoo_zones_buildings WHERE tid = '.$t.' AND x = '.$x.' AND y = '.$y.' ORDER BY stamp DESC LIMIT 1';
			$r = $db->query($q);
			if ( is_array($r[0]) ) {
				$z['building'] = $r[0];
				
			}
			
			$zone->addAttribute('day',$z['day']);
			$zone->addAttribute('updated',$z['updated']);
			$zone->addAttribute('zombies',$z['zombies']);
			$zone->addAttribute('replen',$z['replen']);
			
			if ( count($z['items']) > 0 ) {
				foreach ( $z['items'] AS $item ) {
					$in = $xml->zone->addChild('item');
					$in->addAttribute('id', $item['id']);
					$in->addAttribute('count', $item['count']);
					$in->addAttribute('broken', $item['broken']);
					
					$iq = ' SELECT * FROM dvoo_items WHERE iid = ' . $item['id'] . ' ';
					$ir = $db->query($iq);
					$in->addAttribute('name', $ir[0]['iname']);
					$in->addAttribute('image', $ir[0]['iimg']);
					$in->addAttribute('category', $ir[0]['icat']);
				}
			}
		
		} 
		echo $xml->asXML();
		break;
	}
	
	case 'checkmail':
	{
		$key = (string) $_REQUEST['apikey'];
		$uid = (int) $_REQUEST['userid'];
		if ( validateKey($key) && $uid > 0 ) {
			$q = ' SELECT scode FROM dvoo_citizens WHERE id = '.$uid.' ';
			$r = $db->query($q);
			if ( $r[0][0] == '' ) {
				$response = array(
					'error' => 3,
					'error_msg' => 'User has never logged into Oval Office.',
				);
			}
			else {
				$q = ' SELECT COUNT(*) FROM dvoo_fl_mailbox WHERE receiver = '.$uid.' AND `read` IS NULL ';
				$r = $db->query($q);
				$newmail = $r[0][0];
				$q = ' SELECT COUNT(*) FROM dvoo_fl_invite WHERE b = '.$uid.' ';
				$r = $db->query($q);
				$newinv = $r[0][0];
				
				$tempkey = md5(microtime());
				$time = time();
				$valid = $time + 3600;
				
				$q = ' INSERT INTO dvoo_tempkeys VALUES ('.$uid.', "'.$tempkey.'", '.$valid.') ON DUPLICATE KEY UPDATE tempkey = "'.$tempkey.'", stamp = '.$valid.' ';
				$db->iquery($q);
				
				$oourl = 'http://die2nite.org/ovaloffice/?user='.$uid.'&tkey='.$tempkey.'&openmail=1';
				
				$response = array(
					'messages' => $newmail,
					'invitations' => $newinv,
					'userid' => $uid,
					'tempkey' => $tempkey,
					'valid' => $valid,
					'oourl' => $oourl,
				);
			}
		}
		elseif ( !validateKey($key) ) {
			$response = array(
				'error' => 1,
				'error_msg' => 'Invalid api key.',
			);
		}
		elseif ( $uid == 0 ) {
			$response = array(
				'error' => 2,
				'error_msg' => 'Missing user id.',
			);
		}
		echo json_encode($response);
		break;
	}
}

function validateKey($key = null) {
		$keys = array(
			'duskdawn' => '6dfa-a76e-5156',
		);
		if ( !is_null($key) && in_array($key, $keys) ) {
			return true;
		}
		return false;
	}
