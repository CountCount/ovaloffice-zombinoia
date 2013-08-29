<?php
include_once 'system.php';
$db = new Database();

// get key (ajax)
$t = (int) $_POST['t'];
$u = (int) $_POST['u'];
$build = $_POST['build'];

$session = $db->query(' SELECT xml FROM dvoo_rawdata WHERE id = '.$u.' ORDER BY time DESC LIMIT 1 ');
$data = unserialize($session[0]['xml']);
$cd = $data['current_day'];

$ab = $db->query(' SELECT b.* FROM dvoo_buildings b ');
$buildings = array();
$parrel = array();
foreach ( $ab AS $b ) {
	$buildings[$b['id']] = array(
		'id' => $b['id'],
		'name' => $b['name'],
		'temp' => $b['temporary'],
		'img' => $b['img'],
		'vp' => $b['vp'],
		'ap' => $b['ap'],
		'rsc' => unserialize($b['rsc']),
	);
	if ( $b['parent'] > 0 ) {
		$parrel[$b['parent']][] = $b['id'];
	}
}
ksort($buildings);
$processed = array();
$superparents = array(
	1033 => '204,255,0', // Werkstatt
	1050 => '153,0,255', // Wachturm
	1062 => '0,255,0', // Portal
	1011 => '0,0,255', // Pumpe
	1051 => '153,51,0', // Fundament
	1010 => '0,204,153' // Stadtmauer
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
$tbl = $db->query(' SELECT b.id FROM dvoo_buildings b INNER JOIN dvoo_town_buildings t ON t.bid = b.id WHERE t.tid = '. $data['town']['id'].' AND day = '.$data['current_day']);
foreach ( $tbl AS $tb ) {
	$tbuildings[] = $tb['id'];
}

$bank = array();
$bis = $db->query(' SELECT i.iid AS id, i.iimg AS img, i.iname AS name, i.icat AS cat, b.icount AS anzahl, b.ibroken AS kaputt FROM dvoo_items i INNER JOIN dvoo_bankitems b ON i.iid = b.iid AND b.cday = '.$data['current_day'].' WHERE b.tid = '.$data['town']['id'].' AND b.ibroken = 0 ');

foreach ( $bis AS $bi ) {
	$bank[$bi['id']] = array(
		'name' => $bi['name'], 
		'img' => $bi['img'], 
		'kaputt' => $bi['kaputt'], 
		'anzahl' => $bi['anzahl']
	);
}

$bcnt = count($build);

$req_ap = 0; // required ap
$blb = ''; // bau-list-building
$req_rsc = array();
$build_list = '';
foreach ( $build AS $bid ) {
	$req_ap += $buildings[$bid]['ap'];
	$gen_vp += $buildings[$bid]['vp'];
	$blb .= '<li><img src="'.$data['system']['icon_url'].$buildings[$bid]['img'].'.gif" />&nbsp;'.$buildings[$bid]['name'].'</li>';
	$blb_copy .= ':fleche: '.$buildings[$bid]['name']."\n";
	foreach ( $buildings[$bid]['rsc'] AS $rsci => $rscc ) {
		if ( !isset($req_rsc[$rsci]) ) {
			$req_rsc[$rsci] = $rscc;
		}
		else {
			$req_rsc[$rsci] += $rscc;
		}
	}
	$build_list .= ','.$bid;
}
$missing = array();
$out = '';
$copy = '';
$outl = '';
$copyl = '';
$spent_deff = 0;



foreach ( $req_rsc AS $rri => $rrc ) {
	$outl .= '<li><img src="'.$data['system']['icon_url'].'item_'.$rsc_items[$rri]['img'].'.gif" title="'.$rsc_items[$rri]['name'].'" /> x '.$rrc;
	$copyl .= ':*: '.$rsc_items[$rri]['name'].' x'.$rrc;
	if ( $rrc > $bank[$rri]['anzahl']) {
		$diff = $rrc - $bank[$rri]['anzahl'];
		$outl .= ' <span class="minus">('.t('ITEMS_MISSING', array('%d' => $diff, '%s' => array($diff, t('MISSES'), t('MISS')))).')</span>';
		$copyl .= ' [i][bad]('.t('ITEMS_MISSING', array('%d' => $diff, '%s' => array($diff, t('MISSES'), t('MISS')))).')[/bad][/i]';
		$missing[$rri] = $diff;
	}
	else {
		$diff = $bank[$rri]['anzahl'] - $rrc;
		$outl .= ' <span class="plus">('.t('ITEMS_REMAINING', array('%d' => $diff, '%s' => array($diff, t('REMAINS'), t('REMAIN')))).')</span>';
		$copyl .= ' [i]('.t('ITEMS_REMAINING', array('%d' => $diff, '%s' => array($diff, t('REMAINS'), t('REMAIN')))).')[/i]';
	}
	$outl .= '</li>';
	$copyl .= "\n\n";
	
	//deff
	if ( $rsc_items[$rri]['cat'] == 'Armor') {
		$spent_deff += $rrc;
	}
}
$outl .= '</ul>';

// output & copy
$out .= '<h4>'.t('CONSTRUCTION_PERMIT_REQUESTED', array('%d' => $bcnt)).'</h4>';
$out .= '<ul class="bau-list bau-list-buildings">';
$out .= $blb;
$out .= '</ul>';
if ( $gen_vp > 0 ) {
	$out .= '<p>'.t('VP_GAIN', array('%s' => '<strong>'.$gen_vp.'</strong>'));
	if ( $spent_deff > 0 ) { $out .= ' <em>('.t('VP_USE', array('%d' => $spent_deff, '%s' => array(1, t('WILLS'), t('WILL')), '%t' => array(1, t('VP_ITEM'), t('VP_ITEMS')))).')</em>'; }
	$out .= '</p>';
}
$out .= '<p>'.t('REQUIRED_AP_BUILD').': <strong>'.$req_ap.'</strong></p>';
$out .= '<p>'.t('REQUIRED_RESOURCES').':</p><ul class="bau-list bau-list-rsc">';
$out .= $outl;

$copy .= '[rp][i][g]+++ '.t('CONSTRUCTION_PERMIT').' +++[/g][/i]'."\n".t('REQUESTED_BY', array('%s' => $data['user']['name'])).' [bad]- '.t('STATUS_QUO').': '.t('DAY').' '.$data['current_day'].', '.date('H:i',time()).t('TIME_APPENDIX').'[/bad]'."\n\n".t('CONSTRUCTIONS_PERMITTED', array('%d' => $bcnt, '%s' => array(1, t('HAS_BEEN'), t('HAVE_BEEN'))))."\n".$blb_copy."\n\n";
if ( $gen_vp > 0 ) {
	$copy .= t('VP_GAIN', array('%s' => '[g]'.$gen_vp.'[/g]'));
	if ( $spent_deff > 0 ) { $copy .= ' [i]('.t('VP_USE', array('%d' => $spent_deff, '%s' => array(1, t('WILLS'), t('WILL')), '%t' => array(1, t('VP_ITEM'), t('VP_ITEMS')))).')[/i]'; }
	$copy .= "\n\n";
}
$copy .= t('REQUIRED_AP_BUILD').': [g]'.$req_ap.'[/g]'."\n\n".t('REQUIRED_RESOURCES')."\n\n";
$copy .= $copyl;

if ( count($missing) > 0 && in_array(1033, $tbuildings)) { // && in_array(1033, $tbuildings)
/* conversions
 * 162 => 59 => 159 | Verrotteter Baumstumpf > Krummes Brett > Holzbalken
 * 161 => 60 => 160 | Metalltrümmer > Alteisen > Metallstruktur
 * 133 + 1 => 134 | Zementsack + Wasser > Unförmige Zementblöcke
 * 96 => 64 | Unverarbeitete Blechplatte > Blechplatte
 * 185 + 41 + 60 + 81 + 132 + 158  => 39 | Unvollständiger Motor + SuM + Alteisen + Klebeband + Zünder + Knochen  > Motor
 * 167 + 81 + 41 => 163 | Beschädigte Metallsäge + Klebeband + SuM > Metallsäge
 */
	
	$conv = array();
	if ( isset($missing[59]) && $bank[162]['anzahl'] > 0 ) {
		$m = $missing[59] > $bank[162]['anzahl'] ? $bank[162]['anzahl'] : $missing[59];
		$conv[] = array(162,59,$m,1);
	}
	if ( isset($missing[60]) && $bank[161]['anzahl'] > 0 ) {
		$m = max(array($missing[60], $bank[161]['anzahl']));
		$m = $missing[60] > $bank[161]['anzahl'] ? $bank[161]['anzahl'] : $missing[60];
		$conv[] = array(161,60,$m,1);
	}
	if ( isset($missing[159]) ) {
		$b = $bank[59]['anzahl'] - $req_rsc[59];
		if ($b > 0) {
			$m = max(array($missing[159], $b));
			$m = $missing[159] > $b ? $b : $missing[159];
			$conv[] = array(59,159,$m,1);
			$missing[159] -= $m;
		}
	}
	if ( isset($missing[159]) && $missing[159] > 0 && $bank[162]['anzahl'] > 0 ) {
		$m = max(array($missing[159], $bank[162]['anzahl']));
		$m = $missing[159] > $bank[162]['anzahl'] ? $bank[162]['anzahl'] : $missing[159];
		$conv[] = array(162,159,$m,2);
	}
	if ( isset($missing[160]) ) {
		$b = $bank[60]['anzahl'] - $req_rsc[60];
		if ($b > 0) {
			$m = max(array($missing[160], $b));
			$m = $missing[160] > $b ? $b : $missing[160];
			$conv[] = array(60,160,$m,1);
			$missing[160] -= $m;
		}
	}
	if ( isset($missing[160]) && $missing[160] > 0 && $bank[161]['anzahl'] > 0 ) {
		$m = max(array($missing[160], $bank[161]['anzahl']));
		$m = $missing[160] > $bank[161]['anzahl'] ? $bank[161]['anzahl'] : $missing[160];
		$conv[] = array(161,160,$m,2);
	}
	if ( isset($missing[64]) && $bank[96]['anzahl'] > 0 ) {
		$m = max(array($missing[64], $bank[96]['anzahl']));
		$m = $missing[64] > $bank[96]['anzahl'] ? $bank[96]['anzahl'] : $missing[64];
		$conv[] = array(96,64,$m,1);
	}
	if ( isset($missing[134]) && $bank[133]['anzahl'] > 0 ) {
		$m = max(array($missing[134], $bank[133]['anzahl']));
		$m = $missing[134] > $bank[133]['anzahl'] ? $bank[133]['anzahl'] : $missing[134];
		$conv[] = array(133,134,$m);
	}
	
	if ( count($conv) > 0 ) {
		$out .= '<p>'.t('WORKSHOP_CONVERSIONS').':</p><ul class="bau-list bau-list-workshop">';
		$copy .= t('WORKSHOP_CONVERSIONS').':'."\n";
		
		$manu = $saeg = $wap = 0;
		// Multiplikator
		$faktor = 3;
		if ( in_array(1065, $tbuildings) ) { 
			$faktor--; 
			$manu = 1;
		}
		if ( $bank[163]['anzahl'] > 0 ) { 
			$faktor--;
			$saeg = 1;
		}
		
		foreach ( $conv AS $con ) {
			$out .= '<li>'.$con[2].'x <img src="'.$data['system']['icon_url'].'item_'.$rsc_items[$con[0]]['img'].'.gif" title="'.$rsc_items[$con[0]]['name'].'" /> ► <img src="'.$data['system']['icon_url'].'item_'.$rsc_items[$con[1]]['img'].'.gif" title="'.$rsc_items[$con[1]]['name'].'" />'.($con[3] > 1 ? ' ['.t('CAUTION').': '.t('REQUIRED_STEPS', array('%d' => $con[3])).']' : '').'</li>';
			$copy .= ':*: '.$con[2].'x '.$rsc_items[$con[0]]['name'].' :fleche: '.$rsc_items[$con[1]]['name'].($con[3] > 1 ? ' [i]([bad]'.t('CAUTION').':[/bad] '.t('REQUIRED_STEPS', array('%d' => $con[3])).'[/i]' : '')."\n";
			$wap += ($con[2] * $con[3]);
		}
		$out .= '</ul>';
		$out .= '<p>'.t('REQUIRED_AP_WORKSHOP').': <strong>'.($wap * $faktor).'</strong>';
		$copy .= t('REQUIRED_AP_WORKSHOP').': [g]'.($wap * $faktor).'[/g]';
		if ( $manu || $saeg ) {
			$out .= ' <em>(';
			$copy .= ' [i](';
		}
		if ( $manu ) {
			$out .= t('FACTORY').' ';
			$copy .= t('FACTORY').' ';
		}
		if ( $manu && $saeg ) {
			$out .= t('AMP').' ';
			$copy .= t('AMP').' ';
		}
		if ( $saeg ) {
			$out .= t('SAW').' ';
			$copy .= t('SAW').' ';
		}
		if ( $manu || $saeg ) {
			$out .= t('EXIST').')</em>';
			$copy .= t('EXIST').')[/i]';
		}
		$out .= '</p>';
	}
}


$copy .= '[/rp]';


print $out;
print '<div><textarea id="dv-forum-version" style="font-size:10px;border:1px solid #666;z-index:10;width:245px;height:245px;" onclick="$(\'#dv-forum-version\').focus().select();">'.$copy.'</textarea><p style="color:#33c;font-size:.875em;"><strong>'.t('NOTE').':</strong> '.t('PERMIT_COPY_NOTICE').'</p></div>';

print '<script type="text/javascript">
$(function() {
	$("#dv-forum-version").focus().select();
});
</script>';
