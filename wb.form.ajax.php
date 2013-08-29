<?php
include_once 'system.php';
$db = new Database();

// get key (ajax)
$u = (int) $_POST['u'];
$p = (int) $_POST['p'];
if ( $p < 1 || !is_numeric($p) || is_null($p) ) {
  $p = 25;
}

if ( $u > 0 ) {
	if ( trim($_POST['fb']) != ''  ) {
		$user = $db->query('SELECT name FROM dvoo_citizens WHERE id = '.$u.' LIMIT 1');
 	  $res = $db->query('SELECT uid, feedback FROM dvoo_feedback ORDER BY time DESC LIMIT 1');
		$fb = $_POST['fb'];
 	  if ( $u != 51136 ) {
		  $fb = nl2br(strip_tags($_POST['fb']));
		} else {
			$fb = nl2br($_POST['fb']);
		}
		
		$fb = preg_replace_callback('|\[d([0-9]+)\]|', "dice",$fb);
		$fb = preg_replace_callback('|\[ht\]|', "ht",$fb);
		$fb = preg_replace_callback('|\[card\]|', "cards",$fb);
		$fb = preg_replace_callback('|\[cocktail\]|', "cocks",$fb);
		$fb = str_replace(':)', '<img alt=":)" src="'.t('GAMESERVER_SMILEY').'h_smile.gif" />',$fb);
		$fb = str_replace(';)', '<img alt=";)" src="'.t('GAMESERVER_SMILEY').'h_blink.gif" />',$fb);
		$fb = str_replace(':O', '<img alt=":O" src="'.t('GAMESERVER_SMILEY').'h_surprise.gif" />',$fb);
		$fb = str_replace(':(', '<img alt=":(" src="'.t('GAMESERVER_SMILEY').'h_sad.gif" />',$fb);
		$fb = str_replace(':D', '<img alt=":D" src="'.t('GAMESERVER_SMILEY').'h_lol.gif" />',$fb);
		$fb = str_replace(':|', '<img alt=":|" src="'.t('GAMESERVER_SMILEY').'h_neutral.gif" />',$fb);
		
		$db->iquery('INSERT INTO dvoo_feedback VALUES ('.$u.', '.time().',"'.mysql_escape_string($fb).'")');
	}	
}
else {
	# mail / error log?
}
		
$notes = $db->query('SELECT c.id,c.name,c.oldnames,f.time,f.feedback FROM dvoo_feedback f INNER JOIN dvoo_citizens c ON c.id = f.uid AND f.uid > 0 ORDER BY f.time DESC LIMIT '.$p);
	
include 'wb.out.php';

function dice($d) {
	if ($d[1] > 1000) $d[1] = 1000;
	return '<span class="wb-dice">'.rand(1,$d[1]).'<span class="base">[d'.$d[1].']</span></span>';
}
function ht() {
	$ht = array('Heads','Tails');
	return '<span class="wb-dice headtail">'.$ht[rand(0,1)].'</span>';
}
function cards() {
	$cards = array(
			'Ace of Hearts',
			'2 of Hearts',
			'3 of Hearts',
			'4 of Hearts',
			'5 of Hearts',
			'6 of Hearts',
			'7 of Hearts',
			'8 of Hearts',
			'9 of Hearts',
			'10 of Hearts',
			'Jack of Hearts',
			'Queen of Hearts',
			'King of Hearts',
			'Ace of Diamonds',
			'2 of Diamonds',
			'3 of Diamonds',
			'4 of Diamonds',
			'5 of Diamonds',
			'6 of Diamonds',
			'7 of Diamonds',
			'8 of Diamonds',
			'9 of Diamonds',
			'10 of Diamonds',
			'Jack of Diamonds',
			'Queen of Diamonds',
			'King of Diamonds',
			'Ace of Spades',
			'2 of Spades',
			'3 of Spades',
			'4 of Spades',
			'5 of Spades',
			'6 of Spades',
			'7 of Spades',
			'8 of Spades',
			'9 of Spades',
			'10 of Spades',
			'Jack of Spades',
			'Queen of Spades',
			'King of Spades',
			'Ace of Clubs',
			'2 of Clubs',
			'3 of Clubs',
			'4 of Clubs',
			'5 of Clubs',
			'6 of Clubs',
			'7 of Clubs',
			'8 of Clubs',
			'9 of Clubs',
			'10 of Clubs',
			'Jack of Clubs',
			'Queen of Clubs',
			'King of Clubs',
			'Game Rules',
		);
	return '<span class="wb-dice card">'.$cards[rand(0,count($cards)-1)].'</span>';
}
function cocks() {
	$cocks = array(
			'Bloody Mary',
			'Red Beer',
			'Cuba Libre',
			'Dark & Stormy',
			'Cherry Hooker',
			'Highball',
			'Salty Dog',
			'Tequila Sunrise',
			'Vodka Marinostov',
			'Wake the Dead',
			'Smith & Wesson',
			'Freddie Fuddpucker',
			'Banshee',
			'White Russian',
			'Godfather',
			'Peppermint Patty',
			'Rusty Nail',
			'Stinger',
			'Bone Dry Martini',
			'Gibson',
			'Dry Rob Roy',
			'Kamikaze',
			'Sex on the Beach',
			'Zombie',
			'Hurricane',
			'French 75',
			'Jack Rose',
			'Long Island Iced Tea',
			'Ward Eight',
			'Singapore Sling',
			'Between the Sheets',
			'Plutonium Beer',
			'Metal Slug',
			'Super Metal Slug',
			'Apocalypse Beer',
			'Chocolate Beer',
			'Chocolate Prosecco',
			'Viper Special',
			'Vanilla Shocker',
			'Rancid Milk',
			'Scruffy Orange Juice',
			'Mulled Wine',
			'Eggnogg',
			'Ouzo',
			'Epoq-making cocktail',
		);
	return '<span class="wb-dice cocktail">'.$cocks[rand(0,count($cocks)-1)].'</span>';
}