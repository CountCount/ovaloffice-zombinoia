<?php
include 'error.php';

/* ##### QUICK CONFIG ##### */
$maintenance = 0; // default: 0, values: 0, 1
$version = '5.8 LE'; // current OO version
$language = 'es'; // current options: de, en

/* ######################## */
$tempaccess = false;
if ( isset($_GET['tkey']) ) {
	$key = $_GET['tkey'];
	$tempaccess = true;
}
elseif ( isset($_POST['key']) ) {
	$data_string = 'v=220&r=dv&p=2&k=' . secureKey($_POST['key']);
	$dat2_string = 'v=220&r=dv&p=2';
	setcookie("key",secureKey($_POST['key']),time()+(3*86400));
	$key = $_POST['key'];
}
elseif ( isset($_GET['key']) ) { 
	$data_string = 'v=220&r=dv&p=2&k=' . secureKey($_GET['key']);
	$dat2_string = 'v=220&r=dv&p=2';
	setcookie("key",secureKey($_GET['key']),time()+(3*86400));
	$key = $_GET['key'];
}

elseif ( isset($_COOKIE['key']) ) {
	$data_string = 'v=220&r=co&p=2&k=' . secureKey($_COOKIE['key']);
	$dat2_string = 'v=220&r=co&p=2';
	setcookie("key",secureKey($_COOKIE['key']),time()+(3*86400));
	$key = $_COOKIE['key'];
}
else {
	$data_string = 'v=220&p=0&r='.urlencode($_SERVER['HTTP_REFERER']);
	$key = '';
}
$openmail = (isset($_GET['openmail']) ? $_GET['openmail'] : 0);

// start system
ini_set('display_errors', 0);
// session start
session_start();
include_once 'system.php';
$db = new Database();

// exit if maintenance
if ( $maintenance == 1 ) {
	print t('MAINTENANCE_MSG');
	exit;
}

if ( $tempaccess ) {
	$uid = $_GET['user'];
	$q = ' SELECT c.scode FROM dvoo_citizens c INNER JOIN dvoo_tempkeys t ON t.uid = c.id WHERE c.id = '.$uid.' AND t.tempkey = "'.$key.'" AND t.stamp >= '.time().' ';
	$r = $db->query($q);
	if ( is_array($r[0]) ) {
		$key = $r[0][0];
		$data_string = 'v=220&r=tk&p=2&k=' . $key;
		$dat2_string = 'v=220&r=tk&p=2';
	}
	else {
		$data_string = 'v=220&p=0&r='.urlencode($_SERVER['HTTP_REFERER']);
		$key = '';
	}
}
if ( $key != '' ) {
	$r = $db->query('SELECT name FROM dvoo_citizens WHERE scode = "'.$key.'"');
	if ( is_array($r[0]) ) {
		$fname = $r[0][0];
	}
}
else {
	$fname = 'Guest';
}

// html header
print '<?xml version="1.0" encoding="utf-8"?>';
print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es" dir="ltr">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';

// head
print '<head>
	<title>'.$fname.' | Oval Office</title>
	<link rel="canonical" href="http://d2n.sindevel.com/oo/" />
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.5.2.min.js"></script>
	<script type="text/javascript" src="js/jquery.event.drag-1.5.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.8.13.custom.min.js"></script>
	<script type="text/javascript" src="js/slimbox2.js"></script>
	<link rel="stylesheet" href="css/slimbox2.css" type="text/css" media="screen" />
	<script type="text/javascript" src="js/alert.js"></script>
	<link rel="stylesheet" href="css/alert.css" type="text/css" media="screen" />
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
	<link type="text/css" href="css/oo2.css?v='.$version.'" rel="stylesheet" />
	<script src="js/RGraph/RGraph.common.core.js"></script>
	<script src="js/RGraph/RGraph.common.context.js"></script>
	<script src="js/RGraph/RGraph.common.annotate.js"></script>
	<script src="js/RGraph/RGraph.common.effects.js"></script>
	<script src="js/RGraph/RGraph.common.tooltips.js"></script>
	<script src="js/RGraph/RGraph.common.zoom.js"></script>
	<script src="js/RGraph/RGraph.line.js"></script>
	<script src="js/RGraph/RGraph.scatter.js"></script>
</head>';

// body/container
print '<body>';
?>
	<div id="container-wrapper">
	<div id="spy"><div id="spy-content"></div></div>
	<div id="knife"></div>
	<div id="container-head"></div>
	<div id="container">
	<div id="newlogo"><p><?php print t('VERSION').' '.$version; ?></p></div>
	
		<ul id="tabs">
			<li id="link_cott" class="event-button" style="top:0px;" onclick="eventSpy(1);"><img class="OLD-eventLogo" src="img/choc.png" /></li>
			
			<li id="link_office6" class="empty" style="clear:left;"><a href="#office6">Estadística</a></li>
			<li id="link_office8" class="empty"><a href="#office8">Salon</a></li>
			<li id="link_office1"><a href="#office1">Foyer</a></li>
			
		</ul>
	<div id="intro">
		<span id="headtownday"></span>
	</div>
		<div id="tabcontents">
		<div id="office1" class="tabcontent">
			<div id="foyer-id" class="subtabcontent">
				<div class="clearfix">
					<h3 id="CitizenIdentificationHeader">Identificación en curso ...</h3>
					<div id="CitizenIdentificationContent" class="loading"></div>
				</div>
			</div>
		</div>
			
	
			
			<div id="office6" class="tabcontent hideme"><div class="loading">
			</div></div>
			
			<div id="office8" class="tabcontent hideme"><div class="loading">
			</div></div>
			
		</div>
		
</div>	
<div id="container-foot"></div>
</div>

<div id="disclaimer"><div style="float:right;margin:6px 6px 12px 12px;text-decoration:none;border:none;text-align:center;"><g:plusone></g:plusone><br/><br/>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="QEVH6ZPKPXDC4">
<input type="hidden" name="custom" value="D2N-<?php print $key; ?>">
<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
</form>
</div>
"Oval Office" es un proyecto de apoyo al juego <a href="http://www.zombinoia.com?ref=SinSniper" target="_new">Zombinoia</a>.<br/>No nos responsabilizamos por informaciones incompletas, incorrectas o desactualizadas.<br/>
La mayoría de iconos pertenecen a <a href="http://www.zombinoia.com?ref=SinSniper" target="_new">Zombinoia</a> y por lo tanto son propiedad intelectual de <a href="http://www.motion-twin.com/" target="_new">Motion Twin</a>.<br/>
Código: <a href="mailto:countcount.cc@gmail.com">SinSniper</a><br/>
Diseño: <a href="http://buntmacher.net" target="_new">buntmacher</a><br/>
Traducción al español: <span style="color:#333;">Azuron</span>
</div>

<div id="dynascript"></div>
	<script type="text/javascript">
				var office = {};
				office["1"] = true;
				office["6"] = false;
				office["8"] = false;

				var phpreg = {};
				phpreg["6"] = 'stat';
				phpreg["8"] = 'wb.main';
				
				var loading = '<div class="loading"></div>';
				var amt = '#office1';
				$("ul#tabs li a").click(function (e) { 
					e.preventDefault();
					var newAmt = $(this).attr('href');
					//var lOf = 'loadO' + newAmt.substr(2);
					//lOf(userid);
					//window[lOf](userid);
					loadOffice(newAmt.substr(7),userid);
					$(amt).fadeOut();
					$(newAmt).fadeIn('slow');
					amt = newAmt;
				});
				
				$("ul#tabs li a").hover(function () {
					$(this).addClass("hilitetab");
				}, function () {
					$(this).removeClass("hilitetab");
				});

				
				function processXML(u) {
					$('#infoblock_zone').remove();
					$('#infoblock-zone').remove();
					$('#CitizenIdentificationContent').html(' ').addClass('loading');
					// process XML
					var oo_xml = $.ajax({
						type: 'POST',
						url: 'xml.ajax.php',
						data: '<?php print $data_string; ?>&u='+u,
						success: function(msg) {
							$('#CitizenIdentificationHeader').html('Identificación completada.');
							$('#CitizenIdentificationContent').html(msg);
							$('#ooidtl').click();
							refreshOffice();
						}
					});
				}
				
				function loadTabContent(u) {	
					//loadOffice6(u);
					//loadOffice8(u);
				}
				
				function refreshOffice() {
					for ( i = 3; i < 9; i++ ) {
						office[i] = false;
						loadOffice(i,userid);
					}
				}
				
				function loadOffice(i,u) {
					if ( office[i] == false ) {
						// load map content
						$('#link_office'+i).addClass("empty");
						$('#office'+i).html(loading);
						var ooo = $.ajax({
							type: 'POST',
							url: phpreg[i]+'.ajax.php',
							data: '<?php print $dat2_string; ?>&u='+u,
							success: function(msg) {
								$('#infoblock-zone').remove();
								$('#office'+i).html(msg);
								$('#link_office'+i).removeClass("empty");
							}
						});
						office[i] = true;
					}
				}
				
			
				function loadDeadContent(u) {	
					var loading = '<div class="loading"></div>';
					office["6"] = true;
					office["8"] = true;
					// load chat content
					$('#link_office8').addClass("empty");
					$('#office8').html(loading);
					var oo_fb = $.ajax({
						type: 'POST',
						url: 'wb.main.ajax.php',
						data: '<?php print $dat2_string; ?>&u='+u,
						success: function(msg) {
							$('#office8').html(msg);
							$('#link_office8').removeClass("empty");
						}
					});
					
					// load stat content
					$('#link_office6').addClass("empty");
					$('#office6').html(loading);
					var oo_fa = $.ajax({
						type: 'POST',
						url: 'stat.ajax.php',
						data: '<?php print $dat2_string; ?>&u='+u,
						success: function(msg) {
							$('#office6').html(msg);
							$('#link_office6').removeClass("empty");
						}
					});
				}
				
				var curF = "#foyer-intro";
				$("ul#sub-foyer li a").click(function (e) { 
					e.preventDefault();
					var newF = $(this).attr("href");
					$(curF).fadeOut(100, function() { $(newF).fadeIn("slow", function() { $('#sub-foyer').slideDown("slow"); }); });
					curF = newF;
				});
				
				processXML(1);

	function eventSpy(e) {
		// load stat content
		var st = $.ajax({
			type: "POST",
			url: "etc.ajax.php",
			data: "e="+e+"&k=<?php print $key; ?>",
			success: function(msg) {
				$("#spy-content").hide();
				$("html, body").animate({scrollTop:90}, "slow");
				$("#spy").animate({
					width: "12px",
					height: "720px",
					left: "489px",
					top: "95px"
				}, 250, function() {
					$("#spy").animate({
						width: "930px",
						left: "15px"
					}, 250, function() {
						$("#spy-content").html(msg).fadeIn(500);
					});
				});
			}
		});
	}
	function eventSignup(e,k,o,s) {
		// load stat content
		var st = $.ajax({
			type: "POST",
			url: "ets.ajax.php",
			data: "e="+e+"&k="+k+"&o="+o+"&s="+s,
			success: function(msg) {
				var r = (0 - (e - s));
				eventSpy(r);
			}
		});
	}
	
	</script>	
	<script type="text/javascript">
  window.___gcfg = {lang: 'en-GB'};

  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>
</body>
</html>

<?php

function secureKey($k) {
	return htmlspecialchars(strip_tags($k));
}
