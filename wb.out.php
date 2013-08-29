<?php

foreach ($notes AS $note) {
	print '<div class="fb_post'.($note['id'] == 51136 ? ' admin' : '').($note['id'] == 2 ? ' dv_admin' : '').'">';
	if ( !is_null($note['name']) && trim($note['name']) != '' ) {
		print '<p class="fb_name">' . $note['name'] . ($note['oldnames'] != '' ? ' <span style="font-size:.825em;">(formerly known as '.substr($note['oldnames'],2).')</span>' : '') . '</p>';
	}
	print '<p class="fb_time">' . date('d M Y, H:i',$note['time']) . '</p>';
	print '<p class="fb_text">' . stripslashes($note['feedback']) . '</p>';
	print '</div>';
}