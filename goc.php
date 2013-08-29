<?php
exit;

/*
				function startGOC() {
					$("#santa").addClass("santa_end");
				}
				function endGOC() {
					$("#santa").remove();
				}
				function checkGOC(p,s) {
					$('#santa').remove();
					p = typeof(p) != 'undefined' ? p : 1;
					s = typeof(s) != 'undefined' ? s : 1;
					var goc = $.ajax({
						type: 'POST',
						url: 'goc.php',
						data: 'p='+p+'&s='+s+'&u=<?php print $key; ?>',
						success: function(msg) {
							//$('#goc').html(msg);
							eval(msg);
							if ( p == 3 ) {
								$("#santa").addClass("santa_end");
							}
						}
					});
				}
				
				var goccheck = window.setInterval("checkGOC(2)", 60000);
*/

$p = (int) $_POST['p'];
$s = (string) $_POST['s'];
$k = (string) $_POST['u'];
	
include_once 'system.php';
$db = new Database();

if ($p == 1) {
	// HACKER?
	mail('ovaloffice.d2n@gmail.com', 'GOC failed', 'K: '.$k."\n".'T: '.date('d.m.Y, H:i:s')."\n".'C: '.$p.'-'.$s);
	print '$("#santa").remove();$("#goc").remove();';
	exit;
}
else if ($p == 2) {
	// INSERT SANTA
	$r = $db->query('SELECT id FROM dvoo_citizens WHERE scode = "'.$k.'"');
	$z = rand(1,4);
	$r = $db->query('SELECT name FROM dvoo_citizens WHERE scode = "'.$k.'"');
	if ( is_array($r[0]) ) {
		$uname = $r[0][0];
	}
	#if ( ( isset($r[0][0]) && (int) $r[0][0] == 3137 ) || $z == 24 ) {
	if ( $z == 4 && $uname != 'Mogman' && time() <= 1324767599 ) {
		$cq = $db->query('SELECT r.count FROM dvoo_citizen_rewards r INNER JOIN dvoo_citizens c ON c.id = r.uid WHERE c.scode = "'.$k.'" AND r.reward = "Ghost of Santa"');
		if ( is_array($cq[0]) ) {
			$count = $cq[0][0];
		}	
		else {
			$count = 0;
		}
		$cval = md5($k.$count);
		//<img src="santa.gif" id="santa" class="start" onclick="$('#santa').remove();alert('Gotcha!')">
		#print '<img src="img/santa.gif" id="santa" class="santa_start" onclick="checkGOC(3,\''.$cval.'\');">';
		#print '<script type="text/language">$("#santa").addClass("santa_end");</script>';
		print '$(document).ready(function() {
				var santa = document.createElement("img");
				santa.setAttribute("id","santa");
				santa.setAttribute("class","santa_start");
				santa.setAttribute("src","img/santa.gif");
				santa.setAttribute("onclick","checkGOC(3,\''.$cval.'\');");
				$("body").prepend(santa);
				
				window.setTimeout("startGOC()",5000);
				window.setTimeout("endGOC()",25000);
			});';
		#print '</script>';
		exit;
	}
	elseif ( $uname == 'Mogman' ) {
		print 'var z = "goc'.rand(1,3).'";';
		exit;
	}
	else {
		print 'var z = "goc'.$z.'";';
		exit;
	}
}
elseif ($p == 3) {
	$cq = $db->query('SELECT r.count FROM dvoo_citizen_rewards r INNER JOIN dvoo_citizens c ON c.id = r.uid WHERE c.scode = "'.$k.'" AND r.reward = "Ghost of Santa"');
		if ( is_array($cq[0]) ) {
			$count = $cq[0][0];
		}	
		else {
			$count = 0;
		}
		$cval = md5($k.$count);
		if ( $s != $cval ) {
			// HACKER?
			mail('ovaloffice.d2n@gmail.com', 'GOC failed', 'K: '.$k."\n".'T: '.date('d.m.Y, H:i:s')."\n".'C: '.$cval.'-'.$s);
			return '$("#santa").remove();$("#goc").remove();';
			exit;
		}
		else {
			$db->iquery(' INSERT INTO dvoo_citizen_rewards VALUES ((SELECT id FROM dvoo_citizens WHERE scode = "'.$k.'"), "Ghost of Santa", 1) ON DUPLICATE KEY UPDATE count = count + 1 ');
			mail('ovaloffice.d2n@gmail.com', 'GOC found', 'K: '.$k."\n".'T: '.date('d.m.Y, H:i:s')."\n".'C: '.$p.'-'.$s.'-'.$count);
			print '$("#santa").remove();alert("You\'ve seen the Ghost of Santa!");';
			exit;
		}
}
