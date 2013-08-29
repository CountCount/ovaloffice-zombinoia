<?php
exit;
$conv = array(
'Ã„' => 'Ãƒâ€ž',
'Ã¤' => 'ÃƒÂ¤',
'Ã–' => 'Ãƒâ€“',
'Ã¶' => 'ÃƒÂ¶',
'Ãœ' => 'ÃƒÅ“',
'Ã¼' => 'ÃƒÂ¼',
'ÃŸ' => 'ÃƒÅ¸',
'Ã ' => 'ÃƒÂ ',
'Ã¡' => 'ÃƒÂ¡',
'Ã¨' => 'ÃƒÂ¨',
'Ã©' => 'ÃƒÂ©',
);

include 'system.php';
include_once 'dal.php';

$db = new Database();

foreach ( $conv AS $new => $old ) {
	print 'UPDATE dvoo_items SET iname = REPLACE(iname, "'.$old.'", "'.$new.'");'."<br/>";
	print 'UPDATE dvoo_feedback SET feedback = REPLACE(feedback, "'.$old.'", "'.$new.'");'."<br/>";
	#print 'UPDATE dvoo_citizen_rewards SET reward = REPLACE(reward, "'.$old.'", "'.$new.'");'."<br/>";
	#print 'UPDATE dvoo_rewards SET name = REPLACE(name, "'.$old.'", "'.$new.'");'."<br/>";
	print 'UPDATE dvoo_towns SET name = REPLACE(name, "'.$old.'", "'.$new.'");'."<br/>";
	print 'UPDATE dvoo_zones SET building = REPLACE(building, "'.$old.'", "'.$new.'");'."<br/>";
}










