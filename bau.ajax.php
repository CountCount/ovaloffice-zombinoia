<?php
include_once 'system.php';
$db = new Database();

// get version number
$v = (int) $_POST['v'];
// get process number
$p = (int) $_POST['p'];
if ( $p == 2 ) {
	$k = htmlspecialchars(strip_tags($_POST['k']));
	$u = (int) $_POST['u'];
}
elseif ( $p == 1 ) {
	$k = htmlspecialchars(strip_tags($_POST['k']));
	$n = htmlspecialchars(strip_tags($_POST['n']));
}
else {
	// no data send -> start
	print '<script type="text/javascript">
			$("#link_bauamt").remove();
			$("#bauamt").remove();
		</script>';
	exit;
}


$session = $db->query(' SELECT xml FROM dvoo_rawdata WHERE id = '.$u.' ORDER BY time DESC LIMIT 1 ');
if ( $data = unserialize($session[0]['xml']) ) {
	$cd = $data['current_day'];
}

$ac = $db->query(' SELECT COUNT(*) FROM dvoo_buildings WHERE active = 1 ');
$bc = $db->query(' SELECT COUNT(*) FROM dvoo_buildings WHERE active = 1 AND bp IS NOT NULL ');

$ab = $db->query(' SELECT b.* FROM dvoo_buildings b WHERE active = 1 OR parent = 0 ');
$buildings = array();
$blueprints = array();
$parrel = array();
foreach ( $ab AS $b ) {
	$buildings[$b['id']] = array(
		'id' => $b['id'],
		'name' => $b['name'],
		'temp' => $b['temporary'],
		'img' => $b['img'],
		'vp' => $b['vp'],
		'ap' => $b['ap'],
		'bp' => $b['bp'],
		'rsc' => unserialize($b['rsc']),
		'desc' => str_replace("'",'&apos;',$b['desc']),
	);

	if ( $b['parent'] > 0 ) {
		$parrel[$b['parent']][] = $b['id'];
	}
}
$bp = $db->query(' SELECT pid FROM dvoo_town_blueprints WHERE tid = '.$data['town']['id'].' ');
foreach ( $bp AS $p ) {
	$blueprints[] = $p['pid'];
}

ksort($buildings);
$processed = array();
$superparents = array(
	1010 => '137,151,117', // Stadtmauer
	1011 => '147,176,193', // Pumpe
	1033 => '197,186,143', // Werkstatt
	1050 => '237,184,103', // Wachturm
	1051 => '227,145,145', // Fundament
	1062 => '201,130,210', // Portal
);

$rsc_items = array();
$rsc_res = $db->query(' SELECT * FROM dvoo_items ');
foreach ( $rsc_res AS $r ) {
	$rsc_items[$r['iid']] = array(
		'id' => $r['iid'],
		'name' => $r['iname'],
		'img' => $r['iimg'],
		'cat' => $r['icat'],
	);
}

$tbuildings = array();
$tb = $db->query(' SELECT b.id FROM dvoo_buildings b INNER JOIN dvoo_town_buildings t ON t.bid = b.id WHERE t.tid = '. $data['town']['id'].' AND day = '.$data['current_day']);
foreach ( $tb AS $t ) {
	$tbuildings[] = $t['id'];
}


print '<div id="bau_planer" class="subtabcontent"><h3>'.t('CONSTRUCTION_YARD_TITLE').'</h3>';
print '<p>'.$ac[0][0].' buildings of the new system are stored in the database, '.$bc[0][0].' have been processed so far.<br/>To activate a building because you found the blueprint, just click the red cross next to the blueprint.</p>';

if ( (date('z') >= 111 && date('z') <= 114) ) {
	if ( mt_rand(1,1000) > 900 ) {
		print '<div class="easteregg ee'.mt_rand(10000,99999).' egg'.mt_rand(1,4).' rot'.mt_rand(1,5).'" style="top:'.mt_rand(0,900).'px;left:'.mt_rand(0,900).'px;opacity:'.(mt_rand(2,8)/10).';"></div>';
	}
}

print '<div id="construction-permit" style="float:right;width:250px;"></div>';

print '<form id="bau-form" name="bau-form" onsubmit="getConstructionPermit();return false;">';
print '<div class="button"><input type="button" value="'.t('SHOW_ALL').'" onclick="$(\'.noblueprint\').show();" /><input type="button" value="'.t('SHOW_BPONLY').'" onclick="$(\'.noblueprint\').hide();" /><input type="button" value="'.t('SELECT_ALL').'" onclick="$(\'.bau-building\').addClass(\'bau-hilite\');$(\'.built\').removeClass(\'bau-hilite\');$(\'.bau-building td input\').attr(\'checked\', true);" /><input type="reset" value="'.t('SELECT_NONE').'" onclick="$(\'.bau-building\').removeClass(\'bau-hilite\');" /><input type="submit" value="'.t('GET_CONSTRUCTION_PERMIT').'" /></div>';
print '<input type="hidden" value="'.$data["town"]["id"].'" name="t" />
		<input type="hidden" value="'.$data["user"]["id"].'" name="u" />
		<table class="bau-building-table" border="0" cellspacing="0"><tbody>';

foreach ( $superparents AS $b => $c ) {
	render_building($buildings[$b], $processed, 0, 0, 0, 0, $c);
}

print '</tbody></table>';
print '<div class="button"><input type="button" value="'.t('SHOW_ALL').'" onclick="$(\'.noblueprint\').show();" /><input type="button" value="'.t('SHOW_BPONLY').'" onclick="$(\'.noblueprint\').hide();" /><input type="button" value="'.t('SELECT_ALL').'" onclick="$(\'.bau-building\').addClass(\'bau-hilite\');$(\'.built\').removeClass(\'bau-hilite\');$(\'.bau-building td input\').attr(\'checked\', true);" /><input type="reset" value="'.t('SELECT_NONE').'" onclick="$(\'.bau-building\').removeClass(\'bau-hilite\');" /><input type="submit" value="'.t('GET_CONSTRUCTION_PERMIT').'" /></div>';
print '</form><br style="clear:both;" /></div>';

print ''; ?>
<script type="text/javascript">
	$("table.bau-building-table tr input").click(function (e) { 
		var checkId = $(this).attr('value');
		//alert(checkId);
		if ( $('input#c'+checkId).attr('checked') == true ) {
			$('tr#b'+checkId).addClass('bau-hilite');
			var classList = $('tr#b'+checkId).attr('class').split(/\s+/);
			$.each( classList, function(index, item){
					if (item.length == 5 ) {
						 //do something
						 //alert(item);
						 var parentId = item.substring(1, 5);
						 if ( !$('tr#b'+parentId).hasClass('built') ) {
							 $('tr#b'+parentId).addClass('bau-hilite');
							 $('#c'+parentId).attr('checked', true);
							 
							 var subClassList = $('tr#b'+parentId).attr('class').split(/\s+/);
							$.each( subClassList, function(index2, item2){
									if (item2.length == 5 ) {
										 //do something
										 //alert(item);
										 var superParentId = item2.substring(1, 5);
										 if ( !$('tr#b'+superParentId).hasClass('built') ) {
											 $('tr#b'+superParentId).addClass('bau-hilite');
											 $('#c'+superParentId).attr('checked', true);
											 
											 var subSubClassList = $('tr#b'+superParentId).attr('class').split(/\s+/);
												$.each( subSubClassList, function(index3, item3){
														if (item3.length == 5 ) {
															 //do something
															 //alert(item);
															 var superbeParentId = item3.substring(1, 5);
															 if ( !$('tr#b'+superbeParentId).hasClass('built') ) {
																 $('tr#b'+superbeParentId).addClass('bau-hilite');
																 $('#c'+superbeParentId).attr('checked', true);
																}
														}
												});
											 
											}
									}
							});
						}
						 
					}
			});
		}
		else {
			$('tr#b'+checkId).removeClass('bau-hilite');
			$('tr.p'+checkId).removeClass('bau-hilite');
			$('#c'+checkId).attr('checked', false);
			$('tr.p'+checkId+' input').attr('checked', false);
			// subchildren
			
		}
	});
	
	function getConstructionPermit() {
		$('#construction-permit').html(' ').addClass('loading');
		var formdata = $('#bau-form').serialize();
		var bp = $.post(
			'bau.form.ajax.php',
			$('#bau-form').serialize(),
			function(data){
				$('#construction-permit').removeClass('loading');
				$('#construction-permit').html(data);
				$('html, body').animate({scrollTop:350}, 'slow');
			}
		);
	}
	
	function buildBuilding(b) {
		var loading = '<div class="loading"></div>';
		$('#b'+b).html('<td colspan="6">'+loading+'</td>');
		var bb = $.post(
			'bau.update.ajax.php',
			"u=<?php print $u; ?>&a=1&b="+b,
			function(){
				var oo_fa = $.ajax({
					type: 'POST',
					url: 'bau.ajax.php',
					data: 'v=220&r=dv&p=2&u='+<?php print $u; ?>,
					success: function(msg) {
						$('#office7').html(msg);
					}
				});
			}
		);
	}
	
	function findBlueprint(b) {
		var loading = '<div class="loading"></div>';
		$('#b'+b).html('<td colspan="7">'+loading+'</td>');
		var bb = $.post(
			'bau.update.ajax.php',
			"u=<?php print $u; ?>&a=1&p="+b,
			function(){
				var oo_fa = $.ajax({
					type: 'POST',
					url: 'bau.ajax.php',
					data: 'v=220&r=dv&p=2&u='+<?php print $u; ?>,
					success: function(msg) {
						$('#office7').html(msg);
					}
				});
			}
		);
	}
	function loseBlueprint(b) {
		var loading = '<div class="loading"></div>';
		$('#b'+b).html('<td colspan="7">'+loading+'</td>');
		var bb = $.post(
			'bau.update.ajax.php',
			"u=<?php print $u; ?>&a=0&p="+b,
			function(){
				var oo_fa = $.ajax({
					type: 'POST',
					url: 'bau.ajax.php',
					data: 'v=220&r=dv&p=2&u='+<?php print $u; ?>,
					success: function(msg) {
						$('#office7').html(msg);
					}
				});
			}
		);
	}
	
	$('.noblueprint').hide();

</script>
<?php print '';

function render_building($b, &$pro, $l = 0, $p1 = 0, $p2 = 0, $p3 = 0, $c = 'rgba(0,0,0,0.1)') {
	global $data, $buildings, $parrel, $blueprints, $tbuildings, $rsc_items;
	if ( $l == 0 ) {
		print '<tr id="s'.$b['id'].'" style="background:rgba('.$c.',1) url(img/building_'.$b['id'].'.png) top left no-repeat;height:25px;margin-top:5px;"><td colspan="7" style="color:#fff;font-weight:bold;font-size:22px;line-height:25px;font-variant:small-caps;background:transparent url(img/building_'.$b['id'].'.png) top right no-repeat;height:25px;margin-top:5px;">'.$b['name'].'</td></tr>';
	}
	if ( is_array($pro) && !in_array($b['id'], $pro) ) {
		print '<tr id="b'.$b['id'].'" style="background:rgba('.$c.','.(!in_array($b['id'],$tbuildings) ? '.25' : '.5').');" class="bau-building'.($l == 0 ? ' superparent' : ($l == 1 ? ' p'.$p1 : ($l == 2 ? ' p'.$p1.' p'.$p2 : ' p'.$p1.' p'.$p2.' p'.$p3))).( in_array($b['id'],$tbuildings) ? ' built' : '' ).( in_array($b['id'],$blueprints) || in_array($b['id'],$tbuildings) || $b['bp'] == 0 ? ' blueprint' : ' noblueprint' ).'"><td>';
		if ( !in_array($b['id'],$tbuildings) ) {
			if ( in_array($b['id'],$blueprints) || $b['bp'] == 0 ) {
				print '<input id="c'.$b['id'].'" name="build[]" type="checkbox" value="'.$b['id'].'" />';
			}
			else {
				print '<img title="Noch keine Blaupause vorhanden" src="img/item_bplan_qm.gif" />';
			}
			if ( $data['town']['hard'] == 1 ) {
				print '</td><td>';
				print '<img class="clickable" src="css/img/check.png" title="'.t('MARK_AS_ALREADY_BUILT').'" onclick="buildBuilding('.$b['id'].');">';
			}
		}
		else {
			print '<img src="css/img/check.png" title="'.t('ALREADY_BUILT').'">';
			if ( $data['town']['hard'] == 1 ) {
				print '</td><td>';
			}
		}
		print '</td><td>';
		if ( $l > 0 ) {
			print str_repeat('<span style="display:inline-block;width:16px;height:16px;">&nbsp;</span>&nbsp;',$l - 1);
			print '<img src="'.$data['system']['icon_url'].'small_parent.gif" />&nbsp;';
		}
		print '<img src="'.$data['system']['icon_url'].$b['img'].'.gif" />&nbsp;<abbr title=\''.$b['desc'].'\'>'.$b['name'].'</abbr>'.($b['temp'] ? '&nbsp;<img src="'.$data['system']['icon_url'].'small_warning.gif" title="'.t('TEMPORARY_BUILDING').'" />' : '').'</td><td class="tr">'.($b['vp'] > 0 ? $b['vp'].' <img src="http://www.die2nite.com/gfx/icons/small_def.gif" />' : '').'</td><td class="tr">';
		if ( !in_array($b['id'],$tbuildings) && (in_array($b['id'],$blueprints) || $b['bp'] == 0) ) {
			print $b['ap'].'<img src="http://data.dieverdammten.de/gfx/loc/de/small_pa.gif" />';
		}
		print '</td><td class="building-blueprint">';
		if ( !in_array($b['id'],$tbuildings) ) {
			switch ($b['bp']) {
				case NULL:
				{
					print '???';
					break;
				}
				case 0:
				{
					print '<img src="img/item_bplan_no.gif" />';
					break;
				}
				case 1:
				{
					print '<img src="'.t('GAMESERVER_ITEM').'bplan_c.gif" />';
					break;
				}
				case 2:
				{
					print '<img src="'.t('GAMESERVER_ITEM').'bplan_u.gif" />';
					break;
				}
				case 3:
				{
					print '<img src="'.t('GAMESERVER_ITEM').'bplan_r.gif" />';
					break;
				}
				case 4:
				{
					print '<img src="'.t('GAMESERVER_ITEM').'bplan_e.gif" />';
					break;
				}
			}
			if ( $b['bp'] > 0 && !in_array($b['id'],$blueprints) ) {
				print '<img class="clickable" src="css/img/invite-decline.png" title="'.t('MARK_AS_BLUEPRINT_FOUND').'" onclick="findBlueprint('.$b['id'].');">';
			}
			elseif ( $b['bp'] > 0 && in_array($b['id'],$blueprints) ) {
				print '<img class="clickable" src="css/img/invite-accept.png" title="'.t('MARK_AS_BLUEPRINT_LOSE').'" onclick="loseBlueprint('.$b['id'].');">';
			}
		}
		print '</td><td class="building-rsc">';
		if ( !in_array($b['id'],$tbuildings) && (in_array($b['id'],$blueprints) || $b['bp'] == 0) && is_array($b['rsc']) ) {
			foreach ( $b['rsc'] AS $rid => $rcn ) {
				print '<img src="'.$data['system']['icon_url'].'item_'.$rsc_items[$rid]['img'].'.gif" title="'.$rsc_items[$rid]['name'].'">x'.$rcn.'&nbsp;&nbsp;';
			}
		}
		print '</td>';
		print '</tr>';
		$pro[] = $b['id'];
		if ( isset($parrel[$b['id']]) && is_array($parrel[$b['id']]) ) {
			foreach ( $parrel[$b['id']] AS $s ) {
				render_building($buildings[$s], &$pro, $l + 1, $b['id'], $p1, $p2, $c);
			}
		}
	}
}

#print var_export($rsc_items);