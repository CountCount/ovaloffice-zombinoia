<?php
include_once 'system.php';
$db = new Database();

// get key (ajax)
$t = (int) $_POST['t'];
$c = (int) $_POST['c'];
$u = (int) $_POST['u'];
		
$pins = $db->query(' SELECT c.name, c.id, r.job, t.* FROM dvoo_timeplanner t INNER JOIN dvoo_citizens c ON c.id = t.uid INNER JOIN dvoo_town_citizens r ON r.town_id = t.tid AND r.citizen_id = t.uid WHERE t.tid = '.$t.' AND t.day >= '.$c.' ORDER BY t.day DESC, c.name ASC ');
	
include 'ema.out.php';