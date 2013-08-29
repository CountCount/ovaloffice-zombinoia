<?php
	$vt = array(
		0 => t('EMA_T_NONE'),
		1 => t('EMA_T_NOT'),
		2 => t('EMA_T_SPORA'),
		3 => t('EMA_T_ACTIV')
	);
	$p = 0;
	$pd = 0;
	foreach ( $pins AS $pin ) {
		$p++;
		if ( $pd != $pin['day'] ) {
			print '<h4 class="pinsubhead">'.t('DAY').' '.$pin['day'].'</h4>';
			$pd = $pin['day'];
		}
		print '<div class="pin '.($p % 2 == 0 ? 'even' : 'odd').($pin['id'] == $u ? ' own' : '').'">';
		for ( $h = 23; $h >= 0; $h-- ) {
			if ( in_array($h, array(5,11,17,23)) ) {
				print '<div class="t0 tpl sep">'.($h+1).'</div>';
			}
			print '<div class="t'.$pin['tp'.$h].' tpl" title="'.$h.' - '.($h+1).' '.t('HOURS').': '.$vt[$pin['tp'.$h]].'"><div class="hideme tpsymbol tpsymbol'.$pin['tp'.$h].'"></div></div>';
		}
		print '<div class="t0 tpl sep">0</div>';
		
		if ( $pin['coffee'] > 0 ) {
			print '<div class="tpl coffee" title="'.$pin['coffee'].' '.t('EMA_B_COFFEE').'"></div>';
		}
		if ( $pin['drug'] > 0 ) {
			print '<div class="tpl drug8" title="'.$pin['drug'].' '.t('EMA_B_TWINOID').'"></div>';
		}
		if ( $pin['drug2'] > 0 ) {
			print '<div class="tpl drug6" title="'.$pin['drug2'].' '.t('EMA_B_STEROID').'"></div>';
		}
		if ( $pin['alcohol'] > 0 ) {
			print '<div class="tpl alcohol" title="'.$pin['alcohol'].' '.t('EMA_B_ALCOHOL').'"></div>';
		}
		if ( $pin['sleep'] > 0 ) {
			print '<div class="tpl sleep" title="'.$pin['sleep'].' '.t('EMA_B_SIESTA').'"></div>';
		}
		if ( $pin['gamble'] > 0 ) {
			print '<div class="tpl gamble" title="'.$pin['gamble'].' '.t('EMA_B_GAMBLE').'"></div>';
		}
		if ( $pin['alarm'] > 0 ) {
			print '<div class="tpl alarm" title="'.$pin['alarm'].' '.t('EMA_B_ALARM').'"></div>';
		}
		if ( $pin['lunge'] > 0 ) {
			print '<div class="tpl lunge" title="'.$pin['lunge'].' '.t('EMA_B_2NDLUNG').'"></div>';
		}
		if ( $pin['food'] > 0 ) {
			print '<div class="tpl food'.$pin['food'].'" title="'.$pin['food'].' '.t('EMA_B_FOOD').'"></div>';
		}
		if ( $pin['water'] > 0 ) {
			print '<div class="tpl water" title="'.$pin['water'].' '.t('EMA_B_WATER').'"></div>';
		}
		$allap = 6 + $pin['coffee'] + $pin['alarm'] + $pin['sleep'] + $pin['gamble'] + $pin['alcohol'] + $pin['drug'] + $pin['drug2'] + $pin['food'] + $pin['water'] + $pin['lunge'];
		
		$kp = $jobs[$pin['job']]['kp'];
		if ( $pin['topform'] == 1 && $pin['clean'] == 1 ) {
			$kp++;
		}
		if ( $pin['safe'] == 1 ) {
			$kp++;
		}
		if ( $pin['paralyzed'] == 1 ) {
			$kp = 0;
		}

		print '<img src="http://www.dieverdammten.de/gfx/icons/item_'.$jobs[$pin['job']]['img'].'.gif" alt="'.$jobs[$pin['job']]['name'].'" title="'.$jobs[$pin['job']]['name'].'" />';
		print $pin['name'].' '.
			($pin['clean'] ? '<img src="http://www.dieverdammten.de/gfx/icons/status_clean.gif" alt="'.t('EMA_S_CLEAN').'" title="'.t('EMA_S_CLEAN').'" /> ' : '').
			($pin['paralyzed'] ? '<img src="http://www.dieverdammten.de/gfx/icons/status_terror.gif" alt="'.t('EMA_S_TERRORIZED').'" title="'.t('EMA_S_TERRORIZED').'" /> ' : '').
			($pin['hangover'] ? '<img src="http://www.dieverdammten.de/gfx/icons/status_hung_over.gif" alt="'.t('EMA_S_HANGOVER').'" title="'.t('EMA_S_HANGOVER').'" /> ' : '').
			($pin['thirsty'] ? '<img src="http://www.dieverdammten.de/gfx/icons/status_thirst.gif" alt="'.t('EMA_S_THIRSTY').'" title="'.t('EMA_S_THIRSTY').'" /> ' : '').
			'('.$kp.' '.t('CP').($allap > 0 ? ', '.$allap.' '.t('AP') : '').')';
		print'</div>';
	}