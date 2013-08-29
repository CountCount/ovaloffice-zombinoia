<?php
include_once 'system.php';
$db = new Database();

// get key (ajax)
$u = (int) $_POST['u'];
#$p = (int) $_POST['p'];
$id = $_POST['id'];
$cat = (string) $_POST['cat'];
$i = strtolower((string) $_POST['item']);

$session = $db->query(' SELECT xml FROM dvoo_rawdata WHERE id = '.$u.' ORDER BY time DESC LIMIT 1 ');
$data = unserialize($session[0][0]);

// Kategoriesuche
if ( isset($cat) && strlen($cat) > 1 ) {
	$q = ' SELECT * FROM dvoo_items WHERE icat = "'.$cat.'" ';
	$r = $db->query($q);
	$script = '';
	$tac = '';
	print '<a href="javascript:void(0);" onclick="toggleResultView();" title="'.t('MAP_SEARCH_TOGGLE_INFO').'" style="text-decoration: none;">'.t('MAP_SEARCH_TOGGLE_VIEW').'</a><div id="result-plain" class="result-view">';
	foreach ( $r AS $item) {
		$s = ' SELECT z1.day, z1.x, z1.y, z1.stamp, z1.info 
	FROM dvoo_zones z1
	LEFT JOIN dvoo_zones z2 ON z1.tid = z2.tid
	AND z1.x = z2.x
	AND z1.y = z2.y
	AND z2.nvt = 0
	AND z1.stamp < z2.stamp
	AND z2.dried IS NOT NULL
	WHERE z1.tid = ' . $data['town']['id'] . '
	AND z1.nvt = 0
	AND z2.stamp IS NULL 
	AND z1.info LIKE  "%{s:2:\"id\";i:' . $item['iid'] . ';%" 
	AND z1.dried IS NOT NULL
	ORDER BY z1.stamp DESC ';
		$t = $db->query($s);

		if ( count($t) > 0 ) {
			print t('MAP_SEARCH_ITEM_FOUND', array('%s' => '<strong>'.$item['iname'].'</strong>')).'<ul>';
			$tac .= t('MAP_SEARCH_ITEM_FOUND', array('%s' => '[g]'.$item['iname'].'[/g]'))."\n";
			foreach ( $t AS $z ) {
				$info = unserialize($z['info']);
				$items = $info['items'];
				foreach ( $items AS $it ) {
					if ( $it['id'] == $item['iid'] ) {
						$icount = $it['count'];
						break;
					}
				}
				$stamp = explode(' ',$z['stamp']);
				$time = substr($stamp[1],0,5);
				print '<li><strong>['.($z['x'] - $data['town']['x']).'|'.($data['town']['y'] - $z['y']).']</strong> '.($icount ? $icount.'x '.t('EXISTENT') : '').($it['broken'] ? ' <span class="broken">('.t('BROKEN').')</span>' : '').'. <em>'.t('ZONE_INFO_ASOF').' '.($z['day'] == $data['current_day'] ? $time.t('TIME_APPENDIX') : t('DAY').' '.$z['day']).'.</em></li>';
				$scr[] = '#s2_x'.$z['x'].'-y'.$z['y'];
				$tac .= ':*: [g]['.($z['x'] - $data['town']['x']).'|'.($data['town']['y'] - $z['y']).'][/g]('.get_dir_abbr($z['x'],$z['y'],$data['town']['x'],$data['town']['y']).') '.($icount ? $icount.'x' : '').($it['broken'] ? ' [bad]('.t('BROKEN').')[/bad]' : '').' [i]'.t('ZONE_INFO_ASOF').' '.($z['day'] == $data['current_day'] ? $time.t('TIME_APPENDIX') : t('DAY').' '.$z['day']).'[/i]'."\n";
			}
			print '</ul>';
			$script .= '<script type="text/javascript">';
			$script .= '$(".zone-searching").removeClass("zone-search-result");$("#zone-search-item").focus().select();';
			$script .= '$.each(["'.implode('", "', $scr).'"], function(index, value) { $(value).addClass("zone-search-result"); });';
			$script .= '</script>';
			$tac .= "\n";
		}
	}
	print '</div><div id="result-forum" class="result-view hideme"><textarea id="result-forum-tac">'.$tac.'</textarea></div><script type="text/javascript">';
	print 'function toggleResultView() { $(".result-view").toggle(); } ';
	print '</script>';
	print $script;
}
// Itemsuche (via ID)
elseif ( is_numeric($id) && $id > 0 ) {
	$q = ' SELECT * FROM dvoo_items WHERE iid = '.$id.' ';
	$r = $db->query($q);
	$item = $r[0];
	
	$q = ' SELECT z1.day, z1.x, z1.y, z1.`on`, z1.items 
FROM dvoo_zones_visit z1
LEFT JOIN dvoo_zones_visit z2 ON z1.tid = z2.tid
AND z1.x = z2.x
AND z1.y = z2.y
AND z1.`on` < z2.`on`
AND z2.dried IS NOT NULL
WHERE z1.tid = ' . $data['town']['id'] . '
AND z2.`on` IS NULL 
AND z1.items LIKE  "%{s:2:\"id\";i:' . $item['iid'] . ';%" 
AND z1.dried IS NOT NULL
ORDER BY z1.`on` DESC ';
	$r = $db->query($q);
	
	$tac = '';
	
	if ( count($r) == 0 ) {
		print t('MAP_SEARCH_ITEM_NOT_FOUND',array('%s' => '<strong>'.$item['name'].'</strong>'));
		print '<script type="text/javascript">';
		print '$(".zone-searching").removeClass("zone-search-result");$("#zone-search-item").focus().select();';
		print '</script>';
	}
	else {
		print '<a href="javascript:void(0);" onclick="toggleResultView();" title="'.t('MAP_SEARCH_TOGGLE_INFO').'" style="text-decoration: none;">'.t('MAP_SEARCH_TOGGLE_VIEW').'</a><div id="result-plain" class="result-view">';
		print t('MAP_SEARCH_ITEM_FOUND', array('%s' => '<strong>'.$item['iname'].'</strong>')).'<ul>';
		$tac .= t('MAP_SEARCH_ITEM_FOUND', array('%s' => '[g]'.$item['iname'].'[/g]'))."\n";
		foreach ( $r AS $z ) {
			$items = unserialize($z['items']);
			
			foreach ( $items AS $it ) {
				if ( $it['id'] == $item['iid'] ) {
					$icount = $it['count'];
					break;
				}
			}
			
			$time = date("H:i",$z['on']);
			print '<li><strong>['.($z['x'] - $data['town']['x']).'|'.($data['town']['y'] - $z['y']).']</strong> '.($icount ? $icount.'x '.t('EXISTENT') : '').($it['broken'] ? ' <span class="broken">('.t('BROKEN').')</span>' : '').'. <em>'.t('ZONE_INFO_ASOF').' '.($z['day'] == $data['current_day'] ? $time.t('TIME_APPENDIX') : t('DAY').' '.$z['day']).'.</em></li>';
			$scr[] = '#s2_x'.$z['x'].'-y'.$z['y'];
			$tac .= ':*: [g]['.($z['x'] - $data['town']['x']).'|'.($data['town']['y'] - $z['y']).'][/g]('.get_dir_abbr($z['x'],$z['y'],$data['town']['x'],$data['town']['y']).') '.($icount ? $icount.'x' : '').($it['broken'] ? ' [bad]('.t('BROKEN').')[/bad]' : '').' [i]'.t('ZONE_INFO_ASOF').' '.($z['day'] == $data['current_day'] ? $time.t('TIME_APPENDIX') : t('DAY').' '.$z['day']).'[/i]'."\n";
		}
		
		print '</ul></div><div id="result-forum" class="result-view hideme"><textarea id="result-forum-tac">'.$tac.'</textarea></div><script type="text/javascript">';
		print 'function toggleResultView() { $(".result-view").toggle(); } ';
		print '$(".zone-searching").removeClass("zone-search-result");$("#zone-search-item").focus().select();';
		print '$.each(["'.implode('", "', $scr).'"], function(index, value) { $(value).addClass("zone-search-result"); });';
		print '</script>';
	}
}
// ItemNAMENssuche
elseif ( strlen($i) > 2 ) {
	$q = ' SELECT * FROM dvoo_items WHERE LOWER(iname) LIKE "%'.$i.'%" ';
	$r = $db->query($q);
	if ( count($r) > 1 ) {
		print '<h5>'.t('MAP_SEARCH_AJAXCOMPLETE').'</h5>';
		foreach ( $r AS $j ) {
			print '<p class="alternative" onclick="$(\'#zone-search-item\').val(\''.$j['iname'].'\');$(\'#zone-search-itemid\').val(\''.$j['iid'].'\');$(\'#zone-search\').submit();"><img src="'.$data['system']['icon_url'].'item_'.$j['iimg'].'.gif" />&nbsp;'.$j['iname'].'?</p>';
		}
	}
	elseif ( count($r) == 1 ) {
		print '<h5>'.t('MAP_SEARCH_RUNNING', array('%s' => '<img src="'.$data['system']['icon_url'].'item_'.$r[0]['iimg'].'.gif" />&nbsp;<em>'.$r[0]['iname'].'</em>')).'</h5><div class="loading"></div>';
		print '<script type="text/javascript">$(\'#zone-search-item\').val(\''.$r[0]['iname'].'\');$(\'#zone-search-itemid\').val(\''.$r[0]['iid'].'\');$(\'#zone-search\').submit();</script>';
	}
	else {
		print t('MAP_SEARCH_NOTHING_FOUND');
	}
}
else {
	print t('MAP_SEARCH_SHORT_TERM');
}

