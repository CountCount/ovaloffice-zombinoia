<?php
include_once 'system.php';
$db = new Database();

$k = htmlspecialchars(strip_tags($_POST['k']));
$u = (int) $_POST['u'];
$p = (int) $_POST['p'];
if ( $p < 1 || !is_numeric($p) || is_null($p) ) {
  $p = 25;
}

$notes = $db->query('SELECT c.id,c.name,c.oldnames,f.time,f.feedback FROM dvoo_feedback f INNER JOIN dvoo_citizens c ON c.id = f.uid AND f.uid > 0 ORDER BY f.time DESC LIMIT '.$p);
	
include 'wb.out.php';
