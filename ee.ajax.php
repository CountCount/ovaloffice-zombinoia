<?php
include_once 'system.php';
$db = new Database();

$ee = htmlspecialchars(strip_tags($_POST['ee']));
$cc = htmlspecialchars(strip_tags($_POST['cc']));

$found = $db->query(' SELECT * FROM dvoo_eastereggs WHERE ee = "'.$ee.'" AND cc = "'.$cc.'"');
if ( !isset($found[0]['cc']) ) {
$db->iquery(' INSERT INTO dvoo_eastereggs VALUES ("'.$ee.'", "'.$cc.'")');

$db->iquery(' INSERT INTO dvoo_citizen_rewards VALUES ((SELECT id FROM dvoo_citizens WHERE scode = "'.$ee.'"), "Osterei gefunden", 1) ON DUPLICATE KEY UPDATE count = count + 1 ');
mail('ovaloffice.dv@gmail.com', 'Easter Egg', 'K: '.$ee."\n".'T: '.date('d.m.Y, H:i:s')."\n".'C: '.$cc);
}
else {
mail('ovaloffice.dv@gmail.com', 'Faules Ei', 'K: '.$ee."\n".'T: '.date('d.m.Y, H:i:s')."\n".'C: '.$cc);
}

print 1;