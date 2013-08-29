<?php
header('HTTP/1.1 400 Bad Requesr'); 
die('Error: The Oval Office map is no longer available. Please update your plugin.');
#include_once 'system.php';
// db layer
include_once 'dal2.php';
$db = new Database();

// functions
include_once 'lang.inc.php';
include_once 'functions.php';

$db = new Database();


if ( isset($_POST['key']) ) {
	$k = htmlspecialchars(strip_tags($_POST['key']));
	$status = simplexml_load_file('http://www.die2nite.com/xml/status');

	// secure site key (d2n.sindevel.com)
	$siteKey = '1047d28543db3ad80932ba020c9fe9bb';
	// ingame Link
	$xml = simplexml_load_file('http://www.die2nite.com/xml?k=' .$k . ';sk=' . $siteKey);
}
elseif ( isset($_REQUEST['xml']) ) {
		$xml = simplexml_load_string($_REQUEST['xml']);
}
else {
	$xml = file_get_contents("php://input");
}
if ( !$xml ) {
//todo: error
header('HTTP/1.1 400 Bad Request'); 
die('Error: XML could not be retrieved.');
}

$error = $xml->error;
$error_code = (string) $error['code'];
if ( $error_code == 'horde_attacking' ) {
	header('HTTP/1.1 503 Service Unavailable'); 
	die('Error: Town is currently under attack by zombies.');
}
elseif ( $error_code == 'not_in_game' ) {
	header('HTTP/1.1 412 Precondition Failed'); 
	die('Error: You have not joined a town yet.');
}
elseif ( $error_code != '' ) {
	header('HTTP/1.1 404 Not Found'); 
	die('Error: An unknown error occurred.');
}

// get main objects p=1
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
$owner = $xml->headers->owner->citizen;
$myzone = $xml->headers->owner->myZone;

// current data array
$data = array();

// system
$data['system']['icon_url'] = (string) $headers['iconurl'];
$data['system']['avatar_url'] = (string) $headers['avatarurl'];

// current day
$data['current_day'] = (int) $game['days'];

// map size
$data['map']['height'] = (int) $map['hei'];
$data['map']['width'] = (int) $map['wid'];

// town data
$data['town']['id'] = (int) $game['id'];
$data['town']['name'] = (string) $city['city'];
$data['town']['x'] = (int) $city['x'];
$data['town']['y'] = (int) $city['y'];
$data['town']['door'] = (int) $city['door'];
$data['town']['water'] = (int) $city['water'];
$data['town']['chaos'] = (int) $city['chaos'];
$data['town']['devast'] = (int) $city['devast'];
$data['town']['hard'] = (int) $city['hard'];

// citizens
if ( isset($citizens) ) {
	foreach ( $citizens->children() AS $ca ) {
		$db->iquery(' INSERT INTO dvoo_citizens VALUES ('.(int) $ca['id'].', "'.(string) $ca['name'].'", "", "", "'.(string) $ca['avatar'].'") ON DUPLICATE KEY UPDATE avatar = "'.(string) $ca['avatar'].'" ');
		$db->iquery('INSERT INTO dvoo_town_citizens VALUES ('.$data['town']['id'].', '.(int) $ca['id'].','.(int) $ca['ban'].','.(int) $ca['hero'].',"'.(string) $ca['job'].'",'.(int) $ca['dead'].','.(int) $ca['out'].','.(is_null($ca['x']) ? $data['town']['x'] : (int) $ca['x']).','.(is_null($ca['y']) ? $data['town']['y'] : (int) $ca['y']).') ON DUPLICATE KEY UPDATE ban = '.(int) $ca['ban'].', hero = '.(int) $ca['hero'].', job = "'.(string) $ca['job'].'", dead = '.(int) $ca['dead'].', `out` = '.(int) $ca['out'].', x = '.(is_null($ca['x']) ? $data['town']['x'] : (int) $ca['x']).', y = '.(is_null($ca['y']) ? $data['town']['y'] : (int) $ca['y']).' ');
		
		$data['citizens'][(int) $ca['id']] = array(
			'name' => (string) $ca['name'],
			'out' => (int) $ca['out'],
			'ban' => (int) $ca['ban'],
			'hero' => (int) $ca['hero'],
			'job' => (string) $ca['job'],
			'dead' => (int) $ca['dead'],
			'x' => (is_null($ca['x']) ? $data['town']['x'] : (int) $ca['x']),
			'y' => (is_null($ca['y']) ? $data['town']['y'] : (int) $ca['y']),
			'rx' => (is_null($ca['x']) ? 0 : (int) $ca['x'] - $data['town']['x']),
			'ry' => (is_null($ca['y']) ? 0 : (int) $ca['y'] - $data['town']['y']),
		);
		
		$data['map'][$data['citizens'][(int) $ca['id']]['y']][$data['citizens'][(int) $ca['id']]['x']]['citizens'][(int) $ca['id']] = (string) $ca['name'];

	}
}

// owner citizen
$data['user'] = array(
	'id' => (int) $owner['id'],
	'name' => (string) $owner['name'],
	'avatar' => (string) $owner['avatar'],
	'x' => (int) $owner['x'],
	'y' => (int) $owner['y'],
	'rx' => (int) $owner['x'] - $data['town']['x'],
	'ry' => (int) $data['town']['y'] - $owner['y'],
	'out' => (int) $owner['out'],
	'ban' => (int) $owner['ban'],
	'hero' => (int) $owner['hero'],
	'job' => (string) $owner['job'],
	'dead' => (int) $owner['dead'],
); //core data
#var_dump($data['user']);
$db->iquery(' UPDATE dvoo_citizens SET scode = "'.$k.'" WHERE id = '.$data['user']['id']);
$db->iquery(' UPDATE dvoo_town_citizens SET x = '.$data['user']['x'].' AND y = '.$data['user']['y'].' ');

// autoID, time, ip, referer, p, k, n
$db->iquery(' INSERT INTO dvoo_login_log VALUES (NULL, '.time().', "'.$_SERVER['REMOTE_ADDR'].'", "'.$_SERVER['HTTP_REFERER'].'", 3, "'.$k.'", "'.((string) $owner['name']).' (GM)" ) ');


// my zone
$items = array();
#if ( is_array($myzone->item) ) {
	foreach ( $myzone->item AS $item ) {
		$items[] = array(
			'id' => (int) $item['id'],
			'count' => (int) $item['count'],
			'broken' => (int) $item['broken'],
		);
	}
#}

$info['items'] = $items;
$ze = $db->query(' SELECT * FROM dvoo_zones_zones WHERE tid = '.$data['town']['id'].' AND day = '.$data['current_day'].' AND x = '.((int) $owner['x']).' AND y = '.((int) $owner['y']).' ');
if ( !is_array($ze[0]) ) {
	$q = 'INSERT INTO dvoo_zones_zones VALUES ('.$data['town']['id'].', '.$data['current_day'].', '.((int) $owner['x']).', '.((int) $owner['y']).', 0, NULL, NULL, '.((int) $myzone['z']).', '.time().', "'.((string) $owner['name']).'", '.time().') ';
	$db->iquery($q);
}

$items = mysql_real_escape_string(serialize($items));
$q = ' INSERT INTO dvoo_zones_visit VALUES ('.$data['town']['id'].', '.$data['current_day'].', '.((int) $owner['x']).', '.((int) $owner['y']).', 1, '.((int) $myzone['dried']).', '.((int) $myzone['z']).', "'.$items.'", '.time().', "'.((string) $owner['name']).'") ON DUPLICATE KEY UPDATE dried = '.((int) $myzone['dried']).', z = '.((int) $myzone['z']).', items = "'.$items.'", `on` = '.time().', `by` = "'.((string) $owner['name']).'" ';
		$db->iquery($q);

// todo  return OK?
header('HTTP/1.1 200 OK'); 
print 'The Oval Office map has been updated.';

// -- Piwik Tracking API init -- 
require_once "PiwikTracker.php";
PiwikTracker::$URL = 'http://sindevel.com/piwik/';

$piwikTracker = new PiwikTracker( $idSite = 3 );
// You can manually set the visitor details (resolution, time, plugins, etc.) 
// See all other ->set* functions available in the PiwikTracker.php file
$piwikTracker->setURL('http://d2n.sindevel.com/oo/upd.php');
$piwikTracker->setCustomVariable(1, 'uid', $owner['id']);
$piwikTracker->setCustomVariable(2, 'name', $owner['name']);

// Sends Tracker request via http
$piwikTracker->doTrackPageView('OO update script');