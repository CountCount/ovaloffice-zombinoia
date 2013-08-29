<?php
include_once 'system.php';
$db = new Database();

// get key (ajax)
$t = (int) $_POST['t'];
$u = (int) $_POST['u'];
$c = (int) $_POST['c'];


if ( !is_null($u) && $u != '' && $u > 0 && !isset($_POST['a']) ) {
	// status
	$thirsty = $_POST['thirsty'] == 1 ? 1 : 0;
	$hangover = $_POST['hangover'] == 1 ? 1 : 0;
	// kp
	$paralyzed = $_POST['paralyzed'] == 1 ? 1 : 0;
	$clean = $_POST['clean'] == 1 ? 1 : 0;
	$topform = $_POST['topform'] == 1 ? 1 : 0;
	$safe = $_POST['safe'] == 1 ? 1 : 0;
	// lunch
	$water = $_POST['water'] == 1 ? 6 : 0;
	$food = $_POST['food_yummy'] == 1 ? 7 : ($_POST['food_defoe'] == 1 ? 6 : 0);
	$drug_twin = $_POST['drug_twin'] == 1 ? (8 * (int) $_POST['drug_twin_count']) : 0;
	$drug_ster = $_POST['drug_ster'] == 1 ? (6 * (int) $_POST['drug_ster_count']) : 0;
	$alcohol = $_POST['alcohol'] == 1 ? 6 : 0;
	$coffee = $_POST['coffee'] == 1 ? (4 * (int) $_POST['coffee_count']) : 0;
	$gamble = $_POST['gamble'] == 1 ? 1 : 0;
	$sleep = $_POST['sleep'] == 1 ? 2 : 0;
	$alarm = $_POST['alarm'] == 1 ? 1 : 0;
	$lunge = $_POST['lunge'] == 1 ? 6 : 0;
	// save
	$db->iquery(' INSERT INTO dvoo_timeplanner VALUES ( 
			'.$t.',
			'.$u.', 
			'.$_POST['tp_day'].', 
			'.$_POST['tp0'].',
			'.$_POST['tp1'].',
			'.$_POST['tp2'].',
			'.$_POST['tp3'].',
			'.$_POST['tp4'].',
			'.$_POST['tp5'].',
			'.$_POST['tp6'].',
			'.$_POST['tp7'].',
			'.$_POST['tp8'].',
			'.$_POST['tp9'].',
			'.$_POST['tp10'].',
			'.$_POST['tp11'].',
			'.$_POST['tp12'].',
			'.$_POST['tp13'].',
			'.$_POST['tp14'].',
			'.$_POST['tp15'].',
			'.$_POST['tp16'].',
			'.$_POST['tp17'].',
			'.$_POST['tp18'].',
			'.$_POST['tp19'].',
			'.$_POST['tp20'].',
			'.$_POST['tp21'].',
			'.$_POST['tp22'].',
			'.$_POST['tp23'].',
			'.$water.',
			'.$food.',
			'.$drug_twin.',
			'.$drug_ster.',
			'.$alcohol.',
			'.$coffee.',
			'.$gamble.',
			'.$alarm.',
			'.$lunge.',
			'.$thirsty.',
			'.$hangover.',
			'.$paralyzed.',
			'.$clean.',
			'.$topform.',
			'.$safe.',
			'.$sleep.'
			) ON DUPLICATE KEY UPDATE  
			tp0 = '.$_POST['tp0'].',
			tp1 = '.$_POST['tp1'].',
			tp2 = '.$_POST['tp2'].',
			tp3 = '.$_POST['tp3'].',
			tp4 = '.$_POST['tp4'].',
			tp5 = '.$_POST['tp5'].',
			tp6 = '.$_POST['tp6'].',
			tp7 = '.$_POST['tp7'].',
			tp8 = '.$_POST['tp8'].',
			tp9 = '.$_POST['tp9'].',
			tp10 = '.$_POST['tp10'].',
			tp11 = '.$_POST['tp11'].',
			tp12 = '.$_POST['tp12'].',
			tp13 = '.$_POST['tp13'].',
			tp14 = '.$_POST['tp14'].',
			tp15 = '.$_POST['tp15'].',
			tp16 = '.$_POST['tp16'].',
			tp17 = '.$_POST['tp17'].',
			tp18 = '.$_POST['tp18'].',
			tp19 = '.$_POST['tp19'].',
			tp20 = '.$_POST['tp20'].',
			tp21 = '.$_POST['tp21'].',
			tp22 = '.$_POST['tp22'].',
			tp23 = '.$_POST['tp23'].',
			water = '.$water.',
			food = '.$food.',
			drug = '.$drug_twin.',
			drug2 = '.$drug_ster.',
			alcohol = '.$alcohol.',
			coffee = '.$coffee.',
			gamble = '.$gamble.',
			alarm = '.$alarm.',
			lunge = '.$lunge.',
			thirsty = '.$thirsty.',
			hangover = '.$hangover.',
			paralyzed = '.$paralyzed.',
			clean = '.$clean.',
			topform = '.$topform.',
			safe = '.$safe.',
			sleep = '.$sleep
			);
			
			$pins = $db->query(' SELECT c.name, c.id, r.job, t.* FROM dvoo_timeplanner t INNER JOIN dvoo_citizens c ON c.id = t.uid INNER JOIN dvoo_town_citizens r ON r.town_id = t.tid AND r.citizen_id = t.uid WHERE t.tid = '.$t.' AND t.day >= '.$c.' ORDER BY t.day DESC, c.name ASC ');
	
			include 'ema.out.php';
}
elseif ( $_POST['a'] == 'sp' ) {
	// status
	$thirsty = $_POST['thirsty'] == 1 ? 1 : 0;
	$hangover = $_POST['hangover'] == 1 ? 1 : 0;
	// kp
	$paralyzed = $_POST['paralyzed'] == 1 ? 1 : 0;
	$clean = $_POST['clean'] == 1 ? 1 : 0;
	$topform = $_POST['topform'] == 1 ? 1 : 0;
	$safe = $_POST['safe'] == 1 ? 1 : 0;
	// lunch
	$water = $_POST['water'] == 1 ? 6 : 0;
	$food = $_POST['food_yummy'] == 1 ? 7 : ($_POST['food_defoe'] == 1 ? 6 : 0);
	$drug_twin = $_POST['drug_twin'] == 1 ? (8 * (int) $_POST['drug_twin_count']) : 0;
	$drug_ster = $_POST['drug_ster'] == 1 ? (6 * (int) $_POST['drug_ster_count']) : 0;
	$alcohol = $_POST['alcohol'] == 1 ? 6 : 0;
	$coffee = $_POST['coffee'] == 1 ? (4 * (int) $_POST['coffee_count']) : 0;
	$gamble = $_POST['gamble'] == 1 ? 1 : 0;
	$sleep = $_POST['sleep'] == 1 ? 2 : 0;
	$alarm = $_POST['alarm'] == 1 ? 1 : 0;
	$lunge = $_POST['lunge'] == 1 ? 6 : 0;
	// save
	$db->iquery(' INSERT INTO dvoo_timeplanner VALUES ( 
			0,
			'.$u.', 
			0, 
			'.$_POST['tp0'].',
			'.$_POST['tp1'].',
			'.$_POST['tp2'].',
			'.$_POST['tp3'].',
			'.$_POST['tp4'].',
			'.$_POST['tp5'].',
			'.$_POST['tp6'].',
			'.$_POST['tp7'].',
			'.$_POST['tp8'].',
			'.$_POST['tp9'].',
			'.$_POST['tp10'].',
			'.$_POST['tp11'].',
			'.$_POST['tp12'].',
			'.$_POST['tp13'].',
			'.$_POST['tp14'].',
			'.$_POST['tp15'].',
			'.$_POST['tp16'].',
			'.$_POST['tp17'].',
			'.$_POST['tp18'].',
			'.$_POST['tp19'].',
			'.$_POST['tp20'].',
			'.$_POST['tp21'].',
			'.$_POST['tp22'].',
			'.$_POST['tp23'].',
			'.$water.',
			'.$food.',
			'.$drug_twin.',
			'.$drug_ster.',
			'.$alcohol.',
			'.$coffee.',
			'.$gamble.',
			'.$alarm.',
			'.$lunge.',
			'.$thirsty.',
			'.$hangover.',
			'.$paralyzed.',
			'.$clean.',
			'.$topform.',
			'.$safe.',
			'.$sleep.'
			) ON DUPLICATE KEY UPDATE  
			tp0 = '.$_POST['tp0'].',
			tp1 = '.$_POST['tp1'].',
			tp2 = '.$_POST['tp2'].',
			tp3 = '.$_POST['tp3'].',
			tp4 = '.$_POST['tp4'].',
			tp5 = '.$_POST['tp5'].',
			tp6 = '.$_POST['tp6'].',
			tp7 = '.$_POST['tp7'].',
			tp8 = '.$_POST['tp8'].',
			tp9 = '.$_POST['tp9'].',
			tp10 = '.$_POST['tp10'].',
			tp11 = '.$_POST['tp11'].',
			tp12 = '.$_POST['tp12'].',
			tp13 = '.$_POST['tp13'].',
			tp14 = '.$_POST['tp14'].',
			tp15 = '.$_POST['tp15'].',
			tp16 = '.$_POST['tp16'].',
			tp17 = '.$_POST['tp17'].',
			tp18 = '.$_POST['tp18'].',
			tp19 = '.$_POST['tp19'].',
			tp20 = '.$_POST['tp20'].',
			tp21 = '.$_POST['tp21'].',
			tp22 = '.$_POST['tp22'].',
			tp23 = '.$_POST['tp23'].',
			water = '.$water.',
			food = '.$food.',
			drug = '.$drug_twin.',
			drug2 = '.$drug_ster.',
			alcohol = '.$alcohol.',
			coffee = '.$coffee.',
			gamble = '.$gamble.',
			alarm = '.$alarm.',
			lunge = '.$lunge.',
			thirsty = '.$thirsty.',
			hangover = '.$hangover.',
			paralyzed = '.$paralyzed.',
			clean = '.$clean.',
			topform = '.$topform.',
			safe = '.$safe.',
			sleep = '.$sleep
			);
			
			print "<script type='text/javascript'>
				$('div#presetStatus').html('".t('PRESET_SAVED')."').slideDown(250).delay(1000).animate({opacity:0}, 2000).slideUp('slow').css('opacity', 1);
			</script>";
}	
else {
	print '<div class="error">'.t('ERROR_OCCURED').'</div>';
	mail('ovaloffice.dv@googlemail.com', 'TP', 'Bitte im Wartebereich diesen Fehlercode melden: '.date('YmdHis',time()).'.U'.$u.'.T'.$t);
}
			
	

