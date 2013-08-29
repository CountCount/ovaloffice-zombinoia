<?php
include_once 'system.php';
$db = new Database();

// get version number
$v = (int) $_POST['v'];
// get process number
$p = (int) $_POST['p'];
if ( $p == 2 ) {
	$u = (int) $_POST['u'];
}
elseif ( $p == 1 ) {
	$k = htmlspecialchars(strip_tags($_POST['k']));
	$n = htmlspecialchars(strip_tags($_POST['n']));
}
else {
	// no data send -> start
	print '<script type="text/javascript">
			$("#link_finanzamt").remove();
			$("#finanzamt").remove();
		</script>';
	exit;
}


$session = $db->query(' SELECT xml FROM dvoo_rawdata WHERE id = '.$u.' ORDER BY time DESC LIMIT 1 ');

#var_dump($session[0]['xml']);

if ( $data = unserialize($session[0][0]) ) {
	$bank = array();
	$catnames = array(
		'Rsc' => t('Rsc'),
		'Food' => t('Food'),
		'Armor' => t('Armor'),
		'Drug' => t('Drug'),
		'Weapon' => t('Weapon'),
		'Misc' => t('Misc'),
		'Furniture' => t('Furniture'),
		'Box' => t('Box'),
	);
	$catorder = array(
		'Rsc' => 0,
		'Food' => 5,
		'Armor' => 4,
		'Drug' => 3,
		'Weapon' => 6,
		'Misc' => 7,
		'Furniture' => 2,
		'Box' => 1
	);
	$ordercat = array_flip($catorder);
	ksort($ordercat);

	$bis = $db->query(' SELECT i.iid AS id, i.iimg AS img, i.iname AS name, i.icat AS cat, b.icount AS anzahl, b.ibroken AS kaputt, y.icount AS gestern FROM dvoo_items i INNER JOIN dvoo_bankitems b ON i.iid = b.iid AND b.cday = '.$data['current_day'].' LEFT JOIN dvoo_bankitems y ON y.tid = b.tid AND y.iid = i.iid AND y.cday = '.($data['current_day'] - 1).' WHERE b.tid = '.$data['town']['id'].' ORDER BY b.ibroken ASC, b.icount DESC ');
	
	$bih = $db->query(' SELECT DISTINCT(i.iid) AS id, i.iimg AS img, i.iname AS name, b.ibroken AS kaputt FROM dvoo_items i INNER JOIN dvoo_bankitems b ON i.iid = b.iid AND b.cday < '.$data['current_day'].' LEFT JOIN dvoo_bankitems y ON y.tid = b.tid AND y.iid = i.iid AND y.cday = '.$data['current_day'].' WHERE b.tid = '.$data['town']['id'].' AND y.iid IS NULL ORDER BY b.ibroken ASC ');

	foreach ( $bis AS $bi ) {
		$bank[$bi['cat']][$bi['id'].'.'.$bi['kaputt']] = array('name' => $bi['name'], 'img' => $bi['img'], 'kaputt' => $bi['kaputt'], 'anzahl' => $bi['anzahl'], 'gestern' => $bi['gestern']);
	}
	
if ( (date('z') >= 111 && date('z') <= 114) ) {
	if ( mt_rand(1,1000) > 900 ) {
		print '<div class="easteregg ee'.mt_rand(10000,99999).' egg'.mt_rand(1,4).' rot'.mt_rand(1,5).'" style="top:'.mt_rand(0,900).'px;left:'.mt_rand(0,900).'px;opacity:'.(mt_rand(2,8)/10).';"></div>';
	}
}
	
	print '<div style="clear:right;float:right;"><h4>'.t('GRAPH_SETTINGS').'</h4>
<div>'.t('GRAPH_TYPE').' <select id="gtype"><option value="1">'.t('GRAPH_BARS').'</option><option value="2">'.t('GRAPH_LINES').'</option></select></div><div id="bankgraph"></div></div>';
	
	print '<h3 class="bank_header">'.t('BANK_CONTENT').'</h3><p><em>'.t('BANK_CHANGE_MSG', array('%s' => $ytime.' Uhr')).'</em></p><div id="bank_vault">';
	print '<div id="bank_content">';
	foreach ( $ordercat AS $cname ) {//$bank AS $cname => $cat ) {
		$cat = $bank[$cname];	
		print '<h4 class="bank_cat" onclick="bgraph(\''.$cname.'\');">'.$catnames[$cname].'</h4>';
		if ( $cname == 'Armor') { $item_class = ' deff'; } else { $item_class = ''; }
		if ( is_array($cat) ) {
			foreach ( $cat AS $iid => $item ) {
				print '<div onclick="bgraph('.$iid.');" class="item'.($item['kaputt'] ? ' broken' : '').$item_class.'"><img alt="'.$item['name'].($item['kaputt'] ? ' ('.t('BROKEN').')' : '').'" title="'.$item['name'].($item['kaputt'] ? ' ('.t('BROKEN').')' : '').'" src="'.$data['system']['icon_url'].'item_'.$item['img'].'.gif" />&nbsp;'.$item['anzahl'].(
				!is_null($item['gestern']) ? ' '.(($item['anzahl'] > $item['gestern']) ? '<span class="plus">(+'.($item['anzahl'] - $item['gestern']).')</span>' : (($item['anzahl'] < $item['gestern']) ? '<span class="minus">(-'.($item['gestern'] - $item['anzahl']).')</span>' : ''/*'<span class="nochange">(±0)</span>'*/) ) : ' '.'<span class="plus">(+'.($item['anzahl']).')</span>'
				).'</div>';
			}
		}
	}
	print '<br style="clear:left;" /></div>';
	print '<div id="bank_history">'.t('BANK_HISTORY_MSG').'<br/>';
	foreach ( $bih AS $bh ) {
		print '<div class="item history'.($bh['kaputt'] ? ' broken' : '').'"><img onclick="bgraph('.$bh['id'].');" src="'.$data['system']['icon_url'].'item_'.$bh['img'].'.gif" title="'.$bh['name'].'" /></div>';
	}
	print '<br style="clear:both;"/></div>';
	
	print '</div>';
	print '<script type="text/javascript">
	function bgraph(c) {
		// load stat content
		$("#bankgraph").html("<img src=\"bank.dayz.ajax.php?c=" + c + "&t='.$data['town']['id'].'&cb='.date("Ymd",time()).'&g=" + $("#gtype").val() + "&d='.$data['current_day'].'\" />");
	}
</script>';
}
else {
	print '<div class="error">'.t('ERROR_CODE').' [03]: '.t('ERROR_XML_DEFAULT').'</div>';
}