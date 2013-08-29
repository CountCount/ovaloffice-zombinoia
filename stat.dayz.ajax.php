<?php
//Set content-type header
    header("Content-type: image/png");

    include_once 'lang.inc.php';

    //Include phpMyGraph5.0.php
    include_once('pmg5.php');
    include_once 'dal2.php';
		
		$db = new Database();
    
		// get day number
		$d = (int) $_GET['d'];
		$h = (int) $_GET['h'];
		$s = (int) $_GET['s'];
		$cols = (int) $_GET['c'];
		$g = (int) $_GET['g'];

		$data = array();
		$cata = array();
		$q = ' SELECT s.z, COUNT(s.z) AS c FROM dvoo_stat_zombies s INNER JOIN dvoo_towns t ON t.id = s.tid AND t.hard = 0 WHERE s.day = '.$d.' AND (t.stamp - ((t.day - 1) * 86400)) '.($s == 3 ? '>' : '<').' 1311262200 GROUP BY z ORDER BY z ASC ';
		$res = $db->query($q);
		foreach ( $res AS $r ) {
			$cata[(string) $r['z']] = (int) $r['c'];
		}
		if ( count($cata) <= $cols ) {
			$data = $cata;
		}
		else {
			$min = min(array_keys($cata));
			$max = max(array_keys($cata));
			$ks = floor(($max - $min + 1) / $cols);
			for ( $k = 0; $k < ($cols - 1); $k++ ) {
				$keys[$k] = ($min + ($ks * $k)) . ' - ' . ($min + (($k + 1) * $ks - 1));
			}
			$keys[($cols-1)] = ($min + ($ks * ($cols-1))) . ' - ' . $max;
			foreach ( $keys AS $key ) {
				$data[$key] = 0;
			}
			foreach ( $cata AS $z => $c ) {
				$e = $z - $min;
				$kh = floor($e / $ks);
				if ( $kh > ($cols-1) ) { $kh = $cols - 1; }
				$data[$keys[$kh]] += $c;
			}
		}

		//Set config directives
    $cfg = array(
			'title' => t('ZOMBIE_SPREAD', array('%d' => $d, '%s' => $s)),
			'width' => 320,
			'height' => 240,
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
		
/*
//Set content-type header
header("Content-type: image/png");
		
include_once 'system.php';
$db = new Database();

include_once 'pmg5.php';

// get day number
$d = (int) $_GET['d'];

$data = array();
$res = $db->query(' SELECT z, COUNT(z) AS c FROM dvoo_stat_zombies WHERE day = '.$d.' GROUP BY z ORDER BY z ASC ');
foreach ( $res AS $r ) {
	$data[(string) $r['z']] = (int) $r['c'];
}
    #var_dump($data);
    
    //Set config directives
    $cfg['title'] = 'Zombieverteilung';
    $cfg['width'] = 400;
    $cfg['height'] = 250;
		
		//Create phpMyGraph instance
    $graph = new phpMyGraph();

    //Parse
    $graph->parseVerticalLineGraph($data, $cfg);
*/