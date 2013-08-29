<?php
//Set content-type header
    header("Content-type: image/png");

    //Include phpMyGraph5.0.php
    include_once 'pmg5.php';
    include_once 'dal2.php';
		include_once 'lang.inc.php';
		
		$db = new Database();
    
		// get day number
		$c = $_GET['c']; // item/cat
		$t = (int) $_GET['t']; // town
		$g = (int) $_GET['g']; // graph type
		$d = (int) $_GET['d']; // current day
		
		if ( !is_numeric($c) ) {
			//cat search
			$res = $db->query(' SELECT b.cday AS "day", SUM(b.icount) AS "sum", i.icat AS "name" FROM dvoo_bankitems b INNER JOIN dvoo_items i ON i.iid = b.iid WHERE i.icat = "'.$c.'" AND tid = '.$t.' GROUP BY b.cday ');
		}
		else {
				$res = $db->query(' SELECT b.cday AS "day", SUM(b.icount) AS "sum", i.iname AS "name" FROM dvoo_bankitems b INNER JOIN dvoo_items i ON i.iid = b.iid WHERE i.iid = '.$c.' AND tid = '.$t.' GROUP BY b.cday ');
		}

		$data = array();
		$cata = array();
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
		
		foreach ( $res AS $r ) {
			$cata[$r['day']] = (int) $r['sum'];
			$name = $r['name'];
		}
		
		$days = max(array_keys($cata));
		if ( $days < $d ) { $days = $d; }
		for ( $i = 1; $i <= $days; $i++ ) {
			if ( !isset($cata[$i]) ) {
				$cata[$i] = 0;
			}
		}
		ksort($cata);
		
		foreach ( $cata AS $j => $k ) {
			$data[t('DAY').' '.$j] = $k;
		}

		//Set config directives
    $cfg = array(
			'title' => t('GRAPH_BANK_TITLE') . ' - ' . (!is_numeric($c) ? t('CATEGORY') . ' ' . $catnames[$c] : t('ITEM') . ' ' . $name),
			'width' => 450,
			'height' => 250,
			'average-line-visible' => 0,
			'column-divider-visible' => 0,
		);
    
    //Create phpMyGraph instance
    $graph = new phpMyGraph();

    //Parse
    if ( $g == 2 ) {
			$graph->parseVerticalLineGraph($data, $cfg);
		}
		elseif ( $g == 1 ) {
			$graph->parseVerticalColumnGraph($data, $cfg);
		}