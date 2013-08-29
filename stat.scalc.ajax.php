<?php

	include_once 'system.php';
	
	function binom_coef($of, $out) {
		$over = 1;
		if($out == 0) {
			return $over;
		}
		else {
			for($i = 1; $i <= $out; $i++) {
				$over = $over * ( ($of + 1 -$i) / $i);
			}
			return $over;
		}
	}

	function prob_live($zombies, $deff, $bewohner) {
		if($deff < $zombies && $bewohner > 0) {
			$live = 0.0;
			for($i = 0; $i <= $deff; $i++) {
				$live += pow((($bewohner - 1) / $bewohner ),($zombies-$i)) * pow(( 1 / $bewohner), $i) * binom_coef ($zombies , $i);
			}
		}
		else $live = 1.0;
		return $live;
	}
		
	$z = (int) $_POST['z'];
	$d = (int) $_POST['d'];
	$b = (int) $_POST['b'];
	if ( $b > 40 ) {
		$b = 40;
		print '<script type="text/javascript">$("#sc_b").val(40);alert("'.t('OGAME').'");</script>';
	}


print t('YOU_WILL_SURVIVE').' <span style="font-weight:bold;font-size:1.2em;color:#009;">'.(round(prob_live($z, $d, $b),3)*100).'%</span>.';